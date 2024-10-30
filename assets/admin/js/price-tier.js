jQuery(document).ready(function ($) {
    helloprint_show_hide_scaled_div();
    helloprint_update_markup_margin_label();
    jQuery(".hp-add-scaled-price-btn").on("click", function(){
        var btn_to_add = jQuery(this);
        btn_to_add.attr("disabled", "disabled");
        var div_to_copy = jQuery(".first-hp-single-scaled").html();
        jQuery(".wphp-all-scaled-prices-div").append(div_to_copy);
        setTimeout(function(){
            jQuery(".wphp-single-scaled-price:last").find(".hp-remove-single-scale-div").html('<a href="#" class="dashicons-before dashicons-remove btn btn-sm btn-outline-danger hp-remove-single-scale-btn"></a>');
            jQuery(".wphp-single-scaled-price:last").find(".hp_number").val("");
            jQuery(".wphp-single-scaled-price:last").find(".hp_percentage").val("");
            btn_to_add.removeAttr("disabled");
        }, 100)
    });

    jQuery(document).on("click", ".hp-remove-single-scale-btn", function(){
        jQuery(this).closest(".wphp-single-scaled-price").remove();
    })
    jQuery(document).on("change", "#hp_enable_scaled_pricing", function(){
        helloprint_show_hide_scaled_div();
    })

    jQuery(document).on("submit", "#wphp-add-edit-pricing-tier-form", function(){
        var name = jQuery.trim(jQuery("#hp_tier_name").val());
        if (name == "") {
            jQuery("#hp_tier_name").val("");
            jQuery("#hp_tier_name").focus();
            return false;
        }
        var prices = [];
        jQuery(".hp_number").each(function(){
            if (jQuery.inArray(parseFloat(jQuery(this).val()), prices) >= 0){
                jQuery(this).focus();
            } 
            prices.push(parseFloat(jQuery(this).val()));
        })
        var unique_prices = [];
        jQuery.each(prices, function(i, item){
            if (jQuery.inArray(item, unique_prices) === -1){
                unique_prices.push(item);
            } 
        });
        var is_duplicates = (unique_prices.length != prices.length);
        if (is_duplicates) {
            var duplicate_msg = jQuery("#hidden_hp_duplicate_scale_message").val();
            alert(duplicate_msg);
            return false;
        }
    });

    jQuery(document).on("change", ".helloprint_tyre_type", function(){
        helloprint_update_markup_margin_label();
    })
});

function helloprint_show_hide_scaled_div()
{
    var checked = jQuery("#hp_enable_scaled_pricing").prop("checked");
    if (checked) {
        jQuery("#hp-enabled-scaled-pricing-div").show();
        jQuery(".hp_number").attr("required", true);
        jQuery(".hp_percentage").attr("required", true);
    } else {
        jQuery("#hp-enabled-scaled-pricing-div").hide();
        jQuery(".hp_number").removeAttr("required");
        jQuery(".hp_percentage").removeAttr("required");
    }
}

function helloprint_update_markup_margin_label()
{
    var value = jQuery("[name='tier_type']:checked").val();
    if (value == "markup") {
        jQuery(".wphp-markup-margin-label").html(jQuery("#hidden_hp_markup_label").val());
    } else {
        jQuery(".wphp-markup-margin-label").html(jQuery("#hidden_hp_margin_label").val());
    }
}
