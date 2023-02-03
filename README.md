# Render WooCommerce Helpers

Additional classes and functions to help with writing code for WooCommerce

## Getting Started

Basic usage is to use the `RWC()` function which is an alias for RenderWooCommerce instance (`RenderWooCommerce::get_instance()`)

```php
// Most basic version, this will create a variation metabox group with two text inputs
add_action('renderwoocommerce_loaded', function () {
    $panel = RWC()->variationPanel();
    $group = $panel->addGroup();
    $group->text( 'some_text_field_A' );
    $group->text( 'some_text_field_B' );
});

```

> NOTE: `renderwoocommerce_loaded` runs early in the `plugins_loaded` hook. So you can hook into any action that runs on or after `plugins_loaded`.

More options...

```php

// Example Product Data Panels
$args = [
    'id'            => 'panel_id',
    'label'         => "Panel Ttitle",
    'conditionals'  => [ 'show_if_condition', 'show_if_other_condition' ],
    'wrapper_class' => '',
    'priority'      => 1,
    'icon'          => "\\f469",
];
$panel = RWC()->dataPanel( $args );

// ...or Group
$args  = [
    'id'       => 'payment_gateway',
    'hook'     => 'woocommerce_product_options_general_product_data',
    'priority' => 1,
];
$group = RWC()->dataGroup($args);

// Example Variation Panels/Groups
$panel = RWC()->variationPanel();
$group = RWC()->variationGroup()

// Example Addons Panels/Groups
$panel = RWC()->addonPanel();
$group = RWC()->addonGroup();

// You can add fields to either a panel or a group
$panel->datetime( [
    'id'          => '_start_date',
    'label'       => 'Start Date',
    'desc_tip'    => TRUE,
    'description' => 'When should it start?',
] );

$group->select( [
    'id'          => '_some_options',
    'label'       => 'Select One',
    'options'     => [
        NULL => '-- Select ---',
        1    => 'Option 1',
        2    => 'Option 2',
        3    => 'Option 3',
    ],
] );

$panel->text( 'some_text_field' );

$group->hidden('some_hidden_field', [
    'conditionals' => 'show_if_something',
    'filter_cb'    => function ( $post_id, $post, $value, $field ) {
        if ( isset( $value ) ) {
            update_post_meta( $post_id, $field['id'], $value );
        } else {
            delete_post_meta( $post_id, $field['id'] );
        }
    },
]);

// Support for datalists. Can be added off the RWC() instance or the panel/group instance
$color_list_id = RWC()->datalist( [ '#FF0000', '#FF9900', '#FFFF00' ] );

$group->text( '_some_color', [ 'type' => 'color', 'custom_attributes' => [ 'list' => $color_list_id ], ] );

```

The field methods for product data are aliases for the `woocommerce_wp_*` methods provided by WooCommerce, with a few enhancements and extra features. However, if you're familiar with those functions, the arguments are basically the same. The `variation` and `addon` fields have been added to work the same way, making the calls interchangeable. They act like polymorphic functions. The input parameters for, say, `datatime()` is the same regardless if the `$panel` or `$group` is from a `product data`, `variation`, or `addon` section.

The purpose of this is to make it easier and, more importantly, faster to write code that works with any of the three contexts. Rather than managing three different sets of code that do the same thing, just in different areas of a product page.

## Available Fields

* `text`
* `textarea`
* `hidden`
* `radio`
* `checkbox`
* `select` / `select2` [@see select2](https://select2.org/)
* `product`
* `variation`
* `datetime` / `datetime1` [@see datetime1](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/datetime-local)
* `date` / `date1` [@see date1](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/date)
* `time` / `time1` [@see time1](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/time)
* `color`
* `email`
* `number`
* `password`
* `range`
* `tel`
* `url`

## Available $args

Alphabetical order.

* `cbvalue` (string) - checkbox. the value of the checkbox IF checked. Defaults to `1`
* `class` (string)
* `config` (array) - select2
* `custom_attributes` (array) - any additional html attributes to add to the field
* `data_type` (string) - WooCommerce field type
* `desc_tip` (bool) - whether to show the description as a tooltip. Defaults to `FALSE`
* `description` (string)
* `id` (string)
* `label` (string)
* `name` (string)
* `options` (array) - radio, select, select2, product, variation
* `placeholder` (string)
* `style` (string)
* `type` (string) - HTML form type attribute
* `value` (string) - can be auto-populated from the post meta
* `wrapper_class` (string)

## Available Classes

In addition to the conditional classes (that can be placed on `panels`, `groups` and `fields` in the `conditionals` $args key)...

* `show_if_*` and `show_if_*_is_*`
* `hide_if_*` and `hide_if_*_is_*`
* `require_if_` and `require_if_*_is_*`
* `no_require_if_` and `no_require_if_*_is_*`

> NOTE: the `*_if_*_is_*` also matches on field value. The value is converted to lowercase alphanumeric characters with '_' and '-'.
>
> So a field named `some_number` with a value equal to `1` could have a conditional like `show_if_some_number_is_1`.
>
> Or if a field named `some_color` has a value of `#FF0000` could have a conditional like `hide_if_some_color_is__ff0000`.
>
> Or if a field name `some_text` has a value of `[@field] #-100.5!` could have a conditional like `no_require_if_some_text_is___field___-100_5_`.

...There are also a few wrapper classes (that can be placed on `groups` and `fields` in the `wrapper_class` $args key).

### Variation Field `wrapper_class` options

* `form-row-first` - field column is 50% width, placed first
* `form-row-last` - field column is 50% width, placed last


* `form-row-first-third` - field column is 33.3% width, placed first
* `form-row-middle-third` - field column is 33.3% width, placed as the middle
* `form-row-last-third` - field column is 33.3% width, placed last

### Add-on Field `wrapper_class` options

* `rwc-pao-column-1-2` - field column is 50% width
* `rwc-pao-column-1-3` - field column is 33.3% width
* `rwc-pao-column-2-3` - field column is 66.7% width

### Add-on Groups `wrapper_class` options

* `rwc-pao-columns-1-2` - all child field columns are 50% width
* `rwc-pao-columns-1-3` - all child field columns are 33% width
* `rwc-pao-columns` - all child field columns are auto-sized
