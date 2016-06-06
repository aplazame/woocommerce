<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Aplazame_Serializers {
	/**
	 * @return string
	 */
	public static function woo_version() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$woo = get_plugins( '/woocommerce' );

		return $woo['woocommerce.php']['Version'];
	}

	/**
	 * @return array
	 */
	public static function get_meta() {
		return array(
			'module'  => array(
				'name'    => 'aplazame:woocommerce',
				'version' => WC_Aplazame::VERSION,
			),
			'version' => static::woo_version(),
		);
	}

	/**
	 * @param array $items
	 *
	 * @return array
	 */
	public function get_articles( $items ) {
		$articles = array();

		foreach ( $items as $item => $values ) {
			$product = new WC_Product( $values['product_id'] );

			$tax_rate = 100 * ( $values['line_tax'] / $values['line_total'] );

			$articles[] = array(
				'id'          => $values['product_id'],
				'sku'         => $product->get_sku(),
				'name'        => $values['name'],
				'description' => $product->get_post_data()->post_content,
				'url'         => $product->get_permalink(),
				'image_url'   => wp_get_attachment_url( get_post_thumbnail_id( $values['product_id'] ) ),
				'quantity'    => (int) $values['qty'],
				'price'       => Aplazame_Filters::decimals( $values['line_total'] ) / (int) $values['qty'],
				'tax_rate'    => Aplazame_Filters::decimals( $tax_rate ),
			);
		}

		return $articles;
	}

	/**
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public function get_user( $user ) {
		$dateJoined = new DateTime( $user->user_registered );

		return array(
			'id'          => (string) $user->ID,
			'type'        => 'e',
			'gender'      => 0,
			'email'       => $user->user_email,
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'date_joined' => $dateJoined->format( DATE_ISO8601 ),
		);
	}

	/**
	 * @param string $billing_email
	 *
	 * @return array
	 */
	public function get_customer( $billing_email ) {
		return array(
			'type'   => 'n',
			'gender' => 0,
			'email'  => $billing_email,
		);
	}

	/**
	 * @param WC_Order $order
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_address( $order, $type ) {
		$_field = function ( $name ) use ( $order, $type ) {
			$_key = ( $type . '_' . $name );

			return $order->$_key;
		};

		$serializer = array(
			'first_name'       => $_field( 'first_name' ),
			'last_name'        => $_field( 'last_name' ),
			'phone'            => $_field( 'phone' ),
			'street'           => $_field( 'address_1' ),
			'address_addition' => $_field( 'address_2' ),
			'city'             => $_field( 'city' ),
			'state'            => $_field( 'state' ),
			'country'          => $_field( 'country' ),
			'postcode'         => $_field( 'postcode' ),
		);

		return $serializer;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_shipping_info( $order ) {
		$total = $order->get_total_shipping();

		if ( $total ) {
			$tax_rate = 100 * $order->order_shipping_tax / $total;
		} else {
			$tax_rate = 0;
		}

		$serializer = array_merge( $this->get_address( $order, 'shipping' ), array(
			'price'    => Aplazame_Filters::decimals( $total ),
			'tax_rate' => Aplazame_Filters::decimals( $tax_rate ),
			'name'     => $order->get_shipping_method(),
		) );

		return $serializer;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_order( $order ) {
		$serializer = array(
			'id'           => (string) $order->id,
			'articles'     => $this->get_articles( $order->get_items() ),
			'currency'     => get_woocommerce_currency(),
			'total_amount' => Aplazame_Filters::decimals( $order->get_total() ),
			'discount'     => Aplazame_Filters::decimals( $order->get_total_discount() ),
		);

		return $serializer;
	}

	/**
	 * @param WC_Order $order
	 * @param string $checkout_url
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public function get_checkout( $order, $checkout_url, $user ) {
		$serializer = array(
			'toc'      => true,
			'merchant' => array(
				'confirmation_url' => home_url( add_query_arg( 'action', 'confirm' ) ),
				'cancel_url'       => html_entity_decode( $order->get_cancel_order_url() ),
				'checkout_url'     => html_entity_decode( $order->get_cancel_order_url( $checkout_url ) ),
				'success_url'      => html_entity_decode( $order->get_checkout_order_received_url() ),
			),
			'customer' => $user->ID ? $this->get_user( $user ) : $this->get_customer( $order->billing_email ),
			'order'    => $this->get_order( $order ),
			'billing'  => $this->get_address( $order, 'billing' ),
			'meta'     => static::get_meta(),
		);

		$shipping_method = $order->get_shipping_method();

		if ( ! empty( $shipping_method ) ) {
			$serializer['shipping'] = $this->get_shipping_info( $order );
		}

		return $serializer;
	}

	/**
	 * @param array $qs
	 *
	 * @return array
	 */
	public function get_history( $qs ) {
		$orders = array();

		foreach ( $qs as $item => $values ) {
			$order     = new WC_Order( $qs[ $item ]->ID );
			$orderDate = new DateTime( $order->order_date );

			$orders[] = array(
				'id'         => (string) $order->id,
				'amount'     => Aplazame_Filters::decimals( $order->get_total() ),
				'due'        => '',
				'status'     => $order->get_status(),
				'type'       => Aplazame_Helpers::get_payment_method( $order->id ),
				'order_date' => $orderDate->format( DATE_ISO8601 ),
				'currency'   => $order->get_order_currency(),
				'billing'    => $this->get_address( $order, 'billing' ),
				'shipping'   => $this->get_shipping_info( $order ),
			);
		}

		return $orders;
	}
}
