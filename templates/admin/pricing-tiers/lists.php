<?php
/**
* PHP view.
*
*/
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo wp_kses(_translate_helloprint('Pricing Tiers', "helloprint"), true); ?></h1>
    <a href="<?php menu_page_url('add-hp-pricing-tier'); ?>" class=" page-title-action"> <?php echo wp_kses(_translate_helloprint('Create Pricing Tier', "helloprint"), true); ?></a>

    <form id="posts-filter" method="get">

        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php echo wp_kses(_translate_helloprint('Search Tier', "helloprint"), true) ?>:</label>
            <input type="hidden" name="page" value="<?php echo !empty($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 1;?>" />
            <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($s);?>">
            <input type="submit" id="search-submit" class="button" value="<?php echo wp_kses(_translate_helloprint('Search Tier', "helloprint"), false) ?>">
        </p>

    </form>
    <br/><br/>
    <div class="row">
        <div class="col-md-12">
            <div class="pt-3">
                <?php 
                if (isset($_GET['success'], $_GET['hp_nonce']) && wp_verify_nonce( sanitize_key( $_GET['hp_nonce'] ), 'pricing_tier' )) : ?>
                    <div class="alert alert-success notice notice-success" role="alert" id="alertSuccess">
                        <p>
                            <?php echo wp_kses(_translate_helloprint("Pricing Tier  " . sanitize_text_field(wp_unslash($_GET['success'])) . " successfully", "helloprint"), true) ?> </p>
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
                                <th><?php echo wp_kses(_translate_helloprint('Tyre Type', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Name', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Default Markup/Margin', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Is Scaling', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Action', "helloprint"), true) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tiers as $key => $tr): ?>
                                <tr class="<?php echo (0 === $key%2) ? 'even' : 'odd' ;?>">
                                    <td><?php echo esc_html($tr->id); ?></td>
                                    <td><?php 
                                        if (!empty($tr->tier_type) && $tr->tier_type == "markup") {
                                            echo wp_kses(_translate_helloprint('For Markup', "helloprint"), true);
                                        } else {
                                            echo wp_kses(_translate_helloprint('For Margin', "helloprint"), true);
                                        }
                                    ?></td>
                                    <td><?php echo esc_html($tr->name); ?></td>
                                    <td><?php echo esc_html($tr->default_markup) . "%"; ?></td>
                                    <td><?php echo ($tr->enable_scaling == 1) ? wp_kses(_translate_helloprint('Yes', "helloprint"), true) : wp_kses(_translate_helloprint('No', "helloprint"), true); ?></td>
                                    <td>
                                        <a href="<?php menu_page_url('edit-hp-pricing-tier'); ?>&id=<?php echo intval($tr->id) ?>" class="dashicons-before dashicons-edit btn btn-sm btn-outline-primary"></a>
                                        <a onclick="return confirm('Are you sure, you want to delete ?');" href="<?php menu_page_url('delete-helloprint-pricing-tier'); ?>&id=<?php echo intval($tr->id) ?>" class="dashicons-before dashicons-trash btn btn-sm btn-outline-danger"></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    
                    <div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">
                        <span class="displaying-num"><?php echo esc_attr($totals);?> <?php echo wp_kses(_translate_helloprint('items', "helloprint"), true); ?></span>
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

