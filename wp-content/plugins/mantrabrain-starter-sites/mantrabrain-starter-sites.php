<?php
/**
 * Plugin Name: Mantrabrain Starter Sites
 * Description: Starter sites / Demo importer for Mantra Brain Themes
 * Version: 1.1.0
 * Author: Mantrabrain
 * Author URI: https://mantrabrain.com
 * License: GPLv3 or later
 * Text Domain: mantrabrain-starter-sites
 * Domain Path: /languages/
 * @package Mantrabrain_Starter_Sites
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define MANTRABRAIN_STARTER_SITES_PLUGIN_FILE.
if (!defined('MANTRABRAIN_STARTER_SITES_PLUGIN_FILE')) {
    define('MANTRABRAIN_STARTER_SITES_PLUGIN_FILE', __FILE__);
}
// Include the main Mantrabrain Starter Sites class.
if (!class_exists('Mantrabrain_Starter_Sites')) {
    include_once dirname(__FILE__) . '/includes/class-mantrabrain-starter-sites.php';
}

/**
 * Main instance of Mantrabrain Starter Sites.
 *
 * Returns the main instance to prevent the need to use globals.
 *
 * @return Mantrabrain_Starter_Sites
 * @since 1.0.0
 */
function mb_starter_sites()
{
    return Mantrabrain_Starter_Sites::instance();
}

ini_set('max_execution_time', 1500);
// Global for backwards compatibility.

$GLOBALS['mantrabrain-starter-sites'] = mb_starter_sites();
