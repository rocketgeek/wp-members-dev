<?php

class WP_Members_User_Utilities {

    private static $validation_errors = false;

    private static $activate_all = array( 
        'complete' => false,
        'count'    => 0,
        'email'    => false,
        'password' => false,
    );

    private static $confirm_all = array( 
        'complete' => false,
        'count'    => 0,
    );

    private static function set_validation_errors( $val ) {
        self::$validation_errors = $val;
    }

    private static function set_confirm_all_complete() {
        self::$confirm_all['complete'] = true;
    }

    private static function set_confirm_all_count( $count ) {
        self::$confirm_all['count'] = $count;
    }

    private static function set_activate_all_complete() {
        self::$activate_all['complete'] = true;
    }

    private static function set_activate_all_count( $count ) {
        self::$activate_all['count'] = $count;
    }

    private static function get_validation_errors() {
        return self::$validation_errors;
    }

    private static function get_confirm_all_complete() {
        return self::$confirm_all['complete'];
    }

    private static function get_confirm_all_count() {
        return self::$confirm_all['count'];
    }

    private static function get_activate_all_complete() {
        return self::$activate_all['complete'];
    }

    private static function get_activate_all_count() {
        return self::$activate_all['count'];
    }

    public static function admin_menu() {
        $hook = add_management_page( 'WP-Members User Utilities', 'WP-Members User Utilities', 'edit_users', 'wpmem-user-utilities', array( 'WP_Members_User_Utilities', 'admin_page' ) );
        
        add_action( "load-$hook", array( 'WP_Members_User_Utilities', 'admin_page_load' ) );
        add_action( 'admin_notices', array( 'WP_Members_User_Utilities', 'notices' ) );

        add_filter( 'post_acui_import_single_user', array( 'WP_Members_User_Utilities', 'acui_import' ), 10, 3 );
    }

    static function admin_page_load() {
        if ( 'wpmem-user-utilities' == wpmem_get( 'page', false, 'get' ) && 1 == wpmem_get( 'activate-all-confirm', false ) ) {

            // Verify nonce.
            if ( false == check_admin_referer( 'wpmem-user-utilities' ) ) {
                self::set_validation_errors( 'nonce_fail' );
                return;
            }

            // Verify user caps.
            if ( ! current_user_can( 'edit_users' ) ) {
                self::set_validation_errors( 'user_caps' );
                return;
            }

            // If nonce didn't fail and user has edit caps.
            if ( false == self::get_validation_errors() ) {

                $users = get_users( array( 'fields'=>'ID' ) );
                $count = 0;
                foreach ( $users as $user_id ) {
                    //update_user_meta( $user_id, 'active', 1 );
                    //wpmem_set_user_status( $user_id, 0 );
                    $count++;
                }
                self::set_activate_all_complete( $count );
            }
        }
    }

    public static function notices() {

        if ( false != self::get_validation_errors() ) {
            $class   = 'notice notice-error';
            $message = esc_html__( "Submission was invalid. No action taken.", 'wp-members' );
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }

        if ( self::get_activate_all_complete() ) {
            $class   = 'notice notice-success';
            $message = sprintf( esc_html__( "%s users were marked as activated", 'wp-members' ), self::get_activate_all_complete() );

            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }
        if ( self::get_confirm_all_complete() ) {
            $class   = 'notice notice-success';
            $message = sprintf( esc_html__( "%s users were marked as confirmed", 'wp-members' ), self::get_confirm_all_complete() );

            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }
    }

    public static function admin_page() {
        echo "<h1>" . esc_html__( 'WP-Members User Utilities', 'wp-members' ) . "</h1>";

        $form_post = ( function_exists( 'wpmem_admin_form_post_url' ) ) ? wpmem_admin_form_post_url() : '';
        echo '<form name="wpmem-user-utilities" id="wpmem-user-utilities" method="post" action="' . $form_post . '">';

        if ( wpmem_is_enabled( 'mod_reg' ) ) {
            echo '<h2>' . esc_html__( 'Moderated Registration', 'wp-members' ) . '</h2>';
            echo "<p>" . esc_html__( 'This process will mark all existing user accounts as activated in WP-Members.', 'wp-members' ) . 
                '<br />' . esc_html__( 'It will not change any passwords or send any emails to users.', 'wp-members' );
            echo '<p><input type="checkbox" name="activate-all-confirm" value="1" /><label for="activate-all-confirm">' . esc_html__( 'Activate all users?', 'wp-members' ) . '</label></p>';
        }

        if ( wpmem_is_enabled( 'act_link' ) ) {
            echo '<h2>' . esc_html__( 'User Confirmation', 'wp-members' ) . '</h2>';
            echo "<p>" . esc_html__( 'This process will mark all existing user accounts as confirmed in WP-Members.', 'wp-members' );
            echo '<p><input type="checkbox" name="confirm-all-confirm" value="1" /><label for="confirm-all-confirm">' . esc_html__( 'Confirm all users?', 'wp-members' ) . '</label></p>';
        }
        
        echo '<p><input type="submit" name="submit" value="' . esc_html__( 'Submit' ) . '" /></p>'; // @todo Could use a wp submit button here by function.
        wp_nonce_field( 'wpmem-user-utilities' );

        echo '</form>';
    }

    /**
     * Fires on "post_acui_import_single_user" when importing users using
     * the Import Users and Customers plugin.
     * 
     * @since 3.5.0
     * 
     * @param  array   $headers
     * @param  array   $data
     * @param  string  $user_id
     */
    public static function acui_import( $headers, $data, $user_id ) {
    
        $memberships = wpmem_get_memberships();

        foreach ( $headers as $i => $header ) {
            $membership_meta = substr( $header, 16 );
            if ( array_key_exists( $membership_meta, $memberships ) ) {
                if ( 0 != $data[ $i ] ) {
                    $expiration_period = ( isset( $memberships[ $membership_meta ]['expires'] ) ) ? $memberships[ $membership_meta ]['expires'] : false;
                    $date = ( $expiration_period ) ? $data[ $i ] : false;
                    wpmem_set_user_product( $membership_meta, $user_id, $date );
                } else {
                    wpmem_remove_user_product( $membership_meta, $user_id );
                }
            }
        }
        
    }
}
// End of My_Activate_All_Users_Class