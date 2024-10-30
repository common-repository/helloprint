<?php
/**
 * PHP view.
 *
 */
?>
<div class="wrap wphp-bulk-wrap">
    <h1 class="wp-heading-inline"><?php echo wp_kses(_translate_helloprint('Bulk Product Import', "helloprint"), true); ?></h1>

    <br />
    <br />
    <div class="row">
        <?php 
        if (defined('DISABLE_WP_CRON') && (DISABLE_WP_CRON == true)):
            ?>
            <div class="notice notice-error">
                <?php echo esc_html(_translate_helloprint("DISABLE_WP_CRON config setting is set to true, please change to false in wp-config.php otherwise some features may not work as expected.", "helloprint"));?>
            </div>
        <?php else: ?>
        <div class="col-md-6" style="float:left;">
            <span id="hp_total_selected">0</span> <?php echo wp_kses(_translate_helloprint('Product(s) Selected', "helloprint"), true) ?>
            <button id="wphp-product-add-to-cat" class=" page-title-action"> <?php echo wp_kses(_translate_helloprint('Add to Category', "helloprint"), true); ?></button>
        </div>
        <?php endif;?>
        <div class="col-md-6">
            <p class="search-box">
                <label><?php echo wp_kses(_translate_helloprint('Filter Category', "helloprint"), true) ?></label>
                <select id="wphp-filter-category" class="select wphp-select2">
                    <option value=""><?php echo wp_kses(_translate_helloprint('Select Category', "helloprint"), false); ?></option>
                    <?php foreach ($args['allCategories'] as $catg) : ?>
                        <option value="<?php echo esc_attr($catg); ?>"><?php echo esc_attr($catg); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

        </div>

    </div>
    <br /><br />
    <div class="row">
        <div class="col-md-12 overflow-auto">
            <div class="pt-3">

                <?php 
                
                if (isset($_GET['success'], $_GET['hp_nonce']) && wp_verify_nonce( sanitize_key( $_GET['hp_nonce'] ), 'bulk_import' )) : ?>
                    <div class="alert alert-success notice notice-success" role="alert" id="alertSuccess">
                        <p>
                            <?php echo wp_kses(_translate_helloprint("Bulk Import  " . sanitize_text_field(wp_unslash($_GET['success'])) . " successfully", "helloprint"), true) ?> </p>
                        </div>
                    <?php endif; ?>
                    <div class="alert alert-danger" role="alert" id="alertDanger" style="display: none;"></div>

                    <div class="spinner-border text-success" role="status" id="loader" style="display: none;">
                        <span class="sr-only"><?php echo wp_kses(_translate_helloprint('Loading...', "helloprint"), true) ?></span>
                    </div>

                    <table class="wp-list-table widefat fixed striped table-view-list pages" id="tbl-bulk-product-list">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="all-wphp-products" /></th>
                                <th><?php echo wp_kses(_translate_helloprint('KEY', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Status', "helloprint"), true) ?></th>
                                <th><?php echo wp_kses(_translate_helloprint('Name', "helloprint"), true) ?></th>
                                <th class="wphp-product-category"><?php echo wp_kses(_translate_helloprint('Category', "helloprint"), true) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($args['allProducts'] as $cat_key => $prod_cat) :
                                foreach ($prod_cat as $key => $prod) : ?>
                                    <tr class="<?php echo ($i % 2 === 0) ? 'even' : 'odd';
                                    $i++; ?>">
                                    <td>
                                        <input data-name="<?php echo esc_attr($prod); ?>" type="checkbox" value="<?php echo esc_attr($key); ?>" class="select-wphp-product" />
                                    </td>
                                    <td>

                                        <?php echo esc_html($key); ?>
                                        <?php
                                        if (in_array($key, $args['existingHpProducts'], true)) :
                                            ?>
                                            <a style="cursor: pointer;" title="<?php echo esc_attr(_translate_helloprint('This product has been already added', "helloprint")) ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="green" class="bi bi-check" viewBox="0 0 16 16">
                                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo wp_kses(_translate_helloprint('available', "helloprint"), true) ?>

                                    </td>
                                    <td>

                                        <?php
                                        echo esc_html($prod); ?></td>
                                        <td class="wphp-product-category"><?php echo esc_html($cat_key); ?></td>
                                    </tr>

                                <?php endforeach;

                            endforeach;
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <div class="modal fade" id="wphp-category-modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo wp_kses(_translate_helloprint('Add Products', "helloprint"), true) ?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input value="<?php echo esc_attr(_translate_helloprint('Please select at least one product to import', "helloprint")) ?>" type="hidden" id="hidden_helloprint_product_empty_messge_div" />
                    <input value="<?php echo esc_attr(_translate_helloprint('Please select at least one category', "helloprint")) ?>" type="hidden" id="hidden_helloprint_cat_empty_messge_div" />
                    <input value="<?php echo esc_attr(_translate_helloprint('Margin must be between 0 to 99', "helloprint")) ?>" type="hidden" id="hidden_helloprint_invalid_margin_div" />
                    <input type="hidden" id="wphp-bulk-import-job-ids" />
                    <input type="hidden" id="wphp-bulk-new-import-counts" value="0" />
                    <input type="hidden" id="wphp-bulk-remaining-import-counts" value="0" />
                    <div id="wphp-bulk-import-status-message"></div>
                    <div id="wphp-bulk-import-confirmation-email-message"></div>
                    <div id="wphp-bulk-import-donot-close-message"></div>
                    <div id="wphp-bulk-import-category-details-link"></div>
                    <div id="wphp-bulk-import-modal-fields">
                        <p class="wphp-modal-label"><?php echo wp_kses(_translate_helloprint('Add', "helloprint"), true) ?> <span id="helloprint_product_modal_count"></span> <?php echo wp_kses(_translate_helloprint('Product(s) to', "helloprint"), true) ?></p>
                        <p>
                            <select id="wphp-wocommerce-category" class="select wphp-select2">
                                <option value=""><?php echo wp_kses(_translate_helloprint('Select Woocommerce Category', "helloprint"), false) ?></option>
                                <?php echo $args['woocommerceCategories']; ?>
                            </select>
                        </p>
                        <br />
                        <div class="form-field helloprint_product_margin_option_field">
                            <p class="wphp-modal-label"><?php echo wp_kses(_translate_helloprint('Product Margin Option', "helloprint"), true) ?> </p>
                            <ul class="wc-radios">
                                <li>
                                    <label>
                                        <input type="radio" class="select short" name="helloprint_product_margin_option" value="1" checked="checked">
                                        <?php echo wp_kses(_translate_helloprint('Same as Global Setting', "helloprint"), true) ?>
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="radio" class="select short" name="helloprint_product_margin_option" value="0">
                                        <?php echo wp_kses(_translate_helloprint('Individual Product Margin', "helloprint"), true) ?>
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <p class="form-field helloprint_modal_product_margin_field ">
                            <label for="helloprint_modal_product_margin"><?php echo wp_kses(_translate_helloprint('Margin (%)', "helloprint"), true) ?></label>
                            <input type="number" class="short" name="helloprint_product_margin" id="helloprint_modal_product_margin" value="" placeholder="20" min="0" max="99">

                        </p>
                    </div>

                    <div style="display:none;" id="helloprint_bulk-progree-bar" class="wphp-progress">
                        <div id="helloprint_bulk_import-progress-bar" class="wphp-bar">0%</div>
                    </div>
                    <br/>
                    <div id="helloprint_bulk-new-import" ></div>
                    <div id="helloprint_bulk-duplicate-products" ></div>
                    <div id="helloprint_bulk-success-products-message" ></div>
                    <div id="helloprint_bulk-failed-products" ></div>
                </div>
                <div class="modal-footer">

                    <button onclick="submitHelloPrintBulkImport()" id="wphp-modal-submit-bulk-import-product" type="button" class="btn btn-success"><?php echo wp_kses(_translate_helloprint('Submit', "helloprint"), true) ?><span class="wphp-spinner"></span></button>
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal" aria-label="Close"><?php echo wp_kses(_translate_helloprint('Close', "helloprint"), true) ?></button>
                </div>
        </div>
    </div>
</div>
