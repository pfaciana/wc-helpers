<?php

class RenderWooCommerce
{
	protected $version = '1.0.0';

	// public $product_factory = null;

	/**
	 * Main RenderWooCommerce Instance.
	 *
	 * Ensures only one instance of RenderWooCommerce is loaded or can be loaded.
	 *
	 * @return RenderWooCommerce - Main instance.
	 */
	public static function get_instance ()
	{
		static $instance;

		if ( !$instance instanceof static ) {
			$instance = new static;
		}

		return $instance;
	}

	/**
	 * RenderWooCommerce Constructor.
	 */
	protected function __construct ()
	{
		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	protected function define ( $name, $value )
	{
		!defined( $name ) && define( $name, $value );
	}

	/**
	 * Define RenderWooCommerce Constants.
	 */
	protected function define_constants ()
	{
		$this->define( 'RWC_VERSION', $this->version );
		$this->define( 'RENDERWOOCOMMERCE_VERSION', $this->version );
	}

	/**
	 * Publish 'renderwoocommerce_loaded' hook
	 */
	protected function on_plugins_loaded ()
	{
		do_action( 'renderwoocommerce_loaded' );
	}

	/**
	 * Init RenderWooCommerce when WordPress Initialises.
	 */
	protected function init ()
	{
		// Before init action.
		do_action( 'before_renderwoocommerce_init' );

		// Load class instances.
		// $this->product_factory = new WC_Product_Factory();

		// Init action.
		do_action( 'renderwoocommerce_init' );
	}

	/**
	 * Set init hooks on construct
	 */
	protected function init_hooks ()
	{
		add_action( 'plugins_loaded', function () { $this->on_plugins_loaded(); }, -1 );
		add_action( 'init', function () { $this->init(); }, 0 );
	}

	/* Data */

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
	public function dataPanel ( $panel )
	{
		return new \RWC\Product_Data_Panel( ...func_get_args() );
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
	public function dataGroup ( $group )
	{
		return new \RWC\Product_Data_Group( ...func_get_args() );
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
	 *                                Signature: $filter_cb( array $field, string $id )
	 */
	public function dataField ( $field, callable $callback )
	{
		return new \RWC\Product_Data_Field( ...func_get_args() );
	}

	/* Variation */

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
	public function variationPanel ( $panel = NULL )
	{
		return new \RWC\Variation_Data_Panel( ...func_get_args() );
	}

	/**
	 * Add a group to a variation panel
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
	public function variationGroup ( $group = NULL )
	{
		return new \RWC\Variation_Data_Group( ...func_get_args() );
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
	public function variationField ( $field, callable $callback )
	{
		return new \RWC\Variation_Data_Field( ...func_get_args() );
	}

	/* Add-ons */

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
	public function addonPanel ( $panel = [] )
	{
		return new \RWC\Product_Addons_Panel( ...func_get_args() );
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
	public function addonGroup ( $group = NULL )
	{
		return new \RWC\Product_Addons_Group( ...func_get_args() );
	}

	/**
	 * Add a field to an addons group or panel
	 *
	 * @param string|array $field     {
	 * @type callable      $filter_cb custom function to sanitize and filter $_POST data
	 *                                Signature: $filter_cb( array $data, int $index, string $value, array $field )
	 * @type string        $group     group id to attach to
	 * @type string        $panel     panel id to attach to
	 * @type string        $hook      alternative to attaching to a group or panel by id
	 *                                }
	 * @param callable     $callback  callback for when the field hook is run
	 *                                Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 */
	public function addonField ( $field, callable $callback )
	{
		return new \RWC\Product_Addons_Field( ...func_get_args() );
	}

	/**
	 * Output a datalist html element with the provided options
	 *
	 * @param array  $options list of options
	 * @param string $id      datalist id. If not provided, a unique id will be generated
	 *
	 * @return string datalist id
	 */
	public function datalist ( $options, $id = NULL )
	{
		$id = !empty( $id ) ? $id : uniqid( 'generated_' );

		add_action( 'admin_footer', function () use ( $id, $options ) {
			echo '<datalist id="' . $id . '">';
			foreach ( $options as $option ) {
				echo '<option value="' . $option . '"></option>';
			}
			echo '</datalist>';
		}, 1 );

		return $id;
	}
}