<?php
/**
* PHP view.
*
*/
?>
<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
    <thead>
        <tr>
            <th colspan="2">
                <h1><?php echo wp_kses(_translate_helloprint("File upload", 'helloprint'), true); ?></h1>
            </th>
        </tr>
        <tr>
            <th class="product-name"><?php echo wp_kses(_translate_helloprint("Product", 'helloprint'), true); ?></th>
            <th><?php echo wp_kses(_translate_helloprint("Files", 'helloprint'), true); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($args['cartItems'] as $cart_key => $item) : 
                if($item['can_upload_file']) :
            ?>
            <tr>
                <td width="50%">
                    <?php echo wp_kses(_translate_helloprint($item['name'], 'helloprint'), true) ?>
                    <dl class="variation">
                        <dt class="variation-Productgroup">
                            <?php echo wp_kses(_translate_helloprint("Quantity", 'helloprint'), true) ?>:
                        </dt>
                        <dd class="variation-Productgroup">
                            <p><?php echo esc_html($item['quantity']) ?></p>
                        </dd>
                        <dt class="variation-Productgroup">
                            <?php echo wp_kses(_translate_helloprint("Delivery Option", 'helloprint'), true) ?>:
                        </dt>
                        <dd>
                            <?php echo wp_kses(_translate_helloprint(ucfirst($item['delivery_option']), 'helloprint'), true); 
                            if (!empty($item["total_delivery_days"])) {
                                echo ' - ' . $item["total_delivery_days"] . ' ' . wp_kses(_translate_helloprint("Day(s)", 'helloprint'), true);
                             } ?>
                        </dd>
                        <?php foreach ($item['options'] as $key => $option) : ?>
                            <?php if ($key === 'appreal_size'): ?>
                                <?php foreach($option as $opt_key => $opt_val):?>
                                <dt class="variation-Productgroup">
                                    <?php echo esc_html($opt_key); ?>:
                                </dt>
                                <dd class="variation-Productgroup">
                                    <p>
                                        [
                                        <?php 
                                            if(is_array($opt_val)) {
                                                $iterarray = [];
                                                foreach ($opt_val as $k => $val) {
                                                    $iterarray[] = $k . ":" . $val; 
                                                }
                                                echo esc_html(implode("; ", $iterarray));
                                            } else {
                                                echo esc_html($opt_val);
                                            }
                                        ?>
                                        ]
                                        
                                    </p>
                                </dd>
                            <?php endforeach;?>
                            <?php else: ?>
                            <dt class="variation-Productgroup">
                                <?php echo wp_kses(_translate_helloprint(ucfirst($key), 'helloprint'), true) ?>:
                            </dt>
                            <dd class="variation-Productgroup">
                                <p><?php echo wp_kses(_translate_helloprint(ucfirst($option), 'helloprint'), true) ?></p>
                            </dd>
                        <?php endif;?>
                        <?php endforeach ?>

                    </dl>
                </td>
                <td>
                    <dl class="variation">
                        <dt class="variation-Productgroup">
                            <?php echo wp_kses(_translate_helloprint("Uploaded Files", "helloprint"), true);?>:
                        </dt>
                        <?php foreach ($item['uploaded_files'] as $k => $file_component) : ?>
                            <?php if (strlen($file_component['file_name']) > 0) : ?>
                                <dd class="upload_container" id="upload_container_<?php echo esc_attr($item['key']); ?>_<?php echo esc_attr($k) ?>" data-is-uploaded="<?php echo (strlen($file_component['file_name']) > 0) ? "1" : "0" ?>">
                                    <strong>
                                        <?php echo esc_html($file_component['file_name']) ?>
                                    </strong>
                                    <div class="upload_container_status">
                                        <a class="file_upload_link wphp-pointer" download href="<?php echo esc_url_raw(get_site_url() . $file_component['file_path']) ?>"><?php echo wp_kses(_translate_helloprint("Download", "helloprint"), true);?></a>
                                        <a class="wphp_file_upload_remove wphp-pointer" data-cart-item-key="<?php echo esc_attr($item['key']) ?>" data-file-component-id="<?php echo esc_attr($k) ?>"><?php echo wp_kses(_translate_helloprint('Remove', 'helloprint'), true) ?></a>
                                    </div>

                                </dd>
                            <?php endif ?>
                        <?php endforeach ?>
                        <div class="upload_container_button">

                            <h4><?php echo wp_kses(_translate_helloprint("Upload new file", "helloprint"), true);?></h4>
                            <div class="wphp-cart-file-upload">
                                <input type="file" class="wphp_cart_file_upload_<?php echo esc_attr($cart_key) ?>" name="wphp_product_file_upload[]" multiple="multiple">
                            </div>
                            <div data-cart-item-key="<?php echo esc_attr($item['key']) ?>"></div>
                            <input type="hidden" class="data-item-key" value="<?php echo esc_attr($item['key']) ?>">
                        </div>
                    </dl>
                </td>
            </tr>
        <?php endif;
            endforeach; ?>
    </tbody>
    <input type="hidden" name="wphp_cart_items_count" id="wphp_cart_items_count" value="<?php echo count($args['cartItems']) ?>">
</table>