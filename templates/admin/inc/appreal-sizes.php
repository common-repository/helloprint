<th scope="row" >
    <?php echo wp_kses(_translate_helloprint('APPREAL SIZES', "helloprint"), true) ?>
</th>
<td>
    <input type="hidden" id="helloprint_hidden_preset_one_appreal_msg" value="<?php echo wp_kses(_translate_helloprint("Please enter at least one appreal size", "helloprint"), false);?>" />
    <?php foreach ($args["available_options"] as $key => $option):
         $option = (array) $option;
         if (esc_attr($option['code']) === 'apparelSize') : 
    ?>
        <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php echo wp_kses(_translate_helloprint('Sizes', "helloprint"), true) ?>
                        </th>
                        <?php if (esc_attr($option['isUnisex']) == true) : ?>
                        <th scope="row"><?php echo wp_kses(_translate_helloprint('Quantity', 'helloprint'), true) ?></th>
                        <?php else : ?>
                            <th scope="row"><?php echo wp_kses(_translate_helloprint('Men', 'helloprint'), true) ?></th>
                            <th scope="row"><?php echo wp_kses(_translate_helloprint('Women', 'helloprint'), true) ?></th>
                        <?php endif ?>
                    </tr>
                    <?php foreach ($option['value'] as $key => $value) : ?>
                    <tr>
                        <th>
                            <?php echo wp_kses(_translate_helloprint(esc_attr($value), 'helloprint'), true) ?>
                        </th>
                        <?php if(esc_attr($option['isUnisex']) == true):?>
                            <td>
                            <input class="regular-text helloprint_custom_option_input_preset_field helloprint_preset_appreal_size" data-name="appreal_sizes" data-keytype="quantity" data-keyvalue="<?php echo esc_attr($value);?>" id="helloprint_preset_quantity_<?php echo esc_attr($value);?>" name="appreal_sizes[quantity][<?php echo esc_attr($value);?>]" type="number" value="<?php echo !empty($args["default_options"]["quantity"][esc_attr($value)]) ? $args["default_options"]["quantity"][esc_attr($value)] : 0;?>" min="0"  >
                            </td>
                        <?php else: ?>
                            <td>
                            <input class="regular-text helloprint_custom_option_input_preset_field helloprint_preset_appreal_size" data-name="appreal_sizes" data-keytype="men" data-keyvalue="<?php echo esc_attr($value);?>" id="helloprint_preset_men_<?php echo esc_attr($value);?>" name="appreal_sizes[men][<?php echo esc_attr($value);?>]" type="number" value="<?php echo !empty($args["default_options"]["men"][esc_attr($value)]) ? $args["default_options"]["men"][esc_attr($value)] : 0;?>" min="0"  >
                            
                            </td>
                            <td>
                            <input class="regular-text helloprint_custom_option_input_preset_field helloprint_preset_appreal_size" data-name="appreal_sizes" data-keytype="women" data-keyvalue="<?php echo esc_attr($value);?>" id="helloprint_preset_women_<?php echo esc_attr($value);?>" name="appreal_sizes[women][<?php echo esc_attr($value);?>]" type="number" value="<?php echo !empty($args["default_options"]["women"][esc_attr($value)]) ? $args["default_options"]["women"][esc_attr($value)] : 0;?>" min="0"  >
                            </td>
                        <?php endif;?>

                    </tr>

                    <?php endforeach;?>
                
                </tbody>
        </table>
    <?php 
        endif;
        endforeach;
    ?>
</td>