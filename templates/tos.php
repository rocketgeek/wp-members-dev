<?php
/**
 * WP-Members TOS Page
 *
 * This template can be overridden by copying it to yourtheme/wp-members/templates/tos.php.
 *
 * HOWEVER, on occasion WP-Members will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  	RocketGeek
 * @package 	WP_Members
 * @version 	1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
} ?>
<html>
<head>
	<title><?php echo esc_html( wpmem_get_text( 'tos_title', true ) ); ?> | <?php echo esc_html( bloginfo( 'name' ) ); ?></title>
</head>
<body>

<?php
$wpmem_tos = get_option( 'wpmembers_tos' );

echo wpautop( wp_unslash( $wpmem_tos ) );

echo '<p>' . sprintf( wpmem_get_text( 'tos_close' ), '[<a href="javascript:self.close()">', '</a>]' )
	. ' ' . sprintf( wpmem_get_text( 'tos_print' ), '[<a href="javascript:window.print()">', '</a>]' ) 
	. '</p>';
?>
</body>
</html>