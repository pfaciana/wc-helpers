<?php

add_action( 'admin_print_footer_scripts', function () {
	?>
	<style type="text/css">
		.woocommerce_variable_attributes .upload_image_button {
			text-align: center;
		}

		.woocommerce_variable_attributes .upload_image_button img {
			display: inline-block !important;
			width:   auto;
			height:  100%;
		}

		.woocommerce_variation.wc-metabox {
			border: 1px solid #eee;
		}

		.woocommerce_variation.wc-metabox.open {
			border:     1px solid #ccc;
			box-shadow: inset 0 0 25px #ccc;
			background: #fdfdfd;
		}

		.woocommerce_variation.wc-metabox.open > * {
			background: transparent;
		}

		.rwc-variation-options label {
			display: inline-block;
			padding: 4px 1em 2px 0
		}

		.rwc-variation-options input[type=checkbox],
		.rwc-variation-options input[type=radio] {
			margin:         0 5px 0 0 !important;
			vertical-align: middle
		}

		.rwc-variation-row .select2 {
			display: block !important;
			width:   100% !important;
			margin:  2px 0 0;
		}

		.rwc-variation-row .select2-selection__rendered {
			padding: 5px;
		}

		.rwc-variation-row .select2-container .select2-selection--single {
			height: auto;
		}

		.form-row.form-row-first, .form-row.form-row-last,
		.form-row.form-row-first-third, .form-row.form-row-middle-third, .form-row.form-row-last-third {
			margin-top: 0;
		}

		.clear-both .form-row.form-row-first, .clear-both .form-row.form-row-last,
		.clear-both .form-row.form-row-first-third, .clear-both .form-row.form-row-middle-third, .clear-both .form-row.form-row-last-third {
			margin-bottom: 0;
		}

		.form-row.form-row-first-third, .form-row.form-row-middle-third, .form-row.form-row-last-third {
			width: 32%;
			float: left;
		}

		.form-row.form-row-first-third {
			clear: both;
		}

		.form-row.form-row-middle-third {
			margin-left:  2%;
			margin-right: 2%;
		}

		.form-row.form-row-last-third {
			float: right;
		}
	</style>
	<?php
} );

/**
 * Get the html id for a variation field
 *
 * @param array $field {
 * @type string $id    the variation $id
 * @type string $loop  the variation index in the list of variations
 *                     }
 * @return string the html id
 */
function wc_variations_get_id ( $field )
{
	return "{$field['id']}{$field['loop']}";
}

/**
 * Get the html name for a variation field
 *
 * @param array $field    {
 * @type string $id       the variation $id
 * @type string $loop     the variation index in the list of variations
 * @type bool   $multiple whether the field has multiple values
 *                        }
 * @return string the html name
 */
function wc_variations_get_name ( $field )
{
	$name = "{$field['id']}[{$field['loop']}]";

	return empty( $field['multiple'] ) ? $name : $name . '[]';
}

/**
 * Get the value for a variation field
 *
 * @param array   $field         {
 * @type string   $id            the variation $id
 * @type \WP_Post $variation     the variation object
 * @type string   $data_type     the field data type
 * @type string   $cbvalue       the checkbox value, if applicable
 * @type string   $value         the variation value, if it exists
 * @type string   $default_value the default value
 *                               }
 * @param string  $default       the default value
 *
 * @return string the value
 */
function wc_variations_get_value ( $field, $default = '' )
{
	if ( isset( $field['value'] ) ) {
		return $field['value'];
	}

	$attributes = implode( '', array_values( $field["variation"]->attributes ) );

	if ( ( $value = get_post_meta( $field['variation']->ID, $field['id'], TRUE ) ) !== '' && !empty( $attributes ) ) {
		return $value;
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

	// New variation only
	if ( empty( $attributes ) ) {
		return $default_value;
	}

	return NULL;
}

/**
 * Get the variation field data
 *
 * @param array $field             {
 * @type string $id                the variation $id
 * @type string $loop              the variation index in the list of variations
 * @type string $wrapper_class     html wrapper classes
 * @type string $conditionals      html conditional classes for the wrapper
 * @type array  $custom_attributes html attributes
 * @type bool   $required          whether the field is required
 *                                 }
 *
 * @return array the variation field, data-name and data-loop
 */
function wc_variations_get_info ( $field )
{
	$field['data-name'] = esc_attr( $field['id'] );
	$field['data-loop'] = esc_attr( $field['loop'] );

	$field['name']  = wc_variations_get_name( $field );
	$field['value'] = wc_variations_get_value( $field );
	$field['id']    = wc_variations_get_id( $field );

	$field['wrapper_class'] = trim( ( $field['wrapper_class'] ?? '' ) . ' ' . ( $field['conditionals'] ?? '' ) );

	if ( $field['required'] ) {
		$field['custom_attributes'] = ( $field['custom_attributes'] ?? [] ) + [ 'required' => 'required' ];
	}

	return [ $field, $field['data-name'], $field['data-loop'] ];
}

/**
 * Output a text input field
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
 * @type string $type                     field type attribute value. Defaults to `text`
 * @type string $data_type                WooCommerce field type. Can be ['price', 'decimal', 'stock', 'url']
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
function wc_variations_text_input ( $field )
{
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : '';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : FALSE;
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];
	$field['data_type']     = isset( $field['data_type'] ) ? $field['data_type'] : $field['type'];

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	switch ( $data_type ) {
		case 'price':
			$field['class'] .= ' wc_input_price rwc-variation-price rwc-variation-field';
			$field['value'] = wc_format_localized_price( $field['value'] );
			break;
		case 'decimal':
			$field['class'] .= ' wc_input_decimal rwc-variation-decimal rwc-variation-field';
			$field['value'] = wc_format_localized_decimal( $field['value'] );
			break;
		case 'stock':
			$field['class'] .= ' wc_input_stock rwc-variation-stock rwc-variation-field';
			$field['value'] = wc_stock_amount( $field['value'] );
			break;
		case 'url':
			$field['class'] .= ' wc_input_url rwc-variation-url rwc-variation-field';
			$field['value'] = esc_url( $field['value'] );
			break;
		case 'date':
			$field['class'] .= ' rwc-variation-date rwc-variation-field';
			break;
		case 'datetime':
			$field['class'] .= ' rwc-variation-datetime rwc-variation-field';
			break;
		case 'time':
			$field['class'] .= ' rwc-variation-time rwc-variation-field';
			break;
		default:
			$field['class'] .= ' rwc-variation-input rwc-variation-field';
			break;
	}

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="form-row rwc-variation-row wc-variation-row wc-variation-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" '//
		. 'name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' //
		. 'placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a hidden field
 *
 * @param array $field        {
 * @type string $id           field id
 * @type string $value        field value. Can be determined by WooCommerce's OrderUtil
 * @type string $class        field class. Defaults to `short`
 *                            }
 *
 * @return void
 */
function wc_variations_hidden_input ( $field )
{
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class'] = isset( $field['class'] ) ? $field['class'] : '';

	$field['class'] .= ' rwc-variation-hidden rwc-variation-field';

	$field['data_type'] = 'hidden';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	echo '<input type="hidden" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '" ' //
		. 'id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" /> ';
}

/**
 * Output a textarea field
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
 * @type string $placeholder              field placeholder
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `short`
 * @type string $wrapper_class            html wrapper class
 * @type string $rows                     number of rows for the textarea. Defaults to `2`
 * @type string $cols                     number of columns for the textarea. Defaults to `20`
 *                                        }
 *
 * @return void
 */
function wc_variations_textarea_input ( $field )
{
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : '';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : FALSE;
	$field['rows']          = isset( $field['rows'] ) ? $field['rows'] : 2;
	$field['cols']          = isset( $field['cols'] ) ? $field['cols'] : 20;

	$field['class'] .= ' rwc-variation-textarea rwc-variation-field';

	$field['data_type'] = 'textarea';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="form-row rwc-variation-row wc-variation-row wc-variation-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	echo '<textarea class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '"  name="' . esc_attr( $field['name'] ) . '" '//
		. 'id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '" ' //
		. 'rows="' . esc_attr( $field['rows'] ) . '" cols="' . esc_attr( $field['cols'] ) . '" ' . implode( ' ', $custom_attributes ) //
		. '>' . esc_textarea( $field['value'] ) . '</textarea> ';

	if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a checkbox field
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type string $cbvalue                  the checkbox value
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `checkbox`
 * @type string $wrapper_class            html wrapper class
 *                                        }
 *
 * @return void
 */
function wc_variations_checkbox ( $field )
{
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : FALSE;

	$default_cbvalue        = '1';
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : $default_cbvalue;
	$field['default_value'] = !empty( $field['default_value'] ) ? $default_cbvalue : NULL;
	$field['default_value'] = isset( $field['selected'] ) && !empty( $field['selected'] ) ? $default_cbvalue : $field['default_value'];
	$field['default_value'] = isset( $field['checked'] ) && !empty( $field['checked'] ) ? $default_cbvalue : $field['default_value'];

	$field['class'] .= ' rwc-variation-checkbox rwc-variation-field';

	$field['data_type'] = 'checkbox';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="form-row rwc-variation-options rwc-variation-row wc-variation-row wc-variation-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '">';

	echo '<label for="' . esc_attr( $field['id'] ) . '">';
	echo '<input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' //
		. 'name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" ' //
		. 'value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], FALSE ) . '  ' . implode( ' ', $custom_attributes ) . '/> ';
	echo wp_kses_post( $field['label'] ) . '</label>';

	if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a checkbox field without a paragraph wrapper
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type string $cbvalue                  the checkbox value
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `checkbox`
 * @type string $wrapper_class            html wrapper class
 *                                        }
 *
 * @return void
 */
function wc_variations_options_checkbox ( $field )
{
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : FALSE;

	$default_cbvalue        = '1';
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : $default_cbvalue;
	$field['default_value'] = !empty( $field['default_value'] ) ? $default_cbvalue : NULL;
	$field['default_value'] = isset( $field['selected'] ) && !empty( $field['selected'] ) ? $default_cbvalue : $field['default_value'];
	$field['default_value'] = isset( $field['checked'] ) && !empty( $field['checked'] ) ? $default_cbvalue : $field['default_value'];

	$field['class'] .= ' rwc-variation-checkbox rwc-variation-field';

	$field['data_type'] = 'checkbox';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	?>
	<label class="tips rwc-variation-options rwc-variation-row wc-variation-row wc-variation-<?= esc_attr( $field['id'] ) ?>-setting <?= esc_attr( $field['wrapper_class'] ) ?>"
	       for="<?= esc_attr( $field['id'] ) ?>"
	       data-name="<?= esc_attr( $name ) ?>" data-loop="<?= esc_attr( $loop ) ?>"
		<?php if ( $field['description'] ) { ?> data-tip="<?= $field['description'] ?>" <?php } ?>>
		<?= wp_kses_post( $field['label'] ?? ucwords( trim( str_replace( '_', ' ', $name ) ) ) ) ?>
		<input type="checkbox"
		       class="<?= esc_attr( $field['class'] ) ?>"
		       style="<?= esc_attr( $field['style'] ) ?>"
		       name="<?= esc_attr( $field['name'] ) ?>"
		       id="<?= esc_attr( $field['id'] ) ?>"
		       value="<?= esc_attr( $field['cbvalue'] ) ?>"
			<?= checked( $field['value'], $field['cbvalue'], FALSE ) ?>
			<?= implode( ' ', $custom_attributes ) ?>
		/>
	</label>
	<?php
}

/**
 * Output a radio field
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type array  $options                  key => value pairs for the radio options, $key is the value and $value is the label
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `checkbox`
 * @type string $wrapper_class            html wrapper class
 *                                        }
 *
 * @return void
 */
function wc_variations_radio ( $field )
{
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : FALSE;

	$field['class'] .= ' rwc-variation-radio rwc-variation-field';

	$field['data_type'] = 'radio';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	echo '<p class="form-row rwc-variation-options rwc-variation-row wc-variation-row wc-variation-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	foreach ( $field['options'] as $key => $value ) {
		echo '<label><input
				name="' . esc_attr( $field['name'] ) . '"
				value="' . esc_attr( $key ) . '"
				type="radio"
				class="' . esc_attr( $field['class'] ) . '"
				style="' . esc_attr( $field['style'] ) . '"
				' . checked( esc_attr( $field['value'] ), esc_attr( $key ), FALSE ) . '
				/> ' . esc_html( $value ) . '</label> &nbsp; ';
	}

	if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a select field
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type array  $options                  key => value pairs for the radio options, $key is the value and $value is the label
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `select short`
 * @type string $wrapper_class            html wrapper class
 *                                        }
 *
 * @return void
 */
function wc_variations_select ( $field )
{
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	$field = wp_parse_args( $field, [
		'data_type'         => 'select',
		'class'             => 'select',
		'style'             => '',
		'wrapper_class'     => '',
		'desc_tip'          => FALSE,
		'custom_attributes' => [],
	] );

	$field['class'] .= ' rwc-variation-' . $field['data_type'] . ' rwc-variation-field';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	$wrapper_attributes = [
		'class'     => 'form-row rwc-variation-row wc-variation-row wc-variation-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ),
		'data-name' => esc_attr( $name ),
		'data-loop' => esc_attr( $loop ),
	];

	$label_attributes = [
		'for' => $field['id'],
	];

	$field_attributes          = (array) $field['custom_attributes'];
	$field_attributes['style'] = $field['style'];
	$field_attributes['id']    = $field['id'];
	$field_attributes['name']  = $field['name'];
	$field_attributes['class'] = $field['class'];

	?>
	<p <?php echo wc_implode_html_attributes( $wrapper_attributes ); ?>>
		<label <?php echo wc_implode_html_attributes( $label_attributes ); ?>><?php echo wp_kses_post( $field['label'] ); ?></label>
		<?php if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
			echo wc_help_tip( $field['description'] );
		} ?>
		<select <?php echo wc_implode_html_attributes( $field_attributes ); // WPCS: XSS ok.
		?>>
			<?php
			foreach ( $field['options'] as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $field['value'] ) . '>' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
		<?php if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		} ?>
	</p>
	<?php
}

/**
 * Output a select2 field
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type array  $options                  key => value pairs for the radio options, $key is the value and $value is the label
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil`
 * @type string $placeholder              field placeholder
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `select short`
 * @type string $wrapper_class            html wrapper class
 * @type array  $config                   {
 *                                        select2 config array
 * @type bool   $multiple                 is multiple select
 * @type string $placeholder              placeholder text. Defaults to `Search &hellip;`
 *                                        }
 *                                        }
 *
 * @return void
 */
function wc_variations_select2 ( $field )
{
	$field = wp_parse_args( $field, [
		'data_type'         => 'select2',
		'style'             => '',
		'name'              => $field['id'],
		'multiple'          => $field['config']['multiple'] ?? TRUE,
		'placeholder'       => $field['config']['placeholder'] ?? 'Search &hellip;',
		'custom_attributes' => [],
	] );

	$field['class'] .= ' rwc-select2';

	isset( $field['placeholder'] ) && ( $field['config']['placeholder'] = $field['placeholder'] );
	isset( $field['multiple'] ) && ( $field['config']['multiple'] = $field['multiple'] );

	if ( !empty( $field['config'] ) ) {
		$field['custom_attributes']['data-config'] = json_encode( $field['config'] );
	}

	if ( !empty( $field['multiple'] ) ) {
		$field['custom_attributes']['multiple'] = $field['multiple'];
	}

	wc_variations_select( $field );
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
function wc_variations_select_product ( $field )
{
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, [
		'data_type'         => 'select_product',
		'multiple'          => TRUE,
		'class'             => 'wc-product-search',
		'style'             => '',
		'wrapper_class'     => '',
		'placeholder'       => 'Search for a product&hellip;',
		'desc_tip'          => FALSE,
		'custom_attributes' => [],
	] );

	$field['class'] .= ' rwc-variation-select-product rwc-variation-field';

	[ $field, $name, $loop ] = wc_variations_get_info( $field );

	$wrapper_attributes = [
		'class'     => 'form-row rwc-variation-row wc-variation-row wc-variation-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ),
		'data-name' => esc_attr( $name ),
		'data-loop' => esc_attr( $loop ),
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
	}

	$product_ids = empty( $product_ids = $field['value'] ) ? [] : (array) $product_ids;

	?>
	<p <?php echo wc_implode_html_attributes( $wrapper_attributes ); ?>>
		<label <?php echo wc_implode_html_attributes( $label_attributes ); ?>><?php echo wp_kses_post( $field['label'] ); ?></label>
		<select <?= wc_implode_html_attributes( $field_attributes ) ?> data-placeholder="<?= $field['placeholder'] ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?= $post->ID ?>">
			<?php
			foreach ( $product_ids as $product_id ) {
				if ( is_object( $product = wc_get_product( $product_id ) ) ) {
					echo '<option value="' . esc_attr( $product_id ) . '"' . selected( TRUE, TRUE, FALSE ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
				}
			}
			?>
		</select>
		<?php if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
			echo wc_help_tip( $field['description'] );
		} ?>
		<?php if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		} ?>
	</p>
	<?php
}

/**
 * Output a select2 field for product variations
 *
 * @param array $field                    {
 * @type string $id                       field id
 * @type string $name                     field name. Defaults to `$id`
 * @type string $label                    label text
 * @type array  $options                  key => value pairs for the radio options, $key is the value and $value is the label
 * @type string $value                    field value. Can be determined by WooCommerce's OrderUtil`
 * @type string $placeholder              field placeholder
 * @type string $description              field text description
 * @type string $desc_tip                 field hover tip description
 * @type array  $custom_attributes        field attributes
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `select short`
 * @type string $wrapper_class            html wrapper class
 * @type array  $config                   {
 *                                        select2 config array
 * @type bool   $multiple                 is multiple select
 * @type string $placeholder              placeholder text. Defaults to `Search &hellip;`
 *                                        }
 *                                        }
 *
 * @return void
 */
function wc_variations_select_variation ( $field )
{
	$product = wc_get_root_product( $field['variation'] );
	$options = [];

	$available_variations = wc_get_available_variations( $product->ID );

	foreach ( $available_variations as $variation ) {
		if ( $field['show_current'] || $variation['variation_id'] != $field['variation']->ID ) {
			$options[$variation['variation_id']] = '#' . $variation['variation_id'] . ' - ' . implode( ' | ', $variation['attributes'] );
			if ( $variation['variation_id'] == $field['variation']->ID ) {
				$options[$variation['variation_id']] .= ' (current)';
			}
		}
	}

	$field = wp_parse_args( $field, [
		'class'       => 'wc-variation-search',
		'placeholder' => 'Search for a variation&hellip;',
		'options'     => $options,
	] );

	wc_variations_select2( $field );
}

/**
 * Output a date picker field
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
 * @type array  $custom_attributes        {
 * @type string $size                     field size attribute value. Defaults to `10`
 * @type string $maxlength                field maxlength attribute value. Defaults to `10`
 * @type string $pattern                  date pattern attribute value. Defaults to `[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])`
 *                                        }
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `short`
 * @type string $wrapper_class            html wrapper class
 *                                        }
 *
 * @return void
 */
function wc_variations_date ( $field )
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

	wc_variations_text_input( $field );
}

/**
 * Output a datetime picker field
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
 * @type array  $custom_attributes        {
 * @type string $size                     field size attribute value. Defaults to `19`
 * @type string $maxlength                field maxlength attribute value. Defaults to `19`
 * @type string $pattern                  date pattern attribute value. Defaults to `[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])( [0-2][0-9]):([0-5][0-9]):([0-5][0-9])`
 *                                        }
 * @type string $style                    field style
 * @type string $class                    field class. Defaults to `short`
 * @type string $wrapper_class            html wrapper class
 *                                        }
 *
 * @return void
 */
function wc_variations_datetime ( $field )
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

	wc_variations_text_input( $field );
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
function wc_variations_time ( $field )
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

	wc_variations_text_input( $field );
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
function wc_variations_datetime1 ( $field )
{
	$field['data_type'] = 'datetime1';

	wc_variations_text_input( $field );
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
function wc_variations_date1 ( $field )
{
	$field['data_type'] = 'date1';

	wc_variations_text_input( $field );
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
function wc_variations_time1 ( $field )
{
	$field['data_type'] = 'time1';

	wc_variations_text_input( $field );
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
function wc_variations_color ( $field )
{
	$field['data_type'] = 'color';

	wc_variations_text_input( $field );
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
function wc_variations_email ( $field )
{
	$field['data_type'] = 'email';

	wc_variations_text_input( $field );
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
function wc_variations_number ( $field )
{
	$field['data_type'] = 'number';

	wc_variations_text_input( $field );
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
function wc_variations_password ( $field )
{
	$field['data_type'] = 'password';

	wc_variations_text_input( $field );
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
function wc_variations_range ( $field )
{
	$field['data_type'] = 'range';

	wc_variations_text_input( $field );
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
function wc_variations_telephone ( $field )
{
	$field['data_type'] = 'tel';

	wc_variations_text_input( $field );
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
function wc_variations_url ( $field )
{
	$field['data_type'] = 'url';

	wc_variations_text_input( $field );
}

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'es5-util', 'https://cdn.jsdelivr.net/npm/es5-util@1/dist/es5-util.min.js', [], NULL, TRUE );
} );

add_action( 'admin_print_footer_scripts', function () {
	?>
	<script>
		jQuery(function () {
			var $ = jQuery,
				variationPanelSelector = '#variable_product_options', $variationPanel = $(variationPanelSelector),
				variationFieldSelector = '.rwc-variation-field';

			function simpleDebounce(func, wait, immediate) {
				var timeout;
				return function () {
					var context = this, args = arguments;
					clearTimeout(timeout);
					timeout = setTimeout(function () {
						timeout = null;
						if (!immediate) func.apply(context, args);
					}, wait);
					if (immediate && !timeout) func.apply(context, args);
				};
			}

			function showHideVariationFields($el) {
				$el.each(function () {
					var $this = $(this),
						variationRow = $this.closest('.rwc-variation-row'),
						name = variationRow.data('name'),
						loop = variationRow.data('loop'),
						dataLoop = '[data-loop="' + loop + '"]',
						val, slug;

					if ($this.prop("tagName").toLowerCase() === 'input' && $this.attr('type') === 'checkbox') {
						val = $this.is(':checked') ? $this.val() : '';
					} else if ($this.prop("tagName").toLowerCase() === 'input' && $this.attr('type') === 'radio') {
						val = $('input[name="product_variation_' + name + '[' + loop + ']"]:checked').val();
					} else {
						val = $this.val();
					}

					if (es5utils) {
						$variationPanel.find('[class*="show_if_' + name + '_is_"]').filter(dataLoop).hide();
						$variationPanel.find('[class*="hide_if_' + name + '_is_"]').filter(dataLoop).show();

						$variationPanel.find('[class*="require_if_' + name + '_is_"]').filter(dataLoop).find(variationFieldSelector).attr('required', null);
						$variationPanel.find('[class*="no_require_if_' + name + '_is_"]').filter(dataLoop).find(variationFieldSelector).attr('required', 'required');
					}

					if (Array.isArray(val) ? !!val.length : (['', null, false].indexOf(val) === -1)) {
						$variationPanel.find('.show_if_' + name).filter(dataLoop).show();
						$variationPanel.find('.hide_if_' + name).filter(dataLoop).hide();

						$variationPanel.find('.require_if_' + name).filter(dataLoop).find(variationFieldSelector).attr('required', 'required');
						$variationPanel.find('.no_require_if_' + name).filter(dataLoop).find(variationFieldSelector).attr('required', null);

						if (es5utils) {
							slug = es5utils.toString(val, '_').toLowerCase().replace(/[^_a-zA-Z0-9-]/g, '_');
							$variationPanel.find('.show_if_' + name + '_is_' + slug).filter(dataLoop).show();
							$variationPanel.find('.hide_if_' + name + '_is_' + slug).filter(dataLoop).hide();

							$variationPanel.find('.require_if_' + name + '_is_' + slug).filter(dataLoop).find(variationFieldSelector).attr('required', 'required');
							$variationPanel.find('.no_require_if_' + name + '_is_' + slug).filter(dataLoop).find(variationFieldSelector).attr('required', null);
						}
					} else {
						$variationPanel.find('.show_if_' + name).filter(dataLoop).hide();
						$variationPanel.find('.hide_if_' + name).filter(dataLoop).show();

						$variationPanel.find('.require_if_' + name).filter(dataLoop).find(variationFieldSelector).attr('required', null);
						$variationPanel.find('.no_require_if_' + name).filter(dataLoop).find(variationFieldSelector).attr('required', 'required');
					}
				});
			}

			var showHideVariationFieldsDebounce = simpleDebounce(showHideVariationFields, 10);

			$(document).on('DOMNodeInserted', variationPanelSelector, function (e) {
				var newFields = $(e.target).find(variationFieldSelector);
				newFields.length && showHideVariationFieldsDebounce(newFields);
			}).on('change', variationFieldSelector, function (e) {
				showHideVariationFieldsDebounce($(e.target));
			});
		});
	</script>
	<?php
}, 99 );