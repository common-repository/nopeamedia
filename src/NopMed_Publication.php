<?php

defined( 'ABSPATH' ) || exit;

class NopMed_Publication
{

    private static $instance;

    private $post_type = 'publications';

    private $site_url = '';

    protected $api_endpoint = '';

    protected $ordered_post = array();

    const PDF_GEN_OK = 1;

    const PDF_GEN_FAIL = -1;

    const PDF_GEN_ERR = 2;

    const PDF_CONFIG = array(
        'page_size', 
        'primary_color_cmyk', 
        'secondry_color_cmyk',
        '_nopmed_api_key',
        '_nopmed_service_url'
        );

    const SETTINGS = array(
        'odd_page_footer',
        'even_page_footer',
        'pdf_quality',
    );

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', array($this, 'init'));
       
        $this->site_url = get_site_url();
    }

    public function init()
    {
        load_plugin_textdomain( NOPME_PLUGIN_NAME, false, basename( dirname( __DIR__ ) ) . '/languages' );

        $this->register_post_type();
        $this->save_settings();
        
        if (is_admin()) {
            add_action('add_meta_boxes', array($this, 'meta_boxes'));
            add_action('admin_enqueue_scripts', array($this, 'register_publication_script'));
            add_action('enqueue_block_editor_assets', array($this, 'register_sidebar_script') );
            add_action("save_post_{$this->post_type}", array($this, 'save_publication'));
            add_action('before_delete_post', array($this, 'on_delete_post'));
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action('admin_notices', array($this, 'show_pdf_notices'));

            add_filter( 'manage_pages_columns', array($this, 'register_nopeamedia_column') );
            add_filter( 'manage_post_posts_columns', array($this, 'register_nopeamedia_column') );
            add_filter( 'manage_'.$this->post_type.'_posts_columns', array($this, 'register_nopeamedia_column') );

            add_action( 'manage_pages_custom_column', array($this, 'add_pdf_column_data'), 10, 2 );
            add_action( 'manage_post_posts_custom_column', array($this, 'add_pdf_column_data'), 10, 2 );
        } 

        return $this;
    }

    public function register_post_type(){
        // Register the PDF custom post type

        register_post_type(
            $this->post_type,
            array(
                'labels' => array(
                    'name' => esc_html__('Publications', 'nopea-media'),
                    'singular_name' => esc_html__('Publication', 'nopea-media'),
                    'add_new' => esc_html__('Add New', 'nopea-media'),
                    'add_new_item' => esc_html__('Create Publication', 'nopea-media'),
                    'edit' => esc_html__('Edit Publication', 'nopea-media'),
                    'edit_item' => esc_html__('Edit Publication', 'nopea-media'),
                    'new_item' => esc_html__('New Publication', 'nopea-media'),
                    'view' => esc_html__('View Publication', 'nopea-media'),
                    'view_item' => esc_html__('View Publication', 'nopea-media'),
                    'search_items' => esc_html__('Search PDF', 'nopea-media'),
                    'not_found' => esc_html__('No PDF found', 'nopea-media'),
                    'not_found_in_trash' => esc_html__('No PDF found in the trash', 'nopea-media'),
                ),
                'public' => true,
                'show_ui' => true,
                'hierarchical' => true,
                'exclude_from_search' => false,
                'menu_position' => 6,
                'menu_icon' => 'dashicons-media-document',
                'query_var' => true,
                'can_export' => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => false,
                'publicly_queryable' => true,
                'description' => esc_html__('Generate PDF from Post content in Wordpress.', 'nopea-media'),
                'rewrite' => array(
                    'slug' => $this->post_type,
                ),
                'supports' => array(
                    //'author',
                    'title',
                    //'excerpt',
                    //'editor'
                ),
                'taxonomies' => array(
                    'publication_types',
                ),
            )
        );        
    }
    
    public function admin_menu() {
        add_submenu_page('edit.php?post_type=' . $this->post_type,
			esc_html__( 'Settings'),
			esc_html__( 'Settings'),
			'manage_options',
			$this->post_type . '-settings',
			array( $this, 'plugin_settings' )
		);
    }

    public function plugin_settings(){
       require __DIR__ . '/../templates/plugin_settings.php';
    }

    public function save_settings(){
        if ( ! current_user_can( 'manage_options' )) {
			return;
        }
        if( !isset($_GET['page']) || $_GET['page'] != 'publications-settings'){
            return;
        }

        //Make sure the API key is set 
        if(!empty($_POST) && !isset($_POST[sprintf('%s_api_key', NOPME_PREFIX)] )){
            nopme_show_notice(__('API key is required', 'nopea-media'), 'error');
            return;
        }
       
        //when saving settings, update configuration data 
        if(!empty($_POST)){

            foreach(static::PDF_CONFIG as $config){
                if(isset($_POST[$config])){
                    $value = sanitize_text_field($_POST[$config]);
                    update_option($config, $value);
                }
            }
             
            if(NOPME_API_BASE_URL && NopMed_API::get_api_key() ){
                $nm_data = new NopMed_Data();
                $nm_data->seed_settings();
            }
        }  
    }


    // fetch settings for a publication
    public function get_publication_settings($postId = 0){
        $settings = array();
        foreach(static::SETTINGS as $op){
            $settings[$op] = get_post_meta($postId, $op, true);
        }
        return $settings;
    }

    /**
     * Intercept publication save  - send data to backend if
     * the generate pdf checkbox is checked
     */
    public function save_publication() {
        global $post;
        $generate_pdf = isset($_POST['with_pdf_']) ? 1 : 0;
		$show_subarticle_in_new_page = isset($_POST['show_subarticle_in_new_page']) ? 1 : 0;
        update_post_meta($post->ID, 'with_pdf', $generate_pdf);
		update_post_meta($post->ID, 'show_subarticle_in_new_page', $show_subarticle_in_new_page);
        update_post_meta($post->ID, 'pdf_quality', isset($_POST['pdf_quality']) ? intval($_POST['pdf_quality']) : false);
        
        //if generate menu button is clicked
        if (filter_input(INPUT_POST, 'save', FILTER_SANITIZE_STRING) == 'generate_menu') {
            $this->create_nav_menu($post);
            return $post;
        }

        //update publication posts
        if (isset($_POST['posts_order']) && !empty($_POST['posts_order'])) {

            $post_order = stripslashes($_POST['posts_order']);
            $menu_items = json_decode($post_order, true);

            if ($ordered_post = $this->sort_order($menu_items)) { //sort the posts
                
				foreach ($ordered_post as $p) {
                    $dao = new NopMed_Dao($p['id']);
                    $dao->set('position', $p['position']);
                    $dao->set('parent_id', $p['parent_id']);
                    $dao->save();
                }
			}
        }
        
        //update publication config
        foreach(static::SETTINGS as $config){
            if(isset($_POST[$config])){
		$value = str_replace('"', '',sanitize_text_field($_POST[$config]));
                update_post_meta($post->ID, $config, $value);
            }
        }

        //Generate pdf document
        if ($generate_pdf) {
            $this->create_pdf();
        }
        return $post;
    }

    /**
     * Sends WP-Content to API to generate PDF document 
     */
    protected function create_pdf(){
        global $post;
        $postId = $post->ID;
        $option_key = sprintf('%s_pdf_generate_%d', NOPME_PREFIX, $postId);
                if( !$pdf_data = $this->prepare_pdf_data( $post )){
                    add_option($option_key, ['status' => static::PDF_GEN_ERR]);
                    return;
                }

                $response = NopMed_API::create_publication_pdf( $pdf_data );

                if(is_wp_error($response)){
                    add_option($option_key, ['status' => static::PDF_GEN_FAIL, 'message' => $response->get_error_message()]);
                    return;
                }

                $body = json_decode($response['body'], TRUE);
                //base64_encoded data ? 
                if(isset($body['data']) && !empty($body['data']) && ($pdfString = base64_decode($body['data']))){ 
                     
                     $pdf_quality = get_post_meta($post->ID, 'pdf_quality', true);
                     $name = $post->post_name . (intval($pdf_quality) < 2 ? '-'.$pdf_quality : ''); //to maintain consistency with old pdf name
                     $saved = $this->save_pdf_file($name, $pdfString );
                     if($saved) {
                        add_option($option_key, ['status' => static::PDF_GEN_OK, 'link' => $saved]);
                        return;
                     }
                }

         $this->parse_error($option_key, $body);           
    }

    /**
     * Save PDF to wp-upload
     * @return false/pdf-url
     */
    protected function save_pdf_file( $name, $pdfContent ){
        $uploads = wp_get_upload_dir();
        $fname = sprintf('%s/%s/%s.pdf', $uploads['basedir'], NOPME_PLUGIN_NAME, $name); 
        if(!file_put_contents($fname, $pdfContent)){
            return false;
        }
        return sprintf('%s/%s/%s.pdf', $uploads['baseurl'], NOPME_PLUGIN_NAME, $name);  
    }

    //Prepare pdf data for NopeaMedia
    protected function prepare_pdf_data($post) {

        //check if subarticles will be shown on a new page
        $subarticleInNewPage = get_post_meta($post->ID, 'show_subarticle_in_new_page', true) == 1 ? true : false;
        
        $raw = NopMed_Dao::get_publication_posts($post->ID);

        //get the configurations options
        $settings = $this->get_publication_settings($post->ID);

        $posts = array();
        //sort the posts
        foreach ($raw as $i => $dao) {

            $children = NopMed_Dao::get_publication_posts($post->ID, $dao->getPostId());
            $posts[] = $dao;

            //override pdf_type
            if($pdf_quality = get_post_meta($post->ID, 'pdf_quality', true)){
                $settings['pdf_type'] = intval($pdf_quality);
            }

            if (!empty($children)) {
                if ($subarticleInNewPage) { // merge children to top level posts
                    $posts = array_merge($posts, $children);
                } else {
                    $dao->set('children', $children) ; //place child-posts in parent
                    $posts[$i] = $dao;
                }
            }
            unset($children);
        }

        //sort inner posts
        foreach($posts as $p => $pst){
            $posts[$p] = $pst->toArray($settings);
            if($children = $pst->get('children')){
                $posts[$p]['children'] = array_map(function($v) use($settings){
                    return $v->toArray($settings);
                }, $children);
            }
            unset($children, $p);
        }

        $publication = (array) $post;

        $data = array(
            'publication' => $publication,
            'posts' => $posts,
        );

        return $data;
    }

    protected function parse_error( $option_key, $data ){
        $errMsg = '';
        $debug = $data['error']['trace'];
        if(isset($debug['causedBy']) && !empty($debug['causedBy'])){
            $errMsg = "<b>{$debug['causedBy']}</b>";
        }
        $errMsg .= sprintf( __ ("<br/> Error: <b>%s</b>", 'nopea-media'), $data['error']['message']);

        add_option($option_key, ['status' => static::PDF_GEN_FAIL, 'message' => $errMsg]);
    }


    protected function sort_order($posts, $parent = 0)
    {
        $sorted = array();
        foreach ($posts as $i => $node) {
				$sorted[] = array(
					'id' => $node['id'],
					'parent_id' => $parent,
                    'position' => $i
				);
				if (!empty($node['children'])) {
					$sorted = array_merge($sorted, $this->sort_order($node['children'], $node['id']));
				}
		}
        return $sorted;
	}

    protected function create_nav_menu($post)
    {
        $publication_title = $post->post_title;
        $publication_name = $post->post_name;
        // Check if the menu exists
        $menu = wp_get_nav_menu_object($publication_title);
        if ($menu) {
            // delete old menu item
            $menu_list = get_terms('nav_menu');
            $the_menu = null;

            foreach ($menu_list as $ml) {
                if ($ml->slug === $publication_name) {
                    $the_menu = $ml;
                    break;
                }
            }
            wp_delete_nav_menu($the_menu);
        }
        $menu_id = wp_create_nav_menu($publication_title);
        $menu = wp_get_nav_menu_object($publication_title);
        $menu_id = $menu->term_id;

        $posts = NopMed_Dao::get_publication_posts($post->ID);
        //fetch and set children object
        foreach($posts as $dao){
            $children = NopMed_Dao::get_publication_posts($post->ID, $dao->getPostId());
            if($children){
                $dao->set('children', $children);
            }
        }

        $this->create_menu_item($posts, 0, $menu_id, $publication_name);

    }

    protected function create_menu_item($posts, $parent_id, $menu_id, $publication_name){
        foreach ($posts as $post) {
            $menu_item = array(
                'menu-item-title' => $post->get('title'),
                //'menu-item-classes' => 'home',
                'menu-item-url' => home_url('/' . $this->post_type . '/' . $publication_name . '/' . sanitize_title($post->get('title'))),
                'menu-item-object-id' => $post->getPostId(),
                'menu-item-parent-id' => $parent_id,
                'menu-item-position' => $post->get('position') ?? 0,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
            );

            $item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item);
            wp_set_object_terms($item_id, $menu_id, 'nav_menu');
            unset($menu_item);

            if($children = $post->get('children')){
                $this->create_menu_item($children, $item_id, $menu_id, $publication_name);
            }
        }
    }

    public function show_pdf_notices(){
        global $post;
        if(!$post || !is_admin() || $post->post_type != $this->post_type) return;
        $option_key = sprintf('%s_pdf_generate_%d', NOPME_PREFIX, $post->ID);
        $pdf_data = get_option($option_key);
        switch ($pdf_data['status']) {
            case static::PDF_GEN_OK :
                nopme_show_notice(sprintf(__('PDF generated successfully. <a href="%s" target="_blank">View pdf</a>', 'nopea-media'), $pdf_data['link']),
                     'success');
                break;
            case static::PDF_GEN_FAIL :
                $message = sprintf(__('An error occured while generating PDF from article: %s.', 'nopea-media'), $pdf_data['message']);
                nopme_show_notice($message, 'error');
            break;
            case static::PDF_GEN_ERR :
                $class = 'notice notice-error is-dismissible';
                nopme_show_notice(__('PDF generation failed.', 'nopea-media'), 'error');
            break;
        }
        delete_option($option_key);
    }

    /**
     * Delete all posts-meta and configurations
     * TODO: 
     */
    public function on_delete_post( $postId )
    {
        global $wpdb;
        $post = get_post($postId);

        //delete publication data
        if($post->post_type == $this->post_type){

        }else{

        }
    }

    /**
     * Build meta_boxes for PDF Publications
     */
    public function meta_boxes()
    {
        
        add_meta_box(
            sprintf('%s_publication_setting', NOPME_PREFIX),
            esc_html__('Options', 'nopea-media'),
            array($this, 'publication_settings'),
            'publications',
            'normal',
            'high'
        );

        add_meta_box(
            sprintf('%s_post_list', NOPME_PREFIX),
            esc_html__('Posts', 'nopea-media'),
            array($this, 'list_post_html'),
            'publications',
            'normal',
            'low'
        );
    }

    public function publication_settings(){
        global $post;
        if($post){
            $postId = $post->ID;
            $options = $this->get_publication_settings($postId);
            $linkToPdf = $this->get_publication_pdf_link();
            $webPDF = $this->get_publication_pdf_link(1);
            require __DIR__ . '/../templates/publication_settings.php';
        }
    }

    //List all posts belonging to a publication
    public function list_post_html()
    {
        global $post;
        $menu_exists = wp_get_nav_menu_object($post->post_title);
        require __DIR__ . '/../templates/publication_posts.php';
    }

    public function register_publication_script($hook){
        global $post;
        if (in_array($hook, ['post.php', 'post_new.php']) && $post->post_type == $this->post_type) {

            wp_enqueue_style(
                'nopme-style',
                NOPME_PLUGIN_URL . 'dist/style.css',
                 [],
                 true
            );

            wp_enqueue_style(
                'nopme-nestable',
                NOPME_PLUGIN_URL . '/dist/nestable.css',
                [],
                true
            );

            wp_enqueue_script(
                'nopme-publication',
                NOPME_PLUGIN_URL . 'dist/publication.js',
                'jquery',
                '',
                true
            );

            wp_localize_script( 'nopme-publication', 'nopmed', array(
                'pubId' => $post->ID,
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'delete_text' => __('Delete post " {name} " and its subitems ?', 'nopea-media'),
                'nonce' => wp_create_nonce(NOPME_PREFIX)
            ));

        }
    }

    /**
     * Register the sidebar script, additionally loading the translation file
     */
    public function register_sidebar_script() {

		/**
		 * Do not load the scripts in the absence of a token.
		 */
		if(! NopMed_API::get_api_key() ) return;

		global $post;

        $post_id = $post->ID;
	if (!isset($post_id)) return;
        $dao = new NopMed_Dao($post_id);
		$theme = NopMed_Data::get_api_option('theme');
		$layouts = NopMed_Data::get_api_option('layout');

        if(!isset($theme['themes']) || !isset($layouts['layouts'])) return; //make sure the right data was fetched

        wp_enqueue_script( 'sidebar-js', NOPME_PLUGIN_URL . '/dist/sidebar.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n'], true );

        wp_set_script_translations( 'sidebar-js', 'nopea-media', dirname( __DIR__ ) . '/languages');

		$all = $dao->toArray();
		unset($all['blocks']);

        $upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];
		
        wp_localize_script( 'sidebar-js', 'nopmed', [
            'nonce' => wp_create_nonce(NOPME_PREFIX),
            'upload_dir' => $base_dir,
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'data' => $all,
			'themes' => $theme['themes'],
			'layouts' => $layouts['layouts'],
			'publications' => NopMed_Data::get_publications( $post_id )
		]);

		if(is_admin()){
			wp_register_script('admin-js', get_template_directory_uri() . '/admin.js', array('jquery'), '', true);
			wp_enqueue_script('admin-js');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script( 'jquery-ui-sortable' );
		}
	}


    protected function get_publication_pdf_link( $type = '')
    {
        $type = empty($type) ? 
                '' : (intval($type) < 2 ? '-'.intval($type) : '');
        global $post;
        $upload_dir = wp_get_upload_dir();
        $baseurl = $upload_dir['baseurl'];
        $basedir = $upload_dir['basedir'];
        $pdf_name = $post->post_name . $type;

        $pdf_link = sprintf('%s/%s/%s.pdf', $baseurl, NOPME_PLUGIN_NAME, $pdf_name);
        $pdf_file = sprintf('%s/%s/%s.pdf', $basedir, NOPME_PLUGIN_NAME, $pdf_name);
        if (file_exists($pdf_file) && is_file($pdf_file)) {
            return $pdf_link;
        }
        return false;
    }
    
    /*
     * Register PDF column in posts-pages admin list
    */
    public function register_nopeamedia_column( $columns ){
        $columns['nopme_pdf'] = __('PDF', 'nopea-media');
        return $columns;
    }

    /**
     * Add pdf link to posts-pages-publication pdf column
     */
    public function add_pdf_column_data( $column, $post_id ){
        $post_type = get_post_type();
        if($column == 'nopme_pdf'){

            if($this->post_type == $post_type && ($link = $this->get_publication_pdf_link())){ //publication list
                echo '<a href="'.$link.'" target="_blank">'.__('View PDF', 'nopea-media').'</a>';
            }else{
                //for post/page type
                $dao = new NopMed_Dao($post_id);
                 $pdfLink = $dao->get('pdf_generated');
                 if(!empty($pdfLink))
                   echo '<a href="'.$pdfLink.'" target="_blank">'.__('View PDF', 'nopea-media').'</a>';
                else
                  echo '—';
            } 
        }
    }

}
