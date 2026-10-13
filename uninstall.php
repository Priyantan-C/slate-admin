<?php
/** Remove everything Slate Admin stored. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'slate_admin' );
delete_metadata( 'user', 0, 'slate_admin_scheme', '', true );
delete_site_transient( 'slate_admin_latest_release' );
