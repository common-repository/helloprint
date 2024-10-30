<?php


namespace HelloPrint\Inc\Services;


class HelloPrintProductService
{
    public function getProductDetailsForRender($product)
    {
        $product_external_id = get_post_meta($product->get_id(), 'helloprint_external_product_id', true);
            $apiService = (new HelloPrintApiService());
            $response = $apiService->getProductDetailForSelectOptions($product_external_id);
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

            $size_exists = (array_search('size', array_column($response['attributes'], 'id')) !== false);

            $product_show_color_icon = get_post_meta($product->get_id(), 'helloprint_switch_color_icon', true);
            if (empty($product_show_color_icon)) {
                $product_show_color_icon = esc_attr(get_option('helloprint_switch_color_icon'));
            }
            $available = isset($response['available']) ? $response['available'] : false;
            $taxable = false;
            if(wc_tax_enabled() && $product->get_tax_status() == 'taxable'){
                $taxable = true;
            }
            $product_show_print_position_icon = get_post_meta($product->get_id(), 'helloprint_switch_print_position_icon', true);
            if (empty($product_show_print_position_icon)) {
                $product_show_print_position_icon = esc_attr(get_option('helloprint_switch_print_position_icon'));
            }
            return [
                'product_attributes' => $response['attributes'],
                'product_options' => !empty($response['options']) ? $response['options'] : [],
                'product_external_id' => $product_external_id,
                'can_upload_file' => $can_upload_file,
                'enable_product_design' => $enable_product_design,
                'graphic_design_price' => (float)$product_graphic_design_price,
                'size_exists' => $size_exists,
                'all_templates' => $all_templates,
                'product_show_color_icon' => $product_show_color_icon,
                'product_show_print_position_icon' => $product_show_print_position_icon,
                'switch_icon'=> $switch_icon,
                'helloprint_available' => $available,
                'destination_countries' => ($response['destination_countries']) ?? [],
                'taxable' => $taxable
            ];
    }
}