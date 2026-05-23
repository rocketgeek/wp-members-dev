<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * A drop-in code snippet to update all users' membership
 * access and any applicable expiration date.
 *
 * To Use:
 * 1. Save the code snippet to your theme's functions.php
 * 2. Go to Tools > Update All Users.
 * 3. Follow prompts on screen.
 * 4. Remove the code snippet when completed.
 */

class WP_Members_Bulk_Edit_Users {

    public $settings = array(
        'enable_products' => "Membership",
        'mod_reg' => "Activation",
        'act_link' => "Confirmation",
    );
 
    function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    function admin_menu() {
        global $wpmem;
        if ( 1 == $wpmem->act_link || 1 == $wpmem->mod_reg || 1 == $wpmem->enable_products ) {
            $hook = add_users_page( 'WP-Members Bulk Edit Users', 'Bulk Edits', 'edit_users', 'wpmem-bulk-user-update', array( $this, 'admin_page' ) );
            add_action( "load-$hook", array( $this, 'admin_page_load' ) );
        }
    }

    function admin_page_load() {
        global $update_all_complete;
        $update_all_complete = false;

        $utility_state = wpmem_get( 'wpmem_bulk_utility_state', false, 'request' );

        if ( isset( $_GET['page'] ) && 'wpmem-bulk-user-update' == $_GET['page'] ) {
            
        }

        if ( 'update-all-users' == wpmem_get( 'page' ) && 1 == wpmem_get( 'update-all-confirm' ) ) {
            $users = get_users( array( 'fields'=>'ID' ) );
            // This is where we loop through users and update them.
            foreach ( $users as $user_id ) {
                
                switch ( $utility_state ) {

                    case 'activation_confirm':
                        update_user_meta( $user_id, 'wpmem_activated', 1 );
                        break;

                    case 'confirmation_confirm':
                        update_user_meta( $user_id, 'wpmem_confirmed', 1 );
                        break;

                }                
            }
            $update_all_complete = true;
        }
    }

    function admin_page() {
        global $wpmem, $update_all_complete;

        $utility_state = wpmem_get( 'wpmem_bulk_utility_state', false, 'request' );

        echo '<div class="wrap">';
        echo "<h2>" . esc_html__( 'WP-Members Bulk User Update', 'wp-members' ) . "</h2>";
        echo '<form name="wpmem-bulk-update-all-users" id="wpmem-bulk-update-all-users" method="post" action="' . wpmem_admin_form_post_url() . '">';

        switch ( $utility_state ) {

            case false:
                
                echo '<p>This utility allows you to run various bulk edits to all users.</p>';
                echo '<p>Select the utility to run:</p>';
                echo '<select name="wpmem_bulk_utility_state">
                        <option value="">Select option</option>';
                foreach ( $this->settings as $setting => $label ) {
                    if ( 1 == $wpmem->{$setting} ) {
                       echo '<option value="start_' . esc_attr( strtolower( $label ) ). '">' . $label . '</option>';
                    }
                }
                echo '</select>
                    <input type="submit" name="submit" value="Submit" />';
                break;

            case 'start_activation':
            case 'start_confirmation':
                echo '<p>';
                echo ( 'start_activation' == $utility_state ) ? 'This process will set ALL users as activated.' : 'This process will set ALL users as confirmed.';
                echo '</p>';
                echo '<p><input name="wpmem_bulk_utility_state" type="checkbox" value="activation_confirm" /> ';
                echo ( 'start_activation' == $utility_state ) ? 'Activate all users' : 'Confirm all users';
                echo '</p>';
                echo '<input type="submit" name="submit" value="Submit" />';
                break;

            case 'activation_confirm':
            case 'confirmation_confirm':
                echo '<p>';
                echo ( 'start_activation' == $utility_state ) ? 'All users have been set as activated.' : 'All users have been set as confirmed.'; 
                echo '</p>';
                break;

            case 'start_membership':
                echo '<p>';
                echo 'This will set all users to a valid membership based on imported values.';
                echo '<p>';
                break;

            case 'membership_confirm':
                echo '<p>';
                echo 'All user memberships have been set.';
                echo '</p>';
                break;
        }

        if ( $update_all_complete ) {
            echo '<p>All users were updated.<br />';
            echo 'You may now remove this code snippet if desired.</p>';
        } else {

        }

        echo '</form>';
        echo '</div>';
    }
}
// End of My_Update_All_Users_Class