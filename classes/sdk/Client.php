<?php

class Aplazame_Client
{
    public function __construct($host, $version, $sandbox, $private_api_key)
    {
        $this->host = $host;
        $this->sandbox = $sandbox;
        $this->private_api_key = $private_api_key;

        if ($version) {
            $this->version = explode('.', $version);
            $this->version = $this->version[0];
        } else {
            $this->version = $version;
        }
    }

    protected function endpoint($path)
    {
        return trim(str_replace('://', '://api.', $this->host), '/') . $path;
    }

    public function headers()
    {
        return array(
            'Accept' => 'application/vnd.aplazame.' .
                ($this->sandbox?'sandbox.': '') . $this->version . '+json',
            'Authorization' => 'Bearer ' . $this->private_api_key,
            'User-Agent' => 'WooCommerce/sdk-' . WC_Aplazame::VERSION
        );
    }

    public function request($method, $path, $data=null)
    {
        $args = array(      
            'headers' => $this->headers(),
            'method' => $method,
            'body' => $data
        );

        return $response = wp_remote_request($this->endpoint($path), $args);
    }

    protected function order_request($order_id, $method, $path, $data=null)
    {
        return $this->request('POST', '/orders/' . $order_id . $path, $data);
    }

    public function authorize($order_id)
    {
        return $this->order_request($order_id, 'POST', '/authorize');
    }

    public function refund($order_id, $total_refunded)
    {
        return $this->order_request($order_id, 'POST', '/refund', array(
            'amount' => $total_refunded
        ));
    }

    public function cancel($order_id)
    {
        return $this->order_request($order_id, 'POST', '/cancel');
    }
}
