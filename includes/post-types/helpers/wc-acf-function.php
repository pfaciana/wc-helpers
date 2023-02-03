<?php

/**
 * Convert ACF field to WooCommerce Checkout fields
 *
 * Used when adding ACF fields inside WooCommerce pages and products
 *
 * @param array $field ACF generated form field
 *
 * @return array WooCommerce form field
 */
function wc_from_acf_field ( $field )
{
	$types = [
		'button_group'     => 'radio',
		'true_false'       => 'checkbox',
		'date_picker'      => 'date',
		'date_time_picker' => 'datetime-local',
		'time_picker'      => 'time',
	];

	$args = array_merge( $field, $field['wrapper'] );

	if ( array_key_exists( $field['type'], $types ) ) {
		$args['type'] = $types[$field['type']];
	}

	$args['options'] = $field['choices'] ?? [];

	if ( !empty( $args['options'] ) && !empty( $field['allow_null'] ) ) {
		foreach ( $args['options'] as $key => $value ) {
			unset( $args['options'][$key] );
			$args['options'] = [ NULL => $value ] + $args['options'];
			break;
		}
	}

	$args['default'] = $field['default_value'] ?: '';

	$args['class'] = !empty( $field['class'] ) ? explode( ' ', $args['class'] ) : [];

	$args['custom_attributes'] = [];

	if ( !empty( $field['prepend'] ) ) {
		$args = array_merge( $args, json_decode( $field['prepend'], TRUE ) );
	}

	if ( !empty( $field['min'] ) ) {
		$args['custom_attributes']['min'] = $field['min'];
	}

	if ( !empty( $field['max'] ) ) {
		$args['custom_attributes']['max'] = $field['max'];
	}

	if ( !empty( $field['step'] ) ) {
		$args['custom_attributes']['step'] = $field['step'];
	}

	if ( !empty( $field['multiple'] ) ) {
		$args['custom_attributes']['multiple'] = 'multiple';
	}

	if ( !empty( $field['append'] ) ) {
		$args['custom_attributes'] = array_merge( $args['custom_attributes'], json_decode( $field['append'], TRUE ) );
	}

	unset( $args['ID'] );
	unset( $args['key'] );
	unset( $args['wrapper'] );
	unset( $args['choices'] );

	return $args;
}
