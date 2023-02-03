<?php

/**
 * Attempt to determine the product ID from a variety of product representations.
 *
 * This finds the lowest level of the product, i.e. the variation if a variation is sent
 * If you need the parent product id, use wc_get_root_product()
 *
 * @see wc_get_root_product()
 *
 * @param mixed $product can be a post ID, a variation ID, a WP_Post object, a WC_Cart array, a WC_Order_Item object, or a WC_Product object
 *
 * @return int|FALSE $product Post ID, or false if not found
 */
function wc_get_product_id ( $product )
{
	if ( is_int( $product ) || ctype_digit( $product ) ) {
		return (int) $product;
	}

	if ( is_array( $product ) ) {
		// WooCommerce cart_item
		if ( array_key_exists( 'data', $product ) && !empty( $product['data']->id ) ) {
			return (int) $product['data']->id;
		}
		// WooCommerce cart_item
		if ( array_key_exists( 'product_id', $product ) ) {
			return (int) $product['product_id'];
		}
		if ( !empty( $product['ID'] ) ) {
			return (int) $product['ID'];
		}
		if ( !empty( $product['id'] ) ) {
			return (int) $product['id'];
		}
	}

	if ( is_object( $product ) ) {
		if ( !empty( $product->ID ) ) {
			return (int) $product->ID;
		}
		// WooCommerce order_item
		if ( method_exists( $product, 'get_product_id' ) ) {
			return (int) $product->get_product_id();
		}
		// WooCommerce product
		if ( method_exists( $product, 'get_id' ) ) {
			return (int) $product->get_id();
		}
		if ( property_exists( $product, 'id' ) && !empty( $product->id ) ) {
			return (int) $product->id;
		}
	}

	return FALSE;
}

/**
 * Attempt to determine the product from a variety of product representations.
 *
 * This finds the highest level of the product, i.e. the product page if a variation is sent
 * If you need the variation id, use wc_get_product_id()
 *
 * @see wc_get_product_id()
 *
 * @param mixed $product can be a post ID, a variation ID, a WP_Post object, a WC_Cart array, a WC_Order_Item object, or a WC_Product object
 *
 * @return WC_Product|FALSE $product Post ID, or false if not found
 */
function wc_get_root_product ( $post )
{
	if ( !$post instanceof WP_Post ) {
		$post = wc_get_product_id( $post );
	}

	if ( !( $post = get_post( $post ) ) ) {
		return FALSE;
	}

	if ( !empty( $post->post_parent ) ) {
		$post = wc_get_root_product( (int) $post->post_parent );
	}

	return $post;
}

/**
 * Get the name of a variation
 *
 * @param mixed  $variation can be a post ID, a variation ID, a WP_Post object, a WC_Cart array, a WC_Order_Item object, or a WC_Product object
 * @param string $separator glue/join string for multiple attributes. Defaults to ' - '
 *
 * @return string variation name
 */
function wc_get_variation_name ( $variation, $separator = ' - ' )
{
	$options = array_filter( (array) get_post_meta( wc_get_root_product( $variation )->ID, '_product_attributes', TRUE ) );

	$attributes = [];
	foreach ( wc_get_product( $variation )->get_variation_attributes() as $key => $value ) {
		$attributes[] = "{$options[substr($key, strlen('attribute_'))]['name']}: {$value}";
	}

	return implode( $separator, $attributes );
}

/**
 * Get all the variation names for array of variations
 *
 * @param mixed[] $variations can be an array of a post ID, a variation ID, a WP_Post object, a WC_Cart array, a WC_Order_Item object, or a WC_Product object
 *
 * @return string[] variation names
 */
function wc_get_variation_names ( $variations )
{
	$names = [];
	foreach ( $variations as $variation ) {
		$names[] = wc_get_variation_name( $variation );
	}

	return $names;
}

/**
 * Get the available variations for a product
 *
 * @see wc_get_root_product()
 *
 * @param mixed  $product     can be a post ID, a variation ID, a WP_Post object, a WC_Cart array, a WC_Order_Item object, or a WC_Product object
 * @param string $return      The format to return the results in. Can be 'array' to return an array of variation data or 'objects' for the product objects. Default 'array'.
 * @param bool   $clear_cache whether to force clearing the cache. Defaults to `FALSE`
 * @return mixed
 */
function wc_get_available_variations ( $product, $return = 'array', $clear_cache = FALSE )
{
	static $cache = [];

	$product_id = wc_get_product_id( $product );

	if ( !$clear_cache && array_key_exists( $product_id, $cache ) ) {
		$available_variations = $cache[$product_id];

		if ( 'array' === $return ) {
			$available_variations = array_values( array_filter( $available_variations ) );
		}

		return $available_variations;
	}

	$product = wc_get_product( $product_id );

	$available_variations = $cache[$product_id] = $product->get_available_variations();

	if ( 'array' === $return ) {
		$available_variations = array_values( array_filter( $available_variations ) );
	}

	return $available_variations;
}