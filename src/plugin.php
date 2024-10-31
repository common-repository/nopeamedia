<?php
defined( 'ABSPATH' ) || exit;

/**
 * Loade plugin classes
 */
spl_autoload_register(function ($class) { 
	if( strpos($class, 'NopMed_') !== FALSE )
	include NOPME_PLUGIN_PATH. DIRECTORY_SEPARATOR .'src' .DIRECTORY_SEPARATOR . $class . '.php';
});

NopMed_Publication::getInstance();
add_action('admin_init', 'nopme_redirect_on_activate');

//Register settings link on plugin list pages
add_filter( "plugin_action_links", function( $links, $file) {
	if( basename($file) == basename(NOPME_PLUGIN_FILE) ){
	 	$settings_link = '<a href="'.NOPME_SETTINGS_PAGE.'">'.__('Settings').'</a>';
		array_unshift( $links, $settings_link );
	}
   	return $links;
}, 10, 2);

//register ajax callbacks
add_action('wp_ajax_nopme_delete_pub_post', array('NopMed_Ajax', 'delete_publication_post'));
add_action('wp_ajax_nm_save_post_data', array('NopMed_Ajax', 'save_post_extras'));

/**
 * Redirect to settings page after activation
 */
function nopme_redirect_on_activate(){
    if (get_option(sprintf('%s_activation_done', NOPME_PREFIX), false)) {
        delete_option(sprintf('%s_activation_done', NOPME_PREFIX));
        flush_rewrite_rules();
        exit(wp_redirect(NOPME_SETTINGS_PAGE));
    }
}

/**
 * Activation hook function
 */
function nopme_activate(){
    add_option(sprintf('%s_activation_done', NOPME_PREFIX), true);
}

/**
 * Dactivation hook function
 */
function nopme_deactivate(){

}

//a wrapper function to for admin notices
function nopme_show_notice($message, $type){
    $klass = sprintf('notice notice-%s is-dismissible', $type);
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($klass), $message);
}

//plugin activation hook here
register_activation_hook( NOPME_PLUGIN_FILE,   'nopme_activate' );
register_deactivation_hook( NOPME_PLUGIN_FILE, 'nopme_deactivate');

if (class_exists('WP_CLI')) {
    WP_CLI::add_command('nopeamedia', 'NopMed_WPCLI_Migrate'); //register wp-cli command to migrate old nopeamedia content
}