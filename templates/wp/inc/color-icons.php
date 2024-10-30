<div class="wphp-color-sec wphp-product-selector wphp-img-selector">
    <label for="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>"><?php echo wp_kses(_translate_helloprint(esc_attr($attribute['name']), 'helloprint'), true) ?>
        <span class="wphp-spinner wphp-display-none" id ="wphp-colours-spinner"></span>
    </label>
    <div class="wphp-grids">
        <?php foreach ($attribute['options'] as $option_key => $option) : ?>

            <?php if(!empty($option['image'])) :
                $has_colour_icon_switch = true;
                ?>
                <a class="wphp-color 
                    <?php 
                        if (
                            isset(
                                $_GET[esc_attr($attribute['id'])]
                            ) && sanitize_text_field(wp_unslash($_GET[$attribute['id']])) == $option_key
                        ){
                            printf('selected');
                        } else {
                            echo '';
                        }
                    ?>">
                    <img src="<?php echo esc_url($option['image']); ?>" alt="">
                    <p class="unitColor wphp-text-center"><?php echo esc_html($option['subText']) ?></p>
                    <small class="actualColor wphp-text-center"><?php echo wp_kses(_translate_helloprint($option['name'], 'helloprint'), true)?> </small>
                    <input class="wphp-colorRadio"
                        type="radio"
                        value="<?php echo esc_attr($option_key) ?>"
                        name="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>"
                        id="wphp_product_option_<?php echo esc_attr($attribute['id']) ?>"
                        data-select-id="<?php echo esc_attr($attribute['id']) ?>"
                        data-label="<?php echo wp_kses(_translate_helloprint($attribute['name'], 'helloprint'), false)?>"
                        data-position="<?php echo esc_attr($data_position) ?>"
                        data-text="<?php echo esc_attr($option['name']); ?>"
                        <?php 
                            if(isset($_GET[esc_attr($attribute['id'])])){
                                sanitize_text_field(wp_unslash($_GET[$attribute['id']])) == $option_key ? printf('checked') : '' ;
                            }
                        ?> >
                </a>
        <?php   
        endif; ?>
        <?php endforeach; 
        $show_color_icons = ($has_colour_icon_switch == false) ? false : $show_color_icons;
        ?>
    </div>
    <span id="wphp-scrollend-color"></span>
</div>