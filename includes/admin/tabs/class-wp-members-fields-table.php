<?php
/**
 * WP-Members WP_Members_Fields_Table class
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

/**
 * Extends the WP_List_Table to create a table of form fields.
 *
 * @since 3.1.8
 */
class WP_Members_Fields_Table extends WP_List_Table {
	
	private $excludes = array( 'user_registered', '_wpmem_user_confirmed', 'active', 'wpmem_reg_ip', 'exp_type', 'expires', 'user_id' );
	
	private $no_delete = array( 'username', 'user_email', 'first_name', 'last_name', 'user_url' );
	
	/**
	 * Checkbox at start of row.
	 *
	 * @since 3.1.8
	 *
	 * @param $item
	 * @return string The checkbox.
	 */
	function column_cb( $item ) {
		if ( in_array( $item['meta'], $this->no_delete ) || in_array( $item['meta'], $this->excludes ) ) {
			return;
		} else {
			return sprintf( '<input type="checkbox" name="delete[]" value="%s" title="%s" />', esc_attr( $item['meta'] ), esc_html__( 'delete', 'wp-members' ) );
		}
	}

	function column_meta( $item ) {
		if ( '' == $item['edit'] ) {
			return $item['meta'];
		} else {
			$link = add_query_arg( array(
				'page'  => 'wpmem-settings',
				'tab'   => 'fields',
				'mode'  => 'edit',
				'edit'  => 'field',
				'field' => $item['meta'],
			), admin_url( 'options-general.php' ) );
			return '<a href="' . esc_url( $link ) . '"><span class="dashicons dashicons-edit"></span></a> <a href="' . esc_url( $link ) . '" data-tooltip="' . esc_html__( 'Edit this field', 'wp-members' ) . '">' . esc_attr( $item['meta'] ) . '</a>';
		}
	}

	/**
	 * Returns table columns.
	 *
	 * @since 3.1.8
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'   =>  '<input type="checkbox"  data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '" />',
			'label'    => esc_html__( 'Display Label', 'wp-members' ),
			'meta'     => esc_html__( 'Meta Key',      'wp-members' ),
			'type'     => esc_html__( 'Field Type',    'wp-members' ),
			'display'  => '<input name="wpmem_all_fields_display" type="checkbox" id="wpmem_all_fields_display" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> '   . esc_html__( 'Registration', 'wp-members' ), // esc_html__( 'Registration',  'wp-members' ), @todo Wait until fix
			'req'      => '<input name="wpmem_all_fields_required" type="checkbox" id="wpmem_all_fields_required" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> ' . esc_html__( 'Required',     'wp-members' ),
			'profile'  => '<input name="wpmem_all_fields_profile" type="checkbox" id="wpmem_all_fields_profile" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> '   . esc_html__( 'Profile',      'wp-members' ),
		);

		if ( wpmem_is_woo_active() ) {
			global $wpmem;
			//if ( wpmem_is_enabled( 'woo/add_checkout_fields' ) ) {
			if ( 1 == $wpmem->woo->add_checkout_fields ) {
				$columns['wcchkout'] = '<input name="wpmem_all_fields_wcchkout" type="checkbox" id="wpmem_all_fields_wcchkout" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> ' . esc_html__( 'WC Chkout', 'wp-members' );
			}
			//if ( wpmem_is_enabled( 'woo/add_my_account_fields' ) ) {
			if ( 1 == $wpmem->woo->add_my_account_fields ) {
				$columns['wcaccount'] = '<input name="wpmem_all_fields_wcaccount" type="checkbox" id="wpmem_all_fields_wcaccount" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> ' . esc_html__( 'WC My Acct', 'wp-members' );
			}
			//if ( wpmem_is_enabled( 'woo/add_update_fields' ) ) {
			if ( 1 == $wpmem->woo->add_update_fields ) {
				$columns['wcupdate'] = '<input name="wpmem_all_fields_wcupdate" type="checkbox" id="wpmem_all_fields_wcupdate" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> ' . esc_html__( 'WC Update', 'wp-members' );
			}
		}

		$columns['userscrn'] = '<input name="wpmem_all_fields_uscreen" type="checkbox" id="wpmem_all_fields_uscreen" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> '   . esc_html__( 'Users',        'wp-members' );
		$columns['usearch']  = '<input name="wpmem_all_fields_usearch" type="checkbox" id="wpmem_all_fields_usearch" value="1" data-tooltip="' . esc_html__( 'Click to check all', 'wp-members' ) . '"> '   . esc_html__( 'Search',       'wp-members' );
		
		$columns['edit'] = '';

		return $columns;
	}

	/**
	 * Set up table columns.
	 *
	 * @since 3.1.8
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Iterates through the columns
	 *
	 * @since 3.1.8
	 *
	 * @param  array  $item
	 * @param  string $column_name
	 * @return string $item[ $column_name ]
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			default:
	  			return ( isset( $item[ $column_name ] ) ) ? $item[ $column_name ] : '';
		}
	}

	/**
	 * Sets actions in the bulk menu.
	 *
	 * @since 3.1.8
	 *
	 * @return array $actions
	 */
	function get_bulk_actions() {
		echo '<select name="action" id="bulk-action-selector-top">
<option value="-1">' . esc_html__( 'Bulk actions' ) . '</option>
	<option value="delete">' . esc_html__( 'Delete Selected', 'wp-members' ) . '</option>
	<option value="save">' . esc_html__( 'Save Settings', 'wp-members' ) . '</option>
</select>
<input type="submit" name="update_fields" id="doaction" class="button action" value="' . esc_html__( 'Apply' ) . '" />
<input type="submit" name="add_field" id="add_field" class="button action" value="' . esc_html__( 'Add Field', 'wp-members' ) . '" />';
		return array();
	}

	/**
	 * Handles "delete" column - checkbox
	 *
	 * @since 3.1.8
	 *
	 * @param  array  $item
	 * @return string 
	 */
	function column_delete( $item ) {
		$can_delete = ( $item['meta_key'] == 'user_nicename' || $item['meta_key'] == 'display_name' || $item['meta_key'] == 'nickname' ) ? true : false;
		return ( ( $can_delete ) || ! $item['native'] ) ? sprintf( $item['native'] . '<input type="checkbox" name="field[%s]" value="delete" />', esc_attr( $item['meta'] ) ) : '';
	}
	
	/**
	 * Sets rows so that they have field IDs in the id.
	 *
	 * @since 3.1.8
	 *
	 * @global wpmem
	 * @param  array $columns
	 */
	function single_row( $columns ) {
		if ( in_array( $columns['meta'], $this->excludes ) ) {
			echo '<tr id="' . esc_attr( $columns['meta'] ) . '" class="nodrag nodrop">';
			echo $this->single_row_columns( $columns );
			echo "</tr>\n";
		} else {
			echo '<tr id="list_items_' . esc_attr( $columns['order'] ) . '" class="list_item" list_item="' . esc_attr( $columns['order'] ) . '">';
			echo $this->single_row_columns( $columns );
			echo "</tr>\n";
		}
	}
	
	public function process_bulk_action() {

	//nonce validations,etc
	
		$action = $this->current_action();
	
		switch ( $action ) {
	
			case 'delete':
	
				// Do whatever you want
				wp_safe_redirect( esc_url( add_query_arg() ) );
				break;
	
			default:
				// do nothing or something else
				return;
				break;
		}
		return;
	}
	
}