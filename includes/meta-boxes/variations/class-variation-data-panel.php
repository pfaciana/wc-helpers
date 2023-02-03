<?php

namespace RWC;

class Variation_Data_Panel
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

		$this->addPanel( $args );
	}

	/**
	 * Create a variation panel
	 *
	 * @param array       $panel          {
	 * @type string       $id             unique id
	 * @type string|array $conditionals   tab conditional classes
	 * @type string|array $wrapper_class  panel classes
	 * @type int          $priority       action order priority
	 * @type string       $hook           alternative to attaching to a panel by id
	 *                                    }
	 */
	protected function addPanel ( $panel )
	{
		$panel = wp_parse_args( $panel, [
			'id'            => NULL,
			'conditionals'  => [],
			'wrapper_class' => [],
			'priority'      => 10,
			'hook'          => 'woocommerce_product_after_variable_attributes',
		] );

		add_action( $this->getActionTag( $panel ), function ( $loop, $variation_data, $variation ) use ( $panel ) {
			$this->dataPanels( $panel, ...func_get_args() );
		}, $panel['priority'], 3 );
	}

	/**
	 * Output a variation panel
	 *
	 * @param array       $panel          {
	 * @type string       $id             unique id
	 * @type string|array $conditionals   tab conditional classes
	 * @type string|array $wrapper_class  panel classes
	 * @type int          $priority       action order priority
	 * @type string       $hook           alternative to attaching to a panel by id
	 *                                    }
	 * @param int         $loop           the variation index in the list of variations
	 * @param array       $variation_data the variation post meta
	 * @param \WP_Post    $variation      the variation post object
	 * @return void
	 */
	protected function dataPanels ( $panel, $loop, $variation_data, $variation )
	{
		$panel['conditionals']  = $this->getClassString( $panel['conditionals'] );
		$panel['wrapper_class'] = $this->getClassString( $panel['wrapper_class'] );
		?>
		<div id="<?= $panel['id'] ?>_variation_data_panel" class="variation_options_panel <?= $panel['wrapper_class'] ?> <?= $panel['conditionals'] ?>">
			<?php do_action( $this->getActionTag( [ 'panel' => $this->id ] ), $loop, $variation_data, $variation ) ?>
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
	 *                           Signature: $callback ( array $panel, int $loop, array $variation_data, WC_Product_Variation $variation )
	 * @param int      $priority hook priority
	 *
	 * @return void
	 */
	public function onLoad ( $callback, $priority = 10 )
	{
		add_action( $this->getActionTag( [ 'panel' => $this->id ] ), function ( $loop, $variation_data, $variation ) use ( $callback ) {
			$callback( [ 'variation_data' => $variation_data, 'loop' => $loop, 'variation' => $variation ], ...func_get_args() );
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
	 * @see Variation_Data_Group
	 *
	 * @param string|array $args          {
	 * @type string        $id            unique id
	 * @type string|array  $conditionals  tab conditional classes
	 * @type string|array  $wrapper_class group classes
	 * @type int           $priority      action order priority
	 * @type string        $hook          alternative to attaching to a group by id
	 *                                    }
	 *
	 * @return Variation_Data_Group
	 */
	public function addGroup ( $args = [] )
	{
		if ( is_scalar( $args ) ) {
			$args = [ 'id' => $args ];
		}

		$args = wp_parse_args( $args, [ 'panel' => $this->id ] );

		$args['id'] = !empty( $args['id'] ) ? $args['id'] : uniqid( 'generated_' );

		return new Variation_Data_Group( $args );
	}

	/**
	 * Add a field to this panel
	 *
	 * ```php
	 *
	 * // Option 1 - field id and callback params
	 * $panel->addField('some_field_id', function($args) {
	 *      wc_variations_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * // Option 2 - args array and a callback
	 * $panel->addField(['id' => 'some_field_id', 'hook' => 'some_custom_hook'], function($args) {
	 *      wc_variations_text_input([
	 *          'label'       => 'Some WC Custom Field',
	 *      ] + $args);
	 * });
	 *
	 * ```
	 *
	 * @see Variation_Data_Field
	 *
	 * @param string|array $args      {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( int $variation_id, int $loop, string $value, array $field )
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *
	 * @return Variation_Data_Field
	 */
	public function addField ( $args, callable $callback )
	{
		$args = wp_parse_args( $this->normalizeArgs( $args ), [ 'panel' => $this->id ] );

		return new Variation_Data_Field( $args, $callback );
	}

	/**
	 * @param array    $args              {
	 * @type string    $id                field id
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type string    $cbvalue           the checkbox value
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `checkbox`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *
	 * @return void
	 */
	public function optionsCheckbox ( $args, callable $callback = NULL )
	{
		$args = wp_parse_args( $this->normalizeArgs( $args ), [ 'hook' => 'woocommerce_variation_options' ] );

		$this->field( 'options_checkbox', $args, $callback );
	}
}