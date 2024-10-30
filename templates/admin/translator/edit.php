<?php
/**
* PHP view.
*
*/
?>
<div class="wrap">
    <h1><?php echo wp_kses(_translate_helloprint('Edit Translation', "helloprint"), true); ?></h1>
    <form name="wphp-add-translator" method="POST" action="">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo esc_attr($id);?>">

        <table class="form-table" role="presentation">
            <tbody>
                <tr class="example-class">
                    <th scope="row">
                        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('String', "helloprint"), true); ?></label>
                    </th>
                    <td>
                        <div class="example-class">
                         <input style="width:100%"  type="text" class="regular-text" id="string" name="string" value="<?php echo esc_attr($string);?>">
                     </div>
                 </td>
             </tr>

             <tr class="example-class">
                <th scope="row">
                    <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Translation', "helloprint"), true); ?></label>
                </th>
                <td>
                    <div class="example-class">
                    <div id="wphp-markdown-editor"></div>
                    <textarea id="wphp-markdown-textarea" maxlength="500" placeholder="<?php echo wp_kses(_translate_helloprint('/*............ Your Makdown here .........*/', "helloprint"), false); ?>" class="regular-text" name="translation" id="translation" style="display:none;width: 100%; resize: vertical;white-space: pre-wrap; text-indent: 5px;" rows="10"><?php echo esc_attr(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $translation));?></textarea>
                    <?php //echo wp_editor(esc_attr($translation), "translation" , array('media_buttons' => false, 'textarea_name' => 'translation'));?>
                 </div>
             </td>
         </tr>
     </tbody>
 </table>

 <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo wp_kses(_translate_helloprint('Update', "helloprint"), false); ?>"></p>    
</form>
</div>