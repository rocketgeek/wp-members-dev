<?php
/**
 * WP-Members TOS Page
 *
 * Generates teh Terms of Service pop-up.
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
?>

<html>
<head>
	<title><?php wpmem_get_text( 'tos_title', true ); ?> | <?php bloginfo( 'name' ); ?></title>
</head>

<body>

<?php

$wpmem_tos = get_option( 'wpmembers_tos' );

echo stripslashes( $wpmem_tos );

print ( '<br /><br />' );
printf( wpmem_get_text( 'tos_close' ), '[<a href="javascript:self.close()">', '</a>]' );
print ( '&nbsp;&nbsp;' );
printf( wpmem_get_text( 'tos_print' ), '[<a href="javascript:window.print()">', '</a>]' );

?>

</body>
</html>