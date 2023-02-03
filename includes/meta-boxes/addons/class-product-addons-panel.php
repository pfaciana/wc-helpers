<?php

namespace RWC;

class Product_Addons_Panel
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
	 *                                    }
	 */
	public function __construct ( $args = [] )
	{
		if ( !empty( $args ) && is_scalar( $args ) ) {
			$args = [ 'id' => $args ];
		}

		$args['id'] = !empty( $args['id'] ) ? $args['id'] : uniqid( 'generated_' );

		$this->id = $args['id'];

		$this->addPanel( $args );
	}

	/**
	 * Create an addon panel
	 *
	 * @param string|array $panel         {
	 * @type string        $id            unique id
	 * @type string|array  $conditionals  tab conditional classes
	 * @type string|array  $wrapper_class panel classes
	 * @type int           $priority      action order priority. Default to `10`
	 *                                    }
	 */
	protected function addPanel ( $panel )
	{
		$panel = wp_parse_args( $panel, [
			'id'            => NULL,
			'conditionals'  => [],
			'wrapper_class' => [],
			'priority'      => 10,
		] );

		add_action( 'woocommerce_product_addons_panel_before_options', function ( $post, $addon, $loop ) use ( $panel ) {
			$panel = wp_parse_args( $panel, [ 'addon' => $addon, 'loop' => $loop, 'post' => $post ] );
			$this->dataPanels( $panel, $post, $addon, $loop );
		}, $panel['priority'], 3 );
	}

	/**
	 * Output an addon panel
	 *
	 * Hook: woocommerce_product_addons_panel_before_options
	 *
	 * @param array       $panel          {
	 * @type string       $id             unique id
	 * @type string|array $conditionals   tab conditional classes
	 * @type string|array $wrapper_class  panel classes
	 * @type int          $priority       action order priority. Default to `10`
	 *                                    }
	 * @param \WP_Post    $post           the WP_Post object for this product
	 * @param array       $addon          the addon meta data
	 * @param int         $loop           the addon index in the list of variations
	 * @return void
	 */
	protected function dataPanels ( $panel, $post, $addon, $loop )
	{
		$panel['wrapper_class'] = $this->getClassString( $panel['wrapper_class'] );
		$panel['conditionals']  = $this->getClassString( $panel['conditionals'] );
		?>
		<div id="<?= $this->id ?>_product_addons" class="rwc-pao-addons-settings wc-pao-addons-secondary-settings woocommerce_product_addons_panel <?= $panel['wrapper_class'] ?> <?= $panel['conditionals'] ?>">
			<?php do_action( $this->getActionTag( [ 'panel' => $this->id ] ), $post, $addon, $loop ); ?>
		</div>
		<?php
	}

	/**
	 * Hook into the Panel html output for adding custom html
	 *
	 * Shortcut for the panel hook
	 * NOTE: This does not create or validate any fields
	 *
	 * @see Variation_Data_Panel::addPanel()
	 *
	 * @param callable $callback callback for when the panel hook is run
	 *                           Signature: $callback( array $panel, WP_Post $post, array $addon, int $loop )
	 * @param int      $priority hook priority
	 *
	 * @return void
	 */
	public function onLoad ( $callback, $priority = 10 )
	{
		add_action( $this->getActionTag( [ 'panel' => $this->id ] ), function ( $post, $addon, $loop ) use ( $callback ) {
			$callback( [ 'addon' => $addon, 'loop' => $loop, 'post' => $post ], ...func_get_args() );
		}, $priority, 3 );
	}

	/**
	 * Add a group to this panel
	 *
	 * ```php
	 *
	 * // Option 1 - no args, auto generate group id
	 * $panel->addGroup();
	 *
	 * // Option 2 - just the group id
	 * $panel->addGroup('some_group_id');
	 *
	 * // Option 3 - args array param
	 * $panel->addGroup([
	 *     'id'           => 'some_group_id',
	 *     'conditionals' => 'show_if_something',
	 * ]);
	 *
	 * ```
	 *
	 * @see Product_Addons_Group
	 *
	 * @param string|array $args          {
	 * @type string        $id            unique id
	 * @type string|array  $conditionals  tab conditional classes
	 * @type string|array  $wrapper_class panel classes
	 * @type int           $priority      action order priority
	 * @type string        $hook          alternative to attaching to a panel by id
	 *                                    }
	 *
	 * @return Product_Addons_Group
	 */
	public function addGroup ( $args = [] )
	{
		if ( is_scalar( $args ) ) {
			$args = [ 'id' => $args ];
		}

		$args = wp_parse_args( $args, [ 'panel' => $this->id ] );

		$args['id'] = !empty( $args['id'] ) ? $args['id'] : uniqid( 'generated_' );

		return new Product_Addons_Group( $args );
	}

	/**
	 * Add a field to this panel
	 *
	 * ```php
	 *
	 * // Option 1 - field id and callback params
	 * $panel->addField('some_field_id', function($args) {
	 *      wc_addons_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * // Option 2 - args array and a callback
	 * $panel->addField(['id' => 'some_field_id', 'hook' => 'some_custom_hook'], function($args) {
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
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return Product_Addons_Field
	 */
	public function addField ( $args, callable $callback )
	{
		$args = wp_parse_args( $this->normalizeArgs( $args ), [ 'panel' => $this->id ] );

		return new Product_Addons_Field( $args, $callback );
	}
}