<?php

/**
 * PHP view.
 *
 */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_attr(_translate_helloprint('Helloprint Pitchprint Links', "helloprint")); ?></h1>
    <a href="<?php menu_page_url('new-hp-pitch-print'); ?>" class=" page-title-action"> <?php echo esc_attr(_translate_helloprint('Add New Link', "helloprint")); ?></a>

    <form id="posts-filter" method="get">

        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php echo esc_attr(_translate_helloprint('Search Links', "helloprint")) ?>:</label>
            <input type="hidden" name="page" value="<?php echo sanitize_text_field(wp_unslash($_GET['page'])); ?>" />
            <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($s); ?>">
            <input type="submit" id="search-submit" class="button" value="<?php echo esc_attr(_translate_helloprint('Search Pitchprint', "helloprint")) ?>">
        </p>

    </form>
    <br /><br />
    <div class="row">
        <div class="col-md-12">
            <div class="pt-3">
                <div class="alert alert-danger" role="alert" id="alertDanger" style="display: none;"></div>

                <div class="spinner-border text-success" role="status" id="loader" style="display: none;">
                    <span class="sr-only"><?php echo esc_attr(_translate_helloprint('Loading...', "helloprint")) ?></span>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list pages" id="tbl-token-list">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(_translate_helloprint('Name', "helloprint")) ?></th>
                            <th><?php echo esc_html(_translate_helloprint('Pitchprint Design ID', "helloprint")) ?></th>
                            <th><?php echo esc_html(_translate_helloprint('HP Variant Key', "helloprint")) ?></th>
                            <th><?php echo esc_html(_translate_helloprint('Action', "helloprint")) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pitchprints as $key => $pr) : ?>
                            <tr class="<?php echo (0 === $key % 2) ? 'even' : 'odd'; ?>">
                                <td><?php echo esc_html($pr->name); ?></td>
                                <td><?php echo esc_html($pr->pitchprint_design_id); ?></td>
                                <td><?php echo esc_html($pr->hp_variant_key); ?></td>
                                <td>
                                    <a href="<?php menu_page_url('edit-hp-pitch-print'); ?>&id=<?php echo intval($pr->id) ?>" class="dashicons-before dashicons-edit btn btn-sm btn-outline-primary"></a>
                                    <a onclick="return confirm('<?php echo esc_attr(_translate_helloprint('Are you sure, you want to delete ?', 'helloprint')); ?>');" href="<?php menu_page_url('delete-new-hp-pitch-print'); ?>&id=<?php echo intval($pr->id) ?>" class="dashicons-before dashicons-trash btn btn-sm btn-outline-danger"></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>


                <div class="tablenav">
                    <div class="tablenav-pages" style="margin: 1em 0">
                        <span class="displaying-num"><?php echo esc_html($totals); ?> <?php echo esc_html(_translate_helloprint('items', "helloprint")); ?></span>
                        <?php
                        if ($page_links) {
                            echo  wp_kses($page_links, true);
                        }
                        ?>

                    </div>
                </div>

            </div>
        </div>

    </div>

</div>