<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Exception;
use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\FileUploadService;
use HelloPrint\Inc\Services\HelloPrintApiService;
use HelloPrint\Inc\Services\HelloPrintProductService;
use HelloPrint\Inc\Services\ProductPriceService;

class ProductController extends BaseController
{
    public function register()
    {

        add_filter('http_request_timeout', array($this, 'helloprint_custom_http_request_timeout'));

        add_filter('woocommerce_product_class', array($this, 'register_helloprint_product_type_class'), 10, 2);
        add_filter('product_type_selector', array($this, 'add_helloprint_product_type'));
        add_action('woocommerce_product_data_tabs', array($this, 'add_helloprint_product_to_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_helloprint_product_detail_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_helloprint_product_panel'));
        add_action('wp_ajax_get_helloprint_category_detail', array($this, 'get_helloprint_ajax_category_detail'));
        add_action('wp_ajax_nopriv_get_helloprint_category_detail', array($this, 'get_helloprint_ajax_category_detail'));
        add_action('woocommerce_single_product_summary', array($this, 'add_helloprint_product_summary'));
        add_action('woocommerce_is_purchasable', array($this, 'helloprint_is_purchasable'), 10, 2);

        add_action('wp_ajax_get_wphp_variant_filter', array($this, 'get_helloprint_product_variant_filter'));
        add_action('wp_ajax_nopriv_get_wphp_variant_filter', array($this, 'get_helloprint_product_variant_filter'));
        add_action('wp_ajax_get_wphp_product_formatted_price', array($this, 'get_helloprint_product_formatted_price'));
        add_action('wp_ajax_nopriv_get_wphp_product_formatted_price', array($this, 'get_helloprint_product_formatted_price'));

        add_action('wp_ajax_get_wphp_product_detail', array($this, 'get_helloprint_product_details'));
        add_action('woocommerce_order_item_meta_end', array($this, 'helloprint_view_order_details'), 10, 3);
        add_action('wp_ajax_wphp_save_artwork_of_order', array($this, 'helloprint_save_artwork_of_order'));

        add_action('wp_ajax_wphp_delete_artwork_of_order', array($this, 'helloprint_delete_artwork_of_order'));

        add_shortcode('helloprint_product_attributes',  array($this, 'helloprint_product_attributes_shortcode'));
    }

    public function helloprint_is_purchasable($previousResult, $product)
    {

        if (empty($product) || $product->get_type() !== 'helloprint_product') {
            return $previousResult;
        }

        return true;
    }

    public function register_helloprint_product_type_class($classname, $product_type)
    {
        require_once "$this->plugin_path/includes/Services/HelloPrintProductType.php";
        if ($product_type == 'helloprint_product') {
            $classname = 'HelloPrintProductType';
        }
        return $classname;
    }

    public function add_helloprint_product_type($productType)
    {
        $productType['helloprint_product'] = wp_kses(_translate_helloprint('HelloPrint product', 'helloprint'), true);
        return $productType;
    }

    public function add_helloprint_product_to_tab($tabs)
    {
        $tabs['helloprint_product'] = array(
            'label' => wp_kses(_translate_helloprint('HelloPrint Product', 'helloprint'), true),
            'target' => 'helloprint_product_options',
            'class' => 'show_if_helloprint_product',
        );
        return $tabs;
    }

    public function add_helloprint_product_detail_panel()
    {
        $product_id = get_the_ID();
        try {
            $product_external_id = get_post_meta($product_id, 'helloprint_external_product_id', true);
            $product_margin_option = get_post_meta($product_id, 'helloprint_product_margin_option', true);
            $product_markup_margin_option = get_post_meta($product_id, 'helloprint_markup_margin', true);
            if (empty($product_markup_margin_option)) {
                $product_markup_margin_option = "margin";
            }
            if (empty($product_margin_option) && $product_margin_option !== '0') {
                $product_margin_option = 1;
            }

            $product_markup = get_post_meta($product_id, 'helloprint_product_markup', true);
            $helloprint_product_markup_option = get_post_meta($product_id, 'helloprint_product_markup_option', true);
            if (empty($helloprint_product_markup_option) && $helloprint_product_markup_option !== '0') {
                $helloprint_product_markup_option = 1;
            }
            $product_margin = get_post_meta($product_id, 'helloprint_product_margin', true);
            $product_enable_graphic_design = get_post_meta($product_id, 'helloprint_product_graphic_design_fee', true);
            if (empty($product_enable_graphic_design)) {
                $product_enable_graphic_design = 0;
            }

            $product_graphic_design_price = get_post_meta($product_id, 'helloprint_product_graphic_design_price', true);
            $product_options = (new HelloPrintApiService())->getAllProducts();
            $allProductKeys = array_unique(array_reduce(array_map('array_keys', $product_options), 'array_merge', []));
            if (!empty($product_external_id) && !in_array($product_external_id, $allProductKeys)) {
                $product_category_id = get_post_meta($product_id, 'helloprint_external_category_id', true);
                $product = wc_get_product($product_id);
                $product_name = $product->get_name();
                $category_name = !empty($product_category_id) ? ucwords(str_replace(["_", "-"], [" > ", " "], $product_category_id)) : ucwords($product_name);
                $product_options[$category_name] = [$product_external_id => $product_name . " [" . $product_external_id . "]"];
            }

            $global_product_file_upload = esc_attr(get_option('helloprint_product_upload_file'));
            if (empty($global_product_file_upload)) {
                $global_product_file_upload = 'show_on_both_pages';
            }
            $product_upload_file = get_post_meta($product_id, 'helloprint_product_upload_file', true);
            $switch_color_icon = get_post_meta($product_id, 'helloprint_switch_color_icon', true);
            $switch_print_position_icon = get_post_meta($product_id, 'helloprint_switch_print_position_icon', true);
            $product_limit_variant_key = get_post_meta($product_id, 'helloprint_product_limit_variant_key', true);
            $product_sku = get_post_meta($product_id, 'helloprint_product_sku', true);
            /*if (empty($product_upload_file) && !empty($global_product_file_upload)) {
                $product_upload_file = $global_product_file_upload;
            }*/

            $product_show_icon = get_post_meta($product_id, 'helloprint_product_show_icon', true);

            $product_file_upload_options = [
                '' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                'show_on_both_pages' => wp_kses(_translate_helloprint("Show upload on product and cart", 'helloprint'), false),
                'show_on_cart_only' => wp_kses(_translate_helloprint("Show upload on cart only", 'helloprint'), false),
                'show_on_product_only' => wp_kses(_translate_helloprint("Show upload on product only", 'helloprint'), false),
                'no_option' => wp_kses(_translate_helloprint("No option for uploading", 'helloprint'), false)
            ];
            $product_graphic_design_fee_option = [
                '0' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                '1' => wp_kses(_translate_helloprint("Set Graphic Design Fee", 'helloprint'), false),
                '-1' => wp_kses(_translate_helloprint("Disable", 'helloprint'), false)
            ];
            $global_or_individual_options = [
                '1' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                '0' => wp_kses(_translate_helloprint("Pricing Tiers", 'helloprint'), false),
                '2' => wp_kses(_translate_helloprint("Default Margin", 'helloprint'), false),
            ];

            $global_or_individual_options_markup = [
                '1' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                '0' => wp_kses(_translate_helloprint("Pricing Tiers", 'helloprint'), false),
                '2' => wp_kses(_translate_helloprint("Default Markup", 'helloprint'), false),
            ];


            $response = (new HelloPrintApiService())->getProductDetailForSelectOptions($product_external_id);

            $product_show_icon_options = [
                '' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                'enable' => wp_kses(_translate_helloprint("Enable", 'helloprint'), false),
                'disable' => wp_kses(_translate_helloprint("Disable", 'helloprint'), false),
            ];
            $product_limit_variant_key_options =[
                '' => _translate_helloprint("Limit Variant Keys", 'helloprint'),
                '1' => _translate_helloprint("Yes", 'helloprint'),
                '0' => _translate_helloprint("No", 'helloprint'),
            ];
            global $wp_roles;
            $hp_user_roles = [];
            $hp_user_roles[""] = wp_kses(_translate_helloprint("All", 'helloprint'), false);
            
            if (!empty($wp_roles->roles)) {
                foreach ($wp_roles->roles as $k => $role) {
                    if (!empty($role["name"])) {
                        $hp_user_roles[$k] = $role["name"];
                    }
                }
            }
            global $wpdb;
            $tierTableName = $wpdb->prefix . 'helloprint_pricing_tiers';
            $results = $wpdb->get_results("SELECT id,name from $tierTableName where (tier_type='margin' or tier_type='') ORDER BY id ASC");
            $all_pricing_tiers = [];
            if (!empty($product_external_id)) {
                $all_pricing_tiers[""] = wp_kses(_translate_helloprint("Select Pricing Tier", "helloprint"), false);
            }
            foreach ($results as $res) {
                $all_pricing_tiers[$res->id] = $res->name;
            }
            $selected_pricing_tier = "";
            $selected_role_for_pricing = "";
            $productTierTableName = $wpdb->prefix . 'helloprint_product_pricing_tier';
            $product_tiers = $wpdb->get_results("SELECT id,pricing_tier_id,profile from $productTierTableName WHERE product_id='$product_id'");
            if (!empty($product_tiers[0])) {
                $selected_pricing_tier = $product_tiers[0]->pricing_tier_id;
                $selected_role_for_pricing = $product_tiers[0]->profile;
            } 
            $tierTable = $wpdb->prefix . "helloprint_pricing_tiers";
            $selected_tier_details = $wpdb->get_results("SELECT id from $tierTable WHERE id='$selected_pricing_tier'");
            if (empty($selected_tier_details) || empty($selected_tier_details[0]) ) {
                if (empty($helloprint_product_markup_option) || $helloprint_product_markup_option === 0) {
                    $helloprint_product_markup_option = ($product_markup > 0) ? 2 : 1;
                }
                if (empty($product_margin_option) || $product_margin_option === 0) {
                    $product_margin_option = ($product_margin > 0) ? 2 : 1;
                }
            }

            $results_for_markups = $wpdb->get_results("SELECT id,name from $tierTableName where tier_type='markup' ORDER BY id ASC");
            $all_pricing_tiers_markups = [];
            if (!empty($product_external_id)) {
                $all_pricing_tiers_markups[""] = wp_kses(_translate_helloprint("Select Pricing Tier", "helloprint"), false);
            }
            foreach ($results_for_markups as $res) {
                $all_pricing_tiers_markups[$res->id] = $res->name;
            }
            
            
            $data = array(
                'product_attributes' => isset($response['attributes']) ? $response['attributes'] : [],
                'product_options' => $product_options ?? [],
                'product_external_id' => $product_external_id,
                'product_margin' => $product_margin,
                'product_global_or_individual' => $global_or_individual_options,
                'product_graphic_design_fee_options' => $product_graphic_design_fee_option,
                'product_upload_file_options' => $product_file_upload_options,
                'helloprint_product_upload_file' => $product_upload_file,
                'helloprint_product_graphic_design_fee' => $product_enable_graphic_design,
                'helloprint_product_graphic_design_price' => $product_graphic_design_price,
                'helloprint_switch_color_icon' => $switch_color_icon,
                'helloprint_switch_print_position_icon' => $switch_print_position_icon,
                'product_show_icon_options' => $product_show_icon_options,
                'helloprint_product_show_icon' => $product_show_icon,
                'helloprint_product_margin_option' => $product_margin_option,
                'helloprint_product_limit_variant_key'=>$product_limit_variant_key,
                'helloprint_product_sku'=>$product_sku,
                'product_limit_variant_key_options'=>$product_limit_variant_key_options,
                "hp_user_roles" => $hp_user_roles,
                "hp_selected_user_role_pricing" => $selected_role_for_pricing,
                "all_pricing_tiers" => $all_pricing_tiers,
                "hp_selected_pricing_tier" => $selected_pricing_tier,
                "hp_product_markup_margin_option" => $product_markup_margin_option,

                'helloprint_product_markup_option' => $helloprint_product_markup_option,
                "all_pricing_tiers_markups" => $all_pricing_tiers_markups,
                "product_markup" => $product_markup,
                'global_or_individual_options_markup' => $global_or_individual_options_markup
            );
            load_template("$this->plugin_path/templates/admin/tabs/helloprint-product-panel.php", true, $data);
        } catch (Exception $e) {
        }
    }

    public function get_helloprint_ajax_category_detail()
    {
        $categories = (new HelloPrintApiService())->getCategoryProductForSelectOptions(sanitize_text_field(wp_unslash($_POST['helloprint_external_category_id'])));
        wp_send_json_success(array(
            'categories' => $categories
        ));
    }

    public function save_helloprint_product_panel($product_id)
    {
        if ((isset($_POST['product-type']) && sanitize_text_field( wp_unslash( $_POST['product-type'] ) )  == 'helloprint_product') || (isset($_POST['product_type']) &&  sanitize_text_field( wp_unslash( $_POST['product_type'] ) ) == 'helloprint_product') && !empty($_POST['helloprint_external_product_id'])) {
            update_post_meta($product_id, 'helloprint_external_product_id', sanitize_text_field(wp_unslash($_POST['helloprint_external_product_id'])));
            update_post_meta($product_id, 'helloprint_product_margin', sanitize_text_field(wp_unslash($_POST['helloprint_product_margin'])));
            update_post_meta($product_id, 'helloprint_product_markup', sanitize_text_field(wp_unslash($_POST['helloprint_product_markup'])));
            
            update_post_meta($product_id, 'helloprint_product_upload_file', sanitize_text_field(wp_unslash($_POST['helloprint_product_upload_file'])));

            if (isset($_POST['helloprint_product_graphic_design_fee']))
                update_post_meta($product_id, 'helloprint_product_graphic_design_fee', sanitize_text_field(wp_unslash($_POST['helloprint_product_graphic_design_fee'])));

            if (isset($_POST['helloprint_switch_color_icon']))
                update_post_meta($product_id, 'helloprint_switch_color_icon', sanitize_text_field(wp_unslash($_POST['helloprint_switch_color_icon'])));
            if (isset($_POST['helloprint_switch_print_position_icon']))
                update_post_meta($product_id, 'helloprint_switch_print_position_icon', sanitize_text_field(wp_unslash($_POST['helloprint_switch_print_position_icon'])));
            if (isset($_POST['helloprint_product_show_icon']))
                update_post_meta($product_id, 'helloprint_product_show_icon', sanitize_text_field(wp_unslash($_POST['helloprint_product_show_icon'])));
          
            if (isset($_POST['helloprint_product_limit_variant_key'])) {
                if ($_POST['helloprint_product_limit_variant_key']==1 && !empty($_POST['helloprint_product_sku'])) {
                    update_post_meta($product_id, 'helloprint_product_limit_variant_key', sanitize_text_field(wp_unslash($_POST['helloprint_product_limit_variant_key'])));
                    $sku = implode("\n", array_map('sanitize_textarea_field', explode("\n", $_POST['helloprint_product_sku'])));
                    update_post_meta($product_id, 'helloprint_product_sku', $sku);
                }else{
                    if($_POST['helloprint_product_limit_variant_key']!=1 )
                        update_post_meta($product_id, 'helloprint_product_limit_variant_key', sanitize_text_field(wp_unslash($_POST['helloprint_product_limit_variant_key'])));
                    else
                        update_post_meta($product_id, 'helloprint_product_limit_variant_key', '');
                        
                    update_post_meta($product_id, 'helloprint_product_sku', null);
                }
            }
            if (sanitize_text_field(wp_unslash($_POST['helloprint_product_graphic_design_fee']))) {
                $graphic_price = (float)sanitize_text_field(wp_unslash($_POST['helloprint_product_graphic_design_price']));
            } else {
                $graphic_price = 0;
            }
            update_post_meta($product_id, 'helloprint_product_graphic_design_price', sanitize_text_field(wp_unslash($graphic_price)));

            $helloprint_markup_margin = sanitize_text_field(wp_unslash($_POST['helloprint_markup_margin']));
            if (empty($helloprint_markup_margin)) $helloprint_markup_margin = "margin";
            update_post_meta($product_id, 'helloprint_markup_margin', $helloprint_markup_margin);
            if ($helloprint_markup_margin != "markup") {
                if (sanitize_text_field(wp_unslash($_POST['helloprint_product_margin_option']))) {
                    $product_margin_option = (int)sanitize_text_field(wp_unslash($_POST['helloprint_product_margin_option']));
                } else {
                    $product_margin_option = 0;
                }
                $product_mark_mar_option = $product_margin_option;
                update_post_meta($product_id, 'helloprint_product_margin_option', sanitize_text_field(wp_unslash($product_margin_option)));
            } else {
                if (sanitize_text_field(wp_unslash($_POST['helloprint_product_markup_option']))) {
                    $product_markup_option = (int)sanitize_text_field(wp_unslash($_POST['helloprint_product_markup_option']));
                } else {
                    $product_markup_option = 0;
                }
                $product_mark_mar_option = $product_markup_option;
                update_post_meta($product_id, 'helloprint_product_markup_option', sanitize_text_field(wp_unslash($product_markup_option)));
            }

            global $wpdb;
            $productTierTableName = $wpdb->prefix . 'helloprint_product_pricing_tier';
            if ($product_mark_mar_option == 0) {
                $product_tiers = $wpdb->get_results("SELECT id,pricing_tier_id,profile from $productTierTableName WHERE product_id='$product_id'");
                if ($helloprint_markup_margin != "markup") {
                    $pricing_tier_id = sanitize_text_field(wp_unslash($_POST['helloprint_pricing_tier']));
                    $role = sanitize_text_field(wp_unslash($_POST['helloprint_user_roles_for_price']));
                } else {
                    $pricing_tier_id = sanitize_text_field(wp_unslash($_POST['helloprint_pricing_tier_markup']));
                    $role = sanitize_text_field(wp_unslash($_POST['helloprint_user_roles_for_price_markup']));
                }
                if (empty($role)) $role = null;
                if (!empty($product_tiers[0])) {
                    $wpdb->query("UPDATE $productTierTableName SET pricing_tier_id='$pricing_tier_id',profile='$role' WHERE product_id='$product_id'");
                } else {
                    $wpdb->query("INSERT INTO $productTierTableName(product_id,pricing_tier_id,profile) VALUES('$product_id','$pricing_tier_id','$role')");
                }
            } else {
                $wpdb->query("DELETE from $productTierTableName WHERE product_id='$product_id'");
            }
            
            
            // to save the description and image from helloprint api
            if (sanitize_text_field(wp_unslash($_POST['original_post_status'])) == 'auto-draft') {
                $productDetails = (new HelloPrintApiService())->getProductDetailsForLoad(sanitize_text_field(wp_unslash($_POST['helloprint_external_product_id'])));

                $title = !empty($productDetails['product_name']) ? $productDetails['product_name'] : '';
                // save description and title
                $postProduct = array(
                    'ID' =>  $product_id,
                    'post_title' => $title,
                    'post_content'  => !empty($productDetails['description']) ? $productDetails['description'] : ''
                );

                wp_update_post($postProduct);

                // save images if not empty
                if (!empty($productDetails['preview_image'])) {
                    $this->save_img_as_preview($productDetails['preview_image'], $product_id);
                }
            }
        }
    }


    public function add_helloprint_product_summary()
    {
        global $product;
        if (!empty($product) && method_exists($product, "get_type") && $product->get_type() == 'helloprint_product') {
            if (\hp_is_pitchprint_plugin_active()) {
                $version = \_get_helloprint_version();
                wp_enqueue_script('wphp-wp-pitchprint_class', 'https://pitchprint.io/rsc/js/client.js', array('jquery'), $version);
                wp_enqueue_script('wphp-wp-pitchprint_class_noes6', 'https://pitchprint.io/rsc/js/noes6.js', array('jquery'), $version);
            }
            $product_external_id = get_post_meta($product->get_id(), 'helloprint_external_product_id', true);
            $apiService = (new HelloPrintApiService());
            $response = $apiService->getProductDetailForSelectOptions($product_external_id);
            $destination_countries = ($response['destination_countries']) ?? [];
            $all_templates = $apiService->getProductPdfTemplates($product_external_id);
            $product_upload_file = get_post_meta($product->get_id(), 'helloprint_product_upload_file', true);
            if (empty($product_upload_file)) {
                $product_upload_file = esc_attr(get_option('helloprint_product_upload_file'));
            }

            $can_upload_file = (empty($product_upload_file) || in_array($product_upload_file, ['show_on_both_pages', 'show_on_product_only'])) ? true : false;

            $product_show_icon = get_post_meta($product->get_id(), 'helloprint_product_show_icon', true);
            if (empty($product_show_icon)) {
                $product_show_icon = esc_attr(get_option('helloprint_switch_icon'));
            }
            $switch_icon = (!empty($product_show_icon) && ($product_show_icon == 'enable' || $product_show_icon == 1)) ? true : false;
            $product_graphic_options = _helloprint_get_graphic_design_price($product->get_id());
            $enable_product_design = $product_graphic_options['enabled'];
            $product_graphic_design_price = $product_graphic_options['price'];

            $size_exists = false;
            if (!empty($response['attributes'])) {
                $size_exists = (array_search('size', array_column($response['attributes'], 'id')) !== false);
            }

            $product_show_color_icon = get_post_meta($product->get_id(), 'helloprint_switch_color_icon', true);
            if (empty($product_show_color_icon)) {
                $product_show_color_icon = esc_attr(get_option('helloprint_switch_color_icon'));
            }
            $product_show_print_position_icon = get_post_meta($product->get_id(), 'helloprint_switch_print_position_icon', true);
            if (empty($product_show_print_position_icon)) {
                $product_show_print_position_icon = esc_attr(get_option('helloprint_switch_print_position_icon'));
            }
            $available = isset($response['available']) ? $response['available'] : false;
            $taxable = false;

            if(wc_tax_enabled() && get_option("woocommerce_tax_display_shop") == 'incl' && $product->get_tax_status() == 'taxable'){
                $taxable = true;
            }
            $limit_sku_variant = get_post_meta($product->get_id(), 'helloprint_product_limit_variant_key', true);
            if($limit_sku_variant){
                $product_attributes = $this->get_sku_limit_variant($product->get_id(),$product_external_id,$response['attributes']);
            }
            $data = [
                'product_attributes' => $limit_sku_variant ? $product_attributes : (!empty($response['attributes']) ? $response["attributes"] : []),
                'product_options' => !empty($response['options']) ? $response['options'] : [],
                'product_external_id' => $product_external_id,
                'can_upload_file' => $can_upload_file,
                'enable_product_design' => $enable_product_design,
                'graphic_design_price' => (float)$product_graphic_design_price,
                'size_exists' => $size_exists,
                'all_templates' => $all_templates,
                'product_show_color_icon' => $product_show_color_icon,
                'product_show_print_position_icon' => $product_show_print_position_icon,
                'switch_icon' => $switch_icon,
                'helloprint_available' => $available,
                'taxable' => $taxable,
                'destination_countries' => $destination_countries,
                'product_name' => $product->get_name(),
                'product_slug' => $product->get_slug(),
                'product_id' => $product->get_id()
            ];
            load_template("$this->plugin_path/templates/wp/product-template.php", true, $data);
        }
    }


    public function get_helloprint_product_variant_filter()
    {
        check_ajax_referer('wphp-plugin-nonce');
        if (isset($_POST['product_id'])) {
            $product_margin = (new ProductPriceService())->getProductMargin(sanitize_text_field($_POST['product_id']));
            $custom_options = !empty($_POST['wphp_attributes']['helloprint_options']) ? array_map('sanitize_text_field', $_POST['wphp_attributes']['helloprint_options']) : [];
            if (empty($custom_options) && !empty($_POST['wphp_attributes']['wphp_options'])) {
                $custom_options = array_map('sanitize_text_field', $_POST['wphp_attributes']['wphp_options']);
            }
            $helloprint_attributes = isset($_POST['wphp_attributes']) ? (array) array_map('sanitize_text_field', wp_unslash($_POST['wphp_attributes'])) : array();
            $delivery_type = isset($_POST['delivery_type']) ? sanitize_text_field(wp_unslash($_POST['delivery_type'])) : '';

            $product_quantity = isset($_POST['wphp_product_quantity']) ? sanitize_text_field(wp_unslash($_POST['wphp_product_quantity'])) : '';
            $appreal_quantity = isset($_POST['wphp_appreal_quantity']) ? sanitize_text_field(wp_unslash($_POST['wphp_appreal_quantity'])) : -1;
            $selected_attributes = isset($_POST['wphp_selected_attribute']) ? sanitize_text_field(wp_unslash($_POST['wphp_selected_attribute'])) : '';
            $helloprint_extrenal_id = isset($_POST['wphp_external_product_id']) ? sanitize_text_field(wp_unslash($_POST['wphp_external_product_id'])) : '';
            $pricing_page = !empty($_POST['pricing_quantity_page']) ? sanitize_text_field(wp_unslash($_POST['pricing_quantity_page'])) : 1;

            $destination_country = isset($_POST['destination_country']) ? sanitize_text_field(wp_unslash($_POST['destination_country'])) : '';
            $helloprint_attributes = array_map('sanitize_text_field', $helloprint_attributes);
            $helloprint_attributes['helloprint_options'] = $custom_options;
            $lazy_loading = !empty($_POST['lazy_loading']) ? sanitize_text_field(wp_unslash($_POST['lazy_loading'])) : false;

            $response = (new HelloPrintApiService())->getProductVariantsFilter(
                $helloprint_extrenal_id,
                $helloprint_attributes,
                $product_margin,
                $delivery_type,
                $selected_attributes,
                $product_quantity,
                sanitize_text_field($_POST['product_id']),
                $appreal_quantity,
                $destination_country,
                $pricing_page,
                $lazy_loading
            );
            $response['site_currency_symbol'] = get_woocommerce_currency_symbol();
            $response['site_currency'] = get_woocommerce_currency();
            $response['site_country'] = str_replace("_", "-", get_locale());
            $response['thousand_separator'] = esc_attr(wc_get_price_thousand_separator());
            $product_id =  sanitize_text_field($_POST['product_id']);
            $limit_sku_variant = get_post_meta($product_id, 'helloprint_product_limit_variant_key', true);
            if($limit_sku_variant){
                $variant_data = $this->get_sku_limit_variant_for_selected_options($product_id,$helloprint_extrenal_id,$response['variants'],$response['attributeOptions'],$helloprint_attributes);
                $response['variants'] = $variant_data['variants'];
                $response['attributeOptions'] = $variant_data['attributeOptions'];
            }
            $enable_graphic = get_post_meta(sanitize_text_field(wp_unslash($_POST['product_id'])), 'helloprint_product_graphic_design_fee', true);
            // $response['margin'] = $product_margin;
            $response['enable_graphic_design'] = ($enable_graphic == 1) ? true : false;
            $response['graphic_design_price'] = ($response['enable_graphic_design']) ? (float)get_post_meta(sanitize_text_field(wp_unslash($_POST['product_id'])), 'helloprint_product_graphic_design_price', true) : 0;
            if (\hp_is_pitchprint_plugin_active()) {
                if (!empty($response['quotes'])) {
                    $variantKey = !empty($response['quotes'][0]['variantKey']) ?  $response['quotes'][0]['variantKey'] : null;
                    if (!empty($variantKey)) {
                        global $wpdb;
                        $tableName = $wpdb->prefix . 'helloprint_pitch_prints';
                        $query = "SELECT id,name,pitchprint_design_id,hp_variant_key from $tableName WHERE `hp_variant_key` = '$variantKey'";
                        $results = $wpdb->get_results($query);
                        //print_r($results);
                        if (count($results) > 0 && !empty($results[0])) {
                            $design_id = $results[0]->pitchprint_design_id;
                            $response['pitch_print_design_id'] = $design_id;
                            $response['pitch_print_api_key'] = get_option('ppa_api_key');
                        }
                    }
                }
            }
            wp_send_json_success($response);
        }
    }


    public function helloprint_custom_http_request_timeout()
    {
        return 15;
    }

    private function save_img_as_preview($image_url = '', $post_id = null)
    {
        $imgNameArr = explode("/", $image_url);
        $image_name = $imgNameArr[count($imgNameArr) - 1];
        $upload_dir = wp_upload_dir(); // Set upload folder
        if (str_starts_with($image_url, '//')) {
            $image_url = str_replace("//", 'https://', $image_url);
        }
        $image_data = helloprint_get_data_from_url($image_url);// Get image data
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

        // Include image.php
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

        // Assign metadata to attachment
        wp_update_attachment_metadata($attach_id, $attach_data);

        // And finally assign featured image to post
        set_post_thumbnail($post_id, $attach_id);
    }

    public function get_helloprint_product_formatted_price()
    {
        check_ajax_referer('wphp-plugin-nonce');
        wp_send_json_success(wc_price(sanitize_text_field(wp_unslash($_POST['price_amount']))));
    }

    public function get_helloprint_product_details()
    {
        if (isset($_POST['product_id'])) {
            $product_id = sanitize_text_field(wp_unslash($_POST['product_id']));
            $response = (new HelloPrintApiService())->getProductDetailForSelectOptions($product_id);
            wp_send_json_success($response);
        }
    }

    public function helloprint_view_order_details($item_id, $item, $order)
    {
        $product = $item->get_product();
        if (empty($product)) {
            return false;
        }
        $product_upload_file = get_post_meta($product->get_id(), 'helloprint_product_upload_file', true);
        if (empty($product_upload_file)) {
            $product_upload_file = esc_attr(get_option('helloprint_product_upload_file'));
        }

        if(!empty($product_upload_file) && $product_upload_file == "no_option") {
            return false;
        }

        global $wpdb;
        $product_sku = $product->get_sku();
        $line_item_tableName = $wpdb->prefix . 'helloprint_order_line_item_presets';
        $order_preset_tableName = $wpdb->prefix . 'helloprint_order_presets';
        $lineItemPreset = $wpdb->get_results("SELECT * from $line_item_tableName where line_item_id = '$item_id'");
        $sku_Preset = [];
        if (!empty($product_sku)) {
            $sku_Preset = $wpdb->get_results("SELECT * from $order_preset_tableName where helloprint_item_sku = '$product_sku'");
        }
        if ($product->get_type() != "helloprint_product" && empty($lineItemPreset) && empty($sku_Preset)) {
            return false;
        }
        $artworkFileTable = $wpdb->prefix . 'helloprint_order_line_preset_files';
        $artworks = $wpdb->get_results("Select * FROM $artworkFileTable WHERE line_item_id='$item_id'");
        if (empty($artworks)) {
            $order_id = $order->get_id();
            if (!empty($item['helloprint_product_setup'])) {
                $product_setup = json_decode($item['helloprint_product_setup'], true);
                $uploadedFiles = [];
                if (!empty($product_setup['uploaded_files'])) {
                    $uploadedFiles = $product_setup['uploaded_files'];
                }
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = json_decode($uploadedFiles, true);
                }
                foreach ($uploadedFiles as $fileUploads) {
                    if (!empty($fileUploads['file_path'])) {
                        $file_name = (sanitize_text_field(wp_unslash($fileUploads['file_name']))) ?? '';
                        $file_url = (sanitize_text_field(wp_unslash($fileUploads['file_path']))) ?? '';
                        if (!empty($file_name) && !empty($file_url)) {
                            $old_artworks = $wpdb->get_results("Select * FROM $artworkFileTable WHERE order_id='$order_id' and file_url='$file_url'");
                            if (empty($old_artworks)) {
                                $insertQuery = "INSERT INTO $artworkFileTable(order_id,line_item_id,file_url) VALUES('$order_id','$item_id', '$file_url')";
                                $wpdb->query($insertQuery);
                            }
                        }
                    }
                }
                $product_setup['uploaded_files'] = [];
                wc_update_order_item_meta($item_id, 'helloprint_product_setup', json_encode($product_setup));
                $artworks = $wpdb->get_results("Select * FROM $artworkFileTable WHERE line_item_id='$item_id'");
            }
        }
        $data = [
            'order_id' => $order->get_id(),
            'item_id' => $item_id,
            'artworks' => $artworks,
            'submitted_to_helloprint' => $order->get_meta('helloprint_order_status', true),
        ];

        load_template("$this->plugin_path/templates/wp/myaccount-order-details.php", false, $data);
    }

    public function helloprint_save_artwork_of_order()
    {
        check_ajax_referer('wphp-plugin-nonce');
        $files = [];
        $validFileTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif'];
        foreach ($_FILES as $file) {
            if (is_array($file['type']) && count($file['type']) > 0) {
                $exp = $file['type'][0];
            } else {
                $exp = $file['type'];
            }
            $validity = in_array($exp, $validFileTypes);
            if (!$validity) {
                wc_add_notice(wp_kses(_translate_helloprint('Please upload a valid file type for this product. Valid file types are: jpg, jpeg, png, tiff, tif', 'helloprint'), false), 'error');
                return wp_send_json_error([
                    'message' => wp_kses(_translate_helloprint('Please upload a valid file type for this product. Valid file types are: jpg, jpeg, png, tiff, tif', 'helloprint'), false),
                ]);
            }
            $files = $file;
        }
        $uploaded_files = (new FileUploadService())
            ->storeFile($files, $this->plugin_path);

        $order = wc_get_order(sanitize_text_field(wp_unslash($_POST['order_id'])));
        $order_id = $order->get_id();
        $item_id = sanitize_text_field(wp_unslash($_POST['item_id']));
        if (!empty($uploaded_files)) {
            global $wpdb;
            $fileTableName = $wpdb->prefix . 'helloprint_order_line_preset_files';
            foreach ($uploaded_files as $file) {
                $file_url = $file['file_path'];
                $insertQuery = "INSERT INTO $fileTableName(order_id,line_item_id,file_url) VALUES('$order_id','$item_id', '$file_url')";
                $wpdb->query($insertQuery);
            }
        }

        $hp_order_id = "";
        $hp_order_status = get_post_meta($order_id, 'helloprint_order_status', true);
        if (!empty($hp_order_status)) {
            if (!is_array($hp_order_status)) {
                $hp_order_status = json_decode($hp_order_status, true);
            }
            if (!empty($hp_order_status["order_id"])) {
                $hp_order_id = $hp_order_status["order_id"];
            }
        }
        $item = new \WC_Order_Item_Product($item_id);
        $product = wc_get_product($item->get_product_id());
        if (
            empty($hp_order_id) && get_option("helloprint_automatic_send_order", false) && !empty($product) &&
            $product->get_type() === "helloprint_product"
        ) {
            $presetController = new OrderPresetController();
            $presetController->_validate_and_send_hp_order($order_id, $item_id);
        }
        return wp_send_json_success(true);
    }

    public function helloprint_product_attributes_shortcode()
    {
        global $product;
        if (!empty($product) && method_exists($product, "get_type") && $product->get_type() == 'helloprint_product') {
            ob_start();
            $args = (new HelloPrintProductService())->getProductDetailsForRender($product);
            $limit_sku_variant = get_post_meta($product->get_id(), 'helloprint_product_limit_variant_key', true);
            if ($limit_sku_variant) {
                $product_external_id = get_post_meta($product->get_id(), 'helloprint_external_product_id', true);
                $args['product_attributes'] = $this->get_sku_limit_variant($product->get_id(), $product_external_id, $args['product_attributes']);
            }
            include $this->plugin_path . '/templates/wp/product-template.php';
            return ob_get_clean();
        }
        return '';
    }

    public function helloprint_delete_artwork_of_order()
    {
        check_ajax_referer('wphp-plugin-nonce');
        $order = wc_get_order(sanitize_text_field(wp_unslash($_POST['order_id'])));
        $item_id = sanitize_text_field(wp_unslash($_POST['item_id']));
        $id = sanitize_text_field(wp_unslash($_POST['id']));
        global $wpdb;
        $fileTableName = $wpdb->prefix . 'helloprint_order_line_preset_files';
        $wpdb->query("DELETE from $fileTableName where id = '$id'");

        foreach ($order->get_items() as $key => $item) {
            if ($item->get_id() == $item_id) {
                $item = json_decode($item['helloprint_product_setup'], true);
                if (!empty($item['uploaded_files'])) {
                    $item['uploaded_files'] = [];
                    wc_update_order_item_meta($item_id, 'helloprint_product_setup', json_encode($item));
                }
            }
        }

        return wp_send_json_success(true);
    }

    private function get_sku_limit_variant($product_id,$external_product_id,$attributes){ 
        $data = [];
        $product_skus = get_post_meta($product_id, 'helloprint_product_sku', true);
        if(!empty($product_skus)){
            $products = (new HelloPrintApiService())->getAllSKUsByProductKey($external_product_id);
            $product_skus = explode("\n", $product_skus);
            $options =[];
            foreach ($products as $product) {
                $matched = $this->check_if_regex_matched($product_skus, $product);
                if(isset($product['sku']) && $matched == true) {
                    foreach ($product['attributes'] as $key => $value) {
                        $options[$key][] = $value;
                    }
                }
            }
        
            foreach ($attributes as $index => $attribute) {
                $id = $attribute['id'];
                $data[$index] = $attribute;
                $data[$index]['options'] = [];
                $optionData = [];
                foreach ($attribute['options'] as  $key => $option) {
                    if(isset($options[$id]) && is_array($options[$id]) && in_array($key, $options[$id]))
                        $optionData[$key] = $option;               
                }
                $data[$index]['options'] = $optionData;
            }
         }
        return $data;
    }

    public function get_sku_limit_variant_for_selected_options($product_id,$external_product_id,$variants,$attribute_options,$helloprint_attributes)
    {
       
        $product_skus = get_post_meta($product_id, 'helloprint_product_sku', true);
        if(!empty($product_skus)){
            $product_variants = (new HelloPrintApiService())->getAllSKUsByProductKey($external_product_id);
            $product_skus =explode("\n", $product_skus);
            $options =[];
            $helloprint_attributes = array_filter($helloprint_attributes,function($v){
                return !empty($v);
            });
            $helloprint_attributes_keys= array_keys($helloprint_attributes);
            $attrOpts =[];
            
            foreach ($product_variants as $product_variant) {
                $matched = $this->check_if_regex_matched($product_skus, $product_variant);
                if(isset($product_variant['sku']) && $matched == true) {
                    $valid_variant = true;
                    $i=0;
                    
                    foreach($helloprint_attributes  as $key=>$value){
                        $attrOpt = $product_variant['attributes'][$key];
                        $attrOpts[$key][$attrOpt] = $attrOpt;
                        if($key !=='helloprint_options'){
                            if(!(isset($product_variant['attributes'][$key]) && $product_variant['attributes'][$key]==$value)){
                                $valid_variant = false;
                                break;
                            }
                            $i++;
                        }
                    }

                    if ($valid_variant) {
                        foreach ($product_variant['attributes'] as $key => $value) {
                            if(!in_array($key, $helloprint_attributes_keys))
                                $options[$key][$value] = $value;
                        }
                    }
                }
            }
            $variant_data = [];
            foreach ($variants as $variant_key => $variant_value) {
                if(isset($options[$variant_key])){
                    foreach ($variant_value as $key => $value) {
                        if(in_array($key, $options[$variant_key]))
                            $variant_data[$variant_key][$key] = $value;
                    }
                }
            }
            return ['variants' => $variant_data, 'attributeOptions'=>array_merge($options, $attrOpts)];
        }
        return ['variants' => [], 'attributeOptions' => []];
    }

    private function check_if_regex_matched($product_skus, $product)
    {
        if (!isset($product['sku'])) {
            return false;
        }
        if (empty($product_skus) || in_array($product["sku"], $product_skus)) {
            return true;
        }
        foreach ($product_skus as $sku_val) {
            $sku_for_test = $sku_val = trim($sku_val);
            if (strpos($sku_for_test, '/') !== 0) {
                $sku_for_test = "/" . $sku_for_test;
            }
            if (substr($sku_for_test, -1) != "/") {
                $sku_for_test = $sku_for_test . "/";
            }
            if (@preg_match($sku_for_test, $product['sku']) || @preg_match($sku_val, $product['sku'])) {
                return true;
            }
        }
        return false;
    }
}
