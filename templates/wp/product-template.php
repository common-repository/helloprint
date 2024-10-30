<?php
/**
* PHP view.
*
*/
?>
<script type="text/javascript">
    var pluginUrl = '<?php echo esc_url(plugin_dir_url(__DIR__)); ?>';
</script>
<?php
if (!defined('ABSPATH')) {
    exit;
}
global $product;
do_action('gift_card_before_add_to_cart_form');
$counter = 1; 
$data_position = 0; 
$hp_option_iteration = 0;
$enable_color_icon = get_post_meta( $product->get_id() , 'helloprint_switch_color_icon', true);
$enable_icon = get_post_meta( $product->get_id() , 'helloprint_switch_icon' , true);
$show_color_icons = isset($args['product_show_color_icon']) && (('true' == $args['product_show_color_icon']) || ('1' == $args['product_show_color_icon'])  || ('yes' == $args['product_show_color_icon']));
$show_print_position_icons = isset($args['product_show_print_position_icon']) && ('true' == $args['product_show_print_position_icon'] || '1' == $args['product_show_print_position_icon'] || 'yes' == $args['product_show_print_position_icon']);
$hp_product_available = isset($args['helloprint_available']) ? $args['helloprint_available'] : false;
$destination_countries = isset($args['destination_countries']) ? $args['destination_countries'] : [];
?>
<div class="wphp-product-template">
    <?php if(!$hp_product_available) : 
        include("inc/not-available.php");
    else: ?>
    <form class="wphp-product-cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
        <div cellspacing="0">
            <div>
                <?php 
                if(!empty($destination_countries)) {
                    include("inc/destinations.php");
                }
                if (!empty($args['product_attributes'])):?>
                <legend><?php echo wp_kses(_translate_helloprint('CHOOSE YOUR OPTIONS', 'helloprint'), true);?></legend>
                <?php endif;?>
                <?php 
                    $has_apreal_size = false;
                    if( array_search('apparelSize', array_column($args['product_options'], 'code')) !== false ){
                        $has_apreal_size = true;
                    }
                    $switch_icon = $args['switch_icon'];
                    $show_only_incl = get_option("helloprint_show_prices_incl_vat_only");
                    $enable_product_design = $args['enable_product_design'];
                    $graphic_design_price = $args['graphic_design_price'];

                    if($switch_icon || $show_color_icons || $show_print_position_icons) :
                ?>
                <!-- Image Options ------------------------------------------------- -->
                <?php foreach ($args['product_attributes'] as $key => $attribute)  : ?>
                    <?php 
                    
                    if ($switch_icon):
                        if ('size' === $attribute['id']) :
                            $has_icon_switch = false;
                            include("inc/size-icons.php");
                            ?>
                            <?php $data_position++ ?>
                        <?php endif;
                    endif;
                    ?>
                    <?php  
                    
                    if($show_color_icons) : ?>
                        <?php if('colours' == $attribute['id']) :
                            $has_colour_icon_switch = false;
                            include("inc/color-icons.php");
                            ?>
                            <?php $data_position++ ?>
                        <?php endif ?>
                    <?php endif ?>

                    <?php  
                    
                    if($show_print_position_icons) : ?>
                        <?php if('printposition' == $attribute['id']) :
                            $has_print_position_icon = false;
                            include("inc/print-icons.php");
                            ?>
                            <?php $data_position++ ?>
                        <?php endif ?>
                    <?php endif ?>
                <?php endforeach ?>
                <?php endif ?>


                <?php if($switch_icon || !$args['size_exists']){
                    include("inc/custom-size.php");
                }?>

                
                <!-- Other Options ------------------------------------------------- -->
                <div class="wphp-grid wphp-grid-opt">                        
                    <?php 
                        foreach ($args['product_attributes'] as $key => $attribute)  : 
                          
                        ?>
                        <?php 
                        if(
                            ("size" == $attribute['id'] && !$switch_icon) ||
                            ("colours" == $attribute['id'] && !$show_color_icons) || 
                            ("printposition" == $attribute['id'] && !$show_print_position_icons) ||
                            ("colours" != $attribute['id'] && "size" != $attribute['id'] && "printposition" != $attribute['id']) 
                            
                        ) :?>
                            <div>
                                <label for="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>"><?php echo wp_kses(_translate_helloprint(esc_attr($attribute['name']), 'helloprint'), true) ?><span class="wphp-spinner wphp-display-none" id ="wphp-<?php echo esc_attr($attribute['id']) ?>-spinner"></span></label>
                                <select class="wphp-product-option-selector wphp-product-selector
                                wphp-product-option-<?php echo esc_attr($hp_option_iteration);?> wphp-options" data-iteration="<?php echo esc_attr($hp_option_iteration);?>" name="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>" id="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>" data-select-id="<?php echo esc_attr($attribute['id']) ?>" data-label="<?php echo esc_attr(_translate_helloprint($attribute['name'], 'helloprint'))?>" data-position="<?php echo esc_attr($data_position) ?>">
                                    <option value="0"><?php echo wp_kses(_translate_helloprint('Select One', 'helloprint'), false) ?></option>
                                    <?php foreach ($attribute['options'] as $option_key => $option) :  ?>
                                        <?php if("size" == $attribute['id'] && "" != $option['image']) :?>
                                            <option value="<?php echo esc_attr($option_key) ?>"
                                            <?php 
                                                if(isset($_GET[esc_attr($attribute['id'])])){
                                                    sanitize_text_field(wp_unslash($_GET[$attribute['id']])) == $option_key ? printf('selected') : ''; 
                                                }
                                            ?>>
                                            <?php echo wp_kses(_translate_helloprint($option['name'], 'helloprint'), false)?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?php echo esc_attr($option_key); ?>"
                                            <?php 
                                                if(isset($_GET[$attribute['id']])){
                                                    sanitize_text_field(wp_unslash($_GET[$attribute['id']])) == $option_key ? printf('selected') : ''; 
                                                }
                                            ?>>
                                            <?php echo wp_kses(_translate_helloprint($option['name'], 'helloprint'), false)?>
                                            </option>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <?php $hp_option_iteration++; $data_position++ ?>
                        <?php endif ?>
                        <?php 
                            if ("size" == $attribute['id'] && !$switch_icon)
                            {
                                include("inc/custom-size.php");
                            }
                        ?>
                    <?php endforeach ?>
                </div>
                <!-- Quantity Loader ---------------------------------------------- -->
                <div class="wphp-loader wphp-display-none">
                    <?php if (!empty($args['product_attributes'])):?>
                    <hr>
                    <?php endif;?>
                    <?php 
                        $hp_loader_url = !empty(get_option("helloprint_override_icon_url")) ? get_option("helloprint_override_icon_url") : plugin_dir_url( __DIR__ ) . 'images/loader.gif';
                        
                    ?>
                    <img src="<?php echo esc_html($hp_loader_url); ?>">
                </div>
                <div class="wphp-combination-not-found wphp-display-none">
                    <?php if (!empty($args['product_attributes'])):?>
                        <hr>
                    <?php endif;?>
                    <div class="wphp-combination-not-found-message"></div>
                </div>
                <!-- Quantity Opt ------------------------------------------------- -->
                <div class="wphp-quantity-sec wphp-display-none">
                    <?php if (!$has_apreal_size && !empty($args['product_attributes'])):?>
                    <hr>
                    <?php endif;?>
                    <div <?php if($has_apreal_size) echo 'class="wphp-display-none"';?> id="wphp-product-quantity-wrapper">
                        <label for="wphp_product_quantity"><?php echo wp_kses(_translate_helloprint('Quantity', 'helloprint'), true) ?></label>
                        <p>
                            <?php echo wp_kses(_translate_helloprint('Prices shown are per one design. For multiple designs, add multiple items to your cart. In order to keep our prices low, we offer standard quantities. Looking for a different quantity? Request a quote.', 'helloprint'), true) ?>
                        </p>

                        <div class="wphp-quantity-wrp less">
                            <div class="wphp-quantity-grp"></div>
                            <a class="wphp-showmore wphp-show-more-pricing-btn"><?php echo wp_kses(_translate_helloprint('Show more', 'helloprint'), true) ?></a>
                            <a class="wphp-showless wphp-show-less-pricing-btn"><?php echo wp_kses(_translate_helloprint('Show less', 'helloprint'), true) ?></a>
                            <input type="hidden" id="wphp-hidden-current-pricing-page" value="1" />
                            <div  class="wphp-pricing-table-loader wphp-display-none">
                                <?php 
                                    $hp_loader_url = !empty(get_option("helloprint_override_icon_url")) ? get_option("helloprint_override_icon_url") : plugin_dir_url( __DIR__ ) . 'images/loader.gif';    
                                ?>
                                <img src="<?php echo esc_html($hp_loader_url); ?>">
                            </div>
                        </div>

                    </div>

                    <!-- Size Quantity Options ------------------------------------------------- -->
                    <?php 
                    if ( true == $has_apreal_size ) {
                        include("inc/size-selector.php");
                    }?>
                    <!-- Add Design Artwork ------------------------------------------------- -->
                    <?php if($enable_product_design) : ?>
                        <div class="wphp-add-design">
                            <hr>
                            <label><?php echo wp_kses(_translate_helloprint('Would you like us to design your artwork for this product?', 'helloprint'), true) ?>*</label>
                            <div>
                                <input type='hidden' id="wphp-design-price-hidden" value="<?php echo esc_attr($graphic_design_price) ?>" />
                                <input  class="wphp_design" type="radio" name="wphp_design" value="<?php echo esc_attr($graphic_design_price) ?>">
                                <label for="wphp_design"><?php echo wp_kses(_translate_helloprint('Yes please', 'helloprint'), true) ?> (<span class="wphp-design-price-label"><?php echo wc_price($graphic_design_price) ?></span><span class="wphp-design-incl-tax-price-label"></span>)</label>
                                </span>
                            </div>
                            <div>
                                <input checked class="wphp_design" type="radio" name="wphp_design" value="0">
                                <label for="wphp_design"><?php echo wp_kses(_translate_helloprint("No thank you, i'll supply print ready artwork.", 'helloprint'), true) ?>(<span class="wphp-no-design-price-label"><?php echo wc_price(0) ?></span>)</label>
                                </span>
                            </div>
                            <hr>
                        </div>
                    <?php endif ?>  

                    <div class="wphp-delivery-time" <?php ( !$enable_product_design ) ? printf("style='margin-top:15px'") : "" ?>s >
                        <label for="wphp_service_level"><?php echo wp_kses(_translate_helloprint('Delivery Time', 'helloprint'), true) ?></label>
                        <select name="wphp_service_level" id="wphp_service_level" class="wphp-width-100 wphp-options">
                        </select>
                    </div>
                </div>
     
                 <!-- UPload Files ------------------------------------------------- -->
                <?php if($args['can_upload_file']): ?>
                  <div class="wphp-product-file-upload wphp-display-none">
                      <hr>
                      <div>
                          <legend> 
                            <?php echo wp_kses(_translate_helloprint('UPLOAD YOUR FILES', 'helloprint'), true) ?>
                            <img class="wphp-info-icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAACAUlEQVQ4jZ2TzWsTURTFz3tJZjKYj5k2kzitFbsopVboUvwEpdadoiDZGP+IQmmhpQRsoRH9H6QWtEhp9wWrRChupWJ10SC0mSYzzcxE0ySTZJ6LEj8yg1XP9v7Oefde7iPwUHJmfYgPCff9wUACAOxao1CtVZ6tpG9ud7LkN+NCdiQS5DOyLF8QRTFC6FGZOQyGZVqapm1WjPLk8/Toe1dAKvPmrizHnyiKcsarq7bUvJor6dr408mraz8CkgvZkd54bO0488+Q/VxuX721On1tiwJAhOcfe5nHhgWMDQuuAKXnZH93JJwBAF/y4fpQb1/frCAIQRdJCIxDB0bFcZUoEFfO3l6lHCekpKgY7QQEjuB0lw9ymHqOIUliNBjmU5TjAon2tn9V1WY41eXHQCLgGUAIBcf5ExSAu7+/FGOM0Ua9UWQO+w+zg0azVaD1b9UlwzKtfw0wSqZZK9eX6PLcjY/FQvFdJyBwBLulJnZLTQice0fagf725dz1T34AOLS+Tqh5dUDpUfrbQNVm2Niueb6e31N3DMueAgAfAGy9XiwMXkp+abXY+XA4JP2p9fyeunOga+Mvpq9kgY7PdGd+41zsROhRTO6+KElilJCjG2DMgVEyzaKub5ple2Jl9vKHtsc9HIB7M68G+RD3gOcDcQCwbbtgV+uLy+nRz53sdyf/vzp4Zoe6AAAAAElFTkSuQmCC">
                           </legend>
                           <div id="wphp_product_file_upload"></div>
                          <input type="file" class="wphp_product_file_upload" name="wphp_product_file_upload[]" multiple="multiple">
                      </div>
                      <?php include("inc/pitchprint.php"); ?>
                  </div>
                  <div id="wphp-pop-up-modal" class="wphp-modal-container">
                    <div class="wphp-modal">
                      <button class="wphp-close-button">X</button>
                      <table class="wphp-template-table">
                        <thead>
                          <tr class="wphp-cta-head">
                            <td class="wphp-format-main"><?php echo wp_kses(_translate_helloprint('File Name', 'helloprint'), true) ?></td>
                            <td class="wphp-cta-pdf"><?php echo wp_kses(_translate_helloprint('PDF', 'helloprint'), true) ?></td>
                            <td class="wphp-cta-indesign"><?php echo wp_kses(_translate_helloprint('INDD', 'helloprint'), true) ?></td>
                          </tr> 
                        </thead>
                        <tbody>
                        <?php if(!empty($args['all_templates'])) :
                            foreach($args['all_templates'] as $temp):
                            ?> 
                          <tr class="wphp-cta-item">
                            <td class="wphp-format-main"><?php echo esc_html($temp['name']);?></td>
                            <td class="wphp-cta-pdf">
                                <?php if(!empty($temp['pdf'])) :?>
                                <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($temp['pdf']['url']);?>">
                                    <?php echo wp_kses(_translate_helloprint('PDF', 'helloprint'), true) ?>
                                </a>
                                <?php endif;?>
                            </td>
                            <td class="wphp-cta-indesign">
                                <?php if(!empty($temp['indd'])) :?>
                                <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($temp['indd']['url']);?>">
                                    <?php echo wp_kses(_translate_helloprint('Adobe Indesign', 'helloprint'), true) ?>
                                </a>
                                <?php endif;?>
                            </td>
                          </tr> 
                        <?php endforeach;
                        else :?>
                        <tr class="wphp-cta-item">
                            <td colspan="3">
                                <?php echo wp_kses(_translate_helloprint('Template Not Found', 'helloprint'), true) ?>
                            </td>
                        </tr>

                        <?php endif;?>
                        </tbody>
                      </table>
                    </div>
                  </div>

                <?php endif;?>

                <hr>

                <!-- Review & Add to Cart ------------------------------------------------- -->
                <legend><?php echo wp_kses(_translate_helloprint('REVIEW & ADD TO CART', 'helloprint'), true);?></legend>
                
                <?php if($args['taxable']):?>
                    <?php 
                    if(!$show_only_incl):?>
                    <div class="wphp-grid">
                        <div>
                            <label for="wphp_product_price_exclude_tax"><?php echo wp_kses(_translate_helloprint('Price Excluding Tax', 'helloprint'), true) ?>
                                <!-- <span class="wphp_product_currency"></span> -->
                            </label>
                        </div>
                        <div align="right">
                            <span class="wphp_product_price_exclude_tax"></span>
                            <span class="wphp_product_graphic_service_price_excl_tax"></span>
                            <span class="wphp_product_original_price_excl_tax"></span>
                            <span class="wphp_product_design_artwork_excl_price"></span>
                        </div>
                    </div>
                    <?php endif ?>
                <?php endif ?>
                <div class="wphp-grid">
                    <div><label for="wphp_product_price">                        
                        <?php if($args['taxable']) :?>
                            <?php echo wp_kses(_translate_helloprint('Price Including Tax', 'helloprint'), true) ?>
                        <?php else: ?>
                            <?php echo wp_kses(_translate_helloprint('Price Excluding Tax', 'helloprint'), true) ?>
                        <?php endif ?>
                            <!-- <span class="wphp_product_currency"></span> -->
                        </label>
                    </div>
                    <div align="right">
                        <span class="wphp_product_price"></span>
                        <span class="wphp_product_price_including_graphic_service"></span>
                        <span class="wphp_product_original_price"></span>
                        <span class="wphp_product_design_artwork_price"></span>
                    </div>
                </div>
                <div style="display:none;" class="wphp-grid">
                    <div>
                        <label for="wphp_product_variant_key">
                            <?php echo wp_kses(_translate_helloprint('Variant Key', 'helloprint'), true) ?>
                        </label>
                    </div>
                    <div align="right">
                        <span class="wphp_product_variant_key"></span>
                    </div>
                </div>
                <!-- this can be removed later once verified starts here-->
                <div id="hidden_only_for_testing_margin" style="display:none;">
                <h4><?php echo wp_kses(_translate_helloprint('Without Margin', 'helloprint'), true) ?></h4>
                <div class="wphp-grid">
                    <div><label for="wphp_product_price_without_margin"><?php echo wp_kses(_translate_helloprint('Price Including Tax', 'helloprint'), true) ?>
                            <!-- <span class="wphp_product_currency"></span> -->
                        </label>
                    </div>
                    <div align="right">
                        <span class="wphp_product_price_without_margin"></span>
                    </div>
                </div>
                <div class="wphp-grid">
                    <div>
                        <label for="wphp_product_price_exclude_tax_without_margin"><?php echo wp_kses(_translate_helloprint('Price Excluding Tax', 'helloprint'), true) ?>
                            <!-- <span class="wphp_product_currency"></span> -->
                        </label>
                    </div>
                    <div align="right">
                        <span class="wphp_product_price_exclude_tax_without_margin"></span>
                    </div>
                </div>
                </div>
                <!-- this can be removed later once verified ends here -->

            </div>
                <input type="hidden" id="wphp_select_one" value='<?php echo wp_strip_all_tags(wp_kses(_translate_helloprint('Select One', 'helloprint'), false));?>' />
                <input type="hidden" name="product_id" id="product_id" value="<?php echo esc_attr($product->get_id()) ?>">
                <input type="hidden" name="hello_product_sku" id="hello_product_sku" value="">
                <input type="hidden" name="hello_product_variant_key" id="hello_product_variant_key" value="">
                <input type="hidden" name="wphp_tax_incl" id="wphp_tax_incl" value="<?php echo esc_attr(wc_prices_include_tax())?>">
                <input type="hidden" name="wphp_product_price" id="wphp_product_price_input">
                <input type="hidden" name="wphp_product_incl_tax_price" id="wphp_product_incl_tax_price_input">
                <input type="hidden" name="wphp_product_excl_tax_price" id="wphp_product_excl_tax_price_input">
                <input type="hidden" name="wphp_product_tax_rate" id="wphp_product_tax_rate_input">
                <input type="hidden" name="wphp_product_show_only_incl_vat" id="wphp_product_show_only_incl_vat_input" value="<?php echo (1 == $show_only_incl) ? 1 : 0; ?>">
                <input type="hidden" name="wphp_external_product_id" id="wphp_external_product_id" value="<?php echo esc_attr($args['product_external_id']) ?>">
                <input type="hidden" name="wphp_product_options" id="wphp_product_options">

                <input type="hidden" name="wphp_product_options_labels" id="wphp_product_options_labels">

                <input type="hidden" id="wphp_currency" />
                <input type="hidden" id="wphp_country" />
                <input type="hidden" id="wphp_thousand_separator" />

                <input name="wphp_total_delivery_days" type="hidden" id="wphp_total_delivery_days_including_buffer" />
  

        </div>
        <button data-testmode="<?php echo esc_attr(get_option("helloprint_env_mode")); ?>" type="submit" disabled="disabled" id="wphp-add-to-cart-button" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo wp_kses(_translate_helloprint($product->single_add_to_cart_text(), 'helloprint'), true); ?></button>
    </form>
<?php endif;?>
</div>
<?php do_action('gift_card_after_add_to_cart_form'); ?>
