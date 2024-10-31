<?php
defined( 'ABSPATH' ) || exit; ?>

<table class="form-table">
    <tbody>

        <tr class="user-rich-editing-wrap" >
            
            <td> 
                <p id="meta-options">
                    <label><input type="checkbox" <?=get_post_meta($postId, 'with_pdf', true) ? 'checked=checked' : ''?> value="1" class="checkbox" id="with_pdf" name="with_pdf "><?=__('Generate PDF', 'nopea-media') ?></label>
                </p>
            </td>

            <td> 
            <p id="meta-options">
                <label><input type="checkbox" <?=get_post_meta($postId, 'show_subarticle_in_new_page', true) ? 'checked=checked' : ''?> value="1" class="checkbox" id="show_subarticle_in_new_page" name="show_subarticle_in_new_page"><?=    __('Subarticles in new page', 'nopea-media') ?></label>
            </p>
            </td>-
        </tr>

        <tr>
            <td> 
                <p id="meta-options">
                    <label for="pdf_quality">
                        <b><?= __('PDF Quality', 'nopea-media')?></b>
                    </label>
               </p>
                <br>
                <p id="meta-options">
                    <input type="radio" <?=get_post_meta($postId, 'pdf_quality', true) == 1 ? 'checked=checked' : ''?> value="1" class="radio" id="web" name="pdf_quality"><?=__('Web', 'nopea-media') ?>
                    <a class="pdf-preview " href="#" <?=$webPDF ? 'onclick="window.open(\''.$webPDF.'\')"' : ''?>><?=__('Preview PDF', 'nopea-media')?></a>
                </p>
                <br>

                <p id="meta-options">
                    <input type="radio" <?=get_post_meta($postId, 'pdf_quality', true) == 2 ? 'checked=checked' : ''?> value="2" class="radio" id="print" name="pdf_quality"><?=__('Print', 'nopea-media') ?>
                    <a class="pdf-preview " href="#" <?=$linkToPdf ? 'onclick="window.open(\''.$linkToPdf.'\')"' : ''?>><?=__('Preview PDF', 'nopea-media')?></a>
                </p>

            </td>
            
        </tr>

        <tr class="user-rich-editing-wrap">
           
            <td>  
                 <label><?=__('Odd Pages Footer', 'nopea-media')?></label> <br>
                 <input type="text" id="odd_page_footer" name="odd_page_footer" class="regular-text" value="<?=$options['odd_page_footer']?>">
            </td>

            <td> 
                <label><?=__('Even Pages Footer', 'nopea-media')?></label><br>
                <input type="text" id="even_page_footer" name="even_page_footer" class="regular-text" value="<?=$options['even_page_footer']?>">
            </td>
        </tr>

    </tbody>

</table>

