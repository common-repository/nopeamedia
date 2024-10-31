<?php
defined( 'ABSPATH' ) || exit;
/**
 * NopMed_Dao (Data Access Object)
 * Exposes NopeaMedia content/config data
 */
class NopMed_Dao {

    protected $data = array();

    protected $postId;

    /**
     * Key value of attributes
     * value specifies default data type and validators
     **/
    protected $attributes = array(
        'uid' => array('', ''),
        'title' => array('', ''),
        'name' => array('', ''),
        'children' => array(array(), ''),
        'generate_pdf' => array(false, FILTER_VALIDATE_BOOLEAN),
        'show_footer' => array(false, FILTER_VALIDATE_BOOLEAN),
        'pdf_type' => array(1, FILTER_VALIDATE_INT),
        'theme' => array(1, FILTER_VALIDATE_INT),
        'background' => array(array(), ''),
        'pdf_generated' => array('', ''),
        'extra_pdf' => array(array(), ''),
        'layout' => array(1, FILTER_VALIDATE_INT),
        'layout_config' => array(array(), ''),
        'position' => array(0, FILTER_VALIDATE_INT),
        'parent_id' => array(0, FILTER_VALIDATE_INT),
        'featured_image' => array('', ''),
        'publication_id' => array(array(), ''),
        'blocks' => array(array(), '')
    );

    public function __construct( int $postId ){
        global $wpdb;
        $this->postId = $postId;
        foreach($this->attributes as $attr => $props){
            $meta_key = sprintf('%s%s', NOPME_META_PREFIX, $attr);
            $this->data[$meta_key] = get_post_meta($this->postId, $meta_key, true);
        }
    }

    public function getAttributes(){
        return $this->attributes;
    }

    public function getPostId(){
        return $this->postId;
    }

    /* Get all nopea-media user data 
     * @param array extra - add extra param to post data.
     * must be a multi-dimentional array
     * @return array
     */
    public function toArray( $extra = array()){
        global $wpdb;
        $data = array();
        $attributes = $this->attributes;
        foreach($attributes as $attr => $props){
            $meta_key = sprintf('%s%s', NOPME_META_PREFIX, $attr);
            $value = get_post_meta($this->postId, $meta_key, true);
            //check if the values need to be casted 
            list($default, $validator) = $props;
            if( !empty($validator) ){
                $value = filter_var($value, $validator);
            }        
            $data[$attr] = $value;
        }

        //add general plugin config .. e.g (colors)
		foreach(NopMed_Publication::PDF_CONFIG as $op){
            $data[$op] = get_option($op);
        }

        if(!empty($extra)){
            $data = array_merge($data, $extra);
        }

        return $data;
    }

    public function get( $key ){
        $meta_key = sprintf('%s%s', NOPME_META_PREFIX, $key);
        if(array_key_exists($meta_key, $this->data)){
            list($default, $validator) = $this->attributes[$key];
            $value = $this->data[$meta_key];
            if(empty($value)){ //get defined-default value 
                $value = $default;
            }
            if( !empty($validator) ){
                $value = filter_var($value, $validator);
            } 

            return $value;
        }
        throw new Exception(__("Key not found in attribute list", 'nopea-media'));
    }

    /**
     * Add plugin prefix to attribute key
     * and set the value
     */
    public function set($key, $value){
        if(!array_key_exists($key, $this->attributes)){
            throw new Exception(__("Key not found in attribute list", 'nopea-media'));
        }

        $value = ($key == 'parent_id' ? (int) $value : $value);

        if($key == 'blocks'){
            $value = $this->parse_charts($value);
        }

        $meta_key = sprintf('%s%s', NOPME_META_PREFIX, $key);
        $this->data[$meta_key] = $value;
    }

     /**
     * Parse chart in block content
     */
    protected function parse_charts( $blocks ) {

        $content = json_encode($blocks);

        //checks if post block-content contains chart
        if ( has_shortcode( $content, 'chart' ) ) {
            
            foreach($blocks as $k => $block){
                
                //verify that shortcode is a chart 
                if($block['blockName'] == 'core/shortcode' && (strpos($block['innerHTML'], 'chart') !== FALSE)){
                    
                    //find chart id && checks if plugin is active
                    if(preg_match("/\d+/", $block['innerHTML'], $match) && NOPME_IS_MCHART_INSTALLED) {
                        $args = $match[0]; 
                        $chart_img = m_chart()->get_chart_image($args); //m-chart/components/class-m-chart.php:587
                        
                        //check if chart-image url is found
                        if(!empty($chart_img['url'])){
                            $block['blockName'] = 'core/image'; //replace shortcode with image 
                            $block['attrs']['url'] = $chart_img['url']; 
                            $blocks[$k] = $block;
                        }
                    }
                }
            }
        }

        return $blocks;
    }

    /**
     * Save attribute data as post_meta
     */
    public function save(){
       $attributes = $this->attributes; 
       
       foreach($this->data as $meta_key => $value){
          if(empty($value)){ //use default value as defined in the attributes 
            $attrKey = str_replace(NOPME_META_PREFIX, '', $meta_key);
            list($default, $validator) = $attributes[$attrKey];
            $value = $default;
          } 
          update_post_meta($this->postId, $meta_key, $value);
       }
    }

    public static function get_publication_posts($pubId, $parentId = 0){
        $posts = array();

        $args = array(
            'post_type' => array('post', 'page'),
            'posts_per_page'       => -1,
            'post_status' => array('any'),
            'orderby'    => 'position',
            'order'      => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                'pub' => array(
                    'key' => sprintf('%s%s', NOPME_META_PREFIX, 'publication_id'),
                    'value' => '"'. $pubId .'"',
                    'compare' => 'LIKE',
                ),
               'parent_id' => array(
                    'key' => sprintf('%s%s', NOPME_META_PREFIX, 'parent_id'),
                    'value' => $parentId,
                ),
               'position' => array(
                    'key' => sprintf('%s%s', NOPME_META_PREFIX, 'position'),
                    'compare' => 'EXISTS',
                    'type' => 'NUMERIC'
                )
            ),
        );

        $query = get_posts($args);
    
        foreach($query as $post){
          $dao = new NopMed_Dao($post->ID);
          $dao->set('name', $post->post_name);
          $posts[] = $dao; 
        }
    
        wp_reset_postdata();
        return $posts;
    }

}
