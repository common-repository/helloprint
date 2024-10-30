<div class="wphp-print-position-sec wphp-product-selector wphp-img-selector">
    <label for="wphp_product_option_<?php echo $attribute['id'] ?>"><?php echo wp_kses(_translate_helloprint(esc_attr($attribute['name']), 'helloprint'), true) ?>
    <span class="wphp-spinner wphp-display-none" id ="wphp-printposition-spinner"></span>
    </label>       
    <div class="wphp-grids">
        <?php foreach ($attribute['options'] as $option_key => $option) : ?>
            <?php if(!empty($option['image'])) :
                $has_print_position_icon = true;
                ?>
                <a class="wphp-print-position 
                    <?php 
                        if(isset($_GET[esc_attr($attribute['id'])])){
                            sanitize_text_field(wp_unslash($_GET[$attribute['id']])) == $option_key ? printf('selected') : '' ;
                        }
                    ?>" >
                    <img src="<?php echo esc_url($option['image']) ?>" alt="">
                    <p class="unitPrintPosition wphp-text-center"><?php echo esc_attr($option['subText']) ?></p>
                    <small class="actualPrintPosition wphp-text-center"><?php echo wp_kses(_translate_helloprint($option['name'], 'helloprint'), true);?> </small>
                    <input class="wphp-printPositionRadio"
                        type="radio"
                        value="<?php echo esc_attr($option_key) ?>"
                        name="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>"
                        id="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>"
                        data-select-id="<?php echo esc_attr($attribute['id']) ?>"
                        data-label="<?php echo wp_kses(_translate_helloprint($attribute['name'], 'helloprint'), false)?>"
                        data-position="<?php echo esc_attr($data_position); ?>"
                        data-text="<?php echo esc_attr($option['name']) ?>"
                        <?php 
                            if(isset($_GET[$attribute['id']])) {
                                sanitize_text_field(wp_unslash($_GET[$attribute['id']])) == $option_key ? printf('checked') : '' ;
                            }
                        ?> >
                </a>
        <?php  
        endif; ?>
        <?php endforeach; 
        $show_print_position_icons = ($has_print_position_icon == false) ? false : $show_print_position_icons;
        ?>
    </div>
    <span id="wphp-scrollend-printposition"></span>
</div>