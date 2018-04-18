<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Urb_It_Frontend_Postcode_Validator extends WooCommerce_Urb_It_Frontend
{
    protected $added_assets = false;

    protected $plugin = null;

    public function __construct()
    {
        parent::__construct();
        add_action('wc_ajax_urb_it_validate_postcode', array($this, 'ajax'));
        add_action('woocommerce_single_product_summary', array($this, 'product_page'), 35);
    }

    /**
     * Postcode validator: Ajax
     *
     * @throws WC_Data_Exception
     */
    public function ajax()
    {
        $data_to_validate = array(
            'street' => $_GET['street'],
            'postcode' => $_GET['postcode'],
            'city' => $_GET['city'],
        );

        echo (!empty($data_to_validate) && $this->validate->postcode($data_to_validate)) ? '1' : '0';

        if (!empty($_GET['save'])) {
            wc()->customer->set_shipping_postcode($_GET['postcode']);
        }

        exit;
    }

    // Postcode validator: Assets
    public function add_assets()
    {
        if ($this->added_assets) {
            return;
        }

        $this->added_assets = true;
        ?>
        <style>
            .urb-it-postcode-validator {
                background-image: url('<?php echo $this->url; ?>assets/img/urb-it-logotype.png');
                background-image: linear-gradient(transparent, transparent), url('<?php echo $this->url; ?>assets/img/urb-it-logotype.svg');
            }

            <?php include $this->path . 'assets/css/postcode-validator.css'; ?>
        </style>
        <script>
            <?php include $this->path . 'assets/js/postcode-validator.js'; ?>
        </script>
        <?php
    }

    // Postcode validator: Product page
    public function product_page()
    {
        if ($this->setting('postcode_validator_product_page') !== 'yes') {
            return;
        }

        global $product, $woocommerce;

        if (!$product->is_in_stock() || $product->get_attribute('urb_it_bulky')
            || !$this->validate->product_weight($product)
            || !$this->validate->product_volume($product)) {
            return;
        }

        $postcode = $woocommerce->customer->get_shipping_postcode();
        if (!$postcode) {
            $postcode = $woocommerce->customer->get_postcode();
        }

        $this->template('postcode-validator/form', compact('postcode'));

        add_action('wp_footer', array($this, 'add_assets'));
    }
}

return new WooCommerce_Urb_It_Frontend_Postcode_Validator();
