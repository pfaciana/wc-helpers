<?php

namespace RWC;

class Product_Data_Field
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
	 * new Product_Data_Field(['id' => 'some_field_id', 'panel' => 'some_panel_id'], function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * ```
	 *
	 * @param string|array $args      {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( int $post_id, WP_Post $post, string $value, array $field )
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, string $id )
	 */
	public function __construct ( $args, callable $callback )
	{
		$this->addField( $this->normalizeArgs( $args ), $callback );
	}

	/**
	 * Add a field to a product data group or panel
	 *
	 * @param string|array $field     {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( int $post_id, WP_Post $post, string $value, array $field )
	 * @type string        $group     group id to attach to
	 * @type string        $panel     panel id to attach to
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, string $id )
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

		add_action( $this->getActionTag( $field ), function () use ( $field, $callback ) {
			$callback( [ 'id' => $this->id ], $this->id );
		}, $field['priority'] );

		if ( is_null( $field['filter_cb'] ) ) {
			add_action( 'woocommerce_process_product_meta', [ $this, 'defaultFieldFilter' ], 10, 2 );
		}
		elseif ( !empty( $field['filter_cb'] ) && is_callable( $field['filter_cb'] ) ) {
			add_action( 'woocommerce_process_product_meta', function ( $post_id, $post ) use ( $field ) {
				$postKey = $field['post_key'] = $this->id;
				$value   = isset( $_POST[$postKey] ) ? $_POST[$postKey] : NULL;

				return $field['filter_cb']( $post_id, $post, $value, $field );
			}, 10, 2 );
		}
	}

	/**
	 * Simple product post meta updater
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function defaultFieldFilter ( $post_id, $post )
	{
		if ( empty( $this->id ) ) {
			return;
		}

		if ( isset( $_POST[$this->id] ) ) {
			update_post_meta( $post_id, $this->id, $_POST[$this->id] );
		}
		else {
			delete_post_meta( $post_id, $this->id );
		}
	}
}