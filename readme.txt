=== WP-Members Membership Plugin ===
Contributors: cbutlerjr
Tags: membership, registration, login, authentication, restriction
Requires at least: 4.0
Tested up to: 6.9
Stable tag: 3.5.5.1
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

WP-Members 3.5.5.1 is a bug fix release. WP-Members 3.5.5 is a feature update release. It fixes some minor bugs and adds new filters and some additional features.  WP-Members 3.5.0 is a major update. See changelog for a list of updates. Minimum WP version is 4.0.


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

= 3.5.5.1 =

* Bug fix for checking expiration memberships that require a specific role.  The bug from 3.5.5 causes a user with the role to be viewed as having access even if they are expireed.

= 3.5.5 =

* Bug fix for `wpmem_get_user_count_by_role()` that causes it to return a total count of all users no matter the role requested.
* Bug fix for image field display in profile.
* Add filter to view posts by restriction status (i.e. Posts > All Posts view).
* Add filter to view posts by membership status (i.e. Posts > All Posts view).
* Add support to switch WP-Members password reset link over to WooCommerce link (adds new setting, updates db version).
* Add sorting to [wpmem_user_membership_posts] shortcode.
* Add sorting arguments to `wpmem_get_membership_post_list()` function.
* Add date format support for `wpmem_get_user_expiration()` function.
* Add `wpmem_show_membership_posts_sc_list_item` filter for [wpmem_user_membership_posts] shortcode output.
* Add `wpmem_is_user_deactivated()` API function.
* Add `wpmem_get_user_time_remaining()` API function to check for remaining time on memberships. 
* Add WP CLI command for checking remaining time on memberships.
* Add WP CLI command for listing user memberships.
* Update `wpmem_get_membership_role()` to return false (rather than null) if no role for the membership.
* Code improvement to consolidate `wpmem_logout_link` filter instances into `wpmem_logout_link()` function. 
* Code improvement in `has_access()` logic with better handling of role-based memberships.
* Security patches from 3.5.4.1, 3.5.4.2, 3.5.4.3, 3.5.4.4, and 3.5.4.5

= 3.5.4 =

* Bug fix for register form field label links.
* Bug fix that causes [wpmem_field] shortcode option to be reset when setting other settings or updating.
* Bug fix for maintaining [wpmem_field] shortcode setting on upgrade from pre-3.5.x version.
* Bug fix for updating stylesheet settings, especially if upgrading from pre-3.5.x version.
* Deprecated `$wpmem->select_style`.  This is part of the custom stylesheet settings/options, but is no longer needed.  Using only `$wpmem->cssurl` going forward will make upgrading easier for those who use a custom stylesheet.  This change should be transparent for all upgrades.
* Add additional form support for form field label links (native WP reg, WC forms, dashboard profile, [wpmem_field] shortcode).
* New API functions for user counts: `wpmem_user_count()`, `wpmem_get_user_count_by_meta()`, `wpmem_get_user_count_by_role()`.
* New API function for import: `wpmem_csv_to_array()`.
* Code improvement to radio field type display in native WP and WC My Account reg forms.
* Allow native WC fields in WC My Account reg form.
* Updates to WP CLI commands: All @alias are now @subcommand (changes all underscore commands to dash/hyphen. example:  <wp mem user get_role> is now <wp mem user get-role>).
* Updates to WP CLI commands: Improve and debug `wp mem import memberships` command.
* Updates to WP CLI commands: Improve and debug `wp mem membership` commands (CLI commands to add/update/delete user memberships).
* Updates to WP CLI commands: Add import commands for activate, deactivate, confirm, & unconfirm.
* Updates to WP CLI commands: CLI interface no longer localized (translatable), following core WP on this as the additional strings make the translation files too unwieldy.

= 3.5.3 =

* Add link support for field labels.
* Add wpmem_create_form_label_args filter.
* Add wpmem_form_label_link filter.
* Add new CLI commands for memberships (see release notes).
* Update CLI commands for translation (some were localized, others were not).
* Clean up new field add screen, adds register/profile as separate options (could previously only be selected on main table view), sets proper textarea for multiple checkbox settings.
* Fixes a bug in the default TOS dialog that causes a fatal error when opening the new window.
* Deprecated default TOS in favor of custom linked labels (old field is valid if used, but does not install as default with new install)
* Adds additional output sanitizing.

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
* Adds wpmem_get_user_meta filter hook.

= 3.5.1 =

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