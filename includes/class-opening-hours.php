<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WooCommerce_Urb_it_Opening_Hours
 */
class WooCommerce_Urb_it_Opening_Hours extends WooCommerce_Urb_It
{
    function __construct()
    {
        parent::__construct();
        // Turn off caching of shipping method
        add_filter('option_woocommerce_status_options', array($this, 'turn_off_shipping_cache'));
    }

    // Get opening hours from the Retailer portal
    function get()
    {
        $today = $this->date('today');
        $max_time = clone $today;
        $max_time->modify(self::SPECIFIC_TIME_RANGE);

        $days = apply_filters('woocommerce_urb_it_debug',
            false) ? false : get_transient('woocommerce_urb_it_delivery_days');

        if ($days === false) {
            $this->log('Fetching opening hours from API...');

            try {
                $delivery_hours = $this->urbit->GetDeliveryHours();

                if (!$delivery_hours) {
                    throw new Exception('Empty result');
                }

                $this->log('API result:', $delivery_hours);

                $days = array();

                foreach ($delivery_hours as $day) {
                    if ($day->closed) {
                        continue;
                    }

                    $hours = (object) array(
                        'open' => $this->date($day->opening_time),
                        'first_delivery' => $this->date($day->first_delivery),
                        'last_delivery' => $this->date($day->last_delivery),
                        'close' => $this->date($day->closing_time),
                    );

                    $days[] = $hours;
                }

                set_transient('woocommerce_urb_it_delivery_days', $days, self::TRANSIENT_TTL);
            } catch (Exception $e) {
                $this->error('Error while fetching opening hours: ' . $e->getMessage());
            }
        } else {
            $this->log('Fetched opening hours from cache.');
        }

        return $days;
    }

    // Turn off shipping cache, otherwise there might be problems with the opening hours
    function turn_off_shipping_cache($status_options = array())
    {
        $status_options['shipping_debug_mode'] = '1';

        return $status_options;
    }
}

return new WooCommerce_Urb_it_Opening_Hours();
