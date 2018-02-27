<?php

class UrbRequest
{
    const PROD_BASE_URL  = 'https://api.urb-it.com/v2/';
    const STAGE_BASE_URL = 'https://sandbox.urb-it.com/v2/';

    protected $x_api_key;

    protected $bearer_token;

    protected $stage = true;

    protected $baseUrl;

    public $httpStatus;

    public $httpBody;

    /**
     * UrbRequest constructor.
     * @param string $x_api_key
     * @param string $bearer_token
     * @param bool $stage
     * @throws Exception
     */
    public function __construct($x_api_key = '', $bearer_token = '', $stage = false)
    {
        if (version_compare(PHP_VERSION, '5.2.0') < 0) {
            die('UrbRequest requires at least PHP version 5.2.0.');
        }

        if (!function_exists('curl_init')) {
            die('cURL is required for UrbRequest to work.');
        }

        $this->x_api_key = (string) $x_api_key;
        $this->bearer_token = (string) $bearer_token;
        $this->stage = (bool) $stage;
        $this->baseUrl = ($this->stage ? self::STAGE_BASE_URL : self::PROD_BASE_URL);

        if (!$this->x_api_key) {
            die('X-API-Key is missing.');
        }

        if (!$this->bearer_token) {
            die('Bearer Token is missing.');
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function GetDeliveryHours()
    {
        $this->Call('GET', 'deliveryhours');

        if ($this->httpStatus !== 200) {
            die('HTTP ' . $this->httpStatus);
        }

        return $this->httpBody->items;
    }

    /**
     * @param array $data_to_validate
     * @return bool
     * @throws Exception
     */
    public function ValidateDeliveryAddress($data_to_validate = array('street' => '', 'postcode' => '', 'city' => ''))
    {
        if (!preg_match('/^[\d]{3}\s?[\d]{2}$/', $data_to_validate['postcode'])) {
            die('Invalid postal code.');
        }

        $date_to_validate['postcode'] = str_replace(' ', '', $data_to_validate['postcode']);

        $this->Call('GET', 'address', $data_to_validate);

        if ($this->httpStatus !== 200) {
            $body = $this->httpStatus === 400 ? "\n" . $this->httpBody->address . $this->httpBody->message : '';
            die('HTTP ' . $this->httpStatus . $body);
        }

        return ($this->httpStatus === 200);
    }

    /**
     * @param $checkout_id
     * @param $order
     * @return mixed
     */
    public function SetDeliveryTimePlaceRecipient($checkout_id, $order) {
        $this->Call('PUT', 'checkouts/' . $checkout_id . '/delivery', $order);

        if ($this->httpStatus !== 204) {
            if (isset($this->httpBody)) {
                echo 'checkout_id = ' . $checkout_id;
                print_r($order);
                die($this->httpBody);
            } else {
                echo 'checkout_id = ' . $checkout_id;
                print_r($order);
                die('HTTP ' . $this->httpStatus);
            }
        }

        return $this->httpBody;
    }
    // public function SetDeliveryTimePlaceRecipient($checkout_id, $order) {
    //     $this->Call('PUT', 'checkouts/' . $checkout_id . '/delivery', $order);
    //     if ($this->httpStatus !== 204) {
    //       if (!is_null($this->httpBody)) {
    //             echo 'checkout_id = ' . $checkout_id;
    //             print_r($order);
    //             die($this->httpBody);
    //         } else {
    //             echo 'checkout_id = ' . $checkout_id;
    //             print_r($order);
    //             die('HTTP ' . $this->httpStatus);
    //         }
    //     }

    //     return $this->httpBody;
    // }
        

    /**
     * @param $items
     * @return mixed
     * @throws Exception
     */
    public function CreateCart($items) {
        $this->Call('POST', 'carts', $items);

        if ($this->httpStatus !== 201) {
            if (!is_null($this->httpBody->message)) {
                die($this->httpBody->message);
            } else {
                die('HTTP ' . $this->httpStatus);
            }
        }

        return $this->httpBody->id;
    }

    /**
     * @param $cart_reference
     * @return mixed
     * @throws Exception
     */
    public function InitiateCheckout($cart_reference)
    {
        $this->Call('POST', 'checkouts', array('cart_reference' => $cart_reference));

        if ($this->httpStatus !== 201) {
            if (!is_null($this->httpBody->message)) {
                die($this->httpBody->message);
            } else {
                die('HTTP ' . $this->httpStatus);
            }
        }

        return $this->httpBody->id;
    }

    protected function Call($method = 'GET', $url = '', $postData = array())
    {
        $url = $this->baseUrl . $url;
        $json = '';
        $headers = array(
            'X-API-Key: ' . $this->x_api_key,
        );

        if (($method === 'POST' || $method === 'PUT') && $postData) {
            $output_headers = array(
                'Content-Type: ' . 'application/json',
                'Authorization: ' . $this->bearer_token,
            );
            $json = json_encode($postData);
            $headers = array_merge($headers, $output_headers);
        }

        if ($method === 'GET' && $postData) {
            $url .= '?' . http_build_query($postData);
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($json) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ch);

        $this->httpBody = ($response ? json_decode($response) : null);
        $this->httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $this->httpStatus;
    }
}
