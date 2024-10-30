<?php
/**
 * PHP view.
 *
 */
?>
<hr>
<div class="wphp-size-quantity">
    <legend><?php echo wp_kses(_translate_helloprint('SELECT THE QUANTITY YOU NEED', 'helloprint'), true) ?></legend>
    <div>
        <?php 
        foreach ($args['product_options'] as $key => $option) : ?>
            <?php if (esc_attr($option['code']) === 'apparelSize') : ?>
                <div class="wphp-flex">
                    <label><?php echo wp_kses(_translate_helloprint('Sizes', 'helloprint'), true) ?></label>
                    <?php if (esc_attr($option['isUnisex']) == true) : ?>
                        <label><?php echo wp_kses(_translate_helloprint('Quantity', 'helloprint'), true) ?></label>
                    <?php else : ?>
                        <label><?php echo wp_kses(_translate_helloprint('Men', 'helloprint'), true) ?></label>
                        <label><?php echo wp_kses(_translate_helloprint('Women', 'helloprint'), true) ?></label>
                    <?php endif ?>
                </div>
                <?php foreach ($option['value'] as $key => $value) : ?>
                    <div class="wphp-flex wphp-custom-appreal-size">
                        <label><?php echo wp_kses(_translate_helloprint(esc_attr($value), 'helloprint'), true) ?></label>
                        <input class="wphp-product-options-apparel-size" onClick="this.select();" type="number" value="0" placeholder="0"
                        <?php if(esc_attr($option['isUnisex']) == true):?>
                            data-label="appreal_size[<?php echo esc_attr(_translate_helloprint('Quantity', 'helloprint')) ?>][<?php echo esc_attr(_translate_helloprint(esc_attr($value), 'helloprint'));?>]"
                            name="appreal_size[quantity][<?php echo esc_attr($value);?>]"
                            id="appreal_size_quantity_<?php echo esc_attr($value);?>"
                        <?php else: ?>
                            data-label="appreal_size[<?php echo esc_attr(_translate_helloprint('Men', 'helloprint')) ?>][<?php echo esc_attr(_translate_helloprint(esc_attr($value), 'helloprint'));?>]"
                            name="appreal_size[men][<?php echo esc_attr($value);?>]"
                            id="appreal_men_quantity_<?php echo esc_attr($value);?>"
                        <?php endif;?>
                        min="0"
                        >
                        <?php if (esc_attr($option['isUnisex']) == false) : ?>
                            <input class="wphp-product-options-apparel-size" onClick="this.select();" type="number" value="0" placeholder="0"
                            data-label="appreal_size[<?php echo esc_attr(_translate_helloprint('Women', 'helloprint')) ?>][<?php echo esc_attr(_translate_helloprint(esc_attr($value), 'helloprint'));?>]"
                            name="appreal_size[women][<?php echo esc_attr($value);?>]"
                            id="appreal_women_quantity_<?php echo esc_attr($value);?>"
                            min="0" >
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        <?php endforeach ?>
        <div class="wphp-total">
            <div class="wphp-flex">
                <label><?php echo wp_kses(_translate_helloprint('Total', 'helloprint'), true) ?></label>
                <label id="wphp-product-total-custom-quantity">0</label>
                <input type="hidden" id="wphp-product-total-appreal-size" />
            </div>
            <div id="wphp-min_max_message_div"></div>
            <div class="wphp-flex">
                <label><?php echo wp_kses(_translate_helloprint('Price per piece', 'helloprint'), true) ?></label>
                <label class="wphp-apparealsize-perpiece"></label>
            </div>
            <div class="wphp-flex">
                <label><strong><?php echo wp_kses(_translate_helloprint('Total price', 'helloprint'), true) ?></strong></label>
                <label><strong class="wphp-apparealsize-price"></strong></label>
            </div>
        </div>

        
        <div style="display:none;" id="wphp-appreal-size-nonstandard-error" class="woocommerce">
            <div class="woocommerce-error" role="alert">
                <?php echo wp_kses(_translate_helloprint("You've chosen a non-standard quantity. Please choose any standard quantity.", 'helloprint'), true) ?>
            </div>
        </div>
    </div>
    <hr>
</div>