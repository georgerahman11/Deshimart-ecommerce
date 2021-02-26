<?php
/**
 * Mantrabrain Starter Sites Uninstall
 *
 * Uninstalls the plugin and associated data.
 *
 * @package Importer/Unistaller
 * @version 1.0.0
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

delete_transient('mantrabrain_get_theme_demo_configuration');
/*
 * Only remove ALL demo importer data if MANTRABRAIN_STARTER_SITES_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if (defined('MANTRABRAIN_STARTER_SITES_REMOVE_ALL_DATA') && true === MANTRABRAIN_STARTER_SITES_REMOVE_ALL_DATA) {
    // Delete options.
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'mantrabrain_starter_sites\_%';");
}
