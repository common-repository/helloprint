    <?php 
    $submitted_to_helloprint = $args['submitted_to_helloprint'];
    $is_order_created = !empty($submitted_to_helloprint['order_id']) ? true : false;
    if (!empty($args['artworks']) > 0) : ?>
        <div class="wphp-wp-artwork-section woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
            <?php echo wp_kses(_translate_helloprint("Artworks Files", "helloprint"), true); ?>:
            <address>
                <?php foreach ($args['artworks'] as $artwork) : ?>
                    <?php if (strlen($artwork->file_url) > 0) : ?>
                        <dd class="upload_container">
                            <strong>
                                <?php 
                                    $file_arr = explode('/', $artwork->file_url);
                                    $file_name = end($file_arr );
                                    if (strpos($artwork->file_url, "//") === FALSE) {
                                        $file_name = substr($file_name, strpos($file_name, "-") + 1); 
                                    }
                                    echo esc_html($file_name);
                                ?>
                            </strong>
                            <div class="upload_container_status">
                                <a class="file_upload_link wphp-pointer" download href="<?php echo esc_url_raw(get_site_url() . $artwork->file_url) ?>"><?php echo wp_kses(_translate_helloprint("Download", "helloprint"), true); ?></a>
                                <?php if (!$is_order_created) : ?>
                                <a data-itemid="<?php echo esc_attr($artwork->line_item_id);?>" data-orderid="<?php echo esc_attr($artwork->order_id);?>" data-id="<?php echo esc_attr($artwork->id);?>" href="#" class="wphp_artwork_file_remove btn btn-sm btn-outline-danger wphp-order-details-delete-artwork" style="display:none;"><?php echo wp_kses(_translate_helloprint('Remove', 'helloprint'), true);?></a>
                                <?php endif; ?>
                            </div>

                        </dd>
                    <?php endif ?>
                <?php endforeach ?>
            </address>
        </div>
    <?php endif; ?>
    <?php if (!$is_order_created) : ?>
        <div class="wphp-wp-artwork-section  wphp-artwork-file-upload wphp-order-details-upload-artwork-section" style="display:none;" >

            <hr>
            <div class="wphp_upload_artwork_container wphp_upload_container_button_<?php echo esc_attr($args['item_id']) ?>">
                <legend>
                    <?php echo wp_kses(_translate_helloprint('UPLOAD YOUR FILES', 'helloprint'), true) ?>
                </legend>
                <div id="wphp_artwork_file_upload_<?php echo esc_attr($args['item_id']) ?>">
                    <input type="file" class="wphp_artwork_file_upload wphp_artwork_file_upload_<?php echo esc_attr($args['item_id']) ?>" name="wphp_product_file_upload[]" data-itemid="<?php echo esc_attr($args['item_id']) ?>" multiple="multiple">
                    <input type="hidden" id="wphp_order_id_<?php echo esc_attr($args['item_id']) ?>" class="wphp_order_id" value="<?php echo esc_attr($args['order_id']); ?>">
                    <input type="hidden" id="wphp_item_id_<?php echo esc_attr($args['item_id']) ?>" class="wphp_item_id" value="<?php echo esc_attr($args['item_id']) ?>">
                </div>
            </div>


        </div>
    <?php endif; ?>
