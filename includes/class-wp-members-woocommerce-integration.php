<?php
class WP_Members_WooCommerce_Integration {

    public $add_my_account_fields;
    public $add_checkout_fields;
    public $add_update_fields;
    public $product_restrict;

    public function __construct( $wpmem ) {

        $defaults = array(
            'add_my_account_fields' => 0,
            'add_checkout_fields'   => 0,
            'add_update_fields'     => 0,
            'product_restrict'      => 0,
        );

        // Take array values from settings.
        $woo_settings = $wpmem->woo;
        unset( $wpmem->woo );

        foreach ( $defaults as $key => $default_value ) {
            $this->{$key} = ( isset( $woo_settings[ $key ] ) ) ? $woo_settings[ $key ] : $default_value;
        }

        // Handle "My Account" page registration.
        if ( 1 == $this->add_my_account_fields ) { // if ( wpmem_is_enabled( 'woo/add_my_account_fields' ) ) {
            add_action( 'woocommerce_register_form', 'wpmem_woo_register_form' );
            add_action( 'woocommerce_register_post', 'wpmem_woo_reg_validate', 10, 3 );
        }

        // Handle Registration checkout
        if ( 1 == $this->add_checkout_fields ) { // if ( wpmem_is_enabled( 'woo/add_checkout_fields' ) ) {
            add_filter( 'woocommerce_checkout_fields', 'wpmem_woo_checkout_form' );
            add_action( 'woocommerce_checkout_update_order_meta', 'wpmem_woo_checkout_update_meta' );
            //add_action( 'woocommerce_save_account_details_errors', 'wpmem_woo_reg_validate' );
        }

        if ( 1 == $this->add_update_fields ) { // if (  wpmem_is_enabled( 'woo/add_update_fields' ) ) {
            add_action( 'woocommerce_edit_account_form', 'wpmem_woo_edit_account_form' );
            add_action( 'woocommerce_save_account_details', 'wpmem_woo_edit_account_save' );
            add_filter( 'woocommerce_save_account_details_required_fields', 'wpmem_woo_edit_account_required' );
        }

        if ( 1 == $this->add_checkout_fields || 1 == $this->add_update_fields ) {
            add_filter( 'woocommerce_form_field_multicheckbox', 'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_filter( 'woocommerce_form_field_multiselect',   'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_filter( 'woocommerce_form_field_radio',         'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_filter( 'woocommerce_form_field_select',        'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_filter( 'woocommerce_form_field_checkbox',      'wpmem_form_field_wc_custom_field_types', 10, 4 );
        }

        if ( 1 == $this->product_restrict ) { // if ( wpmem_is_enabled( 'woo/product_restrict' ) ) {
            add_filter( 'woocommerce_is_purchasable', 'wpmem_woo_is_purchasable', PHP_INT_MAX, 2 );
        }
    }
}