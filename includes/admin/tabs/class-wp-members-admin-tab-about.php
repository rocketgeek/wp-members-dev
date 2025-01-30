<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the "about" tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2025  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2025
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_Tab_About {

    /**
     * Creates the About tab.
     *
     * @since 3.1.1
     *
     * @param  string      $tab The admin tab being displayed.
     * @return string|bool      The about tab, otherwise false.
     */
    static function do_tab( $tab ) {
        if ( $tab == 'about' ) {
            // Render the about tab.
            return self::build_settings();
        } else {
            return false;
        }
    }

    static function build_settings() { ?>
        <div class="metabox-holder has-right-sidebar">
            <div class="post-body"><div class="post-body-content">
            <div class="postbox"><div class="inside">
                <div style="width:20%;max-width:300px;min-width:200px;padding:10px;margin:10px;float:right;">
                    <?php wpmem_a_meta_box(); ?>
                    <?php wpmem_a_rating_box(); ?>
                    <?php wpmem_a_rss_box(); ?>
                    <div class="postbox"><div class="inside">
                    <h4><a href="<?php self::do_link( 'wordpass' ); ?>" target="_blank">WordPass</a></h4>
                    <p>Default random passwords can be difficult to for users to use.  WordPass simplifies this process by using words to create passwords. Passwords will be generated in the style of 2*Kayak29, 2Bigcranium2#, or %36POTATOE6.
                    </p>
                    <p>This plugin works with WordPress as well as with any plugin that uses the WordPress password generation function.</p>
                    <p><strong><a href="<?php self::do_link( 'wordpass_free' ); ?>" target="_blank">Try WordPass Free!</a></strong></p>
                    </div></div>
                 </div>
                <h2><?php _e( 'About WP-Members', 'wp-members' ); ?></h2>
                    <p>WP-Members is a WordPress membership plugin that is simple to use but incorporates a powerful API for customization.
                    A simple installation can be up and running in minutes. Yet, using the plugin's API, filters, and actions, the plugin can
                    be customized without touching the main plugin files.</p>
                    <p>Introduced publicly in 2006, WP-Members was the first WordPress Membership plugin and through support of the WP community it continues to grow
                    and be developed.  <strong>Why put your trust in an unknown?  WP-Members has a <?php echo date('Y') - date('Y', strtotime('2006-01-01')); ?> year track record of active development and support.</strong></p>
                    <p><strong><a href="<?php self::do_link( 'docs' ); ?>" target="_blank">Plugin Documentation</a></strong> |
                    <strong><a href="<?php self::do_link( 'support' ); ?>" target="_blank">Premium Support &amp; Extensions</a></strong></p>
                <h2>Priority Support</h2>
                    <p>If you want to make the most out of WP-Members, subscribing to Priority Support is a great way to do that. You'll not only get priority email support, but also a member-only forum  
                and access to the member's only site with a code library of tutorials and customizations. You can also subscribe to the WP-Members Pro Bundle to get everything Priority Support has to offer
                PLUS all of the premium extensions as well.<br /><br />
                <a href="<?php self::do_link( 'support' ); ?>" target="_blank"><strong>Check out the Premium Support options here</strong></a>.<br /><strong>NEW!! <a href="<?php self::do_link( 'one-on-one' ); ?>" target="_blank">One-on-one Consulting Now Available</a>!</strong></p>
                <h2>Premium Extensions</h2>
                    <table>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-advanced-options' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/advanced_options-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-advanced-options' ); ?>" target="_blank">Advanced Options</a></strong><br />
                    This exclusive extension adds a host of additional features to the plugin that are as simple to set up as checking a box. Hides the dashboard,
                    override WP default URLs for login and registration, disable certain WP defaults, change WP-Members defaults, notify admin on user profile
                    update, integrate with other popular plugins like WooCommerce, BuddyPress, and ACF (Advanced Custom Fields), and more.  See a list
                    of available settings here.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-download-protect' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/download_protect-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-download-protect' ); ?>" target="_blank">Download Protect</a></strong><br />Adds file restriction to the core WP-Members functionality. Restrict file downloads to registered users and track download activity.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-invite-codes' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/invitation_codes-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-invite-codes' ); ?>" target="_blank">Invite Codes</a></strong><br />Create invite codes for registration. Use to track sign-ups, or require a valid invite code in order to register.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-security' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_security-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-security' ); ?>" target="_blank">Security</a></strong><br />Set password expirations, require strong passwords, restrict concurrent logins, block specific IPs or email addresses from registering, restrict usernames like "admin" or "support" from being registered.  This extension allows you to block IPs, emails, and restrict username selection.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-text-editor' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_editor-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-text-editor' ); ?>" target="_blank">Text String Editor</a><br />Provides a simple way to edit all of the plugin's user-facing text strings. Includes text that is used in the various forms, custom messages, form headings, etc.</strong></td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-user-list' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_userlist-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-user-list' ); ?>" target="_blank">User List</a></strong><br />Provides a configurable shortcode to create user/member directories.  The extension allows you to set defaults for the shortcode and to override any of those defaults with shortcode parameters for an unlimited number of lists.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-user-tracking' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_user_tracking-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-user-tracking' ); ?>" target="_blank">User Tracking</a></strong><br />Tracks site usage by registered logged in users. Review what pages a user is viewing, download data as CSV.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-mailchimp-integration' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2017/12/mailchimp_logo-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-mailchimp-integration' ); ?>" target="_blank">MailChimp Integration</a></strong><br />Integrate MainChimp newsletter signup directly into your WP-Members registration form and allow users to subscribe/unsubscribe from their profile.</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-paypal-subscriptions' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2017/12/paypal_PNG22-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-paypal-subscriptions' ); ?>" target="_blank">PayPal Subscriptions</a></strong><br />Start charging for access to your membership site.  Easy to set up and manages user expiration dates.  Uses basic PayPal IPN (Instant Payment Notification).</td>
                        </tr>
                        <tr>
                            <td><a href="<?php self::do_link( 'wp-members-salesforce-web-to-lead-integration' ); ?>" target="_blank"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/salesforce-80x80.png" /></a></td>
                            <td><strong><a href="<?php self::do_link( 'wp-members-salesforce-web-to-lead-integration' ); ?>" target="_blank">Salesforce Integration</a></strong><br />Integrates Salesforce Web-to-Lead with the WP-Members registration form data.</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div></div></div><?php
    }

    static private function do_link( $which ) {

        $query_args = array( 
            'utm_source' => 'wpmem',
            'utm_medium' => 'in_plugin',
            'utm_campaign' => 'wpmem_plugin',
        );

        $site_urls = array( 
            'docs' => 'https://rocketgeek.com/plugins/wp-members/docs/',
            'support' => 'https://rocketgeek.com/plugins/wp-members/support-options/',
            'one-on-one' => 'https://rocketgeek.com/product/wp-members-one-on-one-consulting/',
        );

        $domain = 'https://rocketgeek.com/';

        switch ( $which ) {
            case 'docs':
            case 'support':
                $url = add_query_arg( $query_args, $site_urls[ $which ] );
                break;
            
            case 'wordpass_free';
                $url = 'https://wordpress.org/plugins/wordpass/';
                break;
            
            default:
                $path = trailingslashit( 'plugins/' . $which );
                $url = add_query_arg( $query_args, $domain . $path );
                break;
        }

        echo esc_url( $url );
    }

} // End of file.