<?php


class Aplazame_Serializers
{

    public static function woo_version()
    {
        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $woo = get_plugins('/woocommerce');
        return $woo['woocommerce.php']['Version'];
    }

    public static function get_meta()
    {
        return array(
            'module' => array(
                'name' => 'aplazame:woocommerce',
                'version' => WC_Aplazame::VERSION
            ),
            'version' => static::woo_version()
        );
    }

    public function get_articles($items)
    {
        $articles = array();

        foreach($items as $item => $values) {
            $product = new WC_Product($values['product_id']);

            $tax_rate = 100 * ($product->get_price_including_tax() /
                $product->get_price_excluding_tax() - 1);

            $articles[] = array(
                'id' => (string) $product->id,
                'sku' => $product->get_sku(),
                'name' => $product->get_title(),
                'description' => $product->get_post_data()->post_content,
                'url' => $product->get_permalink(),
                'image_url' => wp_get_attachment_url(
                    get_post_thumbnail_id($product->id)),
                'quantity' => (int) $values['qty'],
                'price' => Aplazame_Filters::decimals(
                    $product->get_price_excluding_tax()),
                'tax_rate' => Aplazame_Filters::decimals($tax_rate)
            );
        }

        return $articles;
    }

    public function get_user($user)
    {
        return array(
            'id' => (string) $user->id,
            'type' => 'e',
            'gender' => 0,
            'email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'date_joined' => date(DATE_ISO8601, $user->user_registered));
    }

    public function get_customer($billing_email)
    {
        return array(
            'type' => 'n',
            'gender' => 0,
            'email' => $billing_email);
    }

    public function get_address($order, $type)
    {
        $_field = function($name) use ($order, $type) {
            $_key = ($type . '_' . $name);
            return $order->$_key;
        };

        $serializer = array(
            'first_name' => $_field('first_name'),
            'last_name' => $_field('last_name'),
            'phone' => $_field('phone'),
            'street' => $_field('address_1'),
            'address_addition' => $_field('address_2'),
            'city' => $_field('city'),
            'state' => $_field('state'),
            'country' => $_field('country'),
            'postcode' => $_field('postcode'));

        return $serializer;
    }

    public function get_shipping_info($order)
    {
        $tax_rate = 100 * $order->order_shipping_tax /
            $order->get_total_shipping();

        $serializer = array_merge(
            $this->get_address($order, 'shipping'), array(
                'price' => Aplazame_Filters::decimals(
                    $order->get_total_shipping()),
                'tax_rate' => Aplazame_Filters::decimals($tax_rate),
                'name' => $order->get_shipping_method()
        ));

        return $serializer;
    }

    public function get_order($order)
    {
        $serializer = array(
            'id' => (string) $order->id,
            'articles' => $this->get_articles($order->get_items()),
            'currency' => get_woocommerce_currency(),
            'total_amount' => Aplazame_Filters::decimals($order->get_total()),
            'discount' => Aplazame_Filters::decimals(
                $order->get_total_discount()));

        return $serializer;
    }

    public function get_checkout($order, $checkout_url, $user)
    {
        $serializer = array(
            'toc' => true,
            'merchant' => array(
                'confirmation_url' => home_url(
                    add_query_arg('action', 'confirm')),
                'cancel_url' => html_entity_decode(
                    $order->get_cancel_order_url()),
                'checkout_url' => html_entity_decode(
                    $order->get_cancel_order_url($checkout_url)),
                'success_url' => html_entity_decode(
                    $order->get_checkout_order_received_url())
            ),
            'customer' => $user->id?$this->get_user(
                $user):$this->get_customer($order->billing_email),
            'order' => $this->get_order($order),
            'billing' => $this->get_address($order, 'billing'),
            'meta' => static::get_meta());

        $shipping_method = $order->get_shipping_method();

        if (!empty($shipping_method)) {
            $serializer['shipping'] = $this->get_shipping_info($order);
        }

        return $serializer;
    }

    public function get_history($qs)
    {
        $orders = array();

        foreach($qs as $item => $values) {
            $order = new WC_Order($qs[$item]->ID);

            $orders[] = array(
                'id' => (string) $order->id,
                'amount' => Aplazame_Filters::decimals($order->get_total()),
                'due' => '',
                'status' => $order->get_status(),
                'type' => Aplazame_Helpers::get_payment_method($order->id),
                'order_date' => date(DATE_ISO8601, $order->order_date),
                'currency' => $order->get_order_currency(),
                'billing' => $this->get_address($order, 'billing'),
                'shipping' => $this->get_shipping_info($order)
            );
        }

        return $orders;
    }
}
