<?php

/**
 * PHP view.
 *
 */
?>
<input type="hidden" id="helloprint_hidden_pod_id" name="id" value="<?php echo !empty($id) ? esc_attr($id) : ''; ?>">
<input type="hidden" name="available_options" value="" id="helloprint_pod_available_options" />
<input type="hidden" name="available_option_type" value="" id="helloprint_pod_available_option_type" />
<input type="hidden" value="0" id="helloprint_pod_is_attribute_changed" />
<input type="hidden" name="hp_variant_key" value="<?php echo ($product_type == "hp" && !empty($helloprint_variant_key)) ? $helloprint_variant_key : ''; ?>" id="helloprint_pod_available_variant_key" />
<input type="hidden" value="<?php echo ($product_type == "hp" && !empty($default_quantity)) ? $default_quantity : ''; ?>" id="old_helloprint_pod_default_quantity" />
<input type="hidden" value="<?php echo ($product_type == "hp" && !empty($default_service_level)) ? $default_service_level : ''; ?>" id="old_helloprint_pod_service_level" />
<input type="hidden" value="<?php echo (!empty($helloprint_product_id)) ? $helloprint_product_id : ''; ?>" id="helloprint_pod_old_hp_product_id" />
<input type="hidden" value='<?php echo (!empty($product_attributes)) ? "[" . json_encode(json_decode($product_attributes)) . "]" : ""; ?>' id="helloprint_pod_old_hp_attributes" />
<table class="form-table" role="presentation">
    <tbody>
        <tr class="example-class">
            <th scope="row">
                <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Preset Name*', "helloprint"), true) ?></label>
            </th>
            <td>
                <div class="example-class">
                    <input type="text" class="regular-text" id="order_preset_name" name="order_preset_name" value="<?php echo !empty($order_preset_name) ? esc_attr($order_preset_name) : ''; ?>" required="required">
                </div>
            </td>
        </tr>
        <tr class="example-class">
            <th scope="row">
                <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Product Type', "helloprint"), true) ?></label>
            </th>
            <td>
                <div class="example-class">
                    <input type="radio" class="regular-text helloprint_preset_product_type" id="hp_preset_hp_product" name="product_type" value="hp" <?php if (empty($product_type) || $product_type == "hp") echo "checked='checked'"; ?>> <?php echo wp_kses(_translate_helloprint('Helloprint Product', "helloprint"), true) ?>
                    <input type="radio" class="regular-text helloprint_preset_product_type" id="hp_preset_non_hp_product" name="product_type" value="non_hp" <?php if (!empty($product_type) && $product_type == "non_hp") echo "checked='checked'"; ?>> <?php echo wp_kses(_translate_helloprint('Non-Helloprint Product', "helloprint"), true) ?>
                </div>
            </td>
        </tr>
        <div class="wphp-preset-div-for-non-hp wphp-preset-tr-for-non-hp">
            <?php include("for-non-hp-product.php"); ?>
        </div>
        <div class="wphp-preset-div-for-hp wphp-preset-tr-for-hp">
            <?php include("for-hp-product.php"); ?>
        </div>

        <tr class="helloprint_preset_custom_size_tr "></tr>
        <tr class="example-class">
            <th scope="row">
                <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Artwork Preset', "helloprint"), true) ?></label>
            </th>
            <td>
                <div class="example-class">
                    <div class="wphp-order-preset-file-upload">
                        <?php if (!empty($file_name)) : ?>
                            <div class="wphp-preset-file-remove-div">
                                <span><?php echo esc_attr($file_name); ?></span>
                                <br />
                                <a target="_blank" href="<?php echo esc_url_raw(get_site_url() . $file_url) ?>" class="btn-sm btn-outline-primary"><?php echo wp_kses(_translate_helloprint("Download", "helloprint"), true); ?></a>&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="#" class="wphp-admin-preset-remove btn btn-sm btn-outline-danger"><?php echo wp_kses(_translate_helloprint("Remove", "helloprint"), true); ?></a>
                            </div>
                        <?php endif; ?>
                        <div class="wphp-order-preset-file-upload-page">
                            <div id="helloprint_order_preset_file"></div>
                            <input type="file" class="helloprint_order_preset_file" name="helloprint_order_preset_file_upload">
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <tr class="example-class">
            <th scope="row">
                <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Public Url', "helloprint"), true) ?></label>
            </th>
            <td>
                <div class="example-class">
                    <input type="url" class="regular-text helloprint_order_preset_public_url" id="order_preset_public_url" name="order_preset_public_url" value="<?php echo (!empty($file_url) && $is_public_url) ? $file_url : '';?>" <?php echo (!empty($file_name)) ? " disabled " : "";?> >
                </div>
            </td>
        </tr>
    </tbody>
</table>