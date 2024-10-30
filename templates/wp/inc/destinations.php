<?php
/**
 * PHP view.
 *
 */
?>
<?php if(count($destination_countries) > 1):?>
    <div>
        <?php 
        $country_arr = wc_get_base_location();
        $base_country = ($country_arr['country']) ?? '';
        ?>
        <label for="wphp_product_option_countries"><?php echo wp_kses(_translate_helloprint("CHOOSE DELIVERY COUNTRY", 'helloprint'), true); ?></label>
        <select name="destination_country" data-select-id="destination_country" class="wphp-product-country-selector wphp-country-selector">
            <?php foreach($destination_countries as $country):?>
                <option value="<?php echo esc_attr($country['code']);?>"
                    <?php if($base_country === $country['code']) {
                        echo ' selected="selected" ';
                    }?>
                    >
                    <?php echo esc_attr($country['name']);?></option>
                <?php endforeach;?>
        </select>
    </div>
<?php endif;?>