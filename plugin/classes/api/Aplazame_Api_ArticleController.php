<?php

final class Aplazame_Api_ArticleController {
	public function articles( array $queryArguments ) {
		$page      = ( isset( $queryArguments['page'] ) ) ? $queryArguments['page'] : 1;
		$page_size = ( isset( $queryArguments['page_size'] ) ) ? $queryArguments['page_size'] : 10;

		/** @var WP_Post[] $products */
		$products = get_posts( array(
			'post_type'   => 'product',
			'numberposts' => $page_size,
			'offset'      => ($page - 1) * $page_size,
		) );

		$articles = array();

		foreach ( $products as $product ) {
			$articles[] = Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct( new WC_Product( $product ) );
		}

		return Aplazame_Api_Router::collection( $page, $page_size, $articles );
	}
}
