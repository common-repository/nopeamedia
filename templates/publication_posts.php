<?php defined( 'ABSPATH' ) || exit; 

//output post html
function posts_html($pubId, $post){
    $n = '<li class="dd-item" data-id="' . $post->getPostId() . '" data-name="' . $post->get('title') . '" data-slug="' . $post->get('name') . '" data-post-id="' . $post->getPostId() . '" data-new="0" data-deleted="0">
            <div class="dd-handle">' . $post->get('title') . '</div>
             
            <!-- Start Action links -->
            <div>
                <span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="' . $post->getPostId() . '">
                <i class="far fa-times-circle pub-action" aria-hidden="true"></i>
              </span>
              <span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="' . $post->getPostId() . '" data-post-id="' . $post->getPostId() . '"
                data-edit-url="' . get_edit_post_link($post->getPostId()) . '">
                <i class="fas fa-pencil-alt pub-action" aria-hidden="true"></i>
              </span>
              <span class="button-pdf btn btn-default btn-xs" data-owner-id="' . $post->getPostId() . '" data-post-id="' . $post->getPostId() . '"
                data-pdf-url="' . $post->get('pdf_generated') . '">
                <i class="fas fa-file-alt pub-action" aria-hidden="true"></i>
              </span>
            </div>
            <!-- End Action links -->';
          
    //make children
    $children = NopMed_Dao::get_publication_posts($pubId, $post->getPostId());
    $ch = '';
    if (count($children) != 0) {
        $ch .= '<ol class="dd-list">';
        foreach ($children as $child) {
            $ch .= posts_html($pubId, $child);
        }
        $ch .= '</ol>';
    }
    $n .= $ch;
    $n .= '</li>';
    return $n;
}

$posts = NopMed_Dao::get_publication_posts($post->ID);
$node = '';
foreach ($posts as $p) {
    $node .= posts_html($post->ID, $p);
}
?>

<div class="row">
    <div class="col-md-6">
          <span><?=esc_html__('Drag the post to re-arrange', NOPME_PREFIX)?> </span>
          <div class="dd nestable">
            <ol class="dd-list">
              <?=$node?>
            </ol>
          </div>
          
      </div>
  </div>
  <hr >

    <table class="form-table publication-post-actions">
    <tbody>
        <tr class="user-rich-editing-wrap">
             <td> 
               
            </td> 
            <td> 
              <p style="float:right">
                    <button <?=$menu_exists ? 'onclick="return window.confirm(\''.__('Menu exists, generate new menu?', 'nopea-media').'\')"' : ''?> type="submit" name="save" value="generate_menu" class="button button-primary button-large"><?=__('Generate Menu', 'nopea-media')?></button>
                </p>
            </td> 
        </tr>
    </tbody>

</table>

<textarea style="display:none" name="posts_order" id="posts_order"></textarea>