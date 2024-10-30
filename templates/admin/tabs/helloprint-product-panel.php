<?php
/**
* PHP view.
*
*/
?>
<div id='helloprint_product_options' class='panel woocommerce_options_panel'>
    <div class='options_group'>
        <h3><?php echo wp_kses(_translate_helloprint('Choose your product type','helloprint'), true); ?></h3>
        <?php  
        $product_attributes = $args['product_attributes'];
        $global_color_icon = get_option( 'helloprint_switch_color_icon' ); ?>
        <?php
        
        helloprint_wp_select(array(
            'id' => 'helloprint_external_product_id',
            'label' => wp_kses(_translate_helloprint('Select Product', 'helloprint'), true),
            'id' => 'helloprint_external_product_id',
            'class' => 'select short wphp-select2',
            'options' => $args['product_options'],
            'value' => $args['product_external_id']
        ));
        ?>
        <?php

        woocommerce_wp_radio(
            [
                "id" => "helloprint_product_margin_markup_option",
                'label' => wp_kses(_translate_helloprint('Product Margin/Markup', 'helloprint'), true),
                'class' => 'select wphp-product-markup-margin-radio',
                "wrapper_class" => "wphp-inline-radio-class",
                "name" => "helloprint_markup_margin",
                'options' => [
                    "margin" => wp_kses(_translate_helloprint('Use Margin','helloprint'), true),
                    "markup" => wp_kses(_translate_helloprint('Use Markup','helloprint'), true)
                ],
                'value' => $args['hp_product_markup_margin_option'],
            ]
        );
        ?>

        <div id="wphp-product-add-edit-margin-div" class="wphp-parent-markup-margin-div" >
            <?php 

                helloprint_wp_select(array(
                'id' => 'helloprint_product_margin_option',
                'label' => wp_kses(_translate_helloprint('Product Margin Option', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => $args['product_global_or_individual'],
                'value' => $args['helloprint_product_margin_option'],
            ));
            ?>

            <?php
            helloprint_wp_select(array(
                'id' => 'helloprint_user_roles_for_price',
                'label' => wp_kses(_translate_helloprint('User Role For Margin', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => $args['hp_user_roles'],
                'value' => $args['hp_selected_user_role_pricing']
            ));
            ?>
            <?php
            helloprint_wp_select(array(
                'id' => 'helloprint_pricing_tier',
                'label' => wp_kses(_translate_helloprint('Pricing Tier', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => $args['all_pricing_tiers'],
                'value' => $args['hp_selected_pricing_tier']
            ));
            ?>

            <?php
            woocommerce_wp_text_input(array(
                'id' => 'helloprint_product_margin',
                'type' => 'number',
                'label' => wp_kses(_translate_helloprint('Product margin (%)', 'helloprint'), true),
                'custom_attributes' => array(
                    'min' => 0,
                    'max' => 99
                ),
                'value' => $args['product_margin'],
            ));
            ?>
        </div>

        <div id="wphp-product-add-edit-markup-div" class="wphp-parent-markup-margin-div">
            <?php 

                helloprint_wp_select(array(
                'id' => 'helloprint_product_markup_option',
                'label' => wp_kses(_translate_helloprint('Product Markup Option', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => $args['global_or_individual_options_markup'],
                'value' => $args['helloprint_product_markup_option'],
            ));
            ?>

            <?php
            helloprint_wp_select(array(
                'id' => 'helloprint_user_roles_for_price_markup',
                'label' => wp_kses(_translate_helloprint('User Role For Markup', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => $args['hp_user_roles'],
                'value' => $args['hp_selected_user_role_pricing']
            ));
            ?>
            <?php
            helloprint_wp_select(array(
                'id' => 'helloprint_pricing_tier_markup',
                'label' => wp_kses(_translate_helloprint('Pricing Tier', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => $args['all_pricing_tiers_markups'],
                'value' => $args['hp_selected_pricing_tier']
            ));
            ?>

            <?php
            woocommerce_wp_text_input(array(
                'id' => 'helloprint_product_markup',
                'type' => 'number',
                'label' => wp_kses(_translate_helloprint('Product markup (%)', 'helloprint'), true),
                'custom_attributes' => array(
                    'min' => 0
                ),
                'value' => $args['product_markup'],
            ));
            ?>
        </div>

    <?php
    
    helloprint_wp_select(array(
        'id' => 'helloprint_product_upload_file',
        'label' => wp_kses(_translate_helloprint('Product Upload File', 'helloprint'), true),
        'class' => 'select short wphp-select2',
        'options' => $args['product_upload_file_options'],
        'value' => $args['helloprint_product_upload_file']
    ));
    ?>
    <div class="<?php if(isset($size_exists) && !$size_exists):?>hidden<?php endif;?> helloprint_product_show_icon_field_div">
        <?php
        helloprint_wp_select(array(
            'id' => 'helloprint_product_show_icon',
            'label' => wp_kses(_translate_helloprint('Show Size Images', 'helloprint'), true),
            'class' => 'select short wphp-select2',
            'options' => $args['product_show_icon_options'],
            'value' => $args['helloprint_product_show_icon']
        ));
        ?>
    </div>
    <?php
    helloprint_wp_select(array(
      'id' => 'helloprint_product_graphic_design_fee',
      'label' => wp_kses(_translate_helloprint('Graphic Design Fee', 'helloprint'), true),
      'class' => 'select short wphp-select2',
      'options' => $args['product_graphic_design_fee_options'],
      'value' => $args['helloprint_product_graphic_design_fee'],
  ));
  ?>
  <?php
  woocommerce_wp_text_input(array(
    'id' => 'helloprint_product_graphic_design_price',
    'type' => 'number',
    'custom_attributes' => array(
        'min' => 0
    ),
    'label' => wp_kses(_translate_helloprint('Graphic Design Price', 'helloprint'), true),
    'value' => $args['helloprint_product_graphic_design_price'],
));
?>
<?php
if (isset($product_attributes) && is_array($product_attributes)) {
    foreach($product_attributes as $key => $attribute){
        if ('colours' === $attribute['id']) {
            helloprint_wp_select(array(
                'id' => 'helloprint_switch_color_icon',
                'label' => wp_kses(_translate_helloprint('Show Color Images', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => [
                    '' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                    'yes' => wp_kses(_translate_helloprint("Enable", 'helloprint'), false),
                    'no' => wp_kses(_translate_helloprint("Disable", 'helloprint'), false),
                ],
                'value' => $args['helloprint_switch_color_icon']
            ));
        }
        if ('printposition' === $attribute['id']) {
            helloprint_wp_select(array(
                'id' => 'helloprint_switch_print_position_icon',
                'label' => wp_kses(_translate_helloprint('Show Print Position Images', 'helloprint'), true),
                'class' => 'select short wphp-select2',
                'options' => [
                    '' => wp_kses(_translate_helloprint("Same as Global Setting", 'helloprint'), false),
                    'yes' => wp_kses(_translate_helloprint("Enable", 'helloprint'), false),
                    'no' => wp_kses(_translate_helloprint("Disable", 'helloprint'), false),
                ],
                'value' => $args['helloprint_switch_print_position_icon']
            ));
        }
    }
}

?>
 <?php
  helloprint_wp_select(array(
    'id' => 'helloprint_product_limit_variant_key',
    'label' => esc_attr(_translate_helloprint('Limit Variant Keys', 'helloprint')),
    'class' => 'select short wphp-select2',
    'options' => $args['product_limit_variant_key_options'],
    'value' => $args['helloprint_product_limit_variant_key']
));                   
?>

<?php
    $skuStyle="";
    if($args['helloprint_product_limit_variant_key']!='1'){
        $skuStyle="display:none";
    }

?>
<div id="helloprint_product_sku_div" style="<?php echo $skuStyle?>;">
 <?php
  woocommerce_wp_textarea_input(array(
    'id' => 'helloprint_product_sku',
    'label' => esc_attr(_translate_helloprint('Each SKU on new line', 'helloprint')),
    'value' => $args['helloprint_product_sku'],
    
));
?>
</div>
</div>
</div>