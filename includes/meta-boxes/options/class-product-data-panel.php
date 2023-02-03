<?php

namespace RWC;

class Product_Data_Panel
{
	use Product_Data_Helpers;
	use Product_Data_Fields;

	/**
	 * @var string $id unique panel id
	 */
	protected $id;

	/**
	 * @param array       $args          {
	 * @type string       $id            unique id
	 * @type string       $label         tab label
	 * @type string|array $conditionals  tab conditional classes
	 * @type string|array $wrapper_class panel classes
	 * @type string       $icon          tab icon
	 * @type int          $priority      action order priority
	 *                                   }
	 */
	public function __construct ( $args )
	{
		$this->id = $args['id'];

		$this->addPanel( $args );
	}

	/**
	 * Add a Panel and Tab to the Product Data Tabs
	 *
	 * @param array       $panel         {
	 * @type string       $id            unique id
	 * @type string       $label         tab label
	 * @type string|array $conditionals  tab conditional classes
	 * @type string|array $wrapper_class panel classes
	 * @type string       $icon          tab icon
	 * @type int          $priority      action order priority
	 *                                   }
	 */
	protected function addPanel ( $panel )
	{
		$panel = wp_parse_args( $panel, [
			'id'            => NULL,
			'label'         => NULL,
			'conditionals'  => [],
			'wrapper_class' => [],
			'icon'          => NULL,
			'priority'      => 10,
		] );

		add_action( 'woocommerce_product_write_panel_tabs', function () use ( $panel ) {
			$this->writePanelTabs( $panel );
		}, $panel['priority'] );

		add_action( 'woocommerce_product_data_panels', function () use ( $panel ) {
			$this->dataPanels( $panel );
		}, $panel['priority'] );
	}

	/**
	 * Build the Panel Tabs and output it to the buffer
	 *
	 * Hook: woocommerce_product_write_panel_tabs
	 *
	 * @param array       $panel        {
	 * @type string|array $conditionals tab classes as conditions
	 * @type string       $icon         tab icon
	 * @type string       $label        tab label
	 *                                  }
	 *
	 * @return void
	 */
	public function writePanelTabs ( $panel )
	{
		$panel['conditionals'] = $this->getClassString( $panel['conditionals'] );

		if ( !empty( $panel['icon'] ) ) : ?>
			<style>
				#woocommerce-product-data ul.wc-tabs li.<?= $this->id ?>_options a::before {
					content: "<?= $panel['icon'] ?>";
				}
			</style>
		<?php endif; ?>
		<li class="<?= $this->id ?>_options <?= $this->id ?>_tab <?= $panel['conditionals'] ?>">
			<a href="#<?= $this->id ?>_product_data"><span><?= $panel['label'] ?></span></a>
		</li>
		<?php
	}

	/**
	 * Build the Panel and sends the output it to the buffer
	 *
	 * This automatically is subscribed to by the child group and field items
	 * However, a user a can also directly subscribe to this hook using the `onLoad` method
	 *
	 * Hook: woocommerce_product_data_panels
	 *
	 * @see Product_Data_Group::addGroup()
	 * @see Product_Data_Field::addField()
	 * @see Product_Data_Panel::onLoad()
	 *
	 * @param array       $panel         {
	 * @type string|array $wrapper_class panel classes
	 *                                   }
	 *
	 * @return void
	 */
	protected function dataPanels ( $panel )
	{
		$panel['wrapper_class'] = $this->getClassString( $panel['wrapper_class'] );
		?>
		<div id="<?= $this->id ?>_product_data" class="panel woocommerce_options_panel <?= $panel['wrapper_class'] ?>">
			<?php do_action( $this->getActionTag( [ 'panel' => $this->id ] ), $this->id, ...func_get_args() ) ?>
		</div>
		<?php
	}

	/**
	 * Hook into the Panel html output for adding custom html
	 *
	 * Shortcut for the panel hook
	 * NOTE: This does not create or validate any fields
	 *
	 * @see Product_Data_Panel::addPanel()
	 *
	 * @param callable $callback callback for when the panel hook is run
	 *                           Signature: $callback ( string $panel_id, array $panel )
	 * @param int      $priority hook priority
	 *
	 * @return void
	 */
	public function onLoad ( $callback, $priority = 10 )
	{
		add_action( $this->getActionTag( [ 'panel' => $this->id ] ), function ( $panel_id, $panel ) use ( $callback ) {
			$callback( ...func_get_args() );
		}, $priority, 2 );
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
	 * @return Product_Data_Group
	 */
	public function addGroup ( $args = [] )
	{
		if ( is_scalar( $args ) ) {
			$args = [ 'id' => $args ];
		}

		$args = wp_parse_args( $args, [ 'panel' => $this->id ] );

		return new Product_Data_Group( $args );
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
		$args = wp_parse_args( $this->normalizeArgs( $args ), [ 'panel' => $this->id ] );

		return new Product_Data_Field( $args, $callback );
	}
}