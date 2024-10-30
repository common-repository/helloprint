<th scope="row">
    <label for="helloprint_width"><?php echo wp_kses(_translate_helloprint('Width', "helloprint"), true) ?></label>
</th>
<td>
    <?php
    $min_width = ($args["available_options"][0]->code == "width") ? $args["available_options"][0]->min : $args["available_options"][1]->min;
    $max_width = ($args["available_options"][0]->code == "width") ? $args["available_options"][0]->max : $args["available_options"][1]->max;
    ?>
    <div class="example-class">
        <div class="wphp-input-group">
                <div class="wphp-input-group-area">
                    <input class="regular-text helloprint_custom_option_input_preset_field" data-name="custom_options" data-keytype="width" id="helloprint_preset_width" name="custom_options[width]" type="number" value="<?php echo !empty($args["default_options"]["width"]) ? $args["default_options"]["width"] : '';?>" min="<?php echo $min_width?>" required="true" max="<?php echo $max_width;?>" >
                </div>
                <div class="wphp-input-group-icon"><?php echo ($args["available_options"][0]->code == "width") ? $args["available_options"][0]->unit : $args["available_options"][1]->unit;?></div>
        </div>
        <i><?php echo wp_kses(_translate_helloprint("min :: " . $min_width . ", max :: " . $max_width), true);?></i>
    </div>
</td>
<th scope="row">
    <label for="helloprint_height"><?php echo wp_kses(_translate_helloprint('Height', "helloprint"), true) ?></label>
</th>
<td>
<?php
    $min_height = ($args["available_options"][0]->code == "height") ? $args["available_options"][0]->min : $args["available_options"][1]->min;
    $max_height = ($args["available_options"][0]->code == "height") ? $args["available_options"][0]->max : $args["available_options"][1]->max;
    ?>
    <div class="example-class">
    <div class="wphp-input-group">
                <div class="wphp-input-group-area">
                    <input class="regular-text helloprint_custom_option_input_preset_field" data-name="custom_options" data-keytype="height" id="helloprint_preset_height" name="custom_options[height]" type="number" value="<?php echo !empty($args["default_options"]["height"]) ? $args["default_options"]["height"] : '';?>" min="<?php echo $min_height;?>" required="true" max="<?php echo $max_height;?>" >
                </div>
                <div class="wphp-input-group-icon"><?php echo ($args["available_options"][0]->code == "height") ? $args["available_options"][0]->unit : $args["available_options"][1]->unit;?></div>
        </div>
        <i><?php echo wp_kses(_translate_helloprint("min :: " . $min_height . ", max :: " . $max_height), true);?></i>
    </div>
    
</td>
