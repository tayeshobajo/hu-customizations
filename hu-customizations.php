<?php
/**
 * Plugin Name: Hazmat University Customizations
 * Description: Core Customization for Hazmat University Website
 * Author: Tai Shobajo
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) or exit;

require __DIR__ . '/vendor/autoload.php';

define('HU_CUSTOMIZATIONS_SYSTEM_FILE_PATH', __FILE__);
define('HU_CUSTOMIZATIONS_VERSION_NUMBER', '1.0.0');

define( 'HU_CUSTOMIZATIONS_SYSTEM_SRC_DIRECTORY', plugin_dir_path( __FILE__ ). 'src');
define( 'HU_CUSTOMIZATIONS_SYSTEM_ASSETS_URL', plugin_dir_url( __FILE__ ). 'assets');
define( 'HU_CUSTOMIZATIONS_SYSTEM_ASSETS_DIRECTORY', plugin_dir_path( __FILE__ ). 'assets');
define( 'HU_CUSTOMIZATIONS_SYSTEM_ASSETS_IMG_URL', HU_CUSTOMIZATIONS_SYSTEM_ASSETS_URL. '/img');
define( 'HU_CUSTOMIZATIONS_SYSTEM_ASSETS_IMG_DIRECTORY', HU_CUSTOMIZATIONS_SYSTEM_ASSETS_DIRECTORY. '/img');

add_action( 'plugins_loaded', 'wc_gateway_init', 11);

function wc_gateway_init() {
    \HUCustomizations\Init::get_instance();
}