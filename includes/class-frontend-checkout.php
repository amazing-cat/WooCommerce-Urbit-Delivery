<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Urb_It_Frontend_Checkout extends WooCommerce_Urb_It_Frontend
{
    protected $added_assets = false;

    function __construct()
    {
        parent::__construct();
        add_action('woocommerce_review_order_after_shipping', array($this, 'fields'));
        add_action('woocommerce_after_checkout_form', array($this, 'add_assets'));
        add_action('woocommerce_before_checkout_form', array($this, 'notice_checkout'));
        add_action('woocommerce_before_cart', array($this, 'notice_checkout'));
        add_action('woocommerce_review_order_after_shipping', array($this, 'notice_checkout_shipping'));
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_form'));
        add_action('woocommerce_after_checkout_form', array($this, 'sync_cart'));
        add_action('woocommerce_checkout_order_processed', array($this, 'sync_checkout'));
        add_action('preparation_time', array($this, 'send_order'), 10, 5);
        add_filter('woocommerce_shipping_packages', array($this, 'check_urbit_now_availability'), 100, 1);
    }

    function sync_checkout($order_id) {
        $environment = get_option(self::SETTINGS_PREFIX . 'environment');
        $cart_reference = get_option(self::SETTINGS_PREFIX . $environment . '_cart_reference');
        $checkout_id = $this->urbit->InitiateCheckout($cart_reference);
        $order = new WC_Order($order_id);
        update_option(self::SETTINGS_PREFIX . $environment . '_checkout_id_' . $order_id, $checkout_id);
        $message = $order->get_customer_note();
        $delivery_time = null;

        $recipient = array(
            'first_name' => $order->get_shipping_first_name(),
            'last_name' => $order->get_shipping_last_name(),
            'address_1' => $order->get_shipping_address_1(),
            'address_2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'postcode' => $order->get_shipping_postcode(),
            'phone_number' => $order->get_billing_phone(),
            'email' => $order->get_billing_email(),
        );

        $now_offset = $this->create_date($this->now_offset());
        $cron_time = $this->create_date($this->now_offset())->sub(new DateInterval(self::STD_PROCESS_TIME));

        $shipping_method = array_shift($order->get_items('shipping'));
        $delivery_time = $shipping_method->get_method_id() != 'urb_it_one_hour' ?
            new DateTime(get_option(self::SETTINGS_PREFIX . $environment . '_delivery_time')) : $now_offset;

        wp_schedule_single_event($cron_time->getTimestamp(), 'preparation_time', array(date('c', $delivery_time->getTimestamp()), $message, $recipient, $checkout_id, $order_id));
        update_option(self::SETTINGS_PREFIX . $environment . '_delivery_time_' . $order_id, date('c', $delivery_time->getTimestamp()));
    }

    /**
     * Send cart data to Urb-it
     */
    function sync_cart()
    {
        $cart = wc()->cart->get_cart();
        $cart_reference = '';
        $items = array();

        foreach ($cart as $cart_item) {
            $img_data = str_replace(' ', '', $cart_item['data']->get_image());
            $img_data_array = explode('"', $img_data);
            $img_url = 'http:';
            $vat = WC_Tax::get_rates($cart_item['data']->get_tax_class());
            $vat = isset($vat[0]) ? ((float) $vat[0]) / 100.0 : 0.0;

            $price = ((float) $cart_item['data']->get_price()) + ((float) $cart_item['data']->get_price()) * $vat;

            foreach ($img_data_array as $key => $string) {
                if ($string === 'src=') {
                    $img_url .= $img_data_array[$key + 1];
                }
            }

            $item = array(
                'sku' => $cart_item['data']->get_sku(),
                'name' => $cart_item['data']->get_name(),
                'image' => $img_url,
                'vat' => (int) ($vat * 10000),
                'price' => (int) ($price * 100),
                'quantity' => $cart_item['quantity'],
            );

            $items[] = $item;
        }

        if ($items) {
            $cart_reference = $this->urbit->CreateCart(array('items' => $items));
        }
        $environment = get_option(self::SETTINGS_PREFIX . 'environment');
        update_option(self::SETTINGS_PREFIX . $environment . '_cart_reference', $cart_reference);
    }

    // User notice: Checkout (and cart)
    function notice_checkout()
    {
        if ($this->setting('notice_checkout') !== 'yes') {
            return;
        }

        if (!$this->validate->cart_weight()) {
            wc_add_notice(sprintf(__('As the total weight of your cart is over %d kilos, it can unfortunately not be delivered by urb-it.',
                self::LANG), self::ORDER_MAX_WEIGHT), 'notice');
        } elseif (!$this->validate->cart_volume()) {
            wc_add_notice(sprintf(__('As the total volume of your cart is over %d liters, it can unfortunately not be delivered by urb-it.',
                self::LANG), self::ORDER_MAX_VOLUME / 1000), 'notice');
        } elseif (!$this->validate->cart_bulkiness()) {
            wc_add_notice(__('As your cart contains a bulky product, it can unfortunately not be delivered by urb-it.',
                self::LANG), 'notice');
        }
    }

    // User notice: Wrong postcode
    function notice_checkout_shipping()
    {
        if (empty($_POST['address']) || empty($_POST['postcode']) || empty($_POST['city'])
            || $this->setting('notice_checkout') !== 'yes') {
            return;
        }

        $data_to_validate = array(
            'street' => $_POST['address'],
            'postcode' => $_POST['postcode'],
            'city' => $_POST['city'],
        );

        if ($this->validate->postcode($data_to_validate)) {
            return;
        }

        ////////////////
        ?>

        <tr class="urb-it-shipping">
            <th>&nbsp;</th>
            <td style="color: #d00;"><?php _e('As the delivery location is outside urb-it\'s availability zone, urb-it is disabled for this order.',
                    self::LANG); ?></td>
        </tr>

        <?php
        ////////////////
    }

    function fields($is_cart = false)
    {
        $shipping_method =
            wc()->session->get('chosen_shipping_methods', array(get_option('woocommerce_default_shipping_method')));

        $this->log('Chosen shipping method:', $shipping_method);

        if (empty($shipping_method))
            return;

        $message = wc()->session->get('urb_it_message');
        $now_time_with_offset = $this->create_date($this->now_offset());
        $time_offset = $now_time_with_offset->diff(new DateTime());

        if (in_array('urb_it_specific_time', $shipping_method))
            $this->template('checkout/field-delivery-time', array(
                'is_cart' => $is_cart,
                'message' => $message,
                'hide_date_field' => false,
                'hide_time_field' => false,
                'time_offset' => $time_offset,
                'now' => $now_time_with_offset->add(new DateInterval(self::SPECIFIC_TIME_ADD)),
                'days' => $this->opening_hours->get(),
            ));
        elseif (in_array('urb_it_one_hour', $shipping_method))
            $this->template('checkout/field-message', compact('is_cart', 'message', 'time_offset'));
    }

    function validate_form($posted)
    {
        if (isset($_POST['ship_to_different_address']))
            $field_type = 'shipping';

        if (!isset($posted['shipping_method'])
            || (!in_array('urb_it_one_hour', $posted['shipping_method'])
                && !in_array('urb_it_specific_time', $posted['shipping_method'])))
            return;

        $phone = $posted['billing_phone'];

        if (!$phone || !preg_match('/^\+[1-9]\d{6,}$/', $phone))
            throw new Exception(__('Please enter a valid cellphone number. Like +600000000', self::LANG));

        if (in_array('urb_it_specific_time', $posted['shipping_method'])) {
            $valid_time = true;
            $date = trim($_POST['urb_it_date']);
            $time = trim($_POST['urb_it_hour'] . ':' . $_POST['urb_it_minute']);
            $date_limit = $this->date(self::SPECIFIC_TIME_RANGE);
            $date_limit->setTime(23, 59);
            $environment = get_option(self::SETTINGS_PREFIX . 'environment');

            if (!preg_match('/^\d{4}\-\d{2}-\d{2}$/', $date)) {
                try {
                    throw new Exception(sprintf(__('Date is not set. Please choose the right value and try again.',
                        self::LANG)));
                } catch (Exception $e) {
                }
            }

            if (!preg_match('/^\d{1,2}\:\d{2}$/', $time))
                throw new Exception(sprintf(__('Time is not set. Please choose the right values and try again.', self::LANG)));

            if (!$valid_time)
                return;

            $delivery_time = new DateTime($date . ' ' . $time);
            update_option(self::SETTINGS_PREFIX . $environment . '_delivery_time', date('c', $delivery_time->getTimestamp()));

        } elseif (in_array('urb_it_one_hour', $posted['shipping_method'])) {
            $available_hours = $this->opening_hours->get();
            $now = $this->create_date($this->now_offset());

            if ($available_hours[0]->first_delivery->format('Y-m-d') !== $now->format('Y-m-d') ||
                $available_hours[0]->first_delivery > $now || $available_hours[0]->last_delivery < $now)
                throw new Exception(sprintf(__('Urb-it now shipping is not available right now...', self::LANG)));
        }

        if (apply_filters('woocommerce_urb_it_skip_validation', false)) {

            $this->log('Order validation skipped with filter ("woocommerce_urb_it_skip_validation") - aborting.');

            return;
        }
    }

    function send_order($delivery_time, $message, $recipient, $checkout_id, $order_id)
    {
        $this->validate->order($delivery_time, $message, $recipient, $checkout_id);

        $environment = get_option(self::SETTINGS_PREFIX . 'environment');
        delete_option( self::SETTINGS_PREFIX . $environment . '_checkout_id_' . $order_id );
        delete_option( self::SETTINGS_PREFIX . $environment . '_delivery_time_' . $order_id );
    }

    function check_urbit_now_availability($packages)
    {
        $available_hours = $this->opening_hours->get();
        $now = $this->create_date($this->now_offset());

        if ($available_hours[0]->first_delivery->format('Y-m-d') !== $now->format('Y-m-d') ||
            $available_hours[0]->first_delivery > $now || $available_hours[0]->last_delivery < $now)
            foreach ($packages[0]['rates'] as $key => $item)
                if ($key === 'urb_it_one_hour')
                    unset($packages[0]['rates'][$key]);

        return $packages;
    }

    function add_assets()
    {
        if (!apply_filters('woocommerce_urb_it_add_checkout_assets', true) || $this->added_assets)
            return;

        /////////////////

        ?>

        <style>
            <?php include $this->path . 'assets/css/urb-it-checkout.css'; ?>
        </style>

        <script>
            if (!ajaxurl) var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            <?php include $this->path . 'assets/js/urb-it-checkout.js'; ?>
        </script>

        <?php
        /////////////////

        $this->added_assets = true;
    }
}

return new WooCommerce_Urb_It_Frontend_Checkout();
