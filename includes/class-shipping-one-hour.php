<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('WC_Urb_It_One_Hour')) {
    return;
}

class WC_Urb_It_One_Hour extends WC_Shipping_Method
{
    const LANG = WooCommerce_Urb_It::LANG;

    protected $plugin;

    function __construct()
    {
        parent::__construct();
        $this->id = 'urb_it_one_hour';
        $this->method_title = __('urb-it now', self::LANG);
        $this->method_description = __('urb-it now allows deliveries now.', self::LANG);
        $this->init();
    }

    function init()
    {
        // Load the settings API
        $this->init_form_fields();
        $this->init_settings();

        $this->plugin = WooCommerce_Urb_It::instance();

        // Define user set variables
        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->type = $this->get_option('type');
        $this->fee = floatval($this->get_option('fee'));

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', self::LANG),
                'type' => 'checkbox',
                'label' => __('Enable urb-it now', self::LANG),
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Method Title', self::LANG),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', self::LANG),
                'default' => $this->method_title,
                'desc_tip' => true,
            ),
            'type' => array(
                'title' => __('Fee Type', self::LANG),
                'type' => 'select',
                'description' => __('How to calculate delivery charges', self::LANG),
                'default' => 'fixed',
                'options' => array(
                    'fixed' => __('Fixed amount', self::LANG),
                    'percent' => __('Percentage of cart total', self::LANG),
                    'product' => __('Fixed amount per product', self::LANG),
                ),
                'desc_tip' => true,
            ),
            'fee' => array(
                'title' => __('Price', self::LANG),
                'type' => 'price',
                'description' => __('What fee do you want to charge for local delivery, disregarded if you choose free. Leave blank to disable.',
                    self::LANG),
                'default' => 0,
                'desc_tip' => true,
            ),
        );
    }

    function calculate_shipping($package = array())
    {
        $shipping_total = 0;

        if ($this->type == 'fixed') {
            $shipping_total = $this->fee;
        } elseif ($this->type == 'percent') {
            $shipping_total = $package['contents_cost'] * ($this->fee / 100);
        } elseif ($this->type == 'product') {
            foreach ($package['contents'] as $item_id => $values) {
                $_product = $values['data'];

                if ($values['quantity'] > 0 && $_product->needs_shipping()) {
                    $shipping_total += $this->fee * $values['quantity'];
                }
            }
        }

        $rate = array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => apply_filters('woocommerce_urb_it_shipping_cost', $shipping_total, $this),
        );

        $this->add_rate($rate);
    }

    function is_available($package)
    {
        $optional_postcode =
            apply_filters('woocommerce_urb_it_optional_postcode', empty($package['destination']['postcode']), $package,
                $this);

        if ($this->enabled != 'yes') {
            return false;
        }

        // Check the weight of the order
        if (!$this->plugin->validate->cart_weight()) {
            return false;
        }

        // Check the volume of the order
        if (!$this->plugin->validate->cart_volume()) {
            return false;
        }

        $now_time_offset = $this->plugin->create_date($this->plugin->now_offset());

        if (!$this->plugin->validate->opening_hours($now_time_offset)) {
            return false;
        }

        // Check if there are any back-order products in cart
        foreach (wc()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            if ($_product->is_on_backorder($cart_item['quantity'])) {
                return false;
            }
        }

        $is_available = $optional_postcode ? true : $this->plugin->validate->postcode(array(
            'street' => $package['destination']['address'],
            'postcode' => $package['destination']['postcode'],
            'city' => $package['destination']['city'],
        ));

        return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package);
    }
}
