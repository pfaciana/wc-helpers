<?php

/**
 * Get WooCommerce Orders by user
 *
 * @param int|null $user_id If NULL, current user ID will be used
 * @param int      $max     Max number of orders to return, -1 returns all. Defaults to `10`
 * @param string   $status  Order post status. Defaults to `any`
 * @param array    $args    Additional arguments to pass to `get_posts()`
 *
 * @return WP_Post[]|false Array of WP_Post objects or FALSE if user ID is missing
 */
function wc_get_user_orders ( $user_id = NULL, $max = 10, $status = 'any', $args = [] )
{
	$user_id = $user_id ?: get_current_user_id();

	if ( empty( $user_id ) ) {
		return FALSE;
	}

	$args = [
			'numberposts' => $max,
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_type'   => 'shop_order',
			'post_status' => $status,
		] + $args;

	return get_posts( $args );
}

/**
 * Check if user is a new customer
 *
 * @param int    $user_id If NULL, current user ID will be used
 * @param string $status  Order post status. Defaults to `any`
 *
 * @return bool
 */
function wc_is_new_customer ( $user_id = NULL, $status = 'any' )
{
	return empty( wc_get_user_orders( $user_id, 1, $status ) );
}

/**
 * Check if user is a returning customer
 *
 * @param int    $user_id If NULL, current user ID will be used
 * @param string $status  Order post status. Defaults to `any`
 *
 * @return bool
 */
function wc_is_returning_customer ( $user_id = NULL, $status = 'any' )
{
	return !wc_is_new_customer( $user_id, $status );
}