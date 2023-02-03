<?php

namespace RWC;

class Product_Addons_Group
{
	use Product_Data_Helpers;
	use Product_Data_Fields;

	protected $id;

	/**
	 * @param string|array $args          {
	 * @type string        $id            unique id
	 * @type string|array  $conditionals  tab conditional classes
	 * @type string|array  $wrapper_class panel classes
	 * @type int           $priority      action order priority
	 * @type string        $panel         panel id to attach to
	 * @type string        $hook          alternative to attaching to a panel by id
	 *                                    }
	 */
	public function __construct ( $args = NULL )
	{
		if ( !empty( $args ) && is_scalar( $args ) ) {
			$args = [ 'id' => $args ];
		}

		$args['id'] = !empty( $args['id'] ) ? $args['id'] : uniqid( 'generated_' );

		$this->id = $args['id'];

		$this->addGroup( $args );
	}

	/**
	 * Add a group to a addon panel
	 *
	 * @param array       $group         {
	 * @type string       $id            unique id
	 * @type string|array $conditionals  tab conditional classes
	 * @type string|array $wrapper_class panel classes
	 * @type int          $priority      action order priority
	 * @type string       $panel         panel id to attach to
	 * @type string       $hook          alternative to attaching to a panel by id
	 *                                   }
	 */
	protected function addGroup ( $group )
	{
		$group = wp_parse_args( $group, [
			'id'            => NULL,
			'conditionals'  => [],
			'wrapper_class' => [],
			'priority'      => 10,
			'panel'         => NULL,
			'hook'          => NULL,
		] );

		add_action( $this->getActionTag( $group ), function ( $post, $addon, $loop ) use ( $group ) {
			$group['wrapper_class'] = $this->getClassString( $group['wrapper_class'] );
			$group['conditionals']  = $this->getClassString( $group['conditionals'] );
			?>
			<div id="<?= $this->id ?>_product_addons_group" class="product_addons_group <?= $group['wrapper_class'] ?> <?= $group['conditionals'] ?>">
				<?php do_action( $this->getActionTag( [ 'group' => $this->id ] ), ...func_get_args() ) ?>
			</div>
			<?php
		}, $group['priority'], 3 );
	}

	/**
	 * Hook into the Group html output for adding custom html
	 *
	 * Shortcut for the group hook
	 * NOTE: This does not create or validate any fields
	 *
	 * @see Variation_Data_Group::addGroup()
	 *
	 * @param callable $callback callback for when the group hook is run
	 *                           Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 * @param int      $priority hook priority
	 *
	 * @return void
	 */
	public function onLoad ( $callback, $priority = 10 )
	{
		add_action( $this->getActionTag( [ 'group' => $this->id ] ), function ( $post, $addon, $loop ) use ( $callback ) {
			$callback( [ 'addon' => $addon, 'loop' => $loop, 'post' => $post ], ...func_get_args() );
		}, $priority, 3 );
	}

	/**
	 * Add a field to this group
	 *
	 * ```php
	 *
	 * // Option 1 - field id and callback params
	 * $group->addField('some_field_id', function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * // Option 2 - args array and a callback
	 * $group->addField(['id' => 'some_field_id', 'hook' => 'some_custom_hook'], function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * ```
	 *
	 * @see Product_Addons_Field
	 *
	 * @param string|array $args      {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( array $data, int $index, string $value, array $field )
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $group, WP_Post $post, array $addon, int $loop )
	 *
	 * @return Product_Addons_Field
	 */
	public function addField ( $args, callable $callback )
	{
		$args = wp_parse_args( $this->normalizeArgs( $args ), [ 'group' => $this->id ] );

		return new Product_Addons_Field( $args, $callback );
	}
}