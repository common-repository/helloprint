<?php
/**
* PHP view.
*
*/
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo wp_kses(_translate_helloprint('Order Presets', "helloprint"), true) ?></h1>
    <a href="<?php menu_page_url('add-order-preset'); ?>" class=" page-title-action"> <?php echo wp_kses(_translate_helloprint('Add Order Preset', "helloprint"), true) ?></a>

    <form id="posts-filter" method="get">

        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php echo wp_kses(_translate_helloprint('Search Order Preset', "helloprint"), true) ?>:</label>
            <input type="hidden" name="page" value="<?php echo !empty($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 1;?>" />
            <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($s);?>">
            <input type="submit" id="search-submit" class="button" value="<?php echo wp_kses(_translate_helloprint('Search Order Preset', "helloprint"), false) ?>">
        </p>

    </form>
    <br/><br/>
    <div class="row">
        <div class="col-md-12">
            <div class="pt-3">
                <?php 
                if (isset($_GET['success'], $_GET['hp_nonce']) && wp_verify_nonce( sanitize_key( $_GET['hp_nonce'] ), 'order_preset' )) : ?>
                    <div class="alert alert-success notice notice-success" role="alert" id="alertSuccess">
                        <p>
                            <?php echo wp_kses(_translate_helloprint("Order Preset  " . sanitize_text_field(wp_unslash($_GET['success'])) . " successfully", "helloprint"), true) ?> </p>
                        </div>
                    <?php endif; ?>
                    <div class="alert alert-danger" role="alert" id="alertDanger" style="display: none;"></div>

                    <div class="spinner-border text-success" role="status" id="loader" style="display: none;">
                        <span class="sr-only"><?php echo wp_kses(_translate_helloprint('Loading...', "helloprint"), true) ?></span>
                    </div>

                    <table class="wp-list-table widefat fixed striped table-view-list pages" id="tbl-token-list">
                        <thead>
                            <tr>
                                <th><?php echo wp_kses(_translate_helloprint('#ID', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Product Type', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Name', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('SKU', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Variant Key', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Default Service Level', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Default Quanitity', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Action', "helloprint"), true) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($order_presets as $key => $op): ?>
                                <tr class="<?php echo (0 === $key%2) ? 'even' : 'odd' ;?>">
                                    <td><?php echo esc_html($op->id); ?></td>
                                    <td><?php echo ($op->product_type == "non_hp") ? wp_kses(_translate_helloprint('Non-Helloprint', "helloprint"), true) : wp_kses(_translate_helloprint('Helloprint', "helloprint"), true) . " (" . $op->helloprint_product_id . ")"; ?></td>
                                    <td><?php echo esc_html($op->order_preset_name); ?></td>
                                    <td><?php echo esc_html($op->helloprint_item_sku); ?></td>
                                    <td><?php echo esc_html($op->helloprint_variant_key); ?></td>
                                    <td><?php echo esc_html($op->default_service_level); ?></td>
                                    <td><?php echo ($op->product_type == "non_hp") ? esc_html($op->default_quantity) : ''; ?></td>
                                    <td>
                                        <?php if(!empty($op->file_url)):
                                            if (strpos($op->file_url, "//") === FALSE) {
                                                $full_url = !empty($op->file_name) ? esc_url_raw(get_site_url() . $op->file_url) : '';
                                            } else {
                                                $full_url = $op->file_url;
                                            }
                                            if (!empty($full_url)):
                                            ?>
                                            <a target="_blank" href="<?php echo  $full_url;?>" class="dashicons-before dashicons-download btn btn-sm btn-outline-primary"><?php echo wp_kses(_translate_helloprint("Download", "helloprint"), true);?></a>
                                        <?php 
                                            endif;
                                            endif;?>
                                        <a href="<?php menu_page_url('edit-order-preset'); ?>&id=<?php echo intval($op->id) ?>" class="dashicons-before dashicons-edit btn btn-sm btn-outline-primary"></a>
                                        <a onclick="return confirm('Are you sure, you want to delete ?');" href="<?php menu_page_url('delete-helloprint-order-preset'); ?>&id=<?php echo intval($op->id) ?>" class="dashicons-before dashicons-trash btn btn-sm btn-outline-danger"></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    
                    <div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">
                        <span class="displaying-num"><?php echo esc_attr($totals);?> <?php echo wp_kses(_translate_helloprint('items', "helloprint"), true) ?></span>
                        <?php 
                        if ( $page_links ) {
                            echo  wp_kses($page_links, true);
                        }
                        ?>

                    </div>
                </div>


            </div>
        </div>

    </div>

</div>

