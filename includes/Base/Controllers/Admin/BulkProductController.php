<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Exception;
use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\HelloPrintApiService;
use HelloPrint\Inc\Services\ProductPriceService;

class BulkProductController extends BaseController
{
    public function register()
    {

        // action to bulk import 
        add_action('wp_ajax_bulk_import_helloprint_products', array($this, 'bulk_import_helloprint_products'));
        add_action('wp_ajax_wphp_check_remaining_imports', array($this, 'check_remaining_imports'));

        //action to async import
        add_action('wp_async_import_helloprint_products', array($this, 'async_import_helloprint_products'), 10, 5);
    }

    public function helloprint_bulk_product_lists()
    {
        try {
            // for datatables
            wp_enqueue_style('wphp-admin-jquery-ui', $this->plugin_url . 'assets/admin/css/plugins/jquery.ui.css');
            wp_enqueue_style('wphp-admin-datatable-ui', $this->plugin_url . 'assets/admin/css/plugins/dataTables.min.css');
            wp_enqueue_script('wphp-admin-dataTables', $this->plugin_url . 'assets/admin/js/plugins/dataTable.min.js');

            // for modal
            wp_enqueue_script('wphp-admin-bootstrap', $this->plugin_url . 'assets/admin/js/plugins/bootstrap.bundle.min.js');
            wp_enqueue_style('wphp-admin-bootstrap', $this->plugin_url . 'assets/admin/css/plugins/bootstrap.min.css');

            // scripts for bulk product update
            wp_enqueue_script('wphp-admin-bulk-product-add', $this->plugin_url . 'assets/admin/js/bulk-products.js');
            $data = [];
            $apiService = new HelloPrintApiService();
            $data['allProducts'] = $apiService->getAllProducts();
            $data['allCategories'] = array_unique(array_keys($data['allProducts']));
            $data['woocommerceCategories'] = $this->get_woocommerce_categories_tree(0, '');
            $data['existingHpProducts'] = all_existing_wphp_product_ids();
            load_template("$this->plugin_path/templates/admin/bulk-products.php", true, $data);
        } catch (Exception $e) {
        }
    }

    private function get_woocommerce_categories_tree($catId, $depth)
    {
        $depth .= '-';
        $output = '';
        $args = 'orderby=name&order=ASC&hierarchical=1&taxonomy=product_cat&hide_empty=0&parent=';
        $categories = get_categories($args . $catId);
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $showdepth = substr($depth, 1);
                $spacing = '';
                for ($i = 0; $i < strlen($showdepth); $i++) {
                    $spacing .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                $output .=  '<option value="' . esc_attr($category->cat_ID) . '" >' . $spacing . $showdepth . esc_html($category->cat_name) . '</option>';
                $output .=  $this->get_woocommerce_categories_tree($category->cat_ID, $depth);
            }
        }
        return $output;
    }

    public function bulk_import_helloprint_products()
    {

        try {
            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === TRUE) {
                wp_send_json_error(['success' => false, 'message' => esc_html(_translate_helloprint('DISABLE_WP_CRON config setting set to true, please change to false otherwise some features may not work as expected.'))]);
            }
            check_ajax_referer('wphp-plugin-nonce');
            // $products = array_map( 'sanitize_text_field', wp_unslash( $_POST['products'] ) );
            $category = sanitize_text_field(wp_unslash($_POST['category']));
            $marginOption = sanitize_text_field(wp_unslash($_POST['marginOption'])) == 'true' ? 1 : 0;
            if ($marginOption) {
                $margin = (new ProductPriceService())->getGlobalMargin();
            } else {
                $margin = isset($_POST['margin']) ? sanitize_text_field(wp_unslash($_POST['margin'])) : false;
            }
            if (empty( $_POST['products'] ) || empty($category)) {
                return wp_send_json_error(['success' => false, 'message' => wp_kses(_translate_helloprint("Product & category cannot be empty", 'helloprint'), true)]);
            }
            $count = count($_POST['products']);
            $cat = get_term_by('id', $category, 'product_cat', 'ARRAY_A');
            if (isset($cat['slug'])) {
                $category_link = admin_url("edit.php?product_cat=" . $cat['slug'] . "&post_type=product");
            } else {
                $category_link = admin_url("edit.php?post_type=product");
            }
            $counter = 0;
            $last_product_index = $count - 1;
            $msg = '';
            global $table_prefix, $wpdb;
            $bulkImportQueueTable = $table_prefix . 'helloprint_bulk_import_queues';
            $date = date("Y-m-d");
            $product_slugs = array_column($_POST['products'], 'slug');
            $existing_hp_slugs = $this->_get_existing_hp_slugs($product_slugs, $category);
            $job_ids = [];
            $total_duplicates = count($existing_hp_slugs);
            if (count($_POST['products']) > $total_duplicates) {
                foreach (wp_unslash($_POST['products']) as $product) {
                    if ($counter++ == $last_product_index)
                        $msg = ' <a target="_blank" href="' . esc_url($category_link) . '">' . wp_kses(_translate_helloprint("Click Here", "helloprint"), true) . '</a>';
                    //schedule async bulk imports 
                    $product_slug = sanitize_text_field(wp_unslash($product['slug']));
                    if (!in_array($product_slug, $existing_hp_slugs)) {
                        $existingcount = $wpdb->get_var("SELECT id from $bulkImportQueueTable WHERE category='$category' and product_id='$product_slug' and date='$date' and imported='0' ");
                        wp_schedule_single_event(strtotime('+2 seconds'), 'wp_async_import_helloprint_products', [$product, $category, $margin, $marginOption, $msg]);
                        if (empty($existingcount)) {
                            $return_id = $wpdb->query("INSERT  INTO $bulkImportQueueTable(category, product_id, date) VALUES 
                            ($category, '$product_slug', '$date')");
                            $job_ids[] = $wpdb->insert_id;
                        } else {
                            $job_ids[] = $existingcount;
                            //wp_next_scheduled('wp_async_import_helloprint_products', [$product, $category, $margin, $marginOption, $msg]);
                        }
                    }
                }
                $difference = count($_POST['products']) - count($existing_hp_slugs);
                $import_product_label = ($difference > 1) ? "products" : "product";
                $need_to_import_message = "<b>" . $difference . " " . wp_kses(_translate_helloprint("new $import_product_label", "helloprint"), true) . "</b> ". wp_kses(_translate_helloprint("to be imported", "helloprint"), false);
                $duplicate_product_label = ($total_duplicates > 1) ? "products" : "product";
                $duplicate_product_message = ($total_duplicates > 0) ? "<b>" . $total_duplicates . " " . wp_kses(_translate_helloprint("Duplicate $duplicate_product_label", "helloprint"), true) . "</b> ". wp_kses(_translate_helloprint("found and will not be imported", "helloprint"), true) : "";
            } else {
                $difference = 0;
                $need_to_import_message = "<b>" . wp_kses(_translate_helloprint("No new product", "helloprint"), true) . "</b> " .  wp_kses(_translate_helloprint("to be imported", "helloprint"), true);
                $duplicate_product_label = (count($_POST['products']) > 1) ? "products" : "product";
                $duplicate_product_message = "<b>" . count($_POST['products']) . " " . wp_kses(_translate_helloprint("Duplicate $duplicate_product_label", "helloprint"), true) . "</b> " .  wp_kses(_translate_helloprint("found and will not be imported", "helloprint"), true);
            }
            $admin_email = get_option('admin_email');
            $category_link_html = wp_kses(_translate_helloprint('To View the Category', "helloprint"), true) . ' <a target="_blank" href="' . esc_url($category_link) . '">' . wp_kses(_translate_helloprint("Click Here", "helloprint"), true) . '</a>';
            $close_message = wp_kses(_translate_helloprint("Do not close the page to ensure all products are uploaded successfully.", "helloprint"), false);
            $email_confirmation_message = wp_kses(_translate_helloprint("A confirmation email will be sent to " . $admin_email . " once all products are imported.", "helloprint"), false);

            $send_message = ($difference > 0) ? $difference . ' ' . wp_kses(_translate_helloprint('Bulk import has been submitted and will run shortly.Do not close the page to successfully complete the upload. A confirmation email will be sent to ' . $admin_email . ' once all products are imported.', 'helloprint'), false) : "";
            wp_send_json_success(['success' => true, "email_confirm" => $email_confirmation_message, "jobs" => json_encode($job_ids), "difference" => $difference, "import_message" => $need_to_import_message, "duplicate_message" => $duplicate_product_message, 'category_link' => $category_link, 'message' => $send_message, 'category_link_html' => $category_link_html, 'category_link' => $category_link, 'close_message' => $close_message]);
        } catch (Exception $e) {
            return wp_send_json_error(['success' => false, 'message' => $e->getMessage(), 'code' => $e->getCode()]);
        }
    }

    public function async_import_helloprint_products($product, $category, $margin, $marginOption, $msg)
    {
        global $table_prefix, $wpdb;
        $bulkImportQueueTable = $table_prefix . 'helloprint_bulk_import_queues';
        $product_slug = sanitize_text_field(wp_unslash($product['slug']));
        $date = date("Y-m-d");
        $attempts = $wpdb->get_var("SELECT attempts from $bulkImportQueueTable WHERE category='$category' and product_id='$product_slug' and date='$date' and imported='0'");
        if ($attempts > 2) {
            return false;
        }
        $wpdb->query("UPDATE $bulkImportQueueTable SET `attempts` = (`attempts` + 1) WHERE category='$category' and product_id='$product_slug' and date='$date' and imported='0'");
        $logger = wc_get_logger();
        $context = array('source' => 'wphp-bulkimport');
        $logger->notice('starting bulk import products BulkProductController :: line no 160', $context);
        $this->import_single_product($product, $category, $margin, $marginOption);
        if (strlen($msg) > 0)
            $this->send_mails_on_import_completion($msg);

        $logger->notice('starting bulk import products BulkProductController :: line no 167', $context);
    }

    private function send_mails_on_import_completion($category_link)
    {
        $body = sprintf(
            'Bulk import is completed successfully. To view please  %s',
            $category_link
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail(get_option('admin_email'), 'Bulk Import Completed!', $body, $headers);
        $logger = wc_get_logger();
        $context = array('source' => 'wphp-bulkimport');
        $logger->notice('send emails on all bulk import products competed BulkProductController :: line no 180', $context);
        
    }


    private function import_single_product($product, $category, $margin, $marginOption)
    {
        global $table_prefix, $wpdb;
        $logger = wc_get_logger();
        $context = array('source' => 'wphp-bulkimport');
        $bulkImportQueueTable = $table_prefix . 'helloprint_bulk_import_queues';
        // Get an array of WC_Product Objects
        $products = wc_get_products(array(
            'limit'         => -1,
            'status'        => 'publish',
            'meta_key'      => 'helloprint_external_product_id',
            'meta_compare'  => '=',
            'meta_value' => $product['slug'],
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => [$category],
                    'operator' => 'IN',
                )
            ),
        ));
        $product_slug = sanitize_text_field(wp_unslash($product['slug']));
        if (!empty($products[0])) {
            $image_id = $products[0]->get_image_id();
            if ($image_id > 0) {
                $wpdb->query("UPDATE $bulkImportQueueTable SET `imported` = 1 WHERE category='$category' and product_id='$product_slug' ");
                return false;
            } else {
                wp_delete_post($products[0]->get_id());
            }
        }
        
        $logger->notice('importing single product ' . $product['name'] . ' BulkProductController :: line no  220', $context);
        ini_set('max_execution_time', 120);

        $prod = new \WC_Product();
        $prod->product_type = 'helloprint_product';
        $prod->is_in_stock = true;
        $prod->price = 100;
        $prod->regular_price = 120;
        $prod->set_name(sanitize_text_field(wp_unslash($product['name'])));
        $prod->set_status('publish');
        $prod->set_catalog_visibility('visible');
        $prod->set_category_ids(array($category));
        $product_id = $prod->save();

        update_post_meta($product_id, 'helloprint_external_product_id', sanitize_text_field(wp_unslash($product['slug'])));
        update_post_meta($product_id, 'helloprint_product_margin', sanitize_text_field(wp_unslash($margin)));
        update_post_meta($product_id, 'helloprint_product_margin_option', sanitize_text_field(wp_unslash($marginOption)));
        $logger->notice("importing single product update margin options an hp product id of " . $product['name'] . " BulkProductController :: line no 238", $context);
        
        $productDetails = (new HelloPrintApiService())->getProductDetailsForLoad(sanitize_text_field(wp_unslash($product['slug'])));

        $logger->notice("fetched the hp product details from api call of " . $product['name'] . " BulkProductController :: line no 243", $context);
        $title = !empty($productDetails['product_name']) ? $productDetails['product_name'] : $product['name'];

        // save title & description if not empty
        if (!empty($title) || !empty($productDetails['description'])) {
            $postProduct = array(
                'ID' =>  $product_id,
                'post_title' => $title,
                'post_content'  => !empty($productDetails['description']) ? $productDetails['description'] : ""
            );

            wp_update_post($postProduct);
            $logger->notice("updated single product title and description from api calls  of " . $title . " BulkProductController :: line no 256", $context);
        }
        // save images if not empty

        $date = date("Y-m-d");
        $product_image = !empty($productDetails['preview_image']) ? $productDetails['preview_image'] : "Product Image Not Found From HP API";
        $logger->notice("Image of product from HP API of " . $product['name'] . " :: " . $product_image . " BulkProductController :: line no 263", $context);
        if (!empty($productDetails['preview_image'])) {
            $logger->notice("start to save image from hp to local  of " . $product['name'] . " product id " . $product_id . " BulkProductController :: line no 266", $context);
            $this->save_image_for_import($productDetails['preview_image'], $product_id);

            $wpdb->query("UPDATE $bulkImportQueueTable SET `imported` = 1 WHERE category='$category' and product_id='$product_slug' and date='$date'");
            $logger->notice("saved image from hp to local  of " . $product['name'] . " product id " . $product_id . " BulkProductController :: line no 271", $context);
        } else {
            $wpdb->query("UPDATE $bulkImportQueueTable SET `imported` = 1 WHERE category='$category' and product_id='$product_slug' and date='$date'");
            $logger->error("dont saved image from hp because empty image from hp api   of " . $product['name'] . " product id " . $product_id . " BulkProductController :: line no 275", $context);
        }

        $products = wc_get_products(array(
            'limit'         => -1,
            'status'        => 'publish',
            'meta_key'      => 'helloprint_external_product_id',
            'meta_compare'  => '=',
            'meta_value' => $product_slug,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => [$category],
                    'operator' => 'IN',
                )
            ),
        ));

        if (count($products) > 1) {
            wp_delete_post($product_id);
        }
    }

    private function save_image_for_import($image_url = '', $post_id = null)
    {
        $imgNameArr = explode("/", $image_url);
        $image_name = $imgNameArr[count($imgNameArr) - 1];
        $upload_dir = wp_upload_dir(); // Set upload folder
        if (str_starts_with($image_url, '//')) {
            $image_url = str_replace("//", 'https://', $image_url);
        }
        $logger = wc_get_logger();
        $context = array('source' => 'wphp-bulkimport');
        $image_data = helloprint_get_data_from_url($image_url); // Get image data
        
        $logger->notice("get image data of product id " . $post_id . " BulkProductController :: line no 312", $context);
        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
        $filename = basename($unique_file_name); // Create image file name

        // Check folder permission and define file location
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        // Create the image  file on the server
        file_put_contents($file, $image_data);
        $logger->notice("put image data of product id " . $post_id . " BulkProductController :: line no 325", $context);
        // Check image file type
        $wp_filetype = wp_check_filetype($filename, null);

        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Create the attachment
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        $logger->notice("insert attachment of product id " . $post_id . " BulkProductController :: line no 340", $context);
        // Include image.php
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        $logger->notice("generate attachment metadata of product id " . $post_id . " BulkProductController :: line no 347", $context);
        // Assign metadata to attachment
        wp_update_attachment_metadata($attach_id, $attach_data);
        $logger->notice("update attachment metadata of product id " . $post_id . " BulkProductController :: line no 351", $context);
        
        // And finally assign featured image to post
        set_post_thumbnail($post_id, $attach_id);
        $logger->notice("set post thumbnail of product id " . $post_id . " BulkProductController :: line no 356", $context);
        
    }

    private function _get_existing_hp_slugs($product_slugs, $category)
    {
        $existing_product_ids = wc_get_products(array(
            'return' => 'ids',
            'limit'         => -1,
            'status'        => 'publish',
            'meta_key'      => 'helloprint_external_product_id',
            'meta_compare'  => 'IN',
            'meta_value' => $product_slugs,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => [$category],
                    'operator' => 'IN',
                )
            ),
        ));
        if (empty($existing_product_ids)) {
            return [];
        }
        global $table_prefix, $wpdb;
        $meta_key_table = $table_prefix . 'postmeta';
        $id_strings = implode(",", $existing_product_ids);
        $existing_hp_slugs = $wpdb->get_results("SELECT meta_value from $meta_key_table WHERE meta_key='helloprint_external_product_id' and post_id IN (" . $id_strings . ")", ARRAY_A);
        if (empty($existing_hp_slugs)) return [];
        $existing_hp_slugs = array_unique(array_column($existing_hp_slugs, 'meta_value'));
        return $existing_hp_slugs;
    }

    public function check_remaining_imports()
    {
        try {
            check_ajax_referer('wphp-plugin-nonce');

            global $table_prefix, $wpdb;
            $bulk_import_queue_table = $table_prefix . 'helloprint_bulk_import_queues';
            $hp_job_ids = sanitize_text_field(wp_unslash($_POST['job_ids']));
            $arr_jobs = json_decode($hp_job_ids, true);
            $arr_jobs = array_map('intval', $arr_jobs);
            $job_ids = implode(",", $arr_jobs);
            $completed_jobs = $wpdb->get_results("SELECT * from $bulk_import_queue_table WHERE imported='1' and id IN (" . $job_ids . ")", ARRAY_A);
            $failed_jobs = $wpdb->get_results("SELECT product_id from $bulk_import_queue_table WHERE imported='0' and attempts > 2 and id IN (" . $job_ids . ")", ARRAY_A);
            $import_product_label = (count($arr_jobs) > 1) ? "products" : "product";
            $import_has_have_label = (count($arr_jobs) > 1) ? "imported." : "imported.";
            $success_message = "<b>" . wp_kses(_translate_helloprint(count($completed_jobs) . "/" . count($arr_jobs) . " " . $import_product_label, "helloprint"), true) . "</b> " . wp_kses(_translate_helloprint($import_has_have_label, "helloprint"), true);
            $failure_product_label = (count($arr_jobs) > 1) ? "products" : "product";
            $failure_has_have_label = (count($arr_jobs) > 1) ? "failed." : "failed.";
            $failure_message = (count($failed_jobs) > 0) ? "<b>" . wp_kses(_translate_helloprint(count($failed_jobs) . "/" . count($arr_jobs)  . " " . $failure_product_label, "helloprint"), true) . "</b> " . wp_kses(_translate_helloprint($failure_has_have_label, "helloprint"), false) : "";
            $percentage = (count($completed_jobs) * 100) / count($arr_jobs);
            $percentage = round($percentage);
            wp_send_json_success([
                'success' => true, "failure_message" => $failure_message, "success_message" => $success_message,
                "failed_jobs" => $failed_jobs, "total_jobs" => count($arr_jobs), "total_success" => count($completed_jobs),
                "total_failed" => count($failed_jobs), "percentage" => $percentage
            ]);
        } catch (Exception $e) {
            return wp_send_json_error(['success' => false, 'message' => $e->getMessage(), 'code' => $e->getCode()]);
        }
    }
}
