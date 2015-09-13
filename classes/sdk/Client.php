<?php


class Aplazame_Exception extends Exception
{
    private $body;
    private $status_code;

    public function __construct($message, $status_code, $body)
    {
        parent::__construct($message);

        $this->status_code = $status_code;
        $this->body = $body;
        $this->error = $body->error;
    }

    public function get_status_code()
    {
        return $this->status_code;
    }

    public function get_body()
    {
        return $this->body;
    }

    public function get_field_error($field)
    {
        $error_list = $this->error->fields->$field;

        if (empty($error_list)) {
            $error = $this->error->message;
        } else {
            $error = $error_list[0];
        }

        return $error;
    }
}


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

    protected function headers()
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

        $response = wp_remote_request($this->endpoint($path), $args);

        if (is_wp_error($response)) {
            throw new Exception('aplazame_client_error');
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response));

        if (($status_code  < 200) || ($status_code  >= 300)) {
            throw new Aplazame_Exception('aplazame_api_error', $status_code, $body);
        }

        return $body;
    }

    protected function order_request($order_id, $method, $path, $data=null)
    {
        return $this->request('POST', '/orders/' . $order_id . $path, $data);
    }

    public function authorize($order_id)
    {
        return $this->order_request($order_id, 'POST', '/authorize');
    }

    public function refund($order_id, $amount, $reason)
    {
        return $this->order_request($order_id, 'POST', '/refund', array(
            'amount' => $amount,
            'reason' => $reason
        ));
    }

    public function cancel($order_id)
    {
        return $this->order_request($order_id, 'POST', '/cancel');
    }
}
