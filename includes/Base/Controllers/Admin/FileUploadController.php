<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use HelloPrint\Inc\Base\Controllers\BaseController;

class FileUploadController extends BaseController
{

    public function register()
    {
        add_action('woocommerce_after_cart_contents', array($this, 'render_cart_file_upload_template'));
    }

    public function render_cart_file_upload_template()
    {
        $wcCartItems = WC()->cart->get_cart();
        $cartItems = [];
        $allFileUpload = false;
        $global_product_file_upload = esc_attr(get_option('helloprint_product_upload_file'));
        foreach ($wcCartItems as $item) {
            if (!empty($item['data']) && method_exists($item['data'], "get_type") && $item['data']->get_type() == 'helloprint_product') {
                $product_upload_file = get_post_meta($item['data']->get_id(), 'helloprint_product_upload_file', true);
                if (empty($product_upload_file)) {
                    $product_upload_file = $global_product_file_upload;
                }
                $can_upload_file = (empty($product_upload_file) || in_array($product_upload_file, ['show_on_both_pages', 'show_on_cart_only'])) ? true : false;
                if ($can_upload_file == true) {
                    $allFileUpload = true;
                }
                $total_delivery_days = "";
                if (!empty($item["helloprint_product_setup"]["total_delivery_days"])) {
                    $total_delivery_days =  $item["helloprint_product_setup"]["total_delivery_days"];
                }
                $cartItems[] = [
                    // 'itemId' => $item['id'],
                    'key' => $item['key'],
                    'name' => $item['data']->get_name(),
                    'quantity' => $item['helloprint_product_setup']['quantity'],
                    'delivery_option' => $item['helloprint_product_setup']['delivery_option'],
                    'options' => $item['helloprint_product_setup']['options'],
                    'uploaded_files' => $item['helloprint_product_setup']['uploaded_files'],
                    'can_upload_file' => $can_upload_file,
                    "total_delivery_days" => $total_delivery_days
                ];
            }
        }

        if (!$allFileUpload) return false;
        $data['cartItems'] = $cartItems;
        load_template("$this->plugin_path/templates/wp/product-file-upload.php", true, $data);
    }
}
