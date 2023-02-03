<?php

namespace RWC;

class Product_Data_Group
{
	use Product_Data_Helpers;
	use Product_Data_Fields;

	protected $id;

	/**
	 * @param string|array $args          {
	 * @type string        $id            unique id
	 * @type string|array  $conditionals  tab conditional classes
	 * @type string|array  $wrapper_class group classes
	 * @type int           $priority      action order priority
	 * @type string        $panel         panel id to attach to
	 * @type string        $hook          alternative to attaching to a group by id
	 *                                    }
	 */
	public function __construct ( $args )
	{
		$this->id = $args['id'];

		$this->addGroup( $args );
	}

	/**
	 * Add a group to a product data panel
	 *
	 * @param string|array $group         {
	 * @type string        $id            unique id
	 * @type string|array  $conditionals  tab conditional classes
	 * @type string|array  $wrapper_class group classes
	 * @type int           $priority      action order priority
	 * @type string        $panel         panel id to attach to
	 * @type string        $hook          alternative to attaching to a group by id
	 *                                    }
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

		add_action( $this->getActionTag( $group ), function () use ( $group ) {
			$group['wrapper_class'] = $this->getClassString( $group['wrapper_class'] );
			$group['conditionals']  = $this->getClassString( $group['conditionals'] );
			?>
			<div id="<?= $group['id'] ?>_product_data_group" class="options_group <?= $group['wrapper_class'] ?> <?= $group['conditionals'] ?>">
				<?php do_action( $this->getActionTag( [ 'group' => $this->id ] ), $this->id, $group ) ?>
			</div>
			<?php
		}, $group['priority'] );
	}

	/**
	 * Hook into the Group html output for adding custom html
	 *
	 * Shortcut for the group hook
	 * NOTE: This does not create or validate any fields
	 *
	 * @see Product_Data_Group::addGroup()
	 *
	 * @param callable $callback callback for when the group hook is run
	 *                           Signature: $callback( string $group_id, array $group )
	 * @param int      $priority hook priority
	 *
	 * @return void
	 */
	public function onLoad ( $callback, $priority = 10 )
	{
		add_action( $this->getActionTag( [ 'group' => $this->id ] ), function ( $group_id, $group ) use ( $callback ) {
			$callback( ...func_get_args() );
		}, $priority, 2 );
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
	 * @see Product_Data_Field
	 *
	 * @param string|array $args      {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( int $post_id, WP_Post $post, string $value, array $field )
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, string $id )
	 *
	 * @return Product_Data_Field
	 */
	public function addField ( $args, callable $callback )
	{
		$args = wp_parse_args( $this->normalizeArgs( $args ), [ 'group' => $this->id ] );

		return new Product_Data_Field( $args, $callback );
	}
}