<?php

if ( ! function_exists( 'rktgk_is_wc_option_enabled' ) ):
function rktgk_is_wc_option_enabled( $option ) {
    if ( ! rktgk_is_woo_active() ) {
        return false;
    }
    $option_to_check = 'woocommerce_enable_' . $option;

    return ( 'yes' == get_option( $option_to_check ) ) ? true : false;
}
endif;

if ( ! function_exists( 'rktgk_is_wc_checkout_registration_enabled' ) ):
function rktgk_is_wc_checkout_registration_enabled() {
    if ( ! rktgk_is_woo_active() ) {
        return false;
    }
    return ( rktgk_is_wc_option_enabled( 'signup_and_login_from_checkout' ) ) ? true : false;
}
endif;

if ( ! function_exists( 'rktgk_is_wc_myaccount_registration_enabled' ) ):
function rktgk_is_wc_myaccount_registration_enabled() {
    if ( ! rktgk_is_woo_active() ) {
        return false;
    }
    return ( rktgk_is_wc_option_enabled( 'myaccount_registration' ) ) ? true : false;
}
endif;

if ( ! function_exists( 'rktgk_is_wc_guest_checkout_enabled' ) ):
function rktgk_is_wc_guest_checkout_enabled() {
    if ( ! rktgk_is_woo_active() ) {
        return false;
    }
    return ( rktgk_is_wc_option_enabled( 'guest_checkout' ) ) ? true : false;
}
endif;