<?php

/**
 * Get the value for a field
 *
 * @param array  $field          {
 * @type string  $data_type      the field data type
 * @type string  $cbvalue        the checkbox value, if applicable
 * @type string  $default_value  the default value
 * @type string  $value          the variation value, if it exists
 *                               }
 * @param string $default        the default value
 *
 * @return string the value
 */
function wc_options_get_value ( $field, $default = '' )
{
	global $thepostid;

	if ( isset( $field['value'] ) ) {
		return $field['value'];
	}

	if ( !isset( $field['default_value'] ) || $field['default_value'] === FALSE ) {
		return NULL;
	}

	$default_value = $field['default_value'] ?? $default;

	if ( !empty( $field['data_type'] ) ) {
		if ( $field['data_type'] === 'checkbox' ) {
			$default_value = $field['cbvalue'];
		}
	}

	// New post only
	if ( !metadata_exists( 'post', $thepostid, '_edit_lock' ) ) {
		return $default_value;
	}

	return NULL;
}

/**
 * Set the field attributes
 *
 * @param array  $field             {
 * @type string  $data_type         the field data type
 * @type string  $class             html classes
 * @type string  $wrapper_class     html wrapper classes
 * @type string  $conditionals      html conditional classes for the wrapper
 * @type array   $custom_attributes html attributes
 * @type bool    $multiple          whether the field has multiple values
 * @type bool    $required          whether the field is required
 *                                  }
 * @param string $addl_class        additional classes to add to the field
 *
 * @return array the modified field array
 */
function wc_options_set_fields ( $field, $addl_class = '' )
{
	$field['wrapper_class'] = trim( ( $field['wrapper_class'] ?? '' ) . ' rwc-options-row ' . ( $field['conditionals'] ?? '' ) );

	$field['class'] = ( $field['class'] ?? '' ) . ' rwc-options-' . ( $field['data_type'] ?? '' ) . ' rwc-options-field ' . $addl_class;

	if ( !empty( $field['required'] ) ) {
		$field['custom_attributes'] = ( $field['custom_attributes'] ?? [] ) + [ 'required' => 'required' ];
	}

	if ( !is_null( $value = wc_options_get_value( $field ) ) ) {
		$field['value'] = $value;
	}

	if ( !empty( $field['multiple'] ) ) {
		$field['name'] .= '[]';
	}

	return $field;
}

/**
 * Output a text input field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $type              field type attribute value. Defaults to `text`
 * @type string        $data_type         WooCommerce field type. Can be ['price', 'decimal', 'stock', 'url']
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `short`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_text_input ( $field, WC_Data $data = NULL )
{
	$field['data_type'] = $field['data_type'] ?? 'text';

	woocommerce_wp_text_input( wc_options_set_fields( $field, 'short' ), $data );
}

/**
 * Output a hidden field
 *
 * @param array        $field {
 * @type string        $id    field id
 * @type string        $value field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $class field class. Defaults to `short`
 *                            }
 * @param WC_Data|null $data  WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_hidden_input ( $field, WC_Data $data = NULL )
{
	$field['data_type'] = 'hidden';

	woocommerce_wp_hidden_input( wc_options_set_fields( $field ), $data );
}

/**
 * Output a textarea field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `short`
 * @type string        $wrapper_class     html wrapper class
 * @type string        $rows              number of rows for the textarea. Defaults to `2`
 * @type string        $cols              number of columns for the textarea. Defaults to `20`
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_textarea_input ( $field, WC_Data $data = NULL )
{
	$field['data_type'] = 'textarea';

	woocommerce_wp_textarea_input( wc_options_set_fields( $field, 'short' ), $data );
}

/**
 * Output a checkbox field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type string        $cbvalue           the checkbox value
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `checkbox`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_checkbox ( $field, WC_Data $data = NULL )
{
	$field['data_type'] = 'checkbox';

	$field['cbvalue'] = isset( $field['cbvalue'] ) ? $field['cbvalue'] : '1';

	woocommerce_wp_checkbox( wc_options_set_fields( $field, 'checkbox' ), $data );
}

/**
 * Output a radio field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type array         $options           key => value pairs for the radio options, $key is the value and $value is the label
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `checkbox`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_radio ( $field, WC_Data $data = NULL )
{
	$field['data_type'] = 'radio';

	woocommerce_wp_radio( wc_options_set_fields( $field, 'select short' ), $data );
}

/**
 * Output a select field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type array         $options           key => value pairs for the radio options, $key is the value and $value is the label
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `select short`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_select ( $field, WC_Data $data = NULL )
{
	$field['data_type'] = 'select';

	woocommerce_wp_select( wc_options_set_fields( $field, 'select short' ), $data );
}

/**
 * Output a select2 field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type array         $options           key => value pairs for the radio options, $key is the value and $value is the label
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil`
 * @type string        $data_type         WooCommerce field type. Can be 'select2'
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `select short`
 * @type string        $wrapper_class     html wrapper class
 * @type array         $config            {
 *                                        select2 config array
 * @type bool          $multiple          is multiple select
 * @type string        $placeholder       placeholder text. Defaults to `Search &hellip;`
 *                                        }
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_select2 ( $field, WC_Data $data = NULL )
{
	$field = wp_parse_args( $field, [
		'data_type'         => 'select2',
		'style'             => 'width: 50%;',
		'name'              => $field['id'],
		'multiple'          => $field['config']['multiple'] ?? TRUE,
		'placeholder'       => $field['config']['placeholder'] ?? 'Search &hellip;',
		'custom_attributes' => [],
	] );

	isset( $field['placeholder'] ) && ( $field['config']['placeholder'] = $field['placeholder'] );
	isset( $field['multiple'] ) && ( $field['config']['multiple'] = $field['multiple'] );

	if ( !empty( $field['config'] ) ) {
		$field['custom_attributes']['data-config'] = json_encode( $field['config'] );
	}

	if ( !empty( $field['multiple'] ) ) {
		$field['custom_attributes']['multiple'] = $field['multiple'];
	}

	woocommerce_wp_select( wc_options_set_fields( $field, 'rwc-select2 select short' ), $data );
}

/**
 * Output a select2 product search field
 *
 * @param array $field             {
 * @type string $id                field id
 * @type string $name              field name. Defaults to `$id`
 * @type string $label             label text
 * @type array  $options           key => value pairs for the radio options, $key is the value and $value is the label
 * @type string $value             field value. Can be determined by WooCommerce's OrderUtil`
 * @type bool   $multiple          whether the field has multiple values
 * @type string $placeholder       field placeholder
 * @type string $description       field text description
 * @type string $desc_tip          field hover tip description
 * @type array  $custom_attributes field attributes
 * @type string $style             field style
 * @type string $class             field class. Defaults to `select short`
 * @type string $wrapper_class     html wrapper class
 *                                 }
 *
 * @return void
 */
function wc_options_select_product ( $field )
{
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, [
		'multiple'          => TRUE,
		'class'             => 'wc-product-search',
		'style'             => 'width: 50%;',
		'wrapper_class'     => '',
		'name'              => $field['id'],
		'placeholder'       => 'Search for a product&hellip;',
		'desc_tip'          => FALSE,
		'custom_attributes' => [],
	] );

	$field['class'] .= ' rwc-options-select-product rwc-options-field';

	$field['data_type'] = 'select_product';

	$wrapper_attributes = [
		'class' => $field['wrapper_class'] . " rwc-options-row form-field {$field['id']}_field",
	];

	$label_attributes = [
		'for' => $field['id'],
	];

	$field_attributes          = (array) $field['custom_attributes'];
	$field_attributes['style'] = $field['style'];
	$field_attributes['id']    = $field['id'];
	$field_attributes['name']  = $field['name'];
	$field_attributes['class'] = $field['class'];
	if ( $field['multiple'] ) {
		$field_attributes['multiple'] = $field['multiple'];
		$field_attributes['name']     .= '[]';
	}

	$tooltip     = !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ? $field['description'] : '';
	$description = !empty( $field['description'] ) && FALSE === $field['desc_tip'] ? $field['description'] : '';

	$product_ids = get_post_meta( $post->ID, $field['id'], TRUE );
	$product_ids = empty( $product_ids ) ? [] : (array) $product_ids;

	?>
	<p <?= wc_implode_html_attributes( $wrapper_attributes ) ?>>
		<label <?= wc_implode_html_attributes( $label_attributes ) ?>><?= wp_kses_post( $field['label'] ); ?></label>
		<select <?= wc_implode_html_attributes( $field_attributes ) ?> data-placeholder="<?= $field['placeholder'] ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?= $post->ID ?>">
			<?php
			foreach ( $product_ids as $product_id ) {
				if ( is_object( $product = wc_get_product( $product_id ) ) ) {
					echo '<option value="' . esc_attr( $product_id ) . '"' . selected( TRUE, TRUE, FALSE ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
				}
			}
			?>
		</select>
		<?php if ( $tooltip ) :
			echo wc_help_tip( $tooltip );
		endif; ?>
		<?php if ( $description ) : ?>
			<span class="description"><?= wp_kses_post( $description ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Output a select2 field for product variations
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type array         $options           key => value pairs for the radio options, $key is the value and $value is the label
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil`
 * @type string        $data_type         WooCommerce field type. Can be 'select2'
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes field attributes
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `select short`
 * @type string        $wrapper_class     html wrapper class
 * @type array         $config            {
 *                                        select2 config array
 * @type bool          $multiple          is multiple select
 * @type string        $placeholder       placeholder text. Defaults to `Search &hellip;`
 *                                        }
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_select_variation ( $field, WC_Data $data = NULL )
{
	global $thepostid, $post;

	$product = wc_get_product( $post );
	$options = [];

	foreach ( $product->get_available_variations() as $variation ) {
		$options[$variation['variation_id']] = '#' . $variation['variation_id'] . ' - ' . implode( ' | ', $variation['attributes'] );
	}

	$field = wp_parse_args( $field, [
		'class'       => 'wc-variation-search',
		'placeholder' => 'Search for a variation&hellip;',
		'options'     => $options,
	] );

	wc_options_select2( $field, $data );
}

/**
 * Output a date picker field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $type              field type attribute value. Defaults to `text`
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes {
 * @type string        $size              field size attribute value. Defaults to `10`
 * @type string        $maxlength         field maxlength attribute value. Defaults to `10`
 * @type string        $pattern           date pattern attribute value. Defaults to `[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])`
 *                                        }
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `short`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_date ( $field, WC_Data $data = NULL )
{
	$defaults = [
		'class'             => [ 'rwc-datepicker' ],
		'custom_attributes' => [
			'size'      => '10',
			'maxlength' => '10',
			'pattern'   => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
		],
	];

	$field['class'] = array_merge( $defaults['class'], explode( ' ', $field['class'] ?? '' ) );
	$field['class'] = implode( ' ', array_unique( $field['class'] ) );

	$field['custom_attributes'] = array_merge( $defaults['custom_attributes'], $field['custom_attributes'] ?? [] );

	$field['data_type'] = 'date';

	wc_options_text_input( $field, $data );
}

/**
 * Output a datetime picker field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $type              field type attribute value. Defaults to `text`
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes {
 * @type string        $size              field size attribute value. Defaults to `19`
 * @type string        $maxlength         field maxlength attribute value. Defaults to `19`
 * @type string        $pattern           date pattern attribute value. Defaults to `[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])( [0-2][0-9]):([0-5][0-9]):([0-5][0-9])`
 *                                        }
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `short`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_datetime ( $field, WC_Data $data = NULL )
{
	$defaults = [
		'class'             => [ 'rwc-datetimepicker' ],
		'custom_attributes' => [
			'size'      => '19',
			'maxlength' => '19',
			'pattern'   => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])( [0-2][0-9]):([0-5][0-9]):([0-5][0-9])',
		],
	];

	$field['class'] = array_merge( $defaults['class'], explode( ' ', $field['class'] ?? '' ) );
	$field['class'] = implode( ' ', array_unique( $field['class'] ) );

	$field['custom_attributes'] = array_merge( $defaults['custom_attributes'], $field['custom_attributes'] ?? [] );

	$field['data_type'] = 'datetime';

	wc_options_text_input( $field, $data );
}

/**
 * Output a time picker field
 *
 * @param array        $field             {
 * @type string        $id                field id
 * @type string        $name              field name. Defaults to `$id`
 * @type string        $label             label text
 * @type string        $value             field value. Can be determined by WooCommerce's OrderUtil
 * @type string        $type              field type attribute value. Defaults to `text`
 * @type string        $placeholder       field placeholder
 * @type string        $description       field text description
 * @type string        $desc_tip          field hover tip description
 * @type array         $custom_attributes {
 * @type string        $size              field size attribute value. Defaults to `8`
 * @type string        $maxlength         field maxlength attribute value. Defaults to `8`
 * @type string        $pattern           date pattern attribute value. Defaults to `([0-2][0-9]):([0-5][0-9]):([0-5][0-9])`
 *                                        }
 * @type string        $style             field style
 * @type string        $class             field class. Defaults to `short`
 * @type string        $wrapper_class     html wrapper class
 *                                        }
 * @param WC_Data|null $data              WC_Data object, will be preferred over post object when passed.
 *
 * @return void
 */
function wc_options_time ( $field, WC_Data $data = NULL )
{
	$defaults = [
		'class'             => [ 'rwc-timepicker' ],
		'custom_attributes' => [
			'size'      => '8',
			'maxlength' => '8',
			'pattern'   => '([0-2][0-9]):([0-5][0-9]):([0-5][0-9])',
		],
	];

	$field['class'] = array_merge( $defaults['class'], explode( ' ', $field['class'] ?? '' ) );
	$field['class'] = implode( ' ', array_unique( $field['class'] ) );

	$field['custom_attributes'] = array_merge( $defaults['custom_attributes'], $field['custom_attributes'] ?? [] );

	$field['data_type'] = 'time';

	wc_options_text_input( $field, $data );
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
function wc_options_datetime1 ( $field )
{
	$field['data_type'] = 'datetime1';

	wc_options_text_input( $field );
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
function wc_options_date1 ( $field )
{
	$field['data_type'] = 'date1';

	wc_options_text_input( $field );
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
function wc_options_time1 ( $field )
{
	$field['data_type'] = 'time1';

	wc_options_text_input( $field );
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
function wc_options_color ( $field )
{
	$field['data_type'] = 'color';

	wc_options_text_input( $field );
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
function wc_options_email ( $field )
{
	$field['data_type'] = 'email';

	wc_options_text_input( $field );
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
function wc_options_number ( $field )
{
	$field['data_type'] = 'number';

	wc_options_text_input( $field );
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
function wc_options_password ( $field )
{
	$field['data_type'] = 'password';

	wc_options_text_input( $field );
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
function wc_options_range ( $field )
{
	$field['data_type'] = 'range';

	wc_options_text_input( $field );
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
function wc_options_telephone ( $field )
{
	$field['data_type'] = 'tel';

	wc_options_text_input( $field );
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
function wc_options_url ( $field )
{
	$field['data_type'] = 'url';

	wc_options_text_input( $field );
}

add_action( 'admin_print_footer_scripts', function () {
	?>
	<script>
		jQuery(function () {
			var $ = jQuery,
				optionsPanelSelector = '#woocommerce-product-data', $optionsPanel = $(optionsPanelSelector),
				optionsFieldSelector = '.rwc-options-field';

			function init() {
				$.datepicker && $('.rwc-datepicker').datepicker({
					changeMonth: true,
					changeYear: true,
					defaultDate: '',
					dateFormat: 'yy-mm-dd',
					numberOfMonths: [1, 1],
					showButtonPanel: true,
					showOn: 'focus',
				});

				$().datetimepicker && $('.rwc-datetimepicker').datetimepicker({
					dateFormat: 'yy-mm-dd',
					timeFormat: "HH:mm:ss",
					changeMonth: true,
					changeYear: true,
					yearRange: '-10:+10',
					showButtonPanel: true,
					firstDay: 0,
					controlType: 'select',
					oneLine: true
				});

				$.timepicker && $('.rwc-timepicker').timepicker({
					timeFormat: "HH:mm:ss",
					showHour: true,
					showMinute: true,
					showSecond: true,
				});

				$().select2 && $('.rwc-select2').filter(':not(.enhanced)').addClass('enhanced').each(function (i, item) {
					var $this = $(this), config = $this.data('config') || {};
					$this.select2(config);
				});
			}

			init();
			if ($('body').hasClass('woocommerce-page')) {
				$(document).on('DOMNodeInserted', optionsPanelSelector, init);
			}

			function showHideOptionsFields($el) {
				$el.each(function () {
					var $this = $(this),
						name = $this.attr('id') || $this.attr('name'),
						val, slug;

					if ($this.prop("tagName").toLowerCase() === 'input' && $this.attr('type') === 'checkbox') {
						val = $this.is(':checked') ? $this.val() : '';
					} else if ($this.prop("tagName").toLowerCase() === 'input' && $this.attr('type') === 'radio') {
						val = $('input[name="' + name + '"]:checked').val();
					} else {
						val = $this.val();
					}

					if (es5utils) {
						$optionsPanel.find('[class*="show_if_' + name + '_is_"]').hide();
						$optionsPanel.find('[class*="hide_if_' + name + '_is_"]').show();

						$optionsPanel.find('[class*="require_if_' + name + '_is_"]').find(optionsFieldSelector).attr('required', null);
						$optionsPanel.find('[class*="no_require_if_' + name + '_is_"]').find(optionsFieldSelector).attr('required', 'required');
					}

					if (Array.isArray(val) ? !!val.length : (['', null, false].indexOf(val) === -1)) {
						$optionsPanel.find('.show_if_' + name).show();
						$optionsPanel.find('.hide_if_' + name).hide();

						$optionsPanel.find('.require_if_' + name).find(optionsFieldSelector).attr('required', 'required');
						$optionsPanel.find('.no_require_if_' + name).find(optionsFieldSelector).attr('required', null);

						if (es5utils) {
							slug = es5utils.toString(val, '_').toLowerCase().replace(/[^_a-zA-Z0-9-]/g, '_');
							$optionsPanel.find('.show_if_' + name + '_is_' + slug).show();
							$optionsPanel.find('.hide_if_' + name + '_is_' + slug).hide();

							$optionsPanel.find('.require_if_' + name + '_is_' + slug).find(optionsFieldSelector).attr('required', 'required');
							$optionsPanel.find('.no_require_if_' + name + '_is_' + slug).find(optionsFieldSelector).attr('required', null);
						}
					} else {
						$optionsPanel.find('.show_if_' + name).hide();
						$optionsPanel.find('.hide_if_' + name).show();

						$optionsPanel.find('.require_if_' + name).find(optionsFieldSelector).attr('required', null);
						$optionsPanel.find('.no_require_if_' + name).find(optionsFieldSelector).attr('required', 'required');
					}
				});
			}

			showHideOptionsFields($optionsPanel.find(optionsFieldSelector));

			$(document).on('change', optionsFieldSelector, function (e) {
				showHideOptionsFields($(e.target));
			});

			$('form#post').attr('novalidate', 'novalidate').on('submit', function (e) {
				var invalidFields = [];

				$(this).find(':invalid').each(function (item) {
					invalidFields.push($('label[for="' + this.id + '"]').text().trim());
				});

				if (invalidFields.length > 0) {
					alert('\u26A0 The following fields are invalid:\n\u00B7\u00A0' + invalidFields.join('\n\u00B7\u00A0'));
					e.preventDefault();
					e.stopImmediatePropagation();
				}
			});
		});
	</script>
	<?php
}, 99 );