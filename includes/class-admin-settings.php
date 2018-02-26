<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WooCommerce_Urb_It_Admin_Settings
 */
class WooCommerce_Urb_It_Admin_Settings extends WooCommerce_Urb_It_Admin
{
    function __construct()
    {
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_tab'), 50);
        add_action('woocommerce_settings_tabs_urb_it', array($this, 'tab_settings'));
        add_action('woocommerce_update_options_urb_it', array($this, 'save_settings'));
    }

    function add_tab($tabs)
    {
        $tabs['urb_it'] = __('urb-it', self::LANG);

        return $tabs;
    }

    function tab_settings()
    {
        woocommerce_admin_fields($this->get_general_settings());
        woocommerce_admin_fields($this->get_technical_settings());
        woocommerce_admin_fields($this->get_api_settings('prod'));
        woocommerce_admin_fields($this->get_api_settings('stage'));
    }

    function save_settings()
    {
        woocommerce_update_options($this->get_general_settings());
        woocommerce_update_options($this->get_technical_settings());
        woocommerce_update_options($this->get_api_settings('prod'));
        woocommerce_update_options($this->get_api_settings('stage'));
    }

    protected function get_general_settings()
    {
        $settings = array(
            'main_title' => array(
                'name' => __('General Settings', self::LANG),
                'type' => 'title',
            ),
            'notice_product_page' => array(
                'name' => __('Notice: Undeliverable Product', self::LANG),
                'desc' => __('Let visitors know on the product page if the product can\'t be delivered by urb-it.',
                    self::LANG),
                'type' => 'checkbox',
                'default' => 'yes',
                'id' => self::SETTINGS_PREFIX . 'notice_product_page',
            ),
            'notice_added_product' => array(
                'name' => __('Notice: Exceeded Cart Limit', self::LANG),
                'desc' => __('Tell the visitor when urb-it\'s deliver limits get exceeded.', self::LANG),
                'type' => 'checkbox',
                'default' => 'yes',
                'id' => self::SETTINGS_PREFIX . 'notice_added_product',
            ),
            'notice_checkout' => array(
                'name' => __('Notice: Undeliverable Order', self::LANG),
                'desc' => __('Explain in checkout and cart why an order can\'t be delivered by urb-it.', self::LANG),
                'type' => 'checkbox',
                'default' => 'yes',
                'id' => self::SETTINGS_PREFIX . 'notice_checkout',
            ),
            'postcode_validator_product_page' => array(
                'name' => __('Postcode Validator', self::LANG),
                'desc' => __('Add a postcode validator on the product page.', self::LANG),
                'type' => 'checkbox',
                'default' => 'yes',
                'id' => self::SETTINGS_PREFIX . 'postcode_validator_product_page',
            ),
            'order_confirmation' => array(
                'name' => __('Order Confirmation', self::LANG),
                'desc' => __('Add a confirmation dialog to order processing.', self::LANG),
                'type' => 'checkbox',
                'default' => 'yes',
                'id' => self::SETTINGS_PREFIX . 'order_confirmation',
            ),
            'section_end' => array(
                'type' => 'sectionend',
            ),
        );

        return apply_filters('woocommerce_urb_it_general_settings', $settings);
    }

    protected function get_technical_settings()
    {
        $statuses = wc_get_order_statuses();
        array_unshift($statuses, 'NONE');
        $settings = array(
            'main_title' => array(
                'name' => __('Technical Settings', self::LANG),
                'type' => 'title',
            ),
            'environment' => array(
                'name' => __('Environment', self::LANG),
                'type' => 'radio',
                'options' => array(
                    'prod' => __('Production', self::LANG),
                    'stage' => __('Stage', self::LANG),
                ),
                'default' => 'stage',
                'id' => self::SETTINGS_PREFIX . 'environment',
            ),
            'log' => array(
                'name' => __('Log', self::LANG),
                'type' => 'radio',
                'options' => array(
                    'errors' => __('Only errors', self::LANG),
                    'everything' => __('Everything', self::LANG),
                ),
                'default' => 'errors',
                'id' => self::SETTINGS_PREFIX . 'log',
            ),
            'now_validation_time' => array(
                'name' => __('Now order auto validation time', self::LANG),
                'type' => 'number',
                'default' => self::DEFAULT_PREPARE_TIME,
                'class' => 'large-text code',
                'id' => self::SETTINGS_PREFIX . 'now_validation_time',
            ),
            'now_status' => array(
                'name' => __('Now order status trigger for confirmation', self::LANG),
                'type' => 'select',
                'options' => $statuses,
                'class' => 'large-text code',
                'id' => self::SETTINGS_PREFIX . 'now_status',
            ),
            'section_end' => array(
                'type' => 'sectionend',
            ),
        );

        return apply_filters('woocommerce_urb_it_technical_settings', $settings);
    }

    protected function get_api_settings($environment = 'stage')
    {
        $settings = array(
            $environment . '_title' => array(
                'name' => sprintf(__('%s Credentials', self::LANG),
                        ($environment === 'prod' ? 'Production' : ucfirst($environment)))
                    . (($this->setting('environment') === $environment) ? (' (' . __('active', self::LANG) . ')') : ''),
                'type' => 'title',
            ),
            $environment . '_x_api_key' => array(
                'name' => __('X-API-Key', self::LANG),
                'type' => 'text',
                'class' => 'large-text code',
                'id' => self::SETTINGS_PREFIX . $environment . '_x_api_key',
            ),
            $environment . '_bearer_token' => array(
                'name' => __('Bearer Token', self::LANG),
                'type' => 'text',
                'class' => 'large-text code',
                'id' => self::SETTINGS_PREFIX . $environment . '_bearer_token',
            ),
            $environment . '_section_end' => array(
                'type' => 'sectionend',
            ),
        );

        return apply_filters('woocommerce_urb_it_api_settings', $settings, $environment);
    }
}

new WooCommerce_Urb_It_Admin_Settings();
