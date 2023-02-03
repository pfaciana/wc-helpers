<?php

/**
 * Plugin Name: Render WC Helpers
 * Plugin URI: https://renderdev.com
 * Author: Render Web Development
 * Author URI: https://renderdev.com
 * Description: Additional classes and functions to help with writing code for WooCommerce
 * Version: 1.1.0
 * Requires: 5.5.1
 * Tested: 5.5.1
 */

defined( 'ABSPATH' ) || exit;

require_once ( __DIR__ ) . '/vendor/autoload.php';

// Include the main WooCommerce class.
if ( !class_exists( 'RenderWooCommerce', FALSE ) ) {
	include_once ( __DIR__ ) . '/includes/class-renderwoocommerce.php';
}

if ( !function_exists( 'RWC' ) ) {
	function RWC ()
	{
		return RenderWooCommerce::get_instance();
	}

	$GLOBALS['renderwoocommerce'] = RWC();
}