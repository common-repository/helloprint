<?php
/**
 * PHP view.
 *
 */
?>
<!-- Custom Size ---------------------------------------- -->
<?php if (!empty($args['product_options'])) : ?>
    <?php if (
        array_search('height', array_column($args['product_options'], 'code'), true) !== false &&
        array_search('width', array_column($args['product_options'], 'code'), true) !== false
    ) : ?>
    <div class="wphp-custom-size check">
        <input type="hidden" value="<?php echo esc_attr(_translate_helloprint("All options cannot be empty and should be numeric value between 10 and 2000.", 'helloprint')); ?>" id="wphp_options_validation_msg" />
        <input type="hidden" value="<?php echo esc_url(site_url()); ?>/product/custom-banners-banners/" id="wphp_product_url" />
        <hr>
        <lagend for=""><?php echo (esc_attr($args['size_exists'])) ? wp_kses(_translate_helloprint("Or calculate a custom size", "helloprint"), true) : wp_kses(_translate_helloprint("Calculate a custom size", "helloprint"), true); ?></lagend>
        <div class="wphp-grid grid-opt">
            <?php
            $iteration = 0;
            $totalval = 0;
            $unit = "";
            $dim = "";
            foreach ($args['product_options'] as $opt) :
                $oldvalue = isset($_GET['print_custom_' . $opt['code']]) ?  sanitize_text_field(wp_unslash($_GET['print_custom_' . $opt['code']])) : '';
                $totalval = (0 == $iteration) ? $oldvalue : (floatval($totalval) * floatval($oldvalue));
                $iteration++;
                ?>
                <?php if ('height' == strtolower($opt['code']) || 'width' == strtolower($opt['code'])) : ?>
                <?php
                $min = esc_attr($opt['min']);
                $max = esc_attr($opt['max']);
                $unit = esc_attr($opt['unit']);
                $dim = esc_attr($opt['dim']);
                ?>
                <div>
                    <label for="wphp_width"><?php echo wp_kses(_translate_helloprint(ucwords($opt['code']), "helloprint"), true); echo ' ('.  wp_kses(_translate_helloprint($opt['unit'], "helloprint"), true) . ')'; ?></label>
                    <input class="wphp_product_options" type="number" name="wphp_options[<?php echo esc_attr($opt['code']); ?>]" id="wphp_custom_<?php echo esc_attr($opt['code']); ?>" value="<?php echo esc_attr($oldvalue); ?>">
                    <input data-label="<?php echo esc_attr(_translate_helloprint(ucwords($opt['code']) , "helloprint")) . esc_attr(_translate_helloprint($opt['unit'], "helloprint")); ?>" value="<?php echo esc_attr($oldvalue); ?>" name="<?php echo esc_attr($opt['code']); ?>" class="wphp_product_options_hidden" type="hidden" id="wphp_custom_<?php echo esc_attr($opt['code']); ?>_hidden" />
                    <label><?php echo wp_kses(_translate_helloprint("Min $min / Max $max ". esc_attr(_translate_helloprint($opt['unit'], "helloprint")), "helloprint"), true); ?></label>
                    <input type="hidden" class="wphp-product-unit" value="<?php echo esc_attr($opt['unit']); ?>" />
                    <input type="hidden" id="wphp-product-dim" value="<?php echo esc_attr($opt['unit']); ?>" />
                    <input type="hidden" class="wphp-product-<?php echo esc_attr($opt['code']); ?>-min" value="<?php echo esc_attr($min) ?>" />
                    <input type="hidden" class="wphp-product-<?php echo esc_attr($opt['code']); ?>-max" value="<?php echo esc_attr($max) ?>" />
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php
        switch ($unit) {
            case 'mm':
            $totalval = floatval($totalval) / 100;
            if ('m2' == $dim) {
                $totalval = floatval($totalval) / 10000;
            }
            break;

            case 'cm':
            $totalval = floatval($totalval) / 10000;
            break;
        }
        ?>
        <label><?php echo wp_kses(_translate_helloprint("Total", "helloprint"), true); ?>: <span class="wphp-custom-total"><?php echo number_format((floatval($totalval)), 2); ?></span> <?php echo wp_kses(_translate_helloprint($opt['dim'], "helloprint"), true); ?></label>
    </div>
    <!-- <button type="button" id="wphp-btn-confirm" name="hp-confirm" class="alt wphp-btn-confirm"><?php echo wp_kses(_translate_helloprint("Confirm", "helloprint"), true); ?></button> -->
    <hr>
</div>
<?php endif; ?>
<?php endif; ?>