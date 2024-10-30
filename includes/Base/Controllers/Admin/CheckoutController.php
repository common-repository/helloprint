<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\HelloPrintApiService;

class CheckoutController extends BaseController
{
    public function register()
    {
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'create_helloprint_order_line_item'), 10, 4);
        add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'hide_helloprint_product_setup_meta_data'), 10, 2);
        //add_action('woocommerce_payment_complete', array($this, 'send_order_to_helloprint'), 10, 1);
        //add_action('woocommerce_thankyou', array($this, 'change_uploaded_files'), 10, 1);
        // add_action('woocommerce_order_status_completed', array($this, 'send_order_to_helloprint'), 10, 1);
        // add_action('woocommerce_review_order_after_payment', array($this, 'send_order_to_helloprint'), 10, 1);

        add_action('woocommerce_order_action_send_to_helloprint', array($this, 'send_order_to_helloprint'), 10, 1);
       if (get_option("helloprint_automatic_send_order", false)) {
            add_action('woocommerce_payment_complete', array($this, 'send_automatic_order_to_helloprint'), 11, 1);
       }
    }

    public function create_helloprint_order_line_item($item, $cart_item_key, $values, $order)
    {
        if (empty($values['helloprint_product_setup'])) {
            return;
        }
        $delivery_option_label_for_order = wp_kses(_translate_helloprint(ucfirst($values['helloprint_product_setup']['delivery_option']), 'helloprint'), false);
        if (!empty($values["helloprint_product_setup"]["total_delivery_days"])) {
            $delivery_option_label_for_order .=  ' - ' . $values["helloprint_product_setup"]["total_delivery_days"] . ' ' . wp_kses(_translate_helloprint("Day(s)", 'helloprint'), true);
        }
        $item->add_meta_data('helloprint_product_setup', json_encode($values['helloprint_product_setup']));
        $item->add_meta_data(wp_kses(_translate_helloprint('Quantity', 'helloprint'), false), ucfirst($values['helloprint_product_setup']['quantity']));
        $item->add_meta_data(wp_kses(_translate_helloprint('Delivery option', 'helloprint'), false), $delivery_option_label_for_order);
        foreach ($values['helloprint_product_setup']['options'] as $key => $option) {
            if (ucfirst($key) == 'Appreal_size') {
                if (!empty($option)) {
                    $appSize = $option; //unserialize($option);
                    if (!empty($appSize)) {
                        foreach ($appSize as $kkey => $size) {
                            if (is_array($size)) {
                                $interArr = [];
                                foreach ($size as $kk => $sz) {
                                    $interArr[] = $kk . ":" . $sz;
                                }
                                $vall = "[";
                                $vall .= implode("; ", $interArr);
                                $vall .= "]";
                                $item->add_meta_data($kkey, $vall);
                            } else {
                                $item->add_meta_data($kkey, $size);
                            }
                        }
                    }
                }
            } else {
                $valueName = wc_clean($option);
                $item->add_meta_data(ucfirst($key), $valueName);
            }
        }



        if (isset($values['helloprint_product_setup']['want_graphic_design'])) {
            $graphic_design = $values['helloprint_product_setup']['want_graphic_design'];
            $is_enable = ($graphic_design) ? wp_kses(_translate_helloprint("Yes please", 'helloprint'), false) : wp_kses(_translate_helloprint("No thank you, i'll supply print ready artwork.", 'helloprint'), false);
            $item->add_meta_data(wp_kses(_translate_helloprint("Want graphic design", "helloprint"), false), $is_enable);
            if ($graphic_design) {
                $gprice = isset($values['helloprint_product_setup']['graphic_design_price']) ? $values['helloprint_product_setup']['graphic_design_price'] : 0;
                $product = wc_get_product($values['product_id']);
                if (!empty($product) && wc_tax_enabled() && $product->get_tax_status() == 'taxable') {
                    $tax = new \WC_Tax();
                    $taxes = $tax->get_rates($product->get_tax_class());
                    $rates = array_shift($taxes);
                    $taxRate = isset($rates['rate']) ? $rates['rate'] : 0;
                    $gprice = $gprice * (1 + ($taxRate / 100));
                }

                $gprice = wc_price($gprice);
                $item->add_meta_data(wp_kses(_translate_helloprint("Graphic design price", "helloprint"), false), $gprice);
            }
        }

        if (isset($values['helloprint_product_setup']['destination_country']) && !empty($values['helloprint_product_setup']['destination_country'])) {
            $item->add_meta_data(wp_kses(_translate_helloprint("Destination Country", "helloprint"), false), $values['helloprint_product_setup']['destination_country']);
        }
        $uploaded_files = (!empty($values['helloprint_product_setup']['uploaded_files']) ? $values['helloprint_product_setup']['uploaded_files'] : '');
        $item->add_meta_data(wp_kses(_translate_helloprint('Uploaded Files', 'helloprint'), false), $uploaded_files);
    }

    public function hide_helloprint_product_setup_meta_data($formatted_meta, $item)
    {
        if (is_array($formatted_meta)) {
            foreach ($formatted_meta as $key => $item) {
                if (isset($item->key) && $item->key == 'helloprint_product_setup') {
                    unset($formatted_meta[$key]);
                }
                if (isset($item->key) && $item->key == 'helloprint_product_order_status') {
                    unset($formatted_meta[$key]);
                }
                if (isset($item->key) && $item->key == 'helloprint_preset_prefer_files') {
                    unset($formatted_meta[$key]);
                }

                if (isset($formatted_meta[$key]->key) && empty($formatted_meta[$key]->display_key) && !empty($formatted_meta[$key]->key)) {
                    $formatted_meta[$key]->display_key = $formatted_meta[$key]->key;
                }
            }
        }
        return $formatted_meta;
    }
    public function send_order_to_helloprint($order_id)
    {
        $logger = wc_get_logger();
        $logger->info("Place order: started ");
        if (!is_numeric($order_id)) {
            $orderDetails = $order_id;
            if (!is_array($orderDetails)) {
                $orderDetails = json_decode($orderDetails, true);
                if (!empty($orderDetails['id'])) {
                    $order_id = $orderDetails['id'];
                }
            }
        }

        if (!$order_id) {
            $logger->info( 'Place order: Invalid order id' );
            return;
        }
        $helloprint_order = false;
        $order = wc_get_order($order_id);
        global $wpdb;
        $preset_tableName = $wpdb->prefix . 'helloprint_order_presets';
        $line_item_tableName = $wpdb->prefix . 'helloprint_order_line_item_presets';
        foreach ($order->get_items() as $item) {
            $itemId = $item->get_id();
            $product = wc_get_product($item['product_id']);
            if ($product !== false) {
                if ($product->get_type() == 'helloprint_product') {
                    $helloprint_order = true;
                    break;
                } else {
                    $product_variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';

                    // Check if product has variation.
                    if ($product_variation_id) { 
                        $product = wc_get_product($product_variation_id);
                    }
                    $sku = $product->get_sku();
                    $lineItemPreset = $wpdb->get_results("SELECT $line_item_tableName.*, $preset_tableName.helloprint_variant_key, $preset_tableName.file_url  from $line_item_tableName INNER JOIN $preset_tableName ON $line_item_tableName.preset_id = $preset_tableName.id where $line_item_tableName.line_item_id = '$itemId' and ($preset_tableName.helloprint_item_sku = '$sku' OR $preset_tableName.helloprint_item_sku = '')");
                    if (empty($lineItemPreset[0]) && !empty($sku)) {
                        $lineItemPreset = $wpdb->get_results("SELECT $preset_tableName.*  from $preset_tableName where helloprint_item_sku = '$sku'");
                    }
                    if (!empty($lineItemPreset[0])) {
                        $helloprint_order = true;
                        break;
                    }
                }
            }
        }
        if ($helloprint_order == true) {
            $order_status = $order->get_meta('helloprint_order_status', true);
            $helloPrintService = new HelloPrintApiService();
            if (isset($order_status['order_id']) && !empty($order_status['order_id'])) {
                $helloprint_order_details = $helloPrintService->getOrderDetails($order_status['request_id']);

                if (!empty($helloprint_order_details['data']['orderItems'])) {
                    $isArtwork = true;
                    foreach ($helloprint_order_details['data']['orderItems'] as $oItem) {
                        if ($oItem['itemStatus'] != 'ARTWORK_RECEIVED' && $oItem['itemStatus'] != 'ARTWORK_REQUIRED') {
                            $isArtwork = false;
                        }
                    }
                    if ($helloprint_order_details['data']['orderStatus'] != 'CANCELLED' && $isArtwork == false) {
                        $logger->info( 'Place order: No artwork file' );
                        return true;
                    }
                }

                if ($helloprint_order_details['data']['orderStatus'] != 'CANCELLED') {
                    $cancelResponse = $helloPrintService->cancelOrder($order_status['order_id']);
                }
            }
            $helloprint_order = $helloPrintService->createOrder($order);
            $res = $helloPrintService->getResponseToJson($helloprint_order);

            if (!empty($res['requestId']) && isset($res['success']) && in_array($res['success'], [1, true])) {
                $logger->info( 'Place order: Order status updated to processing' );
                update_post_meta($order_id, 'helloprint_order_status', [
                    'request_id' => sanitize_text_field(wp_unslash($res['requestId'])),
                    'requestId' => sanitize_text_field(wp_unslash($res['requestId'])),
                    'status' => "PROCESSING"
                ]);
                $logger->info( 'Place order: successfully completed');
            } else {
                if (isset($helloprint_order['status']) && $helloprint_order['status'] == 'VALIDATION_ERROR') {
                    add_helloprint_flash_notice(wp_kses(_translate_helloprint('Validation Error: Required fields are missing', 'helloprint'), false), 'error' );
                }
                if (isset($res['errors']) && !empty($res['errors'])) {
                    foreach ($res['errors'] as $err) {
                        $err = (array) $err;
                        $errmess = (esc_html($err['message'])) ?? '';
                        add_helloprint_flash_notice($errmess, 'error' );
                    }
                }

                $logger->info( 'Place order: error on order'.wp_json_encode($res));
                update_post_meta($order_id, 'helloprint_order_status', [
                    'status' => "UNABLE_TO_CREATE_ORDER"
                ]);
            }
        }else{
            $logger->info( 'Place order: non helloprint order, so order not processed');
            $logger->info( 'Place order: completed');
        }
    }

    public function send_automatic_order_to_helloprint($order_id)
    {
        
        if (!$order_id) {
            return;
        }
        $order = wc_get_order($order_id);
        if (empty($order)) return;

        global $wpdb;
        $preset_tableName = $wpdb->prefix . 'helloprint_order_presets';
        $line_item_file_tableName = $wpdb->prefix . 'helloprint_order_line_preset_files';
        foreach ($order->get_items() as $item) {
            $itemId = $item->get_id();
            $product = wc_get_product($item['product_id']);
            if ($product !== false) {
                // check if the product is HP Product
                if ($product->get_type() == 'helloprint_product') {
                    $presetFiles = $wpdb->get_results("SELECT * from $line_item_file_tableName where line_item_id = '$itemId'");
                    // check if any artwork has been uploaded to the order item
                    if (!empty($presetFiles)) {
                        foreach ($presetFiles as $key => $file) {
                            if ($file->file_url != '') {
                                // trigger send order to the HP if any artwork uploaded found on order item
                                return $this->send_order_to_helloprint($order_id);
                            }
                        }
                    }
                    $hello_print_product_id = get_post_meta($product->get_id(), "helloprint_external_product_id", true);
                    $hpPresets = $wpdb->get_results("SELECT * from $preset_tableName where product_type = 'hp' and helloprint_product_id='$hello_print_product_id' ");
                    // check if any artwork has been uploaded to the Helloprint Order Preset with the current product 
                    if (!empty($hpPresets)) {
                        foreach ($hpPresets as $key => $file) {
                            if ($file->file_url != '' && !empty($file->file_url)) {
                                // trigger send order to the HP if any artwork uploaded found on HP order preset for current product
                                return $this->send_order_to_helloprint($order_id);
                            }
                        }
                    }
                } else {

                    $product_variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';

                    // Check if product has variation.
                    if ($product_variation_id) { 
                        $product = wc_get_product($product_variation_id);
                    }
                    $line_item_tableName = $wpdb->prefix . 'helloprint_order_line_item_presets';
                    $sku = $product->get_sku();
                    $lineItemPreset = $wpdb->get_results("SELECT $line_item_tableName.*, $preset_tableName.helloprint_variant_key, $preset_tableName.file_url  from $line_item_tableName INNER JOIN $preset_tableName ON $line_item_tableName.preset_id = $preset_tableName.id where $line_item_tableName.line_item_id = '$itemId' and ($preset_tableName.helloprint_item_sku = '$sku' OR $preset_tableName.helloprint_item_sku = '')");
                    if (!empty($sku)) {
                        $lineItemPreset = $wpdb->get_results("SELECT $preset_tableName.*  from $preset_tableName where helloprint_item_sku = '$sku'");
                    }
                    if (!empty($lineItemPreset)) {
                        foreach ($lineItemPreset as $key => $file) {
                            if ($file->file_url != '' && !empty($file->file_url)) {
                                return $this->send_order_to_helloprint($order_id);
                            }
                        }
                    }

                }
            }
        }
        return false;
    }
}
