<?php

namespace RWC;

trait Product_Data_Helpers
{
	/**
	 * Generate the action hook prefix depending on which class is using this trait
	 *
	 * @return string the hook prefix to prepend to an action hook name
	 */
	protected function getHookPrefix ()
	{
		$type = 'product';
		$id   = 'options';

		$class = get_class( $this );

		if ( substr( $class, 0, 19 ) === 'RWC\Product_Addons_' ) {
			$id = 'addons';
		}

		if ( substr( $class, 0, 19 ) === 'RWC\Variation_Data_' ) {
			$type = 'variation';
		}

		return "woocommerce_{$type}_{$id}_";
	}

	/**
	 * Get the action hook name for a given generic action, group or panel
	 *
	 * @param string|array $args  {
	 * @type string        $hook  the name of the hook
	 * @type string        $group the group name to set the hook to
	 * @type string        $panel the panel name to set the hook to
	 *                            }
	 *
	 * @return string the hook name
	 */
	protected function getActionTag ( $args )
	{
		if ( !empty( $args['hook'] ) ) {
			return $args['hook'];
		}

		if ( !empty( $args['group'] ) ) {
			return $this->getHookPrefix() . 'group_' . $args['group'];
		}

		return $this->getHookPrefix() . 'panel_' . $args['panel'];
	}

	/**
	 * Convert a string or array of classes to a string
	 *
	 * @param string|array $classes classes to convert to string
	 *
	 * @return string classes as a string
	 */
	protected function getClassString ( $classes = [] )
	{
		empty( $classes ) && ( $classes = '' );
		is_array( $classes ) && ( $classes = implode( ' ', $classes ) );

		return trim( $classes );
	}

	/**
	 * Add attributes to a variation post object
	 *
	 * Find all post meta for a given variation post and add all the post meta that
	 * starts with 'attribute_' to the variation post as an array of attributes
	 *
	 * @param \WP_Post $variation      the variation post object
	 * @param array    $variation_data the variation post meta
	 *
	 * @return \WP_Post the variation post object with the attributes added
	 */
	protected function addVariationAttributes ( $variation, $variation_data )
	{
		$variation->attributes = [];

		foreach ( $variation_data as $key => $value ) {
			if ( stripos( $key, 'attribute_' ) === 0 && !empty( $name = substr( $key, strlen( 'attribute_' ) ) ) ) {
				$variation->attributes[$name] = $value;
			}
		}

		return $variation;
	}

	/**
	 * Normalize the $args parameter
	 *
	 * If $args is a scalar, convert it to an array with the key 'id' having the input value
	 * Otherwise just return $args unaltered
	 *
	 * @param string|array $args
	 *
	 * @return array
	 */
	protected function normalizeArgs ( $args )
	{
		// shorthand
		if ( !empty( $args ) && is_scalar( $args ) ) {
			$args = [ 'id' => $args ];
		}

		return $args;
	}
}