<?php

defined( 'ABSPATH' ) || exit;

class NopMed_WPCLI_Migrate
{

    /**
     * Migrate old content from vv_nopea table to post_meta table
     */
    public function migrate(){
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        global $wpdb;

        $results = $wpdb->get_results("SELECT * from vv_posts");

        foreach($results as $res)
        {
            $dao = new NopMed_Dao($res->post_id);
            foreach($dao->getAttributes() as $attr => $props)
            {
                if(isset($res->{$attr})){
                    $dao->set($attr, $res->{$attr});

                    if($attr == 'featured_image'){
                        $upload = wp_get_upload_dir();
                        $dao->set($attr, $upload['baseurl'] . '/'. $res->{$attr});
                    }

                    if($attr == 'publication_id'){
                        $dao->set($attr, explode(',', $res->{$attr}));
                    }
                    if($attr == 'parent_id' && !empty($res->{$attr})){
                        $parent = $wpdb->get_row("SELECT post_id from vv_posts where id=" . $res->{$attr} );
                        $dao->set($attr, $parent->post_id);
                    }
                }

                $dao->set('blocks', json_decode($res->block_data, true));

                $config = json_decode($res->post_config, true);
                $dao->set('background', $config['background']); unset($config['background']);
                $dao->set('theme', $config['theme']); unset($config['theme']);
                $dao->set('pdf_type', $config['pdfType']); unset($config['pdfType']);
                $dao->set('generate_pdf', $config['create_pdf']); unset($config['create_pdf']);
                $dao->set('show_footer', $config['footer']); unset($config['footer']);
               # $dao->set('extra_pdf', isset($config['pdf_upload'])? $config['pdf_upload'] : '');

		        if(isset($config['pdf_upload'])){
                    $dao->set('extra_pdf', $config['pdf_upload']);
                    unset($config['pdf_upload']);
                }

                unset($config['themes']);

                $dao->set('layout_config', $config);
                $dao->save();
            }
      }
    }

}
