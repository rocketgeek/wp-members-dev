=== WP-Members Membership Plugin ===
Contributors: cbutlerjr
Tags: membership, registration, login, authentication, restriction
Requires at least: 4.0
Tested up to: 6.7
Stable tag: 3.5.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
The original WordPress membership plugin with content restriction, user login, custom registration fields, user profiles, and more.

=== Membership Sites. Simplified. ===

You need a membership site, but you want to focus on your business, not mastering a plugin. WP-Members is simple to use, easy to set up, yet flexible in every way imaginable.

__Simple to install and configure - yet customizable and scalable!__

= Features: =

* Restrict or hide posts, pages, and custom post types
* Limit menu items to logged in users
* User login, registration, and profile integrated into your theme
* Create custom registration and profile fields
* Integrate custom fields into WooCommerce checkout and registration (only supported by shortcode pages, not block editor version)
* Create custom memberships and content restriction
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Create post excerpt teaser content automatically
* [Shortcodes for login, registration, content restriction, and more](https://rocketgeek.com/plugins/wp-members/docs/shortcodes/)
* Create powerful customizations with [more than 120 action and filter hooks](https://rocketgeek.com/plugins/wp-members/docs/filter-hooks/)
* [A library of API functions for extensibility](https://rocketgeek.com/plugins/wp-members/docs/api-functions/)

WP-Members allows you to restrict content as restricted or hidden, limiting access to registered users.

A full Users Guide is [available here](https://rocketgeek.com/plugins/wp-members/docs/). The guide outlines the installation process, and also documents how to use all of the settings.

= Support =

There is [freely available documentation on the plugin's support site](https://rocketgeek.com/plugins/wp-members/docs/). Your question may be answered there. If you need assistance configuring the plugin or have questions on how to implement or customize features, [premium support is available](https://rocketgeek.com/product/wp-members-plugin-support/).

You can get priority support along with all of the plugin's premium extensions in one [cost saving Pro Bundle!](https://rocketgeek.com/product/wp-members-pro-bundle/)

= Premium Support =

Premium support subscribers have access to priority email support, examples, tutorials, and code snippets that will help you extend and customize the base plugin using the plugin's framework. [Visit the site for more info](https://rocketgeek.com/plugins/wp-members/support-options/).

= Free Extensions =

* [Stop Spam Registrations](https://rocketgeek.com/product/stop-spam-registrations/) - Uses stopforumspam.com's API to block spam registrations.
* [Send Test Emails](https://rocketgeek.com/product/send-test-emails/) - A utility to send test versions of the plugin's emails.

= Premium Extensions =

The plugin has several premium extensions for additional functionality. You can purchase any of them individually, or get them all for a significant discount in the Pro Bundle.

* [Advanced Options](https://rocketgeek.com/plugins/wp-members-advanced-options/) - adds additional settings to WP-Members for redirecting core WP created URLs, redirecting restricted content, hiding the WP toolbar, and more! Also includes integrations with popular plugins like WooCommerce, BuddyPress, bbPress, ADF, Easy Digital Downloads, and The Events Calendar.
* [Download Protect](https://rocketgeek.com/plugins/wp-members-download-protect/) - Allows you to restrict access to specific files, requiring the user to be logged in to access.
* [Invite Codes](https://rocketgeek.com/plugins/wp-members-invite-codes/) - set up invitation codes to restrict registration to only those with a valide invite code.
* [MailChimp Integration](https://rocketgeek.com/plugins/wp-members-mailchimp-integration/) - add MailChimp list subscription to your registation form.
* [Memberships for WooCommerce](https://rocketgeek.com/plugins/wp-members-memberships-for-woocommerce/) - Sell memberships through WooCommerce.
* [PayPal Subscriptions](https://rocketgeek.com/plugins/wp-members-paypal-subscriptions/) - Sell restricted content access through PayPal.
* [Security](https://rocketgeek.com/plugins/wp-members-security/) - adds a number of security features to the plugin such as preventing concurrent logins, registration form honey pot (spam blocker), require passwords be changed on first use, require passwords to be changed after defined period of time, require strong passwords, block registration by IP and email, restrict specified usernames from being registered.
* [Text Editor](https://rocketgeek.com/plugins/wp-members-text-editor/) - Adds an editor to the WP-Members admin panel to easily customize all user facing strings in the plugin.
* [User List](https://rocketgeek.com/plugins/wp-members-user-list/) - Display lists of users on your site. Great for creating user directories with detailed and customizable profiles.
* [User Tracking](https://rocketgeek.com/plugins/wp-members-user-tracking/) - Track what pages logged in users are visting and when.
* [WordPass Pro](https://rocketgeek.com/plugins/wordpass/) - Change your random password generator from gibberish to word-based passwords (can be used with or without WP-Members).

Get support along with all of the plugin's premium extensions in one [cost saving Pro Bundle!](https://rocketgeek.com/product/wp-members-pro-bundle/)


== Installation ==

WP-Members is designed to run "out-of-the-box" with no modifications to your WP installation necessary. Please follow the installation instructions below. __Most of the support issues that arise are a result of improper installation or simply not reading/following directions__.

= Basic Install: =

The best way to begin is to review the [Initial Setup Video](https://rocketgeek.com/plugins/wp-members/docs/videos/). There is also a complete [Users Guide available](https://rocketgeek.com/plugins/wp-members/docs/) that covers all of the plugin's features in depth.

1. Upload the `/wp-members/` directory and its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress&reg;

You are ready to begin using WP-Members. Now follow the instructions titled "Locking down your site" below.

= Locking down your site: =

* To restrict posts, you will need to use the `<!--more-->` link in your posts. Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post. You may also use the plugin's auto excerpt setting to create post excerpts automatically. If you do not use the "more" tag or the auto excerpt setting, full post content is going to show on archive templates, unless the post is marked as hidden.
* To begin restricting pages, change the plugin default setting for pages to be blocked. Unlike posts, the `<!--more-->` link is not necessary in the blocking of pages, but __must__ be used if you have the "show excerpts" setting turned on for pages.
* To protect comments, we recommend setting "Users must be registered and logged in to comment" under Settings > Discussion.
* On the Settings > General page, it is recommended that you uncheck "Anyone can register". While not required, this will prevent WP's native registration from colliding with WP-Members, especially if you are using any of the WP-Members additional registration fields.
* Under Settings > Reading, "For each article in a feed, show" is recommended to be set to "Summary."  WordPress installs with full feed settings by default. If you don't change this, your feeds will show full content.

= Additional Setup Information =

There are also some special pages that can be created with simple shortcodes:

* A User Profile page where registered members can edit their information and change/reset their password: [wpmem_profile]
* A Registration page available for those that need a specific URL for registrations (such as email marketing or banner ad landing pages). Note: this is strictly optional as a registration form can also be included by default on blocked content pages: [wpmem_form register]
* A Login page. This is also an optional page as the login form is included by default on blocked content. But if you need a specific login page, this can be created with a simple shortcode: [wpmem_form login]
* [And more shortcodes are available](https://rocketgeek.com/plugins/wp-members/docs/shortcodes/)!

Powerful cusotmizations can be constructed with [over 120 action and filter hooks](https://rocketgeek.com/plugins/wp-members/docs/filter-hooks/), as well as user accessible functions.


== Frequently Asked Questions ==

The FAQs are maintained at https://rocketgeek.com/plugins/wp-members/docs/faqs/


== Upgrade Notice ==

WP-Members 3.5.2 is a bug fix release. WP-Members 3.5.0 is a major update. See changelog for a list of updates. Minimum WP version is 4.0.


== Screenshots ==

1. The default when viewing a blocked post - the plugin will deliver a login screen and registration form in place of blocked content (this default can be changed to other options).

2. Admin Panel - Options Tab - the various option settings for the plugin.

3. Admin Panel - Fields Tab - the plugin field manager allows you to manage (or delete) the installed extra fields and field order, and also add your own custom fields.

4. Admin Panel - Dialogs Tab - the major dialogs that the plugin uses for error and other messages can be edited in the plugin's admin panel.

5. Admin Panel - Emails Tab - all of the emails that are sent by the plugin can be edited in the admin panel.

6. Posts > All Posts - The plugin adds a column to the list of posts and pages to display if a post or page is unblocked or blocked (the opposite of whatver you have set for the plugin's default in the options tab).

7. Posts > Edit Post - The plugin adds a meta box to the post/page editor allowing you to set an individual post to be blocked or unblocked (the opposite of whatver your default setting is).

8. Responsive forms.


== Changelog ==

= 3.5.2 =

* Fixes a bug in the WP_Members_Dialogs::get_text() for unknown keys (reconfirm_link_before & reconfirm_link).
* Fixes a bug in the [wpmem_user_memberships] shortcode that breaks the expiration date display.
* Fixes a bug in the install/upgrade script that causes the "finalize" dialog to display indefinitely for a new install.
* Fixes a bug in the install/upgrade script that didn't properly transfer stylesheet settings if the stylesheet was not the default.
* Fixes a bug in the html email option, fix prevents from calling it twice.
* Fixes a bug in the membership stack reading that caused an infinite loop (may or may not be a bug, depending on specific local install settings).
* Improve handling of multicheckbox and multiselect field types when data is serialized (from WooCommerce).
* Improve all settings to autoload only those which are needed, specifically set to false those which are not.
* Improve email options to not autoload (previously set to true). These only need to load when called.
* Improve wpmem_update_option() to accept an autoload value (defaults to null, just like core WP function).
* Improve membership options to store in a single option to minimize query every object load. Update option when memberships are updated.
* Improve uninstall to remove all possible wpmem_user_count transients.
* Improve uninstall to remove all possible formats of the widget name.
* Improve stylesheet load (checks for a custom URL value rather than the "select_style" setting).
* Review which objects are loaded and when. Improve where possible.  Moved password reset object to only load when doing a password reset.
* Add error handling to WP_Members_Dialogs::get_text() for string keys that do not exist. If one is called, the function will return an empty string and will record the call in the error log.
* Adds new CLI command "wp mem db autoload-size".
* Adds wpmem_get_user_meta filter hook.

= 3.5.1 =

* Fixes a bug in the CLI interface that doesn't load the db tools correctly.
* Fixes a bug in the Fields tab edit view that displays two textarea inputs for select, multiselect, multicheckbox, and radio field types.
* Fixes a bug in the admin email notification that does not display the [fields] shortcode.
* Fixes a bug in the Shortcodes tab that throws a PHP error on settings save.
* Fixes a bug that causes fields to not be added to the WP native registration form or processed properly in the Add New screen.
* Fixes bugs in adding WP-Members fields to WooCommerce forms.
* Improves the select, multiselect, multicheckbox, and radio field types so that inadvertent white space after the delimiter "|" is removed.
* Improves the password reset to use esc_url_raw() instead of esc_url() on the reset link.  Also trims whitespace before assembling query args and rawurlencodes the query args before link assembly.
* Improves admin email notification, especially for HTML formatted email (removes hard `<br>` tag at the end of shortcode fields so they can be used in email subject line).
* Makes $field_arr array key in admin notification email filter `wpmem_notify_filter` obsolete (unlikely that anyone is using this).
* Adds new API functions: wpmem_get_file_field_url(), wpmem_get_field_type(), wpmem_is_file_field(), wpmem_get_field_label(), wpmem_is_field_required().

= 3.5.0 =

* IMPORTANT: WP-Members pluggable functions deprecated for use in theme functions.php.  WP-Members is now initialized when plugins are loaded, which is an earlier load action than previous versions.  If you have any WP-Members pluggable functions that load in the theme functions.php, you'll need to move these to another location, such as a custom plugin file.  Keep in mind, pluggable functions are no longer the preferred way of customizing (and have not been for many years) as most customizations, if not all, can be handled by using the plugin's filter and action hooks.
* IMPORTANT: Legacy password reset (requiring username & email to send a new password via email) is fully obsolete.  Plugin now only sends a password reset link for the user to then access the site and set a new password (no passwords via email).
* IMPORTANT: Legacy login error message is fully obsolete.  Legacy messages still used, in error message, but generation/display is now using the WP Error object class.

Bug fixes:
* Fixes a bug in the login_link shortcode that caused an empty href value.
* Fixes a bug in the login that causes double sessions.
* Fixes a bug in wpmem_user_has_access() that returns false for checking a specified user ID if the check is run when no user is logged in.
* Fixes a bug in wpmem_user_is_current() that throws a PHP error if the user does not have access to the requested membership (should return false in this instance).
* Fixes a bug if WooCommerce registration is used and WP-Members fields are set to be included but no specific WP-Members fields are identified for inclusion (empty value).
* Fixes a bug in the [wpmem_tos] shortcode if no URL is passed.
* Fixes a bug in membership check if the user doesn't have the membership.
* Fixes a bug in WP_Members::do_securify_rest() to check for a post ID, otherwise an error thrown when we try to check if the post is blocked.
* Fixes a bug in the check to see if a restricted WooCommerce product is purchasable by the user.
* Fixes a bug in the install routine when checking if index.php files exist in uploads folder that can cause the update process to fail.
* Fixes a bug in the [wpmem_user_membership_posts] shortcode that caused the title of the list to be whatever the title of the last post was. It should display the name of the membership associated with the list instead.

New features:
* Adds a "novalidate" option by filter toggle to the reg/login forms (for disabling the default HTML5 validation on required fields).
* Adds formatting filters (wpmem_field_shortcode_multi_args, wpmem_field_shortcode_multi_rows, wpmem_field_sc_multi_html) for field shortcode to customize HTML when displaying multiple select/multiple checkbox field results.
* User registration/profile fields are now selectable for each state (reg/profile) in the Fields tab.
* If WooCommerce is enabled, registration/profile fields are selectable for inclusion in WooCommerce checkout, registration, and profile forms.
* Adds "drop-ins" functionality (officially; this has actually been in the plugin since 3.4).
* Adds 'wpmem_user_profile_caps' filter hook for customizing the required user capability to inlcude the WP-Members tabs (experimental until confirmed with other extensions).
* Adds custom object class to handle custom functions when the Import Users and Customers plugin is used and moderated registration or confirmation link settings are enabled. 
* Adds wpmem_get_users()
* Adds wpmem_create_file()
* Adds new login error message if user is not confirmed with link to request a new confirmation link.
* Adds a resend confirmation link form for the user.
* Adds a resend confirmation link action in the admin (hoverlink in Users > All Users).
* Adds default email function for emails that are not completely set up.
* Adds direct shortcodes for [wpmem_login] and [wpmem_reg] that can be used in place of [wpmem_form] with the "login" or "reg" attributes.
* Adds WP_CLI commands for creating and managing db views (views, create-view, drop-view).
* Improves previous WP_CLI commands, now translation-ready and adds inline documentation (which extends to commandline help).
* Code improvement: if user object is filtered in `wpmem_register_form_args`, the form values are based on the filtered user ID.
* Code improvement: logout link in login shortcode uses `rawurlencode()` instead of `urlencode()`.

Security:
* Interim security updates from 3.4.9.x series included and improved.
* Security audit of shortcode object class.  Includes some of the updates from 3.4.9.x and expands on those.  All shortcode inputs from attributes is sanitized, all output is escaped.
* Improved handling of user directories for uploaded files (when used).

Other:
* Updates wpmem_get_memberships() to return an empty array if there are no memberships (previous versions returned a false boolean).
* Updates wpmem_email_to_user() to use tags instead of numeric tags, but numeric values are backward compatible.
* Can resend welcome email (with confirmation link) when confirmation link setting is enabled. This can be via the bulk action menu (multiple users) or hover link (single user).
* Removes obsolete file /admin/tab-options.php.  Users of the WP-Members User List extension version 1.9.4 and earlier will need to update the User List extension for full compatiblity.
* Removes obsolete file /inc/dialogs.php 
* Removes obsolete file /inc/email.php.  
* No longer installs default email content on clean install. (See release notes re: default email content function.)
* Removes stylesheet selector in admin.  Legacy stylesheets remain in the plugin package, so if they are selected, they will be used.  However, now to identify a stylesheet other than the default, you can simply enter the URL of the custom stylesheet location.
* Updates dialogs array used by wpmem_get_text() to include all user facing strings (adds strings that have been added by special features over the past several updates).
* Can no longer directly update from a version earlier than 3.0.0 (not that there are any out there; 92% of all installs are 3.2 or greater).  A 2.x version update is better off with a clean install.

= 3.4.9 =

* Adds wpmem_field_sc_meta_keys filter hook to filter meta keys allowed by the [wpmem_fields] shortcode (default: fields that are in the WP-Members Fields array).
* Adds wpmem_is_login(), wpmem_is_register(), and wpmem_is_profile() conditional functions.
* Adds index.php to user upload directories to prevent directory browsing if not specifically disabled elsewhere.
* Define $woo_connector object variable for PHP 8.2+ with the premium WooCommerce integration extension.
* Early patch fix for export if memberships are enabled but there are no memberships defined (from 3.5.0 included fixes).
* Early patch fix for fields data list in admin notification email if HTML formatted email is enabled (from 3.5.0 included fixes).
* Security update: Review shortcode object class for sanitizing all shortcode attributes and escaping all output.
* Security update: Review admin user profile class for sanitizing all input and escaping all output.
* Security update: Restrict use of the [wpmem_fields] shortcode.  See the release notes on the support site for more detail.
* Update WP version compatibility.

= 3.4.8 =

* Fixes a 3.4.7 bug that causes "extra" user fields to not save option to add to user screen (users > all users).
* Fixes a 3.4.7 bug that throws a php error when saving settings in the main options tab.
* Fixes bug in 3.4.7 that causes custom fields array to be overwritten as empty when updating fields in the plugin's Fields tab.
* Fixes issues with updating WP-Members WooCommerce integration settings in the main WP-Members options tab.
* Security update in Fields tab reorder processing.
* Code improvement udpates to RS Captcha validation processing.
* Adds wpmem_get_form_state() API function (replaces checking $wpmem->regchk directly).
* Destroy user sessions when deactivating a user.

* Removes the following legacy files originally kept for backward compatibility. However, we have moved far beyond where those versions can be supported any longer.
** /admin/post.php
** all legacy translation files (legacy .pot maintained, but use polyglots language packs instead)

* The following are obsolete, scheduled for removal at WP-Members 3.5.4:
** /inc/dialogs.php
** /inc/email.php
** /admin/tab-options.php

= 3.4.7 =

* Code improvement and database upgrade for admin user search functions; removes the wp_wpmembers_user_search_keys table and replaces it with wp_wpmembers_user_search_crud.
* Code improvement in the password reset function for situations where an error may result in an empty user object.
* Code improvement in REST API filtering of blocked content for situations where there may be additional (i.e. custom) values (such as those created by page builders).
* Code improvement in hidden posts checking in case the "post__not_in" query_var is not set.
* Code improvement to make sure required fields are required for the wp user profile, but allowable to be skipped by an admin.
* Code improvement to make sure all object variables are declared for php 8.2.
* Added timestamp field type.
* Added wpmem_get_membership_id() API function.
* Added wpmem_get_membership_slug() API function.
* Added wpmem_is_enabled() API function for checking if specific settings are enabled.
* Added "select all" option to several fields columns in the Fields tab.
* Added previous data array when updating user fields, can be used in filters to check for changes.
* Added ability to make WooCommerce products not purchaseable.
* Added wpmem_user_memberships shortcode to display a user's memberships.
* Added wpmem_user_membership_posts shortcode to display a list of membership restricted posts available to a user.
* Moved WooCommerce options out of "new feature" settings and expanded options.
* Added option to add WP-Members fields to the WooCommerce My Account user profile update.
* Added option to restrict WooCommerce product purchase if the product is set as restricted (requires that WC "product" custom post type be enabled for WP-Members).
* Added wpmem_remove_membership_from_post() to the API.
* Fix bug in wpmem_add_membership_to_post().

= 3.4.6 =

* Fixes a bug in the user profile update object class that prevented non-admin users from updating WP-Members custom fields in the dashboard profile view.
* Fixes some logic in the registration/profile update to check for a valid $user object (if it's a profile). It is rare that it wouldn't be, but this is a "just in case" to avoid unnecessary php notice errors.
* Adds wpmem_get_user_by_meta() API function to retrieve a $user object by user meta (WP's get_user_by() only does username, email, and ID).
* Adds wpmem_add_membership_to_post() API function to programmatically add a membership to a post.  Can be used for bulk and on-the-fly post restriction.
* Adds wpmem_add_membership_to_posts() API functions to programmatically add a membership to a group of posts (an array of IDs or comma separated IDs).  Can be used for bulk and on-the-fly post restriction.
* Adds wpmem_create_membership() API function to programmatically create a membership.  Can be used to create new memberships when hooked to other actions.
* Adds wpmem_create_username_from_email() API function.  If WooCommerce is installed, it will use the WC process, otherwise it uses a email user + number process until it finds a unique value.
* Adds wpmem_login_link(), wpmem_get_login_link(), wpmem_reg_link(), and wpmem_get_reg_link() for getting and displaying links to these identified pages (based on plugin's settings).
* Adds rktgk_wp_parse_args() to general plugin API. This is a utility function that functions like WP's wp_parse_args(), but is fully recursive (which wp_parse_args() is not).
* Adds rktgk_get_row() to the general plugin API. This is a utility that functions like WP's wpdb::get_row(), but incrporates wpdb::prepare() by default (saving a necessary step).
* Updates rktgk_build_html_tag() in the general plugin API to include an "echo" parameter to automatically print result to screen (false by default).
* Adds two new dialog message strings for acct_not_approved & acct_not_validated.
* Adds $tag for the form being generated in the wpmem_{$form}_defaults set of filters (login|changepassword|resetpassword|forgotusername).
* Adds author ID support for [wpmem_field] shortcode to display user meta data based on the post/page author ID (rather than the current user or querystring user).
* Adds filter support for "shortcode_atts_wpmem_profile"
* Improve message handling for password reset when moderated registration and confirmation link settings are enabled (and the user is not activated or confirmed).

= 3.4.5 =

* 3.4.4 is not compatible with [WP-Members Advanced Options](https://rocketgeek.com/plugins/wp-members-advanced-options/) when redirect to login is used.  This version corrects that issue by rolling back the change to only load membership restriction functions when the membership products setting is enabled.
* Adds wpmem_login_form_button_rows filter hook.
* Adds wpmem_pwd_reset_email_link filter hook
* Adds API functions wpmem_profile_url(), wpmem_pwd_reset_url(), wpmem_register_url(), wpmem_forgot_username_url(). 
* Adds API functions wpmem_get_membership_name(), wpmem_get_membership_meta(), wpmem_get_membership_post_list(), wpmem_get_post_memberships(), wpmem_get_memberships().
* Adds API functions wpmem_add_query_where(), wpmem_get_query_where(), wpmem_add_user_view_link(), wpmem_get_user_view_link(), wpmem_get_user_view_count().
* Updates user views to use new API functions and adds capability to more easily customize user views.
* Code improvement: update instances of deprecated function wpmem_gettext() to use wpmem_get_text().
* Code improvement: update wpmem_user_has_role(), $current_user global no longer necessary.
* Code improvement: update select2 library to version 4.1.0.
* CSS update: defines columns widths for Settings > WP-Members > Fields table.

= 3.4.4 =

* Adds excerpt to membership restricted content when excerpts are used and the user is logged in (should work the same as blocked content for a non-logged in user).
* Adds excerpt to wpmem_product_restricted_args arguments to be edited or removed using the filter.
* Adds [memberships] shortcode for admin notification email; this will include a list of memberships for the user in admin notification.
* Fixes potential issue with [wpmem_field] shortcode if field does not have a defined type.
* Updates to [wpmem_profile] and [wpmem_form password] for improved password reset.
* Moves password reset link actions to template_redirect action. This should resolve issues that occur when multiple instances of the_content are run (i.e. the appearance of an invalid key message upon completing the password reset).
* Moves export class to main user object (previously loaded from admin files). @todo Export class file also remains in admin for backward compatibility if file is called directly.
* Moves admin object load (back) to "init" action (from "admin_init") as later load can cause problems with extensions loading on the "wpmem_after_admin_init" action.
* Load dependencies after settings are loaded (allows for conditional loading of certain dependencies).
* Load membership/product restriction only if membership products setting is active.

= 3.4.3 =

* Simplified check_validated() and check_activated() functions, included check for null $user.
* Added wpmem_check_validated and wpmem_check_activated filter hooks.
* Added display="url" attribute to the [wpmem_field] shortcode for file and image field types.
* Fix undefined variable in password reset.
* Improve onboarding process for both new installs and updates.

= 3.4.2 =

* Applies checkbox CSS in add new user form.
* Code consolidation in admin options tab file (remove final use of wpmem_use_ssl()).
* Add wpmem_recaptcha_url filter to allow for changing the URL of the recaptcha script.
* Only apply pwd reset override on frontend (for login error).
* Fixes undefined $wpmem->reg_form_showing.
* Fixes a bug in the password change shortcode that causes a "too few arguments" error.
* Changes wpmem_is_user_current() to wpmem_user_is_current() for backwards compatibility with the plugin's premium PayPal extension.
* Added the action being done as a parameter passed to the wpmem_get_action action hook.
* Added support for arrays, urls, and classes to wpmem_sanitize_field() (alias of rktgk_sanitize_field()). This is in addition to the sanitization already supported.
* apply_custom_product_message() now runs do_shortcode() to natively support shortcodes in custom membership product messages.
* Fixed an issue that did not display the custom product message if the user was not logged in.
* Improved custom product message for non-logged in state (same function is used by both logged in and logged out processes, so cleaned up to handle both states the same).
* Bug fix in password reset that potentially truncates the reset link.
* Bug fix in admin notification email for HTML formatted email (wpautop() was not being applied to email content).
* Bug fix in wpmem_is_reg_type() that returned invalid object var.
* Added email arg for default linebreak.
* Added user ID to email filters.
* Added id, class, and wrapper attributes to [wpmem_logged_in] shortcode (wrapper defaults to "div" but can be changed to "span" or "p" or something else).
* Added user confirmed field to default export fields (if confirmation link setting is enabled).
* Added wpmem_set_user_membership(), wpmem_remove_user_membership(), and wpmem_get_user_memberships() API functions.
* Introduces new installer/onboarding for both new installs and upgrades.

= 3.4.1 =

* Revise the membership hierarchy logic (see release announcement for details).
* Changing "Product" text to "Membership" for clarity (was planned for 3.4.0).
* Changing "Block" text to "Restricted" for clarity (was planned for 3.4.0).
* Added wpmem_is_user_current() api function.
* Added attachements to email function.
* Added wpmem_email_attachments filter.
* Moves external libraries to "vendor" directory.
* Removes a overlooked use of wpmem_page_pwd_reset() which was deprecated as of 3.4.0.
* Sanitize email as email, not text.
* Fixes a bug in the user api for undefined variable when checking the user ip.
* Fixes a bug in 3.4.0 that causes an error in user export.
* Fixes a bug in 3.4.0 that causes the captcha validation to be run twice, resulting in failed captcha during registration.
* Fixes css issue that caused cursor change on all list table screens for drag-and-drop; should only show on Fields tab.

= 3.4.0 =

Here is a list of changes in 3.4.0, but for a more detailed look at the impact of some of these items, be sure to review https://rocketgeek.com/release-announcements/wp-members-3-4-0-wow/

* Rebuilds the login widget functions so there are filter hooks that more closely mimic the main body login filters. Every attempt was made to provide an HTML result that is the same as previous versions, as well as providing support for legacy dialog tags.
* Rebuilt and revised user export functionality.  Now includes an api function that can be used to customize user exports for a variety of uses.

New Feature Settings:
* The default password reset process is now the reset link. New installs will automatically have this setting.  Existing installs can change to this by toggling the setting to use the legacy option in Settings > WP-Members > Options > New Features.
* The default new registration process now uses the email confirmation link.  A user must confirm their email address prior to their account being able to log in.  New installs will automatically have this setting, but you may opt to use the legacy option by changing the setting in Settings > WP-Members > Options > New Features.
* The default emails at install reflect the above changes. Existing installs as always will not have their email content altered by the upgrade script.

* Post restricted message now completely separate from login form.
* Post restricted message now has new wrapper id - #wpmem_restricted_msg

* Improved redirect_to handling in login and register forms.  Can now specify a page by slug alone in the shortcode param for portability.
* Improved Google reCAPTCHA v3 ("invisible captcha") to optionally display on all pages (recommended by Google to improve user "score").
* Improved forms display in Customizer, now can view forms on blocked content (not just shortcode pages).
* Improved functionality of hidden posts. Now saved in WP settings (options) instead of as a transient.
* Improved user count transient. Now expires in 5 minutes instead of 30 seconds (will result in fewer loads of the query).

* Membership products now support hierarchy. This can be used for "levels" or for multiple expiration periods yet still only have to assign one membership to content for all child memberships.

* HTML email for WP-Members emails can be enabled as an option in the Emails tab.

* Login failed dialog now displays login form below the error. Removed "continue" (return) link from default message.
* Login failed dialog (#wpmem_msg) text centered in stylesheet instead of applying in the div tag. Best way to customize is using the WP Customizer.

* Updates to export function.
  - deprecated 'export_fields', use 'fields' instead.

* Clone menus is deprecated. The setting remains in place for users who have it enabled.  But if it is not enabled, the setting is no longer available.

* Expands Customizer functionality so logged out forms show on blocked content (not just shortcodes).

* Adds integration for WP's "registration_errors" filter hook, allowing for standarized custom validation and integration with other plugins.
  
New API functions:
* wpmem_is_reg_form_showing()
  
Deprecated functions:
* wpmem_inc_loginfailed()
* wpmem_inc_regmessage()
* wpmem_inc_login()
* wpmem_page_pwd_reset()
* wpmem_page_user_edit()
* wpmem_page_forgot_username()
* wpmem_inc_memberlinks()
* wpmem_gettext() - use wpmem_get_text() instead.
* $wpmem->texturize()

Bug fixes:
* Fixes a bug in the signon process that causes the "rememberme" option to be ignored.
* Fixes a bug in wpmem_is_blocked() that returns false when checking a specific post ID.
* Fixes a bug in the autoexcerpt function that caused a double "read more" link when excerpt length was set to zero.

