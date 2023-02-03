<?php

namespace RWC;

trait Product_Data_Fields
{
	/**
	 * Parse the $args array to get the $id, $config and $callback
	 *
	 * @param string|array   $arg0 if a string, it's the $id, otherwise it's the $config array
	 * @param array|callable $arg1 if an array, it's the $config array, otherwise it's the $callback
	 *
	 * @return array|false parsed array on success, false on failure
	 */
	protected function parseArgs ()
	{
		if ( empty( func_num_args() ) ) {
			return trigger_error( "RWC: 'args' not defined.", E_USER_ERROR ) && FALSE;
		}

		$args = func_get_args();

		if ( is_string( $args[0] ) ) {
			$id     = $args[0];
			$config = [ 'id' => $id ];
		}
		elseif ( is_array( $args[0] ) and !empty( $args[0]['id'] ) ) {
			$config = $args[0];
			$id     = $config['id'];
		}
		else {
			return trigger_error( "RWC: 'id' not defined.", E_USER_ERROR ) && FALSE;
		}

		if ( func_num_args() === 1 ) {
			return [ $id, $config, NULL ];
		}

		if ( is_array( $args[1] ) ) {
			$config   = wp_parse_args( $args[1], $config );
			$callback = NULL;
		}
		elseif ( is_callable( $args[1] ) ) {
			$callback = $args[1];
		}
		elseif ( !empty( $args[1] ) ) {
			$callback = NULL;
			trigger_error( "RWC: second arg is not a config array or callback.", E_USER_WARNING );
		}

		return [ $id, $config, $callback ];
	}

	/**
	 * Sets the label for the field
	 *
	 * If the label is not set, set it to the id with underscores replaced with spaces and ucwords
	 *
	 * @param array  $config     the $config array
	 * @param string $field_type the name of the field type
	 * @param string $func       the name of the function to call based on the class the field is in
	 *
	 * @return array the modified $config array
	 */
	protected function setLabel ( $config, $field_type, $func )
	{
		if ( !isset( $config['label'] ) ) {
			$config['label'] = ucwords( trim( str_replace( '_', ' ', $config['id'] ) ) );
		}

		return $config;
	}

	/**
	 * Normalize the config array based on the field type and context
	 *
	 * @param array  $config     the $config array
	 * @param string $field_type the name of the field type
	 * @param string $func       the name of the function to call based on the class the field is in
	 *
	 * @return array the modified $config array
	 */
	protected function configHelper ( $config, $field_type, $func )
	{
		$config = $this->setLabel( $config, $field_type, $func );

		return $config;
	}

	/**
	 * Get the function name based on the class the field is in
	 *
	 * @param string $field_type the name of the field type
	 *
	 * @return string the function name
	 */
	protected function getFunc ( $field_type )
	{
		$class = get_class( $this );

		if ( substr( $class, 0, 19 ) === 'RWC\Product_Addons_' ) {
			$data_type = 'addons';
		}
		elseif ( substr( $class, 0, 19 ) === 'RWC\Variation_Data_' ) {
			$data_type = 'variations';
		}
		else {
			$data_type = 'options';
		}

		return "wc_{$data_type}_{$field_type}";
	}

	/**
	 * Add a field to the current class object
	 *
	 * Supports adding a field to all three of the standard, variation and addon types
	 *
	 * @param string         $field_type the name of the field type
	 * @param string|array   $userArgs0  if a string, it's the $id, otherwise it's the $config array
	 * @param array|callable $userArgs1  if an array, it's the $config array, otherwise it's the $callback
	 *
	 * @return void
	 */
	protected function field ( $field_type, ...$userArgs )
	{
		[ $id, $config, $callback ] = $this->parseArgs( ...$userArgs );

		$this->addField( $config, function () use ( $id, $config, $callback, $field_type ) {
			$args = func_get_args();

			$args[0] = $config + $args[0];

			$config = is_callable( $callback ) ? $callback( ...$args ) : $args[0];

			$func = $this->getFunc( $field_type );

			$config = $this->configHelper( $config, $field_type, $func );

			$func( $config );
		} );
	}

	/**
	 * Output a text input field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $type              field type attribute value. Defaults to `text`
	 * @type string    $data_type         WooCommerce field type. Can be ['price', 'decimal', 'stock', 'url']
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `short`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function text ()
	{
		$this->field( 'text_input', ...func_get_args() );
	}

	/**
	 * Output a textarea field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `short`
	 * @type string    $wrapper_class     html wrapper class
	 * @type string    $rows              number of rows for the textarea. Defaults to `2`
	 * @type string    $cols              number of columns for the textarea. Defaults to `20`
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function textarea ()
	{
		$this->field( 'textarea_input', ...func_get_args() );
	}

	/**
	 * Output a hidden field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string $id     field id
	 * @param array  $config {
	 * @type string  $value  field value. Can be determined by WooCommerce's OrderUtil
	 * @type string  $class  field class. Defaults to `short`
	 *                       }
	 *
	 * @return void
	 */
	public function hidden ()
	{
		$this->field( 'hidden_input', ...func_get_args() );
	}

	/**
	 * Output a radio field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type array     $options           key => value pairs for the radio options, $key is the value and $value is the label
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
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function radio ()
	{
		$this->field( 'radio', ...func_get_args() );
	}

	/**
	 * Output a checkbox field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
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
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function checkbox ()
	{
		$this->field( 'checkbox', ...func_get_args() );
	}

	/**
	 * Output a select field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type array     $options           key => value pairs for the radio options, $key is the value and $value is the label
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `select short`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function select ()
	{
		$this->field( 'select', ...func_get_args() );
	}

	/**
	 * Output a select2 field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type array     $options           key => value pairs for the radio options, $key is the value and $value is the label
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil`
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `select short`
	 * @type string    $wrapper_class     html wrapper class
	 * @type array     $config            {
	 *                                    select2 config array
	 * @type bool      $multiple          is multiple select
	 * @type string    $placeholder       placeholder text. Defaults to `Search &hellip;`
	 *                                    }
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function select2 ()
	{
		$this->field( 'select2', ...func_get_args() );
	}

	/**
	 * Output a select2 field for woocommerce products
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type array     $options           key => value pairs for the radio options, $key is the value and $value is the label
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil`
	 * @type bool      $multiple          whether the field has multiple values
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `select short`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function product ()
	{
		$this->field( 'select_product', ...func_get_args() );
	}

	/**
	 * Output a select2 field for product variations
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type array     $options           key => value pairs for the radio options, $key is the value and $value is the label
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil`
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes field attributes
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `select short`
	 * @type string    $wrapper_class     html wrapper class
	 * @type array     $config            {
	 *                                    select2 config array
	 * @type bool      $multiple          is multiple select
	 * @type string    $placeholder       placeholder text. Defaults to `Search &hellip;`
	 *                                    }
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function variation ()
	{
		$this->field( 'select_variation', ...func_get_args() );
	}

	/**
	 * Output a datetime picker field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $type              field type attribute value. Defaults to `text`
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes {
	 * @type string    $size              field size attribute value. Defaults to `19`
	 * @type string    $maxlength         field maxlength attribute value. Defaults to `19`
	 * @type string    $pattern           date pattern attribute value. Defaults to `[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])( [0-2][0-9]):([0-5][0-9]):([0-5][0-9])`
	 *                                    }
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `short`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function datetime ()
	{
		$this->field( 'datetime', ...func_get_args() );
	}

	/**
	 * Output a date picker field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $type              field type attribute value. Defaults to `text`
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes {
	 * @type string    $size              field size attribute value. Defaults to `10`
	 * @type string    $maxlength         field maxlength attribute value. Defaults to `10`
	 * @type string    $pattern           date pattern attribute value. Defaults to `[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])`
	 *                                    }
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `short`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function date ()
	{
		$this->field( 'date', ...func_get_args() );
	}

	/**
	 * Output a time picker field
	 *
	 * Only accepts one or two of the following three arguments
	 * If the $callback is needed, then the first argument must be
	 * the field $id or the $config array with the field $id in it
	 *
	 * @param string   $id                field id
	 * @param array    $config            {
	 * @type string    $name              field name. Defaults to `$id`
	 * @type string    $label             label text
	 * @type string    $value             field value. Can be determined by WooCommerce's OrderUtil
	 * @type string    $type              field type attribute value. Defaults to `text`
	 * @type string    $placeholder       field placeholder
	 * @type string    $description       field text description
	 * @type string    $desc_tip          field hover tip description
	 * @type array     $custom_attributes {
	 * @type string    $size              field size attribute value. Defaults to `8`
	 * @type string    $maxlength         field maxlength attribute value. Defaults to `8`
	 * @type string    $pattern           date pattern attribute value. Defaults to `([0-2][0-9]):([0-5][0-9]):([0-5][0-9])`
	 *                                    }
	 * @type string    $style             field style
	 * @type string    $class             field class. Defaults to `short`
	 * @type string    $wrapper_class     html wrapper class
	 *                                    }
	 * @param callable $callback          callback to modify the config array
	 *                                    This is used when the config is dependent on application state that is not available at the time the initial setup of the field.
	 *                                    Product Signature: $callback( array $field, string $id )
	 *                                    Variation Signature: $callback( array $field, WC_Product_Variation $variation, int $loop, string $id )
	 *                                    Addon Signature: $callback( array $field, WP_Post $post, array $addon, int $loop )
	 *
	 * @return void
	 */
	public function time ()
	{
		$this->field( 'time', ...func_get_args() );
	}

	/**
	 * Output a browser html date input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function datetime1 ()
	{
		$this->field( 'datetime1', ...func_get_args() );
	}

	/**
	 * Output a browser html date input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function date1 ()
	{
		$this->field( 'date1', ...func_get_args() );
	}

	/**
	 * Output a browser html time input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function time1 ()
	{
		$this->field( 'time1', ...func_get_args() );
	}

	/**
	 * Output a browser html color input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function color ()
	{
		$this->field( 'color', ...func_get_args() );
	}

	/**
	 * Output a browser html email input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function email ()
	{
		$this->field( 'email', ...func_get_args() );
	}

	/**
	 * Output a browser html number input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function number ()
	{
		$this->field( 'number', ...func_get_args() );
	}

	/**
	 * Output a browser html password input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function password ()
	{
		$this->field( 'password', ...func_get_args() );
	}

	/**
	 * Output a browser html range input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function range ()
	{
		$this->field( 'range', ...func_get_args() );
	}

	/**
	 * Output a browser html telephone input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function telephone ()
	{
		$this->field( 'tel', ...func_get_args() );
	}

	/**
	 * Output a browser html url input field
	 *
	 * @param array $field                    {
	 * @type string $id                       field id
	 * @type string $name                     field name. Defaults to `$id`
	 * @type string $label                    label text
	 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
	 * @type string $type                     field type attribute value. Defaults to `text`
	 * @type string $placeholder              field placeholder
	 * @type string $description              field text description
	 * @type string $desc_tip                 field hover tip description
	 * @type array  $custom_attributes        field attributes
	 * @type string $style                    field style
	 * @type string $class                    field class. Defaults to `short`
	 * @type string $wrapper_class            html wrapper class
	 *                                        }
	 *
	 * @return void
	 */
	public function url ()
	{
		$this->field( 'url', ...func_get_args() );
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
		return RWC()->datalist( ...func_get_args() );
	}
}