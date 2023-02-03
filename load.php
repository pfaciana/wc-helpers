<?php

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