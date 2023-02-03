<?php

add_action( 'admin_print_footer_scripts', function () {
	?>
	<style type="text/css">
		.clear-both {
			clear: both;
		}

		.clear-both::after, .rwc-pao-row::after {
			content: "";
			display: table;
			clear:   both;
		}

		.rwc-pao-row label {
			float: left;
		}

		.rwc-pao-row input,
		.rwc-pao-row textarea,
		.rwc-pao-row select,
		.rwc-pao-row .select2,
		.rwc-pao-row .wc-radios,
		.rwc-pao-row .wc-radios li,
		.rwc-pao-row .wc-radios label {
			float:  none !important;
			clear:  both !important;
			margin: 0 !important;
		}

		.rwc-pao-row .select2 {
			display: block !important;
			width:   100% !important;
		}

		.rwc-pao-columns, .rwc-pao-columns-1-2, .rwc-pao-columns-1-3 {
			display:         flex !important;
			justify-content: space-between;
		}

		.rwc-pao-columns-1-2 > *, .rwc-pao-column-1-2 {
			width: 50%;
		}

		.rwc-pao-columns-1-3 > *, .rwc-pao-column-1-3 {
			width: 33.3%;
		}

		.rwc-pao-column-2-3 {
			width: 66.7%;
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
function wc_addons_get_id ( $field )
{
	return "wc-pao-addon-{$field['id']}-{$field['loop']}";
}

/**
 * Get the html name for a variation field
 *
 * @param array $field    {
 * @type string $id       the variation $id
 * @type string $loop     the variation index in the list of variations
 * @type string $multiple whether the field has multiple values
 *                        }
 * @return string the html name
 */
function wc_addons_get_name ( $field )
{
	$name = "product_addon_{$field['id']}[{$field['loop']}]";

	return empty( $field['multiple'] ) ? $name : $name . '[]';
}

/**
 * Get the value for a variation field
 *
 * @param array  $field         {
 * @type string  $id            the addon $id
 * @type array   $addon         the addon data array *
 * @type string  $data_type     the field data type
 * @type string  $cbvalue       the checkbox value, if applicable
 * @type string  $value         the addon value, if it exists
 * @type string  $default_value the default value
 *                              }
 * @param string $default       the default value
 *
 * @return string the value
 */
function wc_addons_get_value ( $field, $default = '' )
{
	if ( isset( $field['value'] ) ) {
		return $field['value'];
	}

	if ( isset( $field['addon'][$field['id']] ) ) {
		return $field['addon'][$field['id']];
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

	return $default_value;
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
function wc_addons_get_info ( $field )
{
	$field['data-name'] = esc_attr( $field['id'] );
	$field['data-loop'] = esc_attr( $field['loop'] );

	$field['name']  = wc_addons_get_name( $field );
	$field['value'] = wc_addons_get_value( $field );
	$field['id']    = wc_addons_get_id( $field );

	$field['wrapper_class'] = trim( ( $field['wrapper_class'] ?? '' ) . ' ' . ( $field['conditionals'] ?? '' ) );

	if ( !empty( $field['required'] ) ) {
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
function wc_addons_text_input ( $field )
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

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

	switch ( $data_type ) {
		case 'price':
			$field['class'] .= ' wc_input_price rwc-pao-price rwc-pao-field';
			$field['value'] = wc_format_localized_price( $field['value'] );
			break;
		case 'decimal':
			$field['class'] .= ' wc_input_decimal rwc-pao-decimal rwc-pao-field';
			$field['value'] = wc_format_localized_decimal( $field['value'] );
			break;
		case 'stock':
			$field['class'] .= ' wc_input_stock rwc-pao-stock rwc-pao-field';
			$field['value'] = wc_stock_amount( $field['value'] );
			break;
		case 'url':
			$field['class'] .= ' wc_input_url rwc-pao-url rwc-pao-field';
			$field['value'] = esc_url( $field['value'] );
			break;
		case 'date':
			$field['class'] .= ' rwc-pao-date rwc-pao-field';
			break;
		case 'datetime':
			$field['class'] .= ' rwc-pao-datetime rwc-pao-field';
			break;
		case 'time':
			$field['class'] .= ' rwc-pao-time rwc-pao-field';
			break;
		default:
			$field['class'] .= ' rwc-pao-input rwc-pao-field';
			break;
	}

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<div class="rwc-pao-row wc-pao-row wc-pao-addon-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
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

	echo '</div>';
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
function wc_addons_hidden_input ( $field )
{
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class'] = isset( $field['class'] ) ? $field['class'] : '';

	$field['class'] .= ' rwc-pao-hidden rwc-pao-field';

	$field['data_type'] = 'hidden';

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

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
function wc_addons_textarea_input ( $field )
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

	$field['class'] .= ' rwc-pao-textarea rwc-pao-field';

	$field['data_type'] = 'textarea';

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<div class="rwc-pao-row wc-pao-row wc-pao-addon-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
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

	echo '</div>';
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
function wc_addons_checkbox ( $field )
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

	$field['class'] .= ' rwc-pao-checkbox rwc-pao-field';

	$field['data_type'] = 'checkbox';

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

	// Custom attribute handling
	$custom_attributes = [];

	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<div class="rwc-pao-row wc-pao-row wc-pao-addon-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '">';
	echo '<div class="wc-checkbox">';

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

	echo '</div>';
	echo '</div>';
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
function wc_addons_radio ( $field )
{
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : FALSE;

	$field['class'] .= ' rwc-pao-radio rwc-pao-field';

	$field['data_type'] = 'radio';

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

	echo '<div class="rwc-pao-row wc-pao-row wc-pao-addon-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ) . '" ' //
		. 'data-name="' . esc_attr( $name ) . '" data-loop="' . esc_attr( $loop ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	echo '<ul class="wc-radios">';
	foreach ( $field['options'] as $key => $value ) {
		echo '<li><label><input
				name="' . esc_attr( $field['name'] ) . '"
				value="' . esc_attr( $key ) . '"
				type="radio"
				class="' . esc_attr( $field['class'] ) . '"
				style="' . esc_attr( $field['style'] ) . '"
				' . checked( esc_attr( $field['value'] ), esc_attr( $key ), FALSE ) . '
				/> ' . esc_html( $value ) . '</label>
		</li>';
	}
	echo '</ul>';

	if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</div>';
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
function wc_addons_select ( $field )
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

	$field['class'] .= ' rwc-pao-' . $field['data_type'] . ' rwc-pao-field';

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

	$wrapper_attributes = [
		'class'     => 'rwc-pao-row wc-pao-row wc-pao-addon-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ),
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
	<div <?php echo wc_implode_html_attributes( $wrapper_attributes ); ?>>
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
	</div>
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
 * @type string $data_type                WooCommerce field type. Can be 'select2'
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
function wc_addons_select2 ( $field )
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

	wc_addons_select( $field );
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
function wc_addons_select_product ( $field )
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

	$field['class'] .= ' rwc-pao-select-product rwc-pao-field';

	[ $field, $name, $loop ] = wc_addons_get_info( $field );

	$wrapper_attributes = [
		'class'     => 'rwc-pao-row wc-pao-row wc-pao-addon-' . esc_attr( $field['id'] ) . '-setting ' . esc_attr( $field['wrapper_class'] ),
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
	<div <?php echo wc_implode_html_attributes( $wrapper_attributes ); ?>>
		<label <?php echo wc_implode_html_attributes( $label_attributes ); ?>><?php echo wp_kses_post( $field['label'] ); ?></label>
		<?php if ( !empty( $field['description'] ) && FALSE !== $field['desc_tip'] ) {
			echo wc_help_tip( $field['description'] );
		} ?>
		<div>
			<select <?= wc_implode_html_attributes( $field_attributes ) ?> data-placeholder="<?= $field['placeholder'] ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?= $post->ID ?>">
				<?php
				foreach ( $product_ids as $product_id ) {
					if ( is_object( $product = wc_get_product( $product_id ) ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( TRUE, TRUE, FALSE ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
					}
				}
				?>
			</select>
		</div>
		<?php if ( !empty( $field['description'] ) && FALSE === $field['desc_tip'] ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		} ?>
	</div>
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
 * @type string $data_type                WooCommerce field type. Can be 'select2'
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
function wc_addons_select_variation ( $field )
{
	$options = [];

	if ( !empty( $post = $GLOBALS['post'] ) && ( $product = wc_get_product( $post ) ) && $product->has_child() ) {
		foreach ( wc_get_available_variations( $post->ID ) as $variation ) {
			$options[$variation['variation_id']] = '#' . $variation['variation_id'] . ' - ' . implode( ' | ', $variation['attributes'] );
		}
	}

	$field = wp_parse_args( $field, [
		'class'       => 'wc-variation-search',
		'placeholder' => 'Search for a variation&hellip;',
		'options'     => $options,
	] );

	wc_addons_select2( $field );
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
function wc_addons_date ( $field )
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

	wc_addons_text_input( $field );
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
function wc_addons_datetime ( $field )
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

	wc_addons_text_input( $field );
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
function wc_addons_time ( $field )
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

	wc_addons_text_input( $field );
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
function wc_addons_datetime1 ( $field )
{
	$field['data_type'] = 'datetime1';

	wc_addons_text_input( $field );
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
function wc_addons_date1 ( $field )
{
	$field['data_type'] = 'date1';

	wc_addons_text_input( $field );
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
function wc_addons_time1 ( $field )
{
	$field['data_type'] = 'time1';

	wc_addons_text_input( $field );
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
function wc_addons_color ( $field )
{
	$field['data_type'] = 'color';

	wc_addons_text_input( $field );
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
function wc_addons_email ( $field )
{
	$field['data_type'] = 'email';

	wc_addons_text_input( $field );
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
function wc_addons_number ( $field )
{
	$field['data_type'] = 'number';

	wc_addons_text_input( $field );
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
function wc_addons_password ( $field )
{
	$field['data_type'] = 'password';

	wc_addons_text_input( $field );
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
function wc_addons_range ( $field )
{
	$field['data_type'] = 'range';

	wc_addons_text_input( $field );
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
function wc_addons_telephone ( $field )
{
	$field['data_type'] = 'tel';

	wc_addons_text_input( $field );
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
function wc_addons_url ( $field )
{
	$field['data_type'] = 'url';

	wc_addons_text_input( $field );
}


add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'es5-util', 'https://cdn.jsdelivr.net/npm/es5-util@1.5.3/dist/es5-util.min.js', [], NULL, TRUE );
} );

add_action( 'admin_print_footer_scripts', function () {
	?>
	<script>
		jQuery(function () {
			var $ = jQuery,
				paoPanelSelector = '#product_addons_data', $paoPanel = $(paoPanelSelector),
				paoFieldSelector = '.rwc-pao-field';

			function initProductSelect() {
				// From: plugins/woocommerce/assets/js/admin/wc-enhanced-select.js:76
				$().selectWoo && $(':input.wc-product-search').filter(':not(.enhanced)').addClass('enhanced').each(function (i, item) {
					var select2_args = {
						allowClear: $(this).data('allow_clear') ? true : false,
						placeholder: $(this).data('placeholder'),
						minimumInputLength: $(this).data('minimum_input_length') ? $(this).data('minimum_input_length') : '3',
						escapeMarkup: function (m) {
							return m;
						},
						ajax: {
							url: wc_enhanced_select_params.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									term: params.term,
									action: $(this).data('action') || 'woocommerce_json_search_products_and_variations',
									security: wc_enhanced_select_params.search_products_nonce,
									exclude: $(this).data('exclude'),
									exclude_type: $(this).data('exclude_type'),
									include: $(this).data('include'),
									limit: $(this).data('limit'),
									display_stock: $(this).data('display_stock')
								};
							},
							processResults: function (data) {
								var terms = [];
								if (data) {
									$.each(data, function (id, text) {
										terms.push({id: id, text: text});
									});
								}
								return {
									results: terms
								};
							},
							cache: true
						}
					};

					select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

					$(this).selectWoo(select2_args).addClass('enhanced');

					if ($(this).data('sortable')) {
						var $select = $(this);
						var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');

						$list.sortable({
							placeholder: 'ui-state-highlight select2-selection__choice',
							forcePlaceholderSize: true,
							items: 'li:not(.select2-search__field)',
							tolerance: 'pointer',
							stop: function () {
								$($list.find('.select2-selection__choice').get().reverse()).each(function () {
									var id = $(this).data('data').id;
									var option = $select.find('option[value="' + id + '"]')[0];
									$select.prepend(option);
								});
							}
						});
						// Keep multiselects ordered alphabetically if they are not sortable.
					} else if ($(this).prop('multiple')) {
						$(this).on('change', function () {
							var $children = $(this).children();
							$children.sort(function (a, b) {
								var atext = a.text.toLowerCase();
								var btext = b.text.toLowerCase();

								if (atext > btext) {
									return 1;
								}
								if (atext < btext) {
									return -1;
								}
								return 0;
							});
							$(this).html($children);
						});
					}
				});

				function getEnhancedSelectFormatString() {
					return {
						'language': {
							errorLoading: function () {
								// Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
								return wc_enhanced_select_params.i18n_searching;
							},
							inputTooLong: function (args) {
								var overChars = args.input.length - args.maximum;

								if (1 === overChars) {
									return wc_enhanced_select_params.i18n_input_too_long_1;
								}

								return wc_enhanced_select_params.i18n_input_too_long_n.replace('%qty%', overChars);
							},
							inputTooShort: function (args) {
								var remainingChars = args.minimum - args.input.length;

								if (1 === remainingChars) {
									return wc_enhanced_select_params.i18n_input_too_short_1;
								}

								return wc_enhanced_select_params.i18n_input_too_short_n.replace('%qty%', remainingChars);
							},
							loadingMore: function () {
								return wc_enhanced_select_params.i18n_load_more;
							},
							maximumSelected: function (args) {
								if (args.maximum === 1) {
									return wc_enhanced_select_params.i18n_selection_too_long_1;
								}

								return wc_enhanced_select_params.i18n_selection_too_long_n.replace('%qty%', args.maximum);
							},
							noResults: function () {
								return wc_enhanced_select_params.i18n_no_matches;
							},
							searching: function () {
								return wc_enhanced_select_params.i18n_searching;
							}
						}
					};
				}
			}

			function showHidePaoFields($el) {
				$el.each(function () {
					var $this = $(this),
						paoRow = $this.closest('.rwc-pao-row'),
						name = paoRow.data('name'),
						loop = paoRow.data('loop'),
						dataLoop = '[data-loop="' + loop + '"]',
						val, slug;

					//console.log(name, loop);

					if ($this.prop("tagName").toLowerCase() === 'input' && $this.attr('type') === 'checkbox') {
						val = $this.is(':checked') ? $this.val() : '';
					} else if ($this.prop("tagName").toLowerCase() === 'input' && $this.attr('type') === 'radio') {
						val = $('input[name="product_addon_' + name + '[' + loop + ']"]:checked').val();
					} else {
						val = $this.val();
					}

					if (es5utils) {
						$paoPanel.find('[class*="show_if_' + name + '_is_"]').filter(dataLoop).hide();
						$paoPanel.find('[class*="hide_if_' + name + '_is_"]').filter(dataLoop).show();

						$paoPanel.find('[class*="require_if_' + name + '_is_"]').filter(dataLoop).find(paoFieldSelector).attr('required', null);
						$paoPanel.find('[class*="no_require_if_' + name + '_is_"]').filter(dataLoop).find(paoFieldSelector).attr('required', 'required');
					}

					if (Array.isArray(val) ? !!val.length : (['', null, false].indexOf(val) === -1)) {
						$paoPanel.find('.show_if_' + name).filter(dataLoop).show();
						$paoPanel.find('.hide_if_' + name).filter(dataLoop).hide();

						$paoPanel.find('.require_if_' + name).filter(dataLoop).find(paoFieldSelector).attr('required', 'required');
						$paoPanel.find('.no_require_if_' + name).filter(dataLoop).find(paoFieldSelector).attr('required', null);

						if (es5utils) {
							slug = es5utils.toString(val, '_').toLowerCase().replace(/[^_a-zA-Z0-9-]/g, '_');
							$paoPanel.find('.show_if_' + name + '_is_' + slug).filter(dataLoop).show();
							$paoPanel.find('.hide_if_' + name + '_is_' + slug).filter(dataLoop).hide();

							$paoPanel.find('.require_if_' + name + '_is_' + slug).filter(dataLoop).find(paoFieldSelector).attr('required', 'required');
							$paoPanel.find('.no_require_if_' + name + '_is_' + slug).filter(dataLoop).find(paoFieldSelector).attr('required', null);
						}
					} else {
						$paoPanel.find('.show_if_' + name).filter(dataLoop).hide();
						$paoPanel.find('.hide_if_' + name).filter(dataLoop).show();

						$paoPanel.find('.require_if_' + name).filter(dataLoop).find(paoFieldSelector).attr('required', null);
						$paoPanel.find('.no_require_if_' + name).filter(dataLoop).find(paoFieldSelector).attr('required', 'required');
					}
				});
			}

			initProductSelect();
			showHidePaoFields($paoPanel.find(paoFieldSelector));

			$(document).on('DOMNodeInserted', paoPanelSelector, function (e) {
				initProductSelect();
				showHidePaoFields($(e.target).find(paoFieldSelector));
			}).on('change', paoFieldSelector, function (e) {
				showHidePaoFields($(e.target));
			});

			$('[class*="rwc-pao-column-"]').each(function() {
				$(this).parent().addClass('rwc-pao-columns')
			});
		});
	</script>
	<?php
}, 99 );