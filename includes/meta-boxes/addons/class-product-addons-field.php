<?php

namespace RWC;

class Product_Addons_Field
{
	use Product_Data_Helpers;

	protected $id;

	/**
	 * ```php
	 *
	 * // Option 1 - just id as the arg and a callback
	 * new Product_Addons_Field('some_field_id', function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * // Option 2 - args array and a callback
	 * new Product_Addons_Field(['id' => 'some_field_id', 'panel' => 'some_panel_id'], function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * ```
	 *
	 * @param string|array $args      {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( array $data, int $index, string $value, array $field )
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 */
	public function __construct ( $args, callable $callback )
	{
		$this->addField( $this->normalizeArgs( $args ), $callback );
	}

	/**
	 * Add a field to an addons group or panel
	 *
	 * @param array    $field         {
	 * @type callable  $filter_cb     custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( array $data, int $index, string $value, array $field )
	 * @type string    $group         group id to attach to
	 * @type string    $panel         panel id to attach to
	 * @type string    $hook          alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable $callback      callback for when the field hook is run
	 *                                Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 */
	protected function addField ( $field, callable $callback )
	{
		$field = wp_parse_args( $field, [
			'filter_cb' => NULL,
			'group'     => NULL,
			'panel'     => NULL,
			'hook'      => NULL,
			'priority'  => 10,
		] );

		$this->id = $field['id'] ?? NULL;

		add_action( $this->getActionTag( $field ), function ( $post, $addon, $loop ) use ( $field, $callback ) {
			$callback( [ 'id' => $this->id, 'post' => $post, 'addon' => $addon, 'loop' => $loop ], ...func_get_args() );
		}, $field['priority'], 3 );

		if ( is_null( $field['filter_cb'] ) ) {
			add_filter( 'woocommerce_product_addons_save_data', [ $this, 'defaultFieldFilter' ], 10, 2 );
		}
		elseif ( !empty( $field['filter_cb'] ) && is_callable( $field['filter_cb'] ) ) {
			add_filter( 'woocommerce_product_addons_save_data', function ( $data, $i ) use ( $field ) {
				$postKey = $field['post_key'] = 'product_addon_' . $this->id;
				$value   = isset( $_POST[$postKey][$i] ) ? $_POST[$postKey][$i] : NULL;

				return $field['filter_cb']( $data, $i, $value, $field );
			}, 10, 2 );
		}
	}

	/**
	 * Simple product addon post meta updater
	 *
	 * @param array $data product addon post meta
	 * @param int   $i    product addon loop id
	 */
	public function defaultFieldFilter ( $data, $i )
	{
		if ( empty( $this->id ) ) {
			return $data;
		}

		if ( isset( $_POST['product_addon_' . $this->id][$i] ) ) {
			$data[$this->id] = $_POST['product_addon_' . $this->id][$i];
		}
		else {
			unset( $data[$this->id] );
		}

		return $data;
	}
}