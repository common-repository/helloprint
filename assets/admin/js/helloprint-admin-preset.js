jQuery(document).ready(function ($) {
    if (jQuery(".helloprint_preset_product_type").length > 0) {
        _helloprint_load_hp_non_hp_preset_div();
        jQuery(".helloprint_preset_product_type").on("change", function(){
            _helloprint_load_hp_non_hp_preset_div();
        });
    }

    if (jQuery(".helloprint_prefer_files-div").length > 0) {
        jQuery(".wphp-order-item-preset-div ").each(function(){
            _helloprint_show_hide_authoritive_files(jQuery(this));
        });
    }

});

function _helloprint_load_hp_non_hp_preset_div()
{

    jQuery("#helloprint_pod_available_option_type").val("");
    var product_type = jQuery("[name='product_type']:checked").val();
    jQuery(".helloprint_preset_custom_size_tr").html("");

    jQuery("#helloprint_pod_available_option_type").val("");
    if (product_type == "non_hp") {
        jQuery(".wphp-preset-div-for-non-hp").removeClass("wphp-display-none");
        jQuery(".wphp-preset-div-for-hp").addClass("wphp-display-none");

        jQuery(".wphp-preset-tr-for-non-hp").removeClass("wphp-display-none");
        jQuery(".wphp-preset-tr-for-hp").addClass("wphp-display-none");
        jQuery("#helloprint_order_preset_variant_key").attr("required", "required");
        jQuery("#helloprint_preset_product").removeAttr("required");
        if (jQuery("#helloprint_order_preset_variant_key").val() != '') {
            var selectedServ = jQuery("#helloprint_preset_default_service_level").val();
            var selectedQtys = jQuery("#helloprint_preset_default_quantity").val();
            helloprint_preset_load_quantities(selectedServ, selectedQtys);
        }
    } else {
        jQuery(".wphp-preset-div-for-non-hp").addClass("wphp-display-none");
        jQuery(".wphp-preset-div-for-hp").removeClass("wphp-display-none");

        jQuery(".wphp-preset-tr-for-non-hp").addClass("wphp-display-none");
        jQuery(".wphp-preset-tr-for-hp").removeClass("wphp-display-none");
        jQuery("#helloprint_order_preset_variant_key").removeAttr("required");
        jQuery("#helloprint_preset_product").attr("required", "required");
    }
}

function _helloprint_show_hide_authoritive_files(element)
{
    setTimeout(function(){
        var uploaded_files = element.find(".wphp-single-preset-file").length > 0 && (element.find(".helloprint_artwork_external_url") != "");
        if (element.find("#helloprint_order_details_hp_preset_artworks").length > 0 && uploaded_files) {
            element.find(".helloprint_prefer_files-div").show();
        } else {
            if (element.find("#helloprint_order_details_hp_preset_artworks").length) {
                element.find("[name='helloprint_preset_prefer_files'][value='hp_preset_artwork']").trigger("click");
            } else {
                element.find("[name='helloprint_preset_prefer_files'][value='upload_files']").trigger("click");
            }
            element.find(".helloprint_prefer_files-div").hide();
        }
    }, 500);
}

function _helloprint_only_show_authoritive_files(element)
{
    element.find("[name='helloprint_preset_prefer_files'][value='hp_preset_artwork']").prop("checked", false).removeAttr("checked");
    element.find("[name='helloprint_preset_prefer_files'][value='upload_files']").trigger("click");
    if (element.find("#helloprint_order_details_hp_preset_artworks").length > 0 ) {
        element.find(".helloprint_prefer_files-div").show();
    } else {
        element.find(".helloprint_prefer_files-div").hide();
    }
}
