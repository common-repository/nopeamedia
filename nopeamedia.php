<?php
/*
 
  Plugin Name:       Print PDF Generator and Publisher
  Description:       Print PDF generator and publisher by Nopea.Media
  Version:           1.2.0
  Author:            nopea.media
  Author URI:        https://en.nopea.media
  License:           GPL-3.0+
  License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
  Text Domain:       nopea-media
  Domain Path:       /languages
  
 */
defined( 'ABSPATH' ) || exit;

if(! function_exists('is_plugin_active')){
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

define('NOPME_PREFIX', '_nopmed');
define('NOPME_PLUGIN_FILE', __FILE__);
define('NOPME_PLUGIN_NAME', 'nopea-media');
define('NOPME_META_PREFIX', '_nopmed_');
define('NOPME_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('NOPME_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('NOPME_IS_MCHART_INSTALLED', is_plugin_active( 'm-chart/m-chart.php' )); //check if m-chart is installed
define('NOPME_SETTINGS_PAGE', admin_url('edit.php?post_type=publications&page=publications-settings'));
define('NOPME_API_BASE_URL', (defined('WP_DEBUG') && WP_DEBUG ? get_option(sprintf('%s_service_url', NOPME_PREFIX)) : 'https://api.nopea.media/v2/' )); //API endpoint

/**
 * Initialize plugin.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/plugin.php';

/**
 * Initialize blocks.
 */
require_once plugin_dir_path( __FILE__ ) . 'blocks/init.php';
