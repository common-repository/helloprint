<tr class="wphp-preset-tr-for-hp wphp-preset-product-select-tr">
    
    <th scope="row">
        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Select Product', "helloprint"), true) ?></label>
    </th>
    <td>
        <div class="example-class">
            <select style="" id="helloprint_preset_product" name="helloprint_product" class="select short wphp-select2" >
                <option value=""><?php echo wp_kses(_translate_helloprint('Select Product', "helloprint"), true) ?></option>
                <?php foreach($all_hp_products as $key => $hp_product):
                        if (!is_array($hp_product)) { ?>
                            <option value="<?php echo esc_attr($key);?>" <?php if(!empty($helloprint_product_id) && $helloprint_product_id == $key) { echo "selected='selected'"; }?> ><?php echo $hp_product;?></option>
                            <?php } else { ?>
                            <optgroup label="<?php echo esc_attr($key);?>">
                                <?php foreach ($hp_product as $k => $val) { ?>
                                    <option value="<?php echo esc_attr($k);?>" <?php if(!empty($helloprint_product_id) && $helloprint_product_id == $k) { echo "selected='selected'"; }?>><?php echo esc_html($val);?></option>
                                <?php } ?>
                            </optgroup>
				<?php	}
                        endforeach;?>
            </select>
        </div>
    </td>
    
</tr>