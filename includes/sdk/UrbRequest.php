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

    function __construct($x_api_key = '', $bearer_token = '', $stage = false)
    {
        if (version_compare(PHP_VERSION, '5.2.0') < 0) {
            throw new Exception('UrbRequest requires at least PHP version 5.2.0.');
        }

        if (!function_exists('curl_init')) {
            throw new Exception('cURL is required for UrbRequest to work.');
        }

        $this->x_api_key = (string) $x_api_key;
        $this->bearer_token = (string) $bearer_token;
        $this->stage = (bool) $stage;
        $this->baseUrl = ($this->stage ? self::STAGE_BASE_URL : self::PROD_BASE_URL);

        if (!$this->x_api_key) {
            throw new Exception('X-API-Key is missing.');
        }

        if (!$this->bearer_token) {
            throw new Exception('Bearer Token is missing.');
        }
    }

    function GetDeliveryHours()
    {
        $this->Call('GET', 'deliveryhours');

        if ($this->httpStatus !== 200) {
            throw new Exception('HTTP ' . $this->httpStatus);
        }

        return $this->httpBody->items;
    }

    function ValidateDeliveryAddress($data_to_validate = array('street' => '', 'postcode' => '', 'city' => ''))
    {
        if (!preg_match('/^[\d]{3}\s?[\d]{2}$/', $data_to_validate['postcode'])) {
            throw new Exception('Invalid postal code.');
        }

        $date_to_validate['postcode'] = str_replace(' ', '', $data_to_validate['postcode']);

        $this->Call('GET', 'address', $data_to_validate);

        if ($this->httpStatus !== 200) {
            $body = $this->httpStatus === 400 ? "\n" . $this->httpBody->address . $this->httpBody->message : '';
            throw new Exception('HTTP ' . $this->httpStatus . $body);
        }

        return ($this->httpStatus === 200);
    }

    function SetDeliveryTimePlaceRecipient($checkout_id, $order)
    {
        $this->Call('PUT', 'checkouts/' . $checkout_id . '/delivery', $order);

        if ($this->httpStatus !== 204) {
            if (isset($this->httpBody)) {
                throw new Exception(print_r($this->httpBody->errors, true));
            } else {
                throw new Exception('HTTP ' . $this->httpStatus);
            }
        }

        return $this->httpStatus;
    }

    function CreateCart($items)
    {
        $this->Call('POST', 'carts', $items);

        if ($this->httpStatus !== 201) {
            if (isset($this->httpBody->message)) {
                throw new Exception($this->httpBody->message);
            } else {
                throw new Exception('HTTP ' . $this->httpStatus);
            }
        }

        return $this->httpBody->id;
    }

    function InitiateCheckout($cart_reference)
    {
        $this->Call('POST', 'checkouts', array('cart_reference' => $cart_reference));

        if ($this->httpStatus !== 201) {
            if (isset($this->httpBody->message)) {
                throw new Exception($this->httpBody->message);
            } else {
                throw new Exception('HTTP ' . $this->httpStatus);
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
