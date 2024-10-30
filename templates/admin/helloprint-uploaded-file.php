<?php
/**
 * PHP view.
 *
 */
?>
<div class="wphp-single-order-item-file">
    <script type="text/javascript">
        var pluginUrl = '<?php echo esc_url(plugin_dir_url(__DIR__)); ?>';
    </script>
    <table>
        <tbody>
            <tr>
                <td colspan="5">
                    <p class="">
                        <h4><?php echo wp_kses(_translate_helloprint('Files', 'helloprint'), true);?>:</h4>
                        <?php 

                        foreach ($args['uploaded_files'] as $key => $file) { 
                            if ($file['file_name'] !== '') { ?>
                                <span class="order-files-div">
                                    <span> <?php echo esc_attr($file['file_name']);?> </span>
                                    <br>
                                    <a download href="<?php echo esc_url(get_site_url() . $file['file_path']);?>"><?php echo wp_kses(_translate_helloprint('Download', 'helloprint'), true);?> </a>  &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                                    <a data-file-key="<?php echo esc_attr($key);?>" data-item_id="<?php echo esc_attr($args['item_id']);?>" class="wphp-order-remove-file btn btn-sm btn-outline-danger"  href="#"><?php echo wp_kses(_translate_helloprint('Remove', 'helloprint'), true);?> </a> 
                                    <br>
                                </span>
                                <br>
                            <?php }}?>
                            <div class="upload_container_button ">
                                <div class="wphp-order-item-file-upload">         
                                    <div id="helloprint_order_item_<?php echo esc_attr($args['item_id']);?>"></div>
                                    <input data-itemid="<?php echo esc_attr($args['item_id']);?>" type="file" data-id="helloprint_order_item_<?php echo esc_attr($args['item_id']);?>" class="helloprint_order_item_file_each helloprint_order_item_<?php echo esc_attr($args['item_id']);?>" name="helloprint_order_item_file_upload[]" multiple="multiple" />
                                </div> 
                                <br/>
                                <button class="button button-primary wphp-submit-order-items">Save</button>
                                
                                <div data-cart-item-key="<?php echo esc_attr($args['item_id']) ?>"></div>
                                <input type="hidden" class="data-item-id" value="<?php echo esc_attr($args['item_id']) ?>">
                            </div>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>