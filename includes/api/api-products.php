<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2025  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2025
 */

/**
 * Gets all posts by product key.
 *
 * @since Unknown
 * @since 3.4.5 Alias of wpmem_get_membership_post_list().
 * @deprecated 3.5.0 Use wpmem_get_membership_post_list() instead.
 *
 * @global  stdClass  $wpmem
 * @param   string    $product_key
 * @return  array
 */
function wpmem_get_product_post_list( $product_key ) {
	return wpmem_get_membership_post_list( $product_key );
}

/**
 * Gets all posts by membership key.
 *
 * @since 3.4.5
 *
 * @global  stdClass  $wpmem
 * @param   string    $membership_key
 * @return  array
 */
function wpmem_get_membership_post_list( $membership_key ) {
	global $wpmem;
	return $wpmem->membership->get_all_posts( $membership_key );
}

/**
 * Gets the membership products for a given post.
 *
 * @since 3.3.7
 * @since 3.4.5 Alias of wpmem_get_post_memberships().
 * @deprecated 3.5.0 Use wpmem_get_post_memberships() instead.
 *
 * @global  stdClass  $wpmem
 * @param   integer   $post_id
 * @return  array
 */
function wpmem_get_post_products( $post_id ) {
	return wpmem_get_post_memberships( $post_id );
}

/**
 * Gets the membership products for a given post.
 *
 * @since 3.4.5
 *
 * @global  stdClass  $wpmem
 * @param   integer   $post_id
 * @return  array
 */
function wpmem_get_post_memberships( $post_id ) {
	global $wpmem;
	return $wpmem->membership->get_post_products( $post_id );
}

/**
 * Gets access message if user does not have required membership.
 *
 * @since 3.4.0
 *
 * @global  stdClass  $wpmem
 * @param   array     $post_products
 * @return  string    $message
 */
function wpmem_get_access_message( $post_products ) {
	global $wpmem;
	return $wpmem->membership->get_access_message( $post_products );
}

/**
 * Gets all memberships for the site.
 * Alias of wpmem_get_memberships().
 * 
 * @since Unknown
 * @deprecated 3.5.0 Use wpmem_get_memberships() instead.
 * 
 * @return array
 */
function wpmem_get_products() {
	return wpmem_get_memberships();
}

/**
 * Gets all memberships for the site.
 * 
 * @since Unknown
 * 
 * @return array
 */
function wpmem_get_memberships() {
	global $wpmem;
	return ( ! empty( $wpmem->membership->memberships ) ) ? $wpmem->membership->memberships : array();
}

/**
 * Get array of memberships keyed by ID.
 * 
 * @since Unknown
 * 
 * return array
 */
function wpmem_get_memberships_ids() {
	global $wpmem;
	return array_flip( $wpmem->membership->membership_by_id );
}

/**
 * Get membership id by slug.
 * 
 * @since 3.4.7
 * @todo Fix for child memberships
 * @param   string  $membership_slug
 * @return  int     $ID
 */
function wpmem_get_membership_id( $membership_slug ) {
	$membership = get_page_by_path( $membership_slug, OBJECT, 'wpmem_product' );
	return ( $membership ) ? $membership->ID: false;
}

/**
 * Get membership display title by slug.
 * 
 * @since 3.4.5
 * 
 * @param  string  $membership_slug
 * @return string  Value of $wpmem->membership->memberships[ $membership_slug ]['title'] if set, otherwise, $membership_slug.
 */
function wpmem_get_membership_name( $membership_slug ) {
	global $wpmem;
	return ( isset( $wpmem->membership->memberships[ $membership_slug ]['title'] ) ) ? $wpmem->membership->memberships[ $membership_slug ]['title'] : $membership_slug;
}

/**
 * Get the membership name (slug) by membership ID.
 * 
 * @since 3.4.7
 * 
 * @param  int    $membership_id  The membership ID
 * @return string                 The membership name/slug
 */
function wpmem_get_membership_slug( $membership_id ) {
	return get_post_field( 'post_name', $membership_id );
}

/**
 * Get the role required by a membership (if any).
 * 
 * @since 3.5.0
 * 
 * @param  string  $slug  The membership slug (meta key).
 */
function wpmem_get_membership_role( $membership_slug ) {
	global $wpmem;
	return ( isset( $wpmem->membership->memberships[ $membership_slug ]['role'] ) ) ? $wpmem->membership->memberships[ $membership_slug ]['role'] : '';
}

/**
 * Get meta key for membership (with stem).
 * 
 * @since 3.4.5
 * 
 * @param  string  $membership_slug
 * @return string
 */
function wpmem_get_membership_meta( $membership_slug ) {
	global $wpmem;
	return $wpmem->membership->post_stem . $membership_slug;
}

/**
 * Adds a membership to a post.
 * 
 * @since 3.4.6
 * @since 3.4.7 Added $action param
 * 
 * @param  string  $membership_meta
 * @param  int     $post_id
 * @param  string  $action          Action to run (add|remove default:add)
 */
function wpmem_add_membership_to_post( $membership_meta, $post_id, $action = 'add' ) {

	global $wpmem;
	
	// Get existing post meta.
	$post_memberships = get_post_meta( $post_id, $wpmem->membership->post_meta, true );

	if ( 'remove' == $action ) {
		// If we are removing, remove the meta key from the array.
		if ( is_array( $post_memberships ) ) {
			if ( ( $key = array_search( $membership_meta, $post_memberships ) ) !== false ) {
				unset( $post_memberships[ $key ] );
			}
		}
	} else {
		// If the post has membership restrictions already, add new membership requirement.
		if ( is_array( $post_memberships ) ) {
			if ( ! in_array( $membership_meta, $post_memberships ) ) {
				$post_memberships[] = $membership_meta;
			}
		} else {
			$post_memberships = array( $membership_meta );
		}
	}

	// Update post meta with restriction info.
	update_post_meta( $post_id, $wpmem->membership->post_meta, $post_memberships );

	if ( 'remove' == $action ) {
		delete_post_meta( $post_id, wpmem_get_membership_meta( $membership_meta ) );
	} else {
		update_post_meta( $post_id, wpmem_get_membership_meta( $membership_meta ), 1 );
	}
}

/**
 * Removes a membership from a post.
 * 
 * @since 3.4.7
 * 
 * @param  string  $membership_meta
 * @param  int     $post_id
 */
function wpmem_remove_membership_from_post( $membership_meta, $post_id ) {
	wpmem_add_membership_to_post( $membership_meta, $post_id, 'remove' );
}

/**
 * Adds a membership to an array of post IDs.
 * 
 * @since 3.4.6
 * @since 3.4.7 Added $action param
 * 
 * @param  string         $membership_meta
 * @param  string|array   $post_ids
 * @param  string         $action          Action to run (add|remove default:add)
 */
function wpmem_add_membership_to_posts( $membership_meta, $post_ids, $action = 'add' ) {
	// Make sure $post_ids is an array (prepare comma separated values)
	$posts_array = ( ! is_array( $post_ids ) ) ? explode( ",", $post_ids ) : $post_ids;
	
	if ( 'remove' == $action ) {
		// Run wpmem_remove_membership_from_post() for each ID.
		foreach ( $posts_array as $ID ) {
			wpmem_remove_membership_from_post( $membership_meta, $ID );
		}
	} else {
		// Run wpmem_add_membership_to_post() for each ID.
		foreach ( $posts_array as $ID ) {
			wpmem_add_membership_to_post( $membership_meta, $ID );
		}
	}
}

/**
 * Removes a membership from an array of post IDs.
 * 
 * @since 3.4.7
 * 
 * @param  string         $membership_meta
 * @param  string|array   $post_ids
 */
function wpmem_remove_membership_from_posts( $membership_meta, $post_ids ) {
	wpmem_add_membership_to_posts( $membership_meta, $post_ids, 'remove' );
}

/**
 * Create a membership.
 * 
 * @since 3.4.6
 * 
 * @param  array  $args {
 *     Parameters for creating the membership CPT.
 * 
 *     @type string $title      User readable name of membership.
 *     @type string $name       Sanitized title of the membership to be used as the meta key.
 *     @type string $status     Published status: publish|draft (default: publish)
 *     @type int    $author     User ID of membership author, Optional, defaults to site admin.
 *     @type array  $meta_input
 *         Meta fields for membership CPT (not all are required).
 * 
 *         @type string $name         The sanitized title of the membership.
 *         @type string $default
 *         @type string $role         Roles if a role is required.
 *         @type string $expires      Expiration period if used (num|per).
 *         @type int    $no_gap       If renewal is "no gap" renewal.
 *         @type string $fixed_period (start|end|grace_num|grace_per)
 *         @type int    $set_default_{$key}
 *         @type string $message      Custom message for restriction.
 *         @type int    $child_access If membership hierarchy is used.
 *     }
 * }
 * @return mixed  $post_id|WP_Error
 */
function wpmem_create_membership( $args ) {

	// Get the admin user for default post_author.
	$admin_email = get_option( 'admin_email' );
	$admin_user  = get_user_by( 'email', $admin_email );

	// Set up post args.
	$pre_postarr = array();
	foreach ( $args as $key => $value ) {
		if ( 'meta_input' == $key ) {
			foreach( $value as $meta_key => $meta_value ) {
				$pre_postarr['meta_input'][ 'wpmem_product_' . $meta_key ] = $meta_value;
			}
		} else {

			// If parent is identified by slug.
			if ( 'parent' == $key && ! is_int( $value ) ) {
				$value = wpmem_get_membership_id( $value );
			}

			$pre_postarr[ 'post_' . $key ] = $value;
		}
	}

	// Setup defaults.
	$default_args = array(
		'post_title'  => '',
		'post_name'   => ( isset( $pre_postarr['post_name'] ) ) ? sanitize_title( $pre_postarr['post_name'] ) : sanitize_title( $pre_postarr['post_title'] ),
		'post_status' => 'publish',
		'post_author' => $admin_user->ID,
		'post_type'   => 'wpmem_product',
		'meta_input'  => array(
			'wpmem_product_name'    =>  ( isset( $pre_postarr['meta_input']['wpmem_product_name'] ) ) ? sanitize_title( $pre_postarr['meta_input']['wpmem_product_name'] ) : ( ( isset( $pre_postarr['post_name'] ) ) ? sanitize_title( $pre_postarr['post_name'] ) : sanitize_title( $pre_postarr['post_title'] ) ),
			'wpmem_product_default' => false,
			'wpmem_product_role'    => false,
			'wpmem_product_expires' => false,
		),
	);

	/**
	 * Filter the defaults.
	 * 
	 * @since 3.4.6
	 * 
	 * @param array $default_args {
	 *     Mmembership CPT params for wp_insert_post().
	 * 
	 *     @type string $post_title      User readable name of membership.
	 *     @type string $post_name       Sanitized title of the membership to be used as the meta key.
	 *     @type string $post_status     Published status: publish|draft (default: publish)
	 *     @type int    $post_author     User ID of membership author, Optional, defaults to site admin.
	 *     @type string $post_type       Should not change this: default: wpmem_product.
	 *     @type array  $meta_input
	 *         Meta fields for membership CPT (not all are required).
	 * 
	 *         @type string $wpmem_product_name         The sanitized title of the membership.
	 *         @type string $wpmem_product_default
	 *         @type string $wpmem_product_role         Roles if a role is required.
	 *         @type string $wpmem_product_expires      Expiration period if used (num|per).
	 * 
	 *         The following are optional and are not passed in the default args but could be returned by filter.
	 * 
	 *         @type int    $wpmem_product_no_gap       If renewal is "no gap" renewal.
	 *         @type string $wpmem_product_fixed_period (start|end|grace_num|grace_per)
	 *         @type int    $wpmem_product_set_default_{$wpmem_product_key}
	 *         @type string $wpmem_product_message      Custom message for restriction.
	 *         @type int    $wpmem_product_child_access If membership hierarchy is used.
	 *     }
	 * }
	 */
	$default_args = apply_filters( 'wpmem_create_membership_defaults', $default_args );

	if ( isset( $pre_postarr['meta_input']['wpmem_product_message'] ) ) {
		$pre_postarr['meta_input']['wpmem_product_message'] = wp_kses_post( $pre_postarr['meta_input']['wpmem_product_message'] );
	}

	// Merge with defaults.
	$postarr = rktgk_wp_parse_args( $pre_postarr, $default_args );

	// Insert the new membership as a CPT.
	$post_id = wp_insert_post( $postarr );

	// wp_insert_post() returns post ID on success, WP_Error on fail.
	return $post_id;
}

/**
 * Sets the expiration date for a membership
 * 
 * @since 3.5.0
 * 
 * @param  string  $membership
 * @param  int     $user_id
 * @param  mixed   $set_date
 * @param  mixed   $prev_value
 * @param  boolean $renew
 * @return mixed   $new_value
 */
function wpmem_generate_membership_expiration_date( $membership, $user_id, $set_date = false, $prev_value = false, $renew = false ) {
	global $wpmem;
	return $wpmem->membership->set_product_expiration( $membership, $user_id, $set_date, $prev_value, $renew );
}

// @todo EXPERIMENTAL
// Anything after this line is subject to change in future versions, including the name.
/**
 * Gets the count(s) of a given membership.
 * 
 * @since 3.5.3
 * 
 * @param  string  $membership  The membership slug
 * @param  string  $type        What type of count to get (all|active|expired default:all)
 * @return int     $count
 */
function wpmem_get_membership_count( $membership, $type = "all" ) {
	global $wpdb;
	$user_meta = wpmem_get_membership_meta( $membership );
	switch ( $type ) {
		case "active":
			$period = strtotime( "-" . wpmem_get_expiration_period( $membership ), time() );
			$compare = ">";
			break;
		case "expired":
			$period = strtotime( "-" . wpmem_get_expiration_period( $membership ), time() );
			$compare = "<";
			break;
		default:
			$period = 0;
			$compare = ">";
			break;
	}

	//if ( $period ) {
		// It's an expiration membership
		$sql = "SELECT meta_value AS integer_value FROM " . $wpdb->usermeta . ' WHERE meta_key = "' . esc_sql( $user_meta ) . '" AND meta_value ' . esc_sql( $compare ) . ' ' . esc_sql( $period ) . ' ORDER BY meta_value;';
	//} else {
		// It's not an expiration membership
	//}

	$results = $wpdb->get_results( $sql );

	return count( $results );
}

/**
 * Checks whether a membership is an expiration membership.
 * 
 * @since 3.5.3
 * 
 * @param  string  $membership  The membership slug
 * @return boolean
 */
function wpmem_is_membership_expirable( $membership ) {
	global $wpmem;
	$exp_period = ( isset( $wpmem->membership->memberships[ $membership ]['expires'] ) ) ? $wpmem->membership->memberships[ $membership ]['expires'] : false;
	return ( is_array( $exp_period ) ) ? true : false;
}

/**
 * Gets the expiration period for a membership.
 * 
 * @since 3.5.3
 * 
 * @param  string  $membership  The membership slug
 * @param  boolean $raw         Whether to return raw array (default) or string of period (i.e. "1 year")
 * @return mixed                Returns string of period for use in time() functions (i.e. "1 year") or 
 *                              the raw array (array(1,year)) if $raw=true.
 */
function wpmem_get_expiration_period( $membership, $raw = false ) {
	global $wpmem;
	$exp_period = ( isset( $wpmem->membership->memberships[ $membership ]['expires'] ) ) ? $wpmem->membership->memberships[ $membership ]['expires'] : false;
	if ( is_array( $exp_period ) ) {
		$period = explode("|", $exp_period[0]);
		return ( ! $raw ) ? $period[0] . ' ' . $period[1] : $period; 
	} else {
		return false;
	}
}