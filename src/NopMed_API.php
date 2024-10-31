<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class NopMed_API {

	public static function get_api_key(){
		return get_option(sprintf('%s_api_key', NOPME_PREFIX), false);
	}

	/**
	 * Send HTTP request to NopeaMedia API,
	 * Set execution timeout to five seconds, because combining publication takes a while. 
	 * @param array publication and post data
	 * @return string base64 encoded string as pdf
	 */
	public static function create_publication_pdf( $data ){
		$pages = "";
		$pageNum = 1;
		foreach ($data['posts'] as $key => $post) {
			
			$response1 = wp_remote_post( NOPME_API_BASE_URL . 'pdf/saveArticle', array(
				'blocking' => true,
				'timeout'   => 3000, 
				'body'=> array(
					'post' => $post,
					'pageNum' => $pageNum
				 ),
				'headers' => array(
					'Nopeamedia-Token' => self::get_api_key()
				)
			));
			$body = wp_remote_retrieve_body($response1);
            $body = json_decode($body, true);

            //Oops, there is an error !
            if (isset($body['error'])) {
                return $body['error'];
            }
			$pages .= " " . $body['data'];
			$pageNum = $body['pageNum'];
		}
		$publication['publication'] = $data['publication'];
		$publication['pages'] = $pages;
		$response = wp_remote_post( NOPME_API_BASE_URL . 'pdf/combine', array(
			'blocking' => true,
			'body' => $publication,
			'timeout'   => 5000, 
			'headers' => array(
				'Nopeamedia-Token' => self::get_api_key()
			)
		));

		//update publication quota
		if(!is_wp_error($response) && isset($response['headers']['x-pdf-quota'])){
			update_option(sprintf('%s_publication_quota', NOPME_PREFIX), $response['headers']['x-pdf-quota']);
		}

        return $response;
	}

	/**
	 * Send HTTP request to NopeaMedia API
	 * @param array post data
	 * @param array post configuration
	 * @return string base64 encoded string as pdf
	 */
	public static function create_article_pdf( $post ){
		//add pluging options 
		foreach(NopMed_Publication::PDF_CONFIG as $op){
             $post[$op] = get_option($op);
		}
	
		$response = wp_remote_post( NOPME_API_BASE_URL . 'pdf/article', array(
			'blocking' => true,
			'timeout'   => 3000, 
			'body'=> array(
				'post' => $post
			 ),
			'headers' => array(
				'Nopeamedia-Token' => self::get_api_key()
			)
		));

		return $response;
	}

	public static function get_layout_config(){
      $response = wp_remote_get( NOPME_API_BASE_URL . 'layouts', array('headers' => array(
		'Nopeamedia-Token' => get_option(sprintf('%s_api_key', NOPME_PREFIX))
	  )));

      if(!is_wp_error($response)){
       return json_decode($response['body'], true);
	  }
	  return array();
	}

	public static function get_theme_config(){
        $response = wp_remote_get( NOPME_API_BASE_URL . 'layouts/themes', array('headers' => array(
			'Nopeamedia-Token' => get_option(sprintf('%s_api_key', NOPME_PREFIX))
		)));

		if(!is_wp_error($response)){
            return json_decode($response['body'], true);
		}
		return array();
	}


}