<?php
/**
 * Plugin Name: WooCommerce Urb-it Shipping
 * Plugin URI: http://urb-it.com/
 * Description: Let your customers choose urb-it as shipping method.
 * Version: 3.0.3
 * Author: Webbmekanikern
 * Author URI: http://www.webbmekanikern.se/
 * Text Domain: woocommerce-urb-it
 * Domain Path: /languages/
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('WOOCOMMERCE_URB_IT_PLUGIN_ROOT', __DIR__);

class WooCommerce_Urb_It
{
    const VERSION = '3.0.3';
    const LANG    = 'woocommerce-urb-it';

    const COMPANY_URL = 'https://urb-it.com/';
    const UPDATE_URL  = 'https://download.urb-it.com/woocommerce/woocommerce-urb-it/update.json';

    const ORDER_MAX_WEIGHT = 10; // kg
    const ORDER_MAX_VOLUME = 142000; // cm3 (1 liter = 1000 cm3)

    const OPTION_VERSION               = 'wc-urb-it-version';
    const OPTION_GENERAL               = 'wc-urb-it-general';
    const OPTION_CREDENTIALS           = 'wc-urb-it-credentials';
    const OPTION_OPENING_HOURS         = 'wc-urb-it-opening-hours';
    const OPTION_SPECIAL_OPENING_HOURS = 'wc-urb-it-special-opening-hours';

    const TRANSIENT_TTL       = 60; // seconds
    const SPECIFIC_TIME_RANGE = '+4 days'; // Actually 3 days: today + 2 days forward

    const SETTINGS_PREFIX = 'urb_it_settings_';

    const DATE_FORMAT = DateTime::ATOM;

    const STD_PROCESS_TIME = 'PT1H30M'; // in DateInterval format
    const DEFAULT_PREPARE_TIME = '30'; // in minutes
    const SPECIFIC_TIME_ADD = 'PT15M'; // in minutes

    protected static $_instance;

    protected $timezone;

    protected $log;

    protected $initialized = false;

    protected $environment = 'stage';

    protected static $_modules = array();

    protected $update_checker;

    protected $path = __DIR__ . "/";

    protected $country_codes = array(
        '46',  // Sweden
        '33',  // France
    );

    protected $mobile_prefixes = array(
        '07', // Generic
        '06'  // France
    );

    /**
     * Singleton
     *
     * @return mixed
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance =
                include_once WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-' . (is_admin() ? 'admin' : 'frontend')
                    . '.php';

            self::$_modules = array(
                'order'         => include_once(WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-order.php'),
                'validate'      => include_once(WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-validate.php'),
                'opening_hours' => include_once(WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-opening-hours.php'),
                'coupon'        => include_once(WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-coupon.php'),
            );
        }

        return self::$_instance;
    }

    function __construct()
    {
        register_activation_hook(__FILE__, array(__CLASS__, 'install'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        require_once $this->path . 'includes/plugin-update-checker/plugin-update-checker.php';

        $this->update_checker = PucFactory::buildUpdateChecker(self::UPDATE_URL, __FILE__);
        $this->environment = get_option(self::SETTINGS_PREFIX . 'environment');

        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('woocommerce_shipping_init', array($this, 'shipping_init'));
        add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_method'));
        add_action('widgets_init', array($this, 'register_widget'));
        // add_filter('woocommerce_order_button_html', array($this, 'woocommerce_order_confirm'), 99);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    function __get($name)
    {
        if ($name === 'path') {
            $this->{$name} = WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/';
        } elseif ($name === 'url') {
            $this->{$name} = plugin_dir_url(__FILE__);
        } elseif ($name === 'urbit') {
            try {
                if (!class_exists('UrbRequest')) {
                    require_once WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/sdk/UrbRequest.php';
                }

                $this->{$name} = new UrbRequest(
                    $this->setting('x_api_key'),
                    $this->setting('bearer_token'),
                    $this->environment === 'stage'
                );
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        } elseif (isset(self::$_modules[$name])) {
            $this->{$name} = self::$_modules[$name];
        }

        return $this->{$name};
    }

    /*function woocommerce_order_confirm($input_submit)
    {
        $order_button_text = explode('"', explode('value=', $input_submit)[1])[1];
        $confirm_button = '<div id="order_confirmation" class="button alt">' . esc_attr($order_button_text) . '</div>';

        ob_start();
        $this->template('checkout/confirm_dialog');
        $dialog = ob_get_clean();

        if (get_option(self::SETTINGS_PREFIX . 'order_confirmation') === 'yes') {
            return $dialog . $confirm_button;
        }

        return $input_submit;
    }*/

    /**
     * @param string $name
     * @param bool $raw
     *
     * @return mixed
     */
    function setting($name, $raw = false)
    {
        if (!$raw && in_array($name, array('x_api_key', 'bearer_token'), true))
            $name = $this->environment . '_' . $name;

        return get_option(self::SETTINGS_PREFIX . $name);
    }

    /**
     * @return mixed
     */
    function now_offset()
    {
        return get_option(self::SETTINGS_PREFIX . 'now_validation_time');
    }

    function create_date($minutes)
    {
        $now = new DateTime();

        if (empty($minutes)) {
            $minutes = self::DEFAULT_PREPARE_TIME;
            $this->log('Preparation time is not set by admin. Using default value instead: ' . $minutes);
        }

        $delivery_time = $now->add(new DateInterval('PT' . $minutes . 'M'));

        return $delivery_time->add(new DateInterval(self::STD_PROCESS_TIME));
    }

    /**
     * Save the plugin version on activation, if it doesn't exist
     */
    public static function install()
    {
        add_option(self::OPTION_VERSION, self::VERSION);
    }

    /**
     * Delete all options when the plugin is removed
     */
    public static function uninstall()
    {
        delete_option(self::OPTION_VERSION);
    }

    /**
     * Add multilingual support
     */
    function load_textdomain()
    {
        load_plugin_textdomain(self::LANG, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Include shipping classes
     */
    function shipping_init()
    {
        require_once $this->path . 'includes/class-shipping-one-hour.php';
        require_once $this->path . 'includes/class-shipping-specific-time.php';
    }

    /**
     * Define shipping classes
     *
     * @param $methods
     *
     * @return array
     */
    function add_shipping_method($methods)
    {
        $methods[] = 'WC_Urb_It_One_Hour';
        $methods[] = 'WC_Urb_It_Specific_Time';

        return $methods;
    }

    /**
     * Register postcode validation widget
     */
    function register_widget()
    {
        register_widget('Urb_It_Postcode_Validator_Widget');
    }

    /**
     * Create a DateTime object with the correct timezone
     *
     * @param $string
     *
     * @return DateTime
     */
    function create_datetime($string)
    {
        if ($this->timezone === null) {
            $timezone_string = get_option('timezone_string');

            $this->timezone = new DateTimeZone($timezone_string ? $timezone_string : 'Europe/Stockholm');
        }

        return new DateTime($string, $this->timezone);
    }

    /**
     * @param string $string
     *
     * @return DateTime
     */
    function date($string)
    {
        if ($this->timezone === null) {
            $timezone_string = get_option('timezone_string');

            if (!$timezone_string) {
                $gmt_offset = get_option('gmt_offset');

                if (!is_numeric($gmt_offset)) {
                    $gmt_offset = 0;
                }

                $timezone_string = 'Etc/GMT' . ($gmt_offset >= 0 ? '+' : '') . (string) $gmt_offset;
            }

            $this->timezone = new DateTimeZone($timezone_string);
        }

        return new DateTime($string, $this->timezone);
    }

    /**
     * Include template file
     *
     * @param string $path
     * @param array $vars
     */
    function template($path, $vars = array())
    {
        $path = wc_locate_template($path . '.php', 'urb-it', $this->path . 'templates/');

        extract($vars);

        include $path;
    }

    /**
     * @param string|float $price
     *
     * @return string
     */
    function format_price($price)
    {
        $decimal_separator = wc_get_price_decimal_separator();
        $thousand_separator = wc_get_price_thousand_separator();
        $decimals = wc_get_price_decimals();
        $price_format = get_woocommerce_price_format();

        $price = apply_filters(
            'formatted_woocommerce_price',
            number_format($price, $decimals, $decimal_separator, $thousand_separator),
            $price,
            $decimals,
            $decimal_separator,
            $thousand_separator
        );

        if ($decimals > 0 && apply_filters('woocommerce_price_trim_zeros', false)) {
            $price = wc_trim_zeros($price);
        }

        return html_entity_decode(sprintf($price_format, get_woocommerce_currency_symbol(), $price));
    }

    /**
     * @param string $handle
     * @param string $src
     * @param array $depend
     */
    function add_asset($handle, $src, $depend = array())
    {
        if (substr($src, -3) === '.js') {
            wp_enqueue_script($handle, $this->url . 'assets/js/' . $src, $depend, self::VERSION, true);
        } else {
            echo '<style>';

            include "{$this->path}assets/css/{$src}";

            echo '</style>';
        }
    }

     /**
     * Sanitize phone number to the format "0701234567"
     *
     * @param string $phone
     *
     * @return string
     */
    function sanitize_phone($phone)
    {
        $phone = preg_replace(array('/\D/', '/^(00)?(' . implode('|', $this->country_codes) . ')0?/'), array('', '0'), $phone);
        if(!in_array(substr($phone, 0, 2), $this->mobile_prefixes) || strlen($phone) !== 10) return false;
        return $phone;
    }


    /**
     * @return bool
     */
    function is_ajax()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * @param $message
     *
     * @return bool
     */
    function notify_urbit($message)
    {
        return wp_mail('support@urb-it.com', 'Problem at ' . site_url('/'), $message);
    }

    /**
     * Error log
     */
    function log()
    {
        if ($this->setting('log') !== 'everything') {
            return;
        }

        $this->merge_to_log(func_get_args());
    }

    /**
     * Pass several parameters to error
     */
    function error()
    {
        $this->merge_to_log(func_get_args());
    }

    /**
     * @param array $args
     */
    protected function merge_to_log($args)
    {
        ob_start();

        foreach ($args as $row) {
            if (is_string($row)) {
                echo $row . ' ';
            } else {
                var_dump($row);
            }
        }

        $this->write_to_log(ob_get_clean());
    }

    /**
     * @param string $input
     *
     * @return bool
     */
    protected function write_to_log($input)
    {
        if (!$this->log) {
            if (!class_exists('WC_Logger')) {
                return error_log($input);
            }

            $this->log = new WC_Logger();
        }

        $this->log->add('urb-it', $input);
    }
}

WooCommerce_Urb_It::instance();

require_once WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-shortcode.php';
require_once WOOCOMMERCE_URB_IT_PLUGIN_ROOT . '/includes/class-widget.php';
