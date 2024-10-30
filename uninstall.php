<?php

/**
 * Fired when the plugin is uninstalled.
 * 
 * @package HelloPrint
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}


/**
 * Delete options we have saved
 */
// delete_option('wfw_db_version');
