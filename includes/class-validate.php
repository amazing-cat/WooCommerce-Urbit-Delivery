<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Urb_It_Validate extends WooCommerce_Urb_It
{
    function __construct()
    {
        parent::__construct();
        // $this->plugin = WooCommerce_Urb_It::instance();
    }

    function product_weight($product)
    {
        if (wc_get_weight($product->get_weight(), 'kg') > self::ORDER_MAX_WEIGHT) {
            $valid = false;
        } else {
            $valid = true;
        }

        // Please don't use this filter without urb-it's knowledge
        $valid = apply_filters('woocommerce_urb_it_valid_product_weight', $valid, $product);

        if (!$valid) {
            $this->log('Product #' . $product->id . ' has invalid weight.');
        }

        return $valid;
    }

    function product_volume($product)
    {
        if (wc_get_dimension((int)$product->get_length(), 'cm') * wc_get_dimension((int)$product->get_width(),
                'cm') * wc_get_dimension((int)$product->get_height(), 'cm') > self::ORDER_MAX_VOLUME) {
            $valid = false;
        } else {
            $valid = true;
        }

        // Please don't use this filter without urb-it's knowledge
        $valid = apply_filters('woocommerce_urb_it_valid_product_volume', $valid, $product);

        if (!$valid) {
            $this->log('Product #' . $product->id . ' has invalid volume.');
        }

        return $valid;
    }

    function cart_weight()
    {
        if (wc_get_weight(wc()->cart->cart_contents_weight, 'kg') > self::ORDER_MAX_WEIGHT) {
            $valid = false;
        } else {
            $valid = true;
        }

        // Please don't use this filter without urb-it's knowledge
        $valid = apply_filters('woocommerce_urb_it_valid_cart_weight', $valid);

        if (!$valid) {
            $this->log('Cart has invalid weight.');
        }

        return $valid;
    }

    function cart_volume()
    {
        $total_volume = 0;

        foreach (wc()->cart->get_cart() as $item) {
            $_product = $item['data'];

            $total_volume += wc_get_dimension((int)$_product->get_length(), 'cm')
                * wc_get_dimension((int)$_product->get_width(), 'cm')
                * wc_get_dimension((int)$_product->get_height(), 'cm');
        }

        if ($total_volume > self::ORDER_MAX_VOLUME) {
            $valid = false;
        } else {
            $valid = true;
        }

        // Please don't use this filter without urb-it's knowledge
        $valid = apply_filters('woocommerce_urb_it_valid_cart_volume', $valid);

        if (!$valid) {
            $this->log('Cart has invalid volume.');
        }

        return $valid;
    }

    function cart_bulkiness()
    {
        foreach (wc()->cart->get_cart() as $item) {
            $_product = $item['data'];

            if ($_product->get_attribute('urb_it_bulky')) {
                $this->log('Cart contains a bulky product.');

                return false;
            }
        }

        return true;
    }

    function opening_hours($delivery_time)
    {
        $days = $this->opening_hours->get();

        if (!$days) {
            return false;
        }

        foreach ($days as $day) {
            if ($delivery_time >= $day->open && $delivery_time <= $day->close) {
                return true;
            }
        }

        $this->log('Invalid delivery time - the store is closed. Input: ' . $delivery_time->format('Y-m-d H:i:s'));

        return false;
    }

    function order($delivery_time, $message = '', $recipient, $checkout_id)
    {
        $order_data = array(
            'delivery_time' => $delivery_time,
            'message' => $message,
            'recipient' => $recipient,
        );

        try {
            do_action('woocommerce_urb_it_before_validate_order', $this->urbit);

            $order_data = apply_filters('woocommerce_urb_it_validate_order_data', $order_data);

            $this->log('Validating order data:', $order_data);

            $this->urbit->SetDeliveryTimePlaceRecipient($checkout_id, $order_data);

            $this->log('Validation result:', $this->urbit->httpStatus, $this->urbit->httpBody);

            return true;
        } catch (Exception $e) {
            $this->log('Error while validating order: ' . $e->getMessage());

            if (isset($this->urbit->httpBody->code)) {
                switch ($this->urbit->httpBody->code) {
                    case 'RET-002':
                        throw new Exception(__('Urb-it can unfortunately not deliver to this address.', self::LANG));
                        break;
                    case 'RET-004':
                    case 'RET-005':
                        throw new Exception(__('We can unfortunately not deliver at this time, please choose another.',
                            self::LANG));
                        break;
                    default:
                        throw new Exception($e->getMessage());
                }
            }

            return false;
        }
    }

    function postcode($data_to_validate)
    {
        try {
            $valid = $this->urbit->ValidateDeliveryAddress($data_to_validate);

            if (!$valid) {
                $this->log('Invalid postcode...');
            }

            return apply_filters('woocommerce_urb_it_valid_postcode', $valid, $data_to_validate);
        } catch (Exception $e) {
            $this->log('Error while validating postcode: ' . $e->getMessage());

            return false;
        }
    }
}

return new WooCommerce_Urb_It_Validate();
