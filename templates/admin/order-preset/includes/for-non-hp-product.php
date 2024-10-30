<tr class="example-class wphp-preset-tr-for-non-hp">
                <th scope="row">
                    <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Item SKU', "helloprint"), true) ?></label>
                </th>
                <td>
                    <div class="example-class">
                     <input type="text" class="regular-text" id="helloprint_item_sku" name="helloprint_item_sku" value="<?php echo !empty($helloprint_item_sku) ? esc_attr($helloprint_item_sku) : '';?>">
                 </div>
             </td>
         </tr>
         <tr class="example-class wphp-preset-tr-for-non-hp">
            <th scope="row">
                <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Helloprint Variant Key*', "helloprint"), true) ?></label>
            </th>
            <td>
                <div class="example-class">
                 <input type="text" class="regular-text" id="helloprint_order_preset_variant_key" name="helloprint_variant_key" value="<?php echo ($product_type != "hp" && !empty($helloprint_variant_key)) ? esc_attr($helloprint_variant_key) : '';?>" required="required" >
             </div>
         </td>
     </tr>
     <tr class="example-class wphp-preset-tr-for-non-hp">
        <th scope="row">
            <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Default Service Level', "helloprint"), true) ?></label>
        </th>
        <td>
            <div class="example-class">
             <select style="" id="helloprint_preset_default_service_level" name="default_service_level" class="select short wphp-select2" >
                <?php if (!empty($default_service_level)) :?>
                    <option value="<?php echo esc_attr($default_service_level);?>" selected><?php echo esc_attr(ucwords($default_service_level));?></option>
                <?php else:?>
                    <option value=""><?php echo wp_kses(_translate_helloprint('Select Service Level', "helloprint"), false) ?></option>
                <?php endif;?>
            </select>
        </div>
    </td>
</tr>

<tr id="helloprint_preset_quantity_tr" class="example-class wphp-preset-tr-for-non-hp">
    <th scope="row">
        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Default Quantity', "helloprint"), true) ?></label>
    </th>
    <td>
        <div class="example-class">
         <select style="" id="helloprint_preset_default_quantity" name="default_quantity" class="select short wphp-select2" >
            <option value=""><?php echo wp_kses(_translate_helloprint('Select Quantity', "helloprint"), false) ?></option>
            <?php if (!empty($default_quantity)) :?>
                <option value="<?php echo esc_attr($default_quantity);?>" selected><?php echo esc_attr($default_quantity);?></option>
            <?php endif;?>

        </select>
    </div>
</td>
</tr>