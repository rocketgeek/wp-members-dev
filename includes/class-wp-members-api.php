<?php
/**
 * The WP_Members API Class.
 *
 * @package WP-Members
 * @subpackage WP_Members API Object Class
 * @since 3.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_API {

	public $file_user_id; // A container for the uploaded file user ID.

	/**
	 * Plugin initialization function.
	 *
	 * @since 3.1.1
	 */
	function __construct() {

	}
	
	/**
	 * Get field keys by meta.
	 *
	 * @since 3.1.1
	 *
	 * @return array $field_keys
	 */
	function get_field_keys_by_meta() {
		$field_keys = array();
		foreach ( wpmem_fields() as $key => $field ) {
			$field_keys[ $field[2] ] = $key;
		}
		return $field_keys;
	}
	
	/**
	 * Get select field display values.
	 *
	 * @since 3.1.1
	 *
	 * @param  string $meta_key       The field's meta key.
	 * @return array  $display_values {
	 *     The field's display values in an array.
	 *     Elements are stored_value => display value
	 *
	 *     @type string The display value.
	 * }
	 */
	function get_select_display_values( $meta_key ) {
		$keys = $this->get_field_keys_by_meta();
		$fields = wpmem_fields();
		$raw  = $fields[ $keys[ $meta_key ] ][7];
		$delimiter = ( isset( $fields[ $keys[ $meta_key ][8] ] ) ) ? $fields[ $keys[ $meta_key ][8] ] : '|';
		$display_values = array();
		foreach ( $raw as $value ) {
			$pieces = explode( $delimiter, trim( $value ) );
			if ( $pieces[1] != '' ) {
				$display_values[ $pieces[1] ] = $pieces[0];
			}
		}
		return $display_values;
	}
	
	/**
	 * Gets the display/label value of a field.
	 *
	 * @since 3.1.8
	 *
	 * @param  string $meta    The field meta key.
	 * @param  string $user_id The user's ID.
	 * @param  string $value   The field's value, if given.
	 * @return string $value   The display value.
	 */
	function get_field_display_value( $meta, $user_id, $value = null ) {
		$fields = wpmem_fields('all');
		$field  = $fields[ $meta ];
		$value  = ( $value ) ? $value : get_user_meta( $user_id, $meta, true );
		/*
		 * Nothing more to do for string values.  The following switch
		 * handles values that may be something other than a simple string. 
		 */
		switch ( $field['type'] ) {
			case 'multiselect':
			case 'multicheckbox':
				// @todo Right now this is the saved value as a string, not a "display" value.
				// Two possibilities: delimited string (WP-Members) or serialized (WooCommerce)
				if ( is_array( $value ) ) {
					$value = implode( $field['delimiter'], $value );
				}
				break;
			case 'select':
			case 'radio':
				$value = $field['options'][ $value ];
				break;
			case 'image':
			case 'file':
				$value = wp_get_attachment_url( $value );
				break;
		}
		return $value;
	}
		
	/**
	 * Checks that a given user field value is unique.
	 *
	 * @since 3.1.1
	 *
	 * @param  string $key The field being checked.
	 * @param  string $val The value to check.
	 * @return bool        True if value if is unique.
	 */
	function is_user_value_unique( $key, $val ) {
		
		if ( $this->is_field_in_wp_users( $key ) ) {
			$args = array( 'search' => $val, 'fields' => 'ID' );
		} else {
			$args = array( 'meta_query' => array( array(
				'key'     => $key,
				'value'   => $val,
				'compare' => '=',
			) ) );
		}
		
		$users = get_users( $args );
		
		// If there is data in $users, the value is not unique.
		return ( $users ) ? false : true;
	}
	
	/**
	 * Checks counter for next available number and updates the counter.
	 *
	 * @since 3.1.1
	 *
	 * @param  string $option    The wp_options name for the counter setting (required).
	 * @param  int    $start     Number to start with (optional, default 0).
	 * @param  int    $increment Number to increment by (optional, default 1).
	 * @return int    $number    Next number in the series.
	 */
	function get_incremental_number( $option, $start = 0, $increment = 1 ) {
		
		// Get current number from settings
		$number = get_option( $option );
		
		// If there is no number, start with $start.
		if ( ! $number ) {
			$number = ( $start <= 0 ) ? $start : $start - $increment;
		}
		
		// Increment the number and save the setting.
		$number = $number + $increment;
		update_option( $option, $number, false );
		
		// Return the number.
		return $number;
	}
	
	/**
	 * Generates a unique membership number based on settings.
	 *
	 * @since 3.1.1
	 * @since 3.2.0 Changed "lead" value to "pad".
	 *
	 * @param  array  $args {
	 *     @type string $option    The wp_options name for the counter setting (required).
	 *     @type string $meta_key  The field's meta key (required).
	 *     @type int    $start     Number to start with (optional, default 0).
	 *     @type int    $increment Number to increment by (optional, default 1).
	 *     @type int    $digits    Number of digits for the number (optional).
	 *     @type boolen $pad       Pad leading zeros (optional, default true).
	 * }
	 * @return string $mem_number
	 */
	function generate_membership_number( $args ) {
		$defaults = array(
			'start'     => 0,
			'increment' => 1,
			'digits'    => 8,
			'pad'       => true,
		);
		$args = wp_parse_args( $args, $defaults );
		do {
			// Generate the appropriate next number
			$number = $this->get_incremental_number( $args['option'], $args['start'], $args['increment'] );
			
			// Cast as string, not integer.
			$mem_number = (string)$number;
			
			// Add leading zeros if less than three digits.
			if ( strlen( $mem_number ) < $args['digits'] ) {
				$mem_number = ( $args['pad'] ) ? str_pad( $mem_number, $args['digits'], '0', STR_PAD_LEFT ) : $mem_number;
			}
		} while ( true !== $this->is_user_value_unique( $args['meta_key'], $mem_number ) );
		return $mem_number;
	}
	
	/**
	 * Checks if a given setting is set and enabled.
	 *
	 * @since 3.1.7
	 *
	 * @global object  $wpmem
	 * @param  string  $setting
	 * @return boolean
	 */
	function is_enabled( $setting ) {
		return ( isset( $wpmem->{$setting} ) && $wpmem->{$setting} ) ? true : false;
	}

	/**
	 * Returns a list of wp_users fields.
	 * 
	 * @since 3.5.2
	 */
	public function get_wp_users_fields() {
		return array( 'ID','user_login','user_pass','user_nicename','user_email','user_url','user_registered','user_activation_key','user_status','display_name' );
	}

	/**
	 * Checks if a field is a wp_users field.
	 * A return of false would indicate it is a meta field.
	 * 
	 * @since 3.5.2
	 * 
	 * @param  string  $meta
	 * @return bool
	 */
	public function is_field_in_wp_users( $meta ) {
		return ( in_array( $meta, $this->get_wp_users_fields() ) ) ? true : false;
	}

	/**
	 * Uploads file from the user.
	 *
	 * @since 3.1.0
	 * @since 3.5.5 Moved to API class.
	 *
	 * @param  array    $file
	 * @param  int      $user_id
	 * @return int|bool
	 */
	function do_file_upload( $file = array(), $user_id = false ) {
		
		// Filter the upload directory.
		add_filter( 'upload_dir', array( &$this, 'file_upload_dir' ) );
		add_filter( 'sanitize_file_name', 'wpmem_hash_file_name' );
		
		// Set up user ID for use in upload process.
		$this->file_user_id = ( $user_id ) ? $user_id : 0;
	
		// Get WordPress file upload processing scripts.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		$file_return = wp_handle_upload( $file, array( 'test_form' => false ) );

		remove_filter( 'sanitize_file_name', 'wpmem_hash_file_name' );
	
		if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
			return false;
		} else {
	
			$attachment = array(
				'post_mime_type' => $file_return['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file['name'] ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'guid'           => $file_return['url'],
				'post_author'    => ( $user_id ) ? $user_id : '',
			);
	
			$attachment_id = wp_insert_attachment( $attachment, $file_return['file'] );
	
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_return['file'] );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
	
			if ( 0 < intval( $attachment_id ) ) {
				// Returns an array with file information.
				return $attachment_id;
			}
		}
	
		return false;
	} // End upload_file()
	
	/**
	 * Sets the file upload directory.
	 *
	 * This is a filter function for upload_dir.
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/upload_dir
	 *
	 * @since 3.1.0
	 * @since 3.5.5 Moved to API class.
	 * @since 3.5.5 Updated to add a randomized hash to the user directories.
	 *
	 * @param  array $param {
	 *     The directory information for upload.
	 *
	 *     @type string $path
	 *     @type string $url
	 *     @type string $subdir
	 *     @type string $basedir
	 *     @type string $baseurl
	 *     @type string $error
	 * }
	 * @return array $param
	 */
	function file_upload_dir( $param ) {
		
		$user_id  = ( isset( $this->file_user_id ) ) ? $this->file_user_id : null;

		$file_dir = wpmem_get_file_dir_hash();
		$user_dir = wpmem_get_user_dir_hash( $user_id );

		$args = array(
			'user_id'    => $user_id,
			'wpmem_dir'  => wpmem_get_upload_base(),
			'user_dir'   => trailingslashit( $file_dir ) . $user_dir,
			'basedir'    => $param['basedir'],
			'baseurl'    => $param['baseurl'],
			'file_hash'  => $file_dir,
			'user_hash'  => $user_dir,
		);
		
		/**
		 * Filter the user directory elements.
		 *
		 * @since 3.1.0
		 * @since 3.5.5 Added base vals and hashes to args.
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'wpmem_user_upload_dir', $args );

		$param['subdir'] = '/' . $args['wpmem_dir'] . '/' . $args['user_dir'];
		$param['path']   = $args['basedir'] . '/' . $args['wpmem_dir'] . '/' . $args['user_dir'];
		$param['url']    = $args['baseurl'] . '/' . $args['wpmem_dir'] . '/' . $args['user_dir'];
	
		return $param;
	}

} // End of WP_Members_Utilties class.