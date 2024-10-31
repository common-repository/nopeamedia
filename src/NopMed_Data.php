<?php

defined( 'ABSPATH' ) || exit;

class NopMed_Data
{

    public function __construct()
    {
    }

    public function seed_settings(){

        $layouts = NopMed_API::get_layout_config();
        $theme = NopMed_API::get_theme_config();

        if(!isset($theme['themes']) || !isset($layouts['layouts'])){
           add_action('admin_head', function(){
                nopme_show_notice(__('Invalid token. Please check in settings that you have filled in valid token which you acquired from https://en.nopea.media', 'nopea-media'), 'error');
           }, PHP_INT_MAX);
        }
    
        update_option(sprintf('%s_api_layout', NOPME_PREFIX), $layouts);
		update_option(sprintf('%s_api_theme', NOPME_PREFIX), $theme);

		//prepare pdf directory 
		$upload_dir = wp_get_upload_dir();
		$basedir = $upload_dir['basedir'];
		$new_path = sprintf('%s/%s', $basedir, NOPME_PLUGIN_NAME);
		if(!is_dir($new_path) || !file_exists($new_path)){
			mkdir($new_path);
        }
        
        //move documents from old version
        if(is_dir(  $old_path = sprintf('%s/pdf', $basedir) )){
            $files = array_diff(scandir($old_path), array('.', '..'));
            foreach($files as $file){
                rename(sprintf('%s/%s', $old_path, $file), sprintf('%s/%s', $new_path, $file));
            }
            
            $emptyDir = array_diff(scandir($old_path), array('.', '..'));
            if(empty($emptyDir)){
                rmdir($old_path);
            }
        }   
    }

    public function delete_settings()
    {
        delete_option(sprintf('%s_api_layout', NOPME_PREFIX));
        delete_option(sprintf('%s_api_theme', NOPME_PREFIX));
    }

    public static function get_publications( $post_id )
    {
        //Request publication details with selected values
        global $wpdb;
        $publications = $wpdb->get_results("SELECT post_title as label, ID as value FROM $wpdb->posts WHERE (post_type = 'publications' AND post_status NOT IN ('auto-draft', 'trash') )");
        return $publications;
    }

	/**
	 * simple wrapper for getting any nopme option
	 */
    public static function get_api_option($what)
    {
        if (empty($what)) {
            return array();
        }
        return get_option(sprintf('%s_api_%s', NOPME_PREFIX, $what));
    }

	/**
	 * get nopeamedia api data from db
	 */
    public static function get_api_data($post_id)
    {
        $layouts = self::get_api_option('layout');
        $theme = self::get_api_option('theme');
        if(!empty($layouts) && !empty($theme))
             return array_merge($layouts,$theme);

        return array();
    }

    public static function get_nm_post_option($post_id)
    {
        $option = get_option(sprintf('%spost_%s', NOPME_PREFIX, $post_id));
        if (!$option) {
            $option = array();
        }

        return !$option ? json_encode(array()) : $option;
    }

    public function set_nm_post_option($option, $post_id)
    {
        $key = sprintf('%spost_%s', NOPME_PREFIX, $post_id);
        $data = is_array($option) ? json_encode($option) : $option;
        return update_option($key, $data);
    }

}
