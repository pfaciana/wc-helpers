<?php

namespace RWC;

class Variation_Data_Field
{
	use Product_Data_Helpers;

	protected $id;

	/**
	 * ```php
	 *
	 * // Option 1 - just id as the arg and a callback
	 * new Variation_Addons_Field('some_field_id', function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * // Option 2 - args array and a callback
	 * new Variation_Data_Field(['id' => 'some_field_id', 'panel' => 'some_panel_id'], function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * ```
	 *
	 * @param string|array $args      {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( int $variation_id, int $loop, string $value, array $field )
	 * @type string        $group     group id to attach to
	 * @type string        $panel     panel id to attach to
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 */
	public function __construct ( $args, callable $callback )
	{
		$this->addField( $this->normalizeArgs( $args ), $callback );
	}

	/**
	 * Add a field to a variation group or panel
	 *
	 * @param string|array $field     {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( int $variation_id, int $loop, string $value, array $field )
	 * @type string        $group     group id to attach to
	 * @type string        $panel     panel id to attach to
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 */
	protected function addField ( $field, callable $callback )
	{
		$field = wp_parse_args( $field, [
			'filter_cb' => NULL,
			'group'     => NULL,
			'hook'      => NULL,
			'priority'  => 10,
		] );

		$this->id = $field['id'] ?? NULL;

		add_action( $this->getActionTag( $field ), function ( $loop, $variation_data, $variation ) use ( $field, $callback ) {
			$variation = $this->addVariationAttributes( $variation, $variation_data );
			$callback( [ 'id' => $this->id, 'variation' => $variation, 'loop' => $loop ], $variation, $loop, $this->id );
		}, $field['priority'], 3 );

		if ( is_null( $field['filter_cb'] ) ) {
			add_action( 'woocommerce_save_product_variation', [ $this, 'defaultFieldFilter' ], 10, 2 );
		}
		elseif ( !empty( $field['filter_cb'] ) && is_callable( $field['filter_cb'] ) ) {
			add_action( 'woocommerce_save_product_variation', function ( $variation_id, $loop ) use ( $field ) {
				$postKey = $field['post_key'] = $this->id;
				$value   = isset( $_POST[$postKey][$loop] ) ? $_POST[$postKey][$loop] : NULL;

				return $field['filter_cb']( $variation_id, $loop, $value, $field );
			}, 10, 2 );
		}
	}

	/**
	 * Simple post meta updater
	 *
	 * @param int $variation_id
	 * @param int $loop
	 */
	public function defaultFieldFilter ( $variation_id, $loop )
	{
		if ( empty( $this->id ) ) {
			return;
		}

		if ( isset( $_POST[$this->id][$loop] ) ) {
			update_post_meta( $variation_id, $this->id, $_POST[$this->id][$loop] );
		}
		else {
			delete_post_meta( $variation_id, $this->id );
		}
	}
}