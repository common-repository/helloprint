<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Exception;
use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\FileUploadService;
use HelloPrint\Inc\Services\HelloPrintApiService;
use HelloPrint\Inc\Services\InputProcessService;
use HelloPrint\Inc\Services\ProductPriceService;

class CartController extends BaseController
{
    public function register()
    {
        add_filter('woocommerce_add_to_cart_validation', array($this, 'file_upload_validation'), 20, 5);
        add_filter('woocommerce_add_cart_item_data', array($this, 'helloprint_add_to_cart'), 20, 2);
        add_action('woocommerce_get_item_data', array($this, 'display_helloprint_cart_item'), 10, 2);
        add_action('woocommerce_before_calculate_totals', array($this, 'add_custom_price'), 20, 1);
        add_action('woocommerce_cart_item_price', array($this, 'modify_mini_basket_cart_product_price'), 10, 3);

        //add_action('woocommerce_checkout_update_order_review', array($this, 'update_review_cart_tems'));

        add_filter( 'woocommerce_quantity_input_args', array($this, 'hide_quantity_input_field'), 20, 2 );
    }

    public function file_upload_validation($passed)
    {
        if (!isset($_POST['product_id'])) {
            return $passed;
        }
        $product = wc_get_product(sanitize_text_field(wp_unslash($_POST['product_id'])));
        if (empty($product) || $product->get_type() !== 'helloprint_product') {
            return $passed;
        }
        $files_count = isset($_FILES['wphp_product_file_upload']['name']) ? count($_FILES['wphp_product_file_upload']['name']) : 0;
        if ($files_count === 1) {
            if (sanitize_text_field(wp_unslash($_FILES['wphp_product_file_upload']['type'][0])) == '') {
                return $passed;
            }
        }
        if ($files_count >= 1) {
            $validFileTypes = ['png', 'jpg', 'jpeg', 'pdf', 'tiff', 'tif', 'vnd.openxmlformats-officedocument.wordprocessingml.document', 'msword', 'x-zip-compressed', 'octet-stream', 'postscript'];
            for ($i = 0; $i < $files_count; $i++) {
                $exp = explode('/', sanitize_text_field(wp_unslash($_FILES['wphp_product_file_upload']['type'][$i])));
                $exp = end($exp);
                $validity = in_array($exp, $validFileTypes);
                if (!$validity) {
                    wc_add_notice(wp_kses(_translate_helloprint('Please upload a valid file type for this product. Valid file types are: pdf, jpg, jpeg, png, tiff, tif', 'helloprint'), false), 'error');
                    $passed = false;
                }
            }
        }
        return $passed;
    }
    public function helloprint_add_to_cart($cart_item_data, $product_id)
    {
        global $woocommerce;
        $product = wc_get_product($product_id);
        if (empty($product) || $product->get_type() !== 'helloprint_product') {
            return $cart_item_data;
        }
        session_start();
        $inputProcess = new InputProcessService();
        $helloprint_variant_key = sanitize_text_field(wp_unslash($_POST['hello_product_variant_key']));
        $helloprint_sku = sanitize_text_field(wp_unslash($_POST['hello_product_sku']));
        $delivery_option = $inputProcess->process_service_level(sanitize_text_field(wp_unslash($_POST['wphp_service_level'])));
        $product_quantity = $inputProcess->process_quantity(sanitize_text_field(wp_unslash($_POST['wphp_product_quantity'])));
        /*$product_setup = $inputProcess->process_product_setup_json(sanitize_text_field($_POST['wphp_product_options']));*/
        $product_setup = $inputProcess->process_product_setup_json(sanitize_text_field(wp_unslash($_POST['wphp_product_options_labels'])));
        $total_delivery_days = (int) sanitize_text_field(wp_unslash($_POST["wphp_total_delivery_days"]));
        $product_setup['appreal_size'] = [];
        foreach ($product_setup as $kkey => $opt) {
            if (str_starts_with($kkey, 'appreal_size[')) {
                if ($opt > 0) {
                    $keyString = $kkey . "=" . $opt;
                    parse_str($keyString, $result);
                    foreach ($result['appreal_size'] as $key => $optionss) {
                        if (!isset($product_setup['appreal_size'][$key])) {
                            $product_setup['appreal_size'][$key] = [];
                        }
                        foreach ($optionss as $jk => $val) {
                            $product_setup['appreal_size'][$key][$jk] = $val;
                        }
                    }
                }
                unset($product_setup[$kkey]);
            } else {
               if (!is_array($opt)) {
                    $product_setup[$kkey] = ltrim(str_replace("/n", "", $opt), "n ");
                }
            } 
        }
        $uploaded_file = [];
        if (!empty($_FILES['wphp_product_file_upload'])) {
            $uploaded_file = (new FileUploadService())
                ->storeFile( $_FILES['wphp_product_file_upload'] , $this->plugin_path);
        } else {
            if (!empty($_POST['wphp_product_file_uploaded_path'])) {
                $wphp_uploaded_files = array_map( 'sanitize_text_field', wp_unslash($_POST['wphp_product_file_uploaded_path']) );
                if ($wphp_uploaded_files) {
                    foreach ($wphp_uploaded_files as $uploadedFile) {
                        $uploaded_file[] = json_decode(stripslashes(sanitize_text_field(wp_unslash($uploadedFile))), true);
                    }
                }
            }
            
        }
        $product_graphic_options = _helloprint_get_graphic_design_price($product_id);
        $product_enable_graphic_design = $product_graphic_options['enabled'];
        $cart_item_data['helloprint_product_setup'] = array(
            'sku' => $helloprint_sku,
            'helloprint_variant_key' => $helloprint_variant_key,
            'quantity' => $product_quantity,
            'delivery_option' => $delivery_option,
            'product_price' => sanitize_text_field(wp_unslash($_POST['wphp_product_price'])),
            'options' => $product_setup,
            'uploaded_files' =>  $uploaded_file,
            'custom_options' => isset($_POST['wphp_options']) ? json_encode(array_map('sanitize_text_field',$_POST['wphp_options'])) : '',
            'appreal_size_options' => isset($_POST['appreal_size']) ? json_encode(sanitize_text_field(wp_unslash($_POST['appreal_size']))) : '',
            "total_delivery_days" => $total_delivery_days
        );

        if (!empty($_POST['destination_country'])) {
            $cart_item_data['helloprint_product_setup']['destination_country'] = sanitize_text_field(wp_unslash($_POST['destination_country']));
            //$cart_item_data['wphp_product_setup']['destination_country'] = ();
        }
        if ($product_enable_graphic_design) {
            $wphp_design = (float)sanitize_text_field(wp_unslash($_POST['wphp_design']));
            if ($wphp_design > 0) {
                $product_graphic_design_price = $product_graphic_options['price'];
                /*if(wc_tax_enabled() && wc_prices_include_tax() && $product->get_tax_status()=='taxable'){

                    $tax = new \WC_Tax();
                    $taxes = $tax->get_rates($product->get_tax_class());
                    $rates = array_shift($taxes);
                    $taxRate = isset($rates['rate']) ? $rates['rate'] : 0;
                    $product_graphic_design_price = $product_graphic_design_price * (1 + ($taxRate / 100));
                }*/
                $cart_item_data['helloprint_product_setup']['want_graphic_design'] = true;
                $cart_item_data['helloprint_product_setup']['graphic_design_price'] = $product_graphic_design_price;
            } else {
                $cart_item_data['helloprint_product_setup']['want_graphic_design'] = false;
                if (isset($cart_item_data['helloprint_product_setup']['graphic_design_price'])) {
                    unset($cart_item_data['helloprint_product_setup']['graphic_design_price']);
                }
            }
        }
        if (\hp_is_pitchprint_plugin_active() && !empty($_POST['_w2p_set_option'])) {
            require_once "$this->plugin_path/includes/Base/HP_PitchPrint.php";
            $hpPitchPrint = new \HP_PitchPrint();
            $hpPitchPrint->handle_HP_pitch_print_cart($cart_item_data, $product_id);
        }
        return $cart_item_data;
    }

    private function format_form_data(array $data)
    {

        $matches = array();
        $result = [];

        foreach ($data as $key => $value) {
            preg_match_all("/\[(.*?)\]/", $key, $matches);
            $matches = array_reverse($matches[1]);
            $matches[] =  substr($key, 0, strpos($key, '['));;

            foreach ($matches as $match) {
                $value = [$match => $value];
            }

            $result = array_replace_recursive($result, $value);
        }

        return $result;
    }

    public function display_helloprint_cart_item($item_data, $cart_item)
    {
        if (empty($cart_item['helloprint_product_setup'])) {
            return $item_data;
        }
        foreach ($cart_item['helloprint_product_setup'] as $key => $value) {
            if (in_array($key, ['quantity', 'delivery_option', 'want_graphic_design', 'graphic_design_price', 'destination_country'])) {
                if ($key == 'want_graphic_design') {
                    $value = ($value) ? wp_kses(_translate_helloprint("Yes please", 'helloprint'), false) : wp_kses(_translate_helloprint("No thank you, i'll supply print ready artwork.", 'helloprint'), false);
                }

                if ($key == 'graphic_design_price') {

                    $tax_display_option = get_option("woocommerce_tax_display_cart");
                    if ($tax_display_option == 'incl') {
                        $taxRate = 0;
                        $product = wc_get_product($cart_item['product_id']);
                        if (!empty($product) && wc_tax_enabled() && $product->get_tax_status() == 'taxable') {
                            $tax = new \WC_Tax();
                            $taxes = $tax->get_rates($product->get_tax_class());
                            $rates = array_shift($taxes);
                            $taxRate = isset($rates['rate']) ? $rates['rate'] : 0;
                        }
                        $value = $value * (1 + ($taxRate / 100));
                    }

                    $value = wc_price($value);
                }


                $key_value_for_cart = _translate_helloprint(ucfirst($value), "helloprint");
                if ($key == "delivery_option") {
                    if (!empty($cart_item["helloprint_product_setup"]["total_delivery_days"])) {
                        $key_value_for_cart .=  ' - ' . $cart_item["helloprint_product_setup"]["total_delivery_days"] . ' ' . wp_kses(_translate_helloprint("Day(s)", 'helloprint'), true);
                    }
                }
                $item_data[] = array(
                    'name' => wp_kses(_translate_helloprint(str_replace('_', ' ', ucfirst($key)), 'helloprint'), false),
                    'value' => $key_value_for_cart
                );
            }
            if ($key == 'options') {
                foreach ($value as $optionKey => $option) {
                    if ($optionKey == 'appreal_size') {
                        foreach ($option as $optKey => $optionVal) {
                            $sizevalue = "[";
                            if (is_array($optionVal)) {
                                $iternalarray = [];
                                foreach ($optionVal as $k => $opt) {
                                    if ($opt > 0) {
                                        $iternalarray[] = $k . ":" . $opt;
                                    }
                                }
                                $sizevalue .= implode("; ", $iternalarray);
                            } else {
                                $sizevalue .= $optionVal;
                            }
                            $sizevalue .= "]";
                            $item_data[] = array(
                                'name' => $optKey,
                                'value' => $sizevalue,
                            );
                        }
                    } else {
                        $item_data[] = array(
                            'name' => wp_kses(_translate_helloprint(ucfirst($optionKey), 'helloprint'), false),
                            'value' => ucfirst($option),
                        );
                    }
                }
            }

            if ($key == 'uploaded_files') {
                $file_value = '';
                foreach ($value as $key => $file) {
                    if ($file['file_name'] != '') {
                        $file_value = $file_value . '<span>' . esc_html($file['file_name']) . '</span><br><a download href="' . esc_url(get_site_url() . $file['file_path']) . '">' . wp_kses(_translate_helloprint('Download', 'helloprint'), true) . '</a><br>';
                    }
                }
                if ($file_value != '') {
                    $item_data[] = array(
                        'name' => wp_kses(_translate_helloprint('Uploaded Files', 'helloprint'), false),
                        'value' => $file_value,
                    );
                }
            }
        }
        return $item_data;
    }


    public function add_custom_price($cart)
    {
        // This is necessary for WC 3.0+
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // Avoiding hook repetition (when using price calculations for example)
        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        foreach ($cart->get_cart() as $item) {
            $product = $item['data'];
            if (empty($product) || !method_exists($product, "get_type") || $product->get_type() != 'helloprint_product') {
                continue;
            }
            $product_price = $this->get_helloprint_product_price($item['helloprint_product_setup'], $product);
            if ($product_price > 0) {
                $item['data']->set_price($product_price);
                // $item['data']->set_price(100);
            }
        }
    }

    public function modify_mini_basket_cart_product_price($price, $cart_item, $cart_item_key)
    {

        $product_id = $cart_item['product_id'];
        $product = wc_get_product($product_id);
        if (!empty($product) && method_exists($product, "get_type") && $product->get_type() === 'helloprint_product') {
            $final_price = $this->get_helloprint_product_price($cart_item['helloprint_product_setup'], $product);
            if ($final_price > 0) {
                $price = wc_price($final_price, 4);
                return $price;
            }
        }
        return $price;
    }

    private function get_helloprint_product_price($helloprint_product_setup, $product)
    {
        if (!empty($helloprint_product_setup['custom_options'])) {
            $custom_options = json_decode($helloprint_product_setup['custom_options'], true);
            $options = [];
            foreach ($custom_options as $key => $opt) {
                $options[] = ['code' => strtolower($key), 'value' => $opt];
            }
        }
        $hello_product_service = new HelloPrintApiService();
        $requestArray = [
            "items" => [
                [
                    "variantKey" => $helloprint_product_setup['helloprint_variant_key'],
                    "quantity" => $helloprint_product_setup['quantity'],
                    "serviceLevel" => $helloprint_product_setup['delivery_option'],
                    'options' => !empty($options) ? $options : []
                ]
            ],
            "destinationCountryCode" => ($helloprint_product_setup['destination_country']) ?? null
        ];
        $serviceLevelRes =  $hello_product_service->post('quotes', $requestArray);
        $res = $hello_product_service->getResponseToJson($serviceLevelRes);

        if (!isset($res['data'])) {
            $arrayForRequest = $requestArray;
            unset($arrayForRequest["items"][0]['quantity']);
            $serviceLevelRes =  $hello_product_service->post('quotes', $arrayForRequest);
            $res = $hello_product_service->getResponseToJson($serviceLevelRes);
        }
        if (!isset($res['data'])) {
            unset($requestArray['destinationCountryCode']);
            $serviceLevelRes =  $hello_product_service->post('quotes', $requestArray);
            $res = $hello_product_service->getResponseToJson($serviceLevelRes);
        }
        $res = $hello_product_service->getResponseToJson($serviceLevelRes);
        if (isset($res['data'])) {
            $productPriceService = new ProductPriceService();
            $priceExclTax = $res['data']['items'][$helloprint_product_setup['helloprint_variant_key']][$helloprint_product_setup['quantity']][0]['prices']['centAmountExclTax'];
            $product_price = $productPriceService->getProductPriceMargin($product->get_id(), $priceExclTax, true);
            $final_price = floatval($product_price / 100);
            if (wc_tax_enabled() && wc_prices_include_tax() && $product->get_tax_status() == 'taxable') {
                $tax = new \WC_Tax();
                $taxes = $tax->get_rates($product->get_tax_class());
                $rates = array_shift($taxes);
                $taxRate = isset($rates['rate']) ? $rates['rate'] : 0;
                $final_price = $final_price * (1 + ($taxRate / 100));
            }
            if (!empty($helloprint_product_setup['want_graphic_design'])) {
                if ($helloprint_product_setup['want_graphic_design']) {
                    $final_price += !empty($helloprint_product_setup['graphic_design_price']) ? floatval($helloprint_product_setup['graphic_design_price']) : 0;
                }
            }
            return $final_price;
        } else {
            return 0;
        }
    }

    public function update_review_cart_tems($posted_data)
    {
        $post = array();
        $vars = explode('&', $posted_data);
        foreach ($vars as $k => $value) {
            $v = explode('=', urldecode($value));
            $post[$v[0]] = $v[1];
        }
        $country = $post['billing_country'];
        global $woocommerce;
        $cartItems = $woocommerce->cart->cart_contents;
        $returnArray = [];
        foreach ($cartItems as $key => $cartI) {
            if (isset($cartI['helloprint_product_setup'])) {
                $productSetup = $cartI['helloprint_product_setup'];
                if (isset($productSetup['destination_country'])) {
                    $productSetup['destination_country'] = $country;
                }
                $woocommerce->cart->cart_contents[$key]['helloprint_product_setup'] = $productSetup;
            }
        }
        $woocommerce->cart->set_session();
        $this->add_custom_price(WC()->cart);
        return $posted_data;
    }

    function hide_quantity_input_field($args, $product)
    {
        if ((is_cart() || is_admin()) && !empty($product) && $product->get_type() == 'helloprint_product') {
            $input_value = $args['input_value'];
            $args['min_value'] = $args['max_value'] = $input_value;
        }
        return $args;
    }
}
