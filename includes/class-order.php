<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Urb_It_Order extends WooCommerce_Urb_It
{
    function __construct()
    {
        parent::__construct();
        add_action('init', array($this, 'register_order_status'));
        add_filter('wc_order_statuses', array($this, 'order_statuses'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_data'), 10, 2);
        add_action('woocommerce_order_status_changed', array($this, 'status_event'), 10, 4);
    }

    function register_order_status()
    {
        register_post_status('wc-picked-up', array(
            'label' => __('Picked up', self::LANG),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Picked up <span class="count">(%s)</span>',
                'Picked up <span class="count">(%s)</span>', self::LANG),
        ));
    }

    function order_statuses($order_statuses)
    {
        $new_order_statuses = array();

        // Add new order status after processing
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;

            if ($key === 'wc-processing') {
                $new_order_statuses['wc-picked-up'] = __('Picked up', self::LANG);
            }
        }

        return $new_order_statuses;
    }

    function save_data($order_id, $posted)
    {
        $delivery_time =
            (!empty($_POST['urb_it_date']) && !empty($_POST['urb_it_hour']) && !empty($_POST['urb_it_minute'])) ? (esc_attr($_POST['urb_it_date']) . ' '
                . esc_attr($_POST['urb_it_hour']) . ':' . esc_attr($_POST['urb_it_minute'])) : wc()->session->get('urb_it_delivery_time');
        $message =
            !empty($_POST['urb_it_message']) ? esc_attr($_POST['urb_it_message']) : wc()->session->get('urb_it_message');

        $order = wc_get_order($order_id);

        // If specific time, save the delivery time for later
        if (!empty($delivery_time)) {
            update_post_meta($order_id, '_urb_it_delivery_time', $delivery_time);

            if (apply_filters('woocommerce_urb_it_add_delivery_time_order_note', true, $order)) {
                $order->add_order_note(sprintf(__('Urb-it delivery time: %s', self::LANG), $delivery_time));
            }
        }

        // If there's an message, save it
        if (!empty($message)) {
            update_post_meta($order_id, '_urb_it_message', $message);

            $order->add_order_note(sprintf(__('Urb-it message: %s', self::LANG), $message));
        }
    }

    // Send delivery info on order status change
    function status_event($order_id, $status_from, $status_to, $order)
    {
        $shipping_method = array_shift($order->get_items('shipping'));

        if (($shipping_method->get_method_id() == 'urb_it_one_hour' || $shipping_method->get_method_id() == 'urb_it_specific_time') && 'wc-' . $status_to == get_option(self::SETTINGS_PREFIX . 'now_status')) {
            $environment = get_option(self::SETTINGS_PREFIX . 'environment');
            $delivery_time = get_option(self::SETTINGS_PREFIX . $environment . '_delivery_time_' . $order_id);
            if (!$delivery_time) {
                error_log(print_r("Already sent!", true));
                return;
            }

            $recipient = array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2() != null ? $order->get_shipping_address_2() : '',
                'city' => $order->get_shipping_city(),
                'postcode' => str_replace(' ', '', $order->get_shipping_postcode()),
                'phone_number' => $order->get_billing_phone(),
                'email' => $order->get_billing_email(),
            );

            $checkout_id = get_option(self::SETTINGS_PREFIX . $environment . '_checkout_id_' . $order_id);

            $this->validate->order($delivery_time, $order->get_meta('message'), $recipient, $checkout_id);

            // Clean order actions and info
            $timestamp = new DateTime($delivery_time);
            $timestamp->sub(new DateInterval(self::STD_PROCESS_TIME));
            wp_unschedule_event($timestamp->getTimestamp(), 'preparation_time', array($delivery_time, $order->get_meta('message'), $recipient, $checkout_id, $order_id));
            delete_option( self::SETTINGS_PREFIX . $environment . '_checkout_id_' . $order_id );
            delete_option( self::SETTINGS_PREFIX . $environment . '_delivery_time_' . $order_id );
        }
    }

    // Get order item description
    public function get_item_description($item_id, $product, $order)
    {
        $metadata = $order->has_meta($item_id);
        $title = $product->get_title();

        if ($metadata) {
            $attributes = array();

            foreach ($metadata as $meta) {
                // Skip hidden core fields
                if (in_array($meta['meta_key'], apply_filters('woocommerce_hidden_order_itemmeta', array(
                    '_qty',
                    '_tax_class',
                    '_product_id',
                    '_variation_id',
                    '_line_subtotal',
                    '_line_subtotal_tax',
                    '_line_total',
                    '_line_tax',
                )), true)) {
                    continue;
                }

                // Skip serialised meta
                if (is_serialized($meta['meta_value'])) {
                    continue;
                }

                // Get attribute data
                if (taxonomy_exists(wc_sanitize_taxonomy_name($meta['meta_key']))) {
                    $term = get_term_by('slug', $meta['meta_value'], wc_sanitize_taxonomy_name($meta['meta_key']));
                    if (isset($term->name)) {
                        $meta['meta_value'] = $term->name;
                    }
                }

                $attributes[] = rawurldecode($meta['meta_value']);
            }

            if ($attributes) {
                $title .= ' - ' . implode(', ', $attributes);
            }
        }

        return apply_filters('woocommerce_urb_it_item_description', $title, $item_id, $product, $order);
    }
}

return new WooCommerce_Urb_It_Order;
