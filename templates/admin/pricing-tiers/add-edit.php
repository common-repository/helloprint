<?php
/**
* PHP view.
*
*/
?>
<div class="wrap">
    <h1><?php echo wp_kses(_translate_helloprint(ucwords($action) . ' Tier', "helloprint"), true); ?></h1>
    <input type="hidden" id="hidden_hp_duplicate_scale_message" value="<?php echo wp_kses(_translate_helloprint('Same prices should not repeat for the scaled pricing', "helloprint"), true) ?>" />
    <input type="hidden" id="hidden_hp_markup_label" value="<?php echo wp_kses(_translate_helloprint('Markup', "helloprint"), true) ?>" />
    <input type="hidden" id="hidden_hp_margin_label" value="<?php echo wp_kses(_translate_helloprint('Margin', "helloprint"), true) ?>" />
    <form id="wphp-add-edit-pricing-tier-form" name="wphp-add-edit-pricing-tier" method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $action;?>">
        <input type="hidden" name="id" value="<?php echo $id;?>" />
        <table class="form-table" role="presentation">
                <tbody>
                        <tr class="example-class">
                                <th scope="row">
                                        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Tyre Type', "helloprint"), true) ?></label>
                                </th>
                                <td>
                                        <div class="example-class">
                                        <input type="radio" class="regular-text helloprint_tyre_type" id="hp_type_margin" name="tier_type" value="margin" <?php if(empty($tier_type) || $tier_type == "margin") echo "checked='checked'";?>> <?php echo wp_kses(_translate_helloprint('For Margin', "helloprint"), true) ?>
                                        <input type="radio" class="regular-text helloprint_tyre_type" id="hp_type_markup" name="tier_type" value="markup" <?php if(!empty($tier_type) && $tier_type == "markup") echo "checked='checked'";?> > <?php echo wp_kses(_translate_helloprint('For Markup', "helloprint"), true) ?>     
                                </div>
                             </td>
                        </tr>
                        <tr class="example-class">
                                <th scope="row">
                                        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Name', "helloprint"), true) ?></label>
                                </th>
                                <td>
                                        <div class="example-class">
                                             <input type="text" class="regular-text" id="hp_tier_name" name="name" value="<?php echo $name;?>" required="true" />
                                     </div>
                             </td>
                        </tr>

                        <tr class="example-class">
                                <th scope="row">
                                        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Default', "helloprint"), true) ?> <span class="wphp-markup-margin-label"></span></label>
                                </th>
                                <td>
                                        <div class="example-class">
                                                <div class="wphp-input-group">
                                                        <div class="wphp-input-group-area"><input class="regular-text hp-default-markup-text" id="default_markup" name="default_markup" type="number" value="<?php echo $default_markup;?>" min="1" required="true" max="100" ></div>
                                                        <div class="wphp-input-group-icon">%</div>
                                                </div>
                                        </div>
                                </td>
                        </tr>

                        <tr class="example-class">
                                <th scope="row">
                                        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Enable Scaled Pricing', "helloprint"), true) ?></label>
                                </th>
                                <td>
                                        <div class="example-class">
                                             <input type="checkbox" class="regular-text" id="hp_enable_scaled_pricing" name="enable_scaling" value="1" <?php if($enable_scaling == 1) echo "checked='checked'";?> >
                                        </div>
                                </td>
                        </tr>
                        <tr id="hp-enabled-scaled-pricing-div" class="example-class">
                                <th scope="row">
                                        <label for="helloprint_api_key"><?php echo wp_kses(_translate_helloprint('Scaled Pricing', "helloprint"), true) ?></label>
                                </th>
                                <td>
                                        <div class="example-class wphp-all-scaled-prices-div">
                                                <div class="first-hp-single-scaled" >
                                                        <div class="row wphp-single-scaled-price" >
                                                        <table class="form-table" role="presentation">
                                                        <tbody>
                                                        <tr>
                                                                <td  class="hp-single-scale-sub-div">
                                                                        <div class="wphp-input-group">
                                                                                <div class="wphp-input-group-icon"><?php echo $currency_symbol;?></div>
                                                                                <div class="wphp-input-group-area"><input class="hp_number" name="scaled_price[]" type="number" value="<?php echo (!empty($scalings[0]->price)) ? $scalings[0]->price : '';?>" min="0" ></div>
                                                                                <div class="wphp-input-group-icon"><?php echo wp_kses(_translate_helloprint('and above', "helloprint"), true) ?></div>
                                                                        </div>
                                                                </td>
                                                                <td  class="hp-single-scale-sub-div">
                                                                        <div class="wphp-input-group">
                                                                                <div class="wphp-input-group-area"><input class="hp_percentage" name="scaled_margin[]" type="number" value="<?php echo (!empty($scalings[0]->margin)) ? $scalings[0]->margin : '';?>" min="1" max="100" ></div>
                                                                                <div class="wphp-input-group-icon">%</div>
                                                                        </div>
                                                                </td>
                                                                <td class="hp-remove-single-scale-div"></td>
                                                        </tr>
                                                        </tbody>
                                                        </table>
                                                        <hr/>
                                                </div>
                                                </div>

                                                <?php if(isset($scalings) && count($scalings) > 1):
                                                    foreach ($scalings as $k => $scale):
                                                    if ($k > 0):
                                                ?>
                                                    <div class="row wphp-single-scaled-price" >
                                                        <table class="form-table" role="presentation">
                                                        <tbody>
                                                        <tr>
                                                                <td  class="hp-single-scale-sub-div">
                                                                        <div class="wphp-input-group">
                                                                                <div class="wphp-input-group-icon"><?php echo $currency_symbol;?></div>
                                                                                <div class="wphp-input-group-area"><input class="hp_number" name="scaled_price[]" type="number" value="<?php echo (!empty($scale->price)) ? $scale->price : '';?>" min="0" ></div>
                                                                                <div class="wphp-input-group-icon"><?php echo wp_kses(_translate_helloprint('and above', "helloprint"), true) ?></div>
                                                                        </div>
                                                                </td>
                                                                <td  class="hp-single-scale-sub-div">
                                                                        <div class="wphp-input-group">
                                                                                <div class="wphp-input-group-area"><input class="hp_percentage" name="scaled_margin[]" type="number" value="<?php echo (!empty($scale->margin)) ? $scale->margin : '';?>" min="1" max="100" ></div>
                                                                                <div class="wphp-input-group-icon">%</div>
                                                                        </div>
                                                                </td>
                                                                <td class="hp-remove-single-scale-div">
                                                                    <a href="#" class="dashicons-before dashicons-remove btn btn-sm btn-outline-danger hp-remove-single-scale-btn"></a>
                                                                </td>
                                                        </tr>
                                                        </tbody>
                                                        </table>
                                                        <hr/>
                                                </div>
                                                <?php 
                                                    endif;
                                                    endforeach;
                                                    endif;?>
                                        </div>
                                        <button type="button" class="button button-primary hp-add-scaled-price-btn" style="float:right"><?php echo wp_kses(_translate_helloprint('ADD SCALED PRICING', "helloprint"), true) ?></button>
                                </td>
                        </tr>
     </tbody>
</table>
<p class="submit"><input id="helloprint_add_edit_pricing_btn" type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo wp_kses(_translate_helloprint(ucwords($action_to_perform) . ' Pricing Tier', "helloprint"), false) ?>" ></p>    
</form>
</div>
