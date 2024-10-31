<?php
defined( 'ABSPATH' ) || exit;

class NopMed_Ajax
{

    private static $instance;

    private function construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function save_post_extras()
    {
        global $wpdb;
        $pdf = array();

        $nonce = filter_input(INPUT_POST, 'nm_nonce', FILTER_SANITIZE_STRING);
        $postId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $post = array_map( 'wp_unslash', $_POST['post']);
	
	//use parse_blocks function for the blocks and not the $_POST variable because it was missing data
	$post2 = get_post($postId);
	$blocks = parse_blocks($post2->post_content);
	$post['blocks'] = $blocks;

        //validate request source using wp_nonce
        if($nonce != wp_create_nonce(NOPME_PREFIX)) wp_die();

        $dao = new NopMed_Dao($postId);
        $attributes = $dao->getAttributes();

        $uid = $dao->get('uid');
        if (empty($uid)) {
            $dao->set('uid', static::uuid());
        }

        foreach ($attributes as $attr => $props) {
           isset($post[$attr]) ? $dao->set($attr, $post[$attr]) : null;
        }

        $dao->set('publication_id', isset($post['publication']) ? $post['publication'] : array());
        $dao->set('featured_image', isset($post['image']) ? $post['image'] : "");
        $dao->save();

        //If the user wants a pdf.
        if ($dao->get('generate_pdf') === true ) {
            $pdf = static::create_pdf( $dao );
        }

        $response = array('pdf' => $pdf);
        echo json_encode($response); wp_die();
    }

    public static function create_pdf( $dao )
    {
        $post = $dao->toArray();

        try {

            $response = NopMed_API::create_article_pdf( $post );

            if(is_wp_error($response)){
                return array('error' => true, 'message' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $body = json_decode($body, true);

            //Oops, there is an error !
            if (isset($body['error'])) {
                return $body['error'];
            }

            //Do we have the desired base64_encoded data ?
            if (isset($body['data']) && !empty($body['data']) && ($pdfString = base64_decode($body['data']))) {
                $uploads = wp_get_upload_dir();
                $fname = sprintf('%s/%s/%s.pdf', $uploads['basedir'], NOPME_PLUGIN_NAME, $post['uid']);
                $furl = sprintf('%s/%s/%s.pdf', $uploads['baseurl'], NOPME_PLUGIN_NAME, $post['uid']);
                if (file_put_contents($fname, $pdfString)) {
                    $dao->set('pdf_generated', $furl);
                    $dao->save();
                    return array(
                        'url' => $furl,
                    );
                }
            }

        } catch (Execption $e) {
            return array('error' => [
                'message' => __('Error occured while generating the PDF', 'nopea-media'),
            ]);
        }
    }

    /**
     * Delete post from a publication
     */
    public static function delete_publication_post(){
        $id = sanitize_text_field( $_POST['id'] ); 
        $pubId = sanitize_text_field( $_POST['pubId'] );
        $response = array('deleted' => false);
        if($id && $pubId){
            $dao = new NopMed_Dao($id);
            $pubIds = $dao->get('publication_id');
            
            $pubIds = array_filter($pubIds, function($v) use($pubId){
                 return $v != $pubId;
            });

            $dao->set('publication_id', array_values($pubIds));
            $dao->save();
            $response['deleted'] = true;
        }
        echo json_encode($response); wp_die();
    }

    /** Copied from fzaninotto/Faker
     * Generate name based md5 UUID (version 3).
     * @example '7e57d004-2b97-0e7a-b45f-5387367791cd'
     */
    public static function uuid()
    {
        // fix for compatibility with 32bit architecture; seed range restricted to 62bit
        $seed = mt_rand(0, 2147483647) . '#' . mt_rand(0, 2147483647);

        // Hash the seed and convert to a byte array
        $val = md5($seed, true);
        $byte = array_values(unpack('C16', $val));

        // extract fields from byte array
        $tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
        $tMi = ($byte[4] << 8) | $byte[5];
        $tHi = ($byte[6] << 8) | $byte[7];
        $csLo = $byte[9];
        $csHi = $byte[8] & 0x3f | (1 << 7);

        // correct byte order for big edian architecture
        if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
            $tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8)
             | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
            $tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
            $tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
        }

        // apply version number
        $tHi &= 0x0fff;
        $tHi |= (3 << 12);

        // cast to string
        $uuid = sprintf(
            '%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
            $tLo,
            $tMi,
            $tHi,
            $csHi,
            $csLo,
            $byte[10],
            $byte[11],
            $byte[12],
            $byte[13],
            $byte[14],
            $byte[15]
        );

        return $uuid;
    }
}
