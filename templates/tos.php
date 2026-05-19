<?php
/**
 * WP-Members TOS Page
 *
 * Generates the Terms of Service pop-up.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2026  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2026
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>

<html>
<head>
	<title><?php echo esc_html( wpmem_get_text( 'tos_title', true ) ); ?> | <?php echo esc_html( bloginfo( 'name' ) ); ?></title>
</head>

<body>

<?php

$wpmem_tos = get_option( 'wpmembers_tos' );

echo wp_unslash( $wpmem_tos );

echo '<br /><br />';
printf( esc_html( wpmem_get_text( 'tos_close' ) ), '[<a href="javascript:self.close()">', '</a>]' );
echo '&nbsp;&nbsp;';
printf( esc_html( wpmem_get_text( 'tos_print' ) ), '[<a href="javascript:window.print()">', '</a>]' );

?>

</body>
</html>