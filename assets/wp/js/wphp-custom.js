var uploadedFiles = '';
var updateCount = 0;
var productWithApparealSize;
var productWithPrintArea;
var productWithColorRadio; 
var productWithCountrySelector;
var currentSelectedAttribute;  
var is_delivery_method_changed = false;
var global_lazy_loading = false;
const urlParams = new URLSearchParams(window.location.search);
const format = (num, decimals) => num.toLocaleString('en-US', {
   minimumFractionDigits: 2,      
   maximumFractionDigits: 2,
});
jQuery(document).ready(function ($) {

    wphp_handle_cart_pitchprint();
    if (jQuery(".wphp-size-quantity").length) {
        productWithApparealSize = true;
    }
    if (jQuery(".wphp-color-sec").length) {
        productWithColorRadio = true;
    }
    if (jQuery('.wphp-print-position-sec').length) {
        productWithPrintArea = true;
    }
    if(jQuery(".wphp-product-country-selector").length){
        productWithCountrySelector = true
    }

    deeplink_checks();

    jQuery('.wphp-product-country-selector').on('change', function () {
        //if (jQuery(".wphp-product-option-selector option[value='0']:selected").length == 0) {
            wphp_load_with_destination_country();
       // }   
    });

    jQuery('.wphp-sizeRadio').on('change', function () {
        jQuery(this).parent().siblings().removeClass('selected')
        jQuery(this).parent().addClass('selected');

        if(jQuery(this).closest(".wphp-size-sec").prevAll(".wphp-color-sec").length == 0) {
            if (productWithColorRadio) {
                jQuery('a.wphp-color').removeClass('selected')
                jQuery('.wphp-colorRadio').prop('checked', false); 
            }
        }
        if(jQuery(this).closest(".wphp-size-sec").prevAll(".wphp-print-position-sec").length == 0) {
            if (productWithPrintArea) {
                jQuery('a.wphp-print-position').removeClass('selected')
                jQuery('.wphp-printPositionRadio').prop('checked', false);
            }
        }
        jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
        jQuery('.wphp-quantity-sec').addClass('wphp-display-none');
        jQuery(this).closest('.wphp-size-sec').nextAll('.wphp-grid').find('.wphp-product-option-selector').val(0);
        var attributes = mapAttributes();
        var selectedAttribute = jQuery(this).data('select-id');
        var data = mapData(attributes, selectedAttribute);
        check_quantity_toggle(selectedAttribute);
        showLoader(selectedAttribute);
        sendRequest(data, selectedAttribute);
        document.getElementById("wphp-scrollend-size").scrollIntoView({behavior: 'smooth',});
    });
    
    jQuery('.wphp-colorRadio').on('change', function () {

        if(jQuery(this).closest(".wphp-color-sec").prevAll(".wphp-size-sec").length == 0) {
            if (jQuery(".wphp-size-sec").length > 0) {
                jQuery('a.wphp-size').removeClass('selected')
                jQuery('.wphp-sizeRadio').prop('checked', false); 
            }
        }
        if(jQuery(this).closest(".wphp-color-sec").prevAll(".wphp-print-position-sec").length == 0) {
            if (productWithPrintArea) {
                jQuery('a.wphp-print-position').removeClass('selected')
                jQuery('.wphp-printPositionRadio').prop('checked', false);
            }
        }
        jQuery(this).parent().siblings().removeClass('selected')
        jQuery(this).parent().addClass('selected')
        if (productWithPrintArea) {
            jQuery('a.wphp-print-position').removeClass('selected')
            jQuery('.wphp-printPositionRadio').prop('checked', false);
        }
        jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
        jQuery('.wphp-quantity-sec').addClass('wphp-display-none');
        jQuery(this).closest('.wphp-color-sec').nextAll('.wphp-grid').find('.wphp-product-option-selector').val(0);
        var attributes = mapAttributes();
        var selectedAttribute = jQuery(this).data('select-id');
        var data = mapData(attributes, selectedAttribute);
        check_quantity_toggle(selectedAttribute);
        showLoader(selectedAttribute);
        sendRequest(data, selectedAttribute);
        document.getElementById("wphp-scrollend-color").scrollIntoView({behavior: 'smooth',});
    });
    jQuery('.wphp-printPositionRadio').on('change', function () {
        jQuery(this).parent().siblings().removeClass('selected')
        jQuery(this).parent().addClass('selected')
        jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
        jQuery('.wphp-quantity-sec').addClass('wphp-display-none');
        jQuery(this).closest('.wphp-print-position-sec').nextAll('.wphp-grid').find('.wphp-product-option-selector').val(0);
        var attributes = mapAttributes();
        var selectedAttribute = jQuery(this).data('select-id');
        var data = mapData(attributes, selectedAttribute);
        check_quantity_toggle(selectedAttribute);
        showLoader(selectedAttribute);
        sendRequest(data, selectedAttribute);
        document.getElementById("wphp-scrollend-printposition").scrollIntoView({behavior: 'smooth',});
    });

    if (jQuery('.wphp-product-selector').length > 0) {
        var optionslength = jQuery('.wphp-product-option-selector').length;
        for (index = 0; index < optionslength; index++) {
            jQuery('.wphp-product-option-'+index).on('change', function () {
                var it = jQuery(this).data("iteration");
                for (i = it+1; i < optionslength; i++) {
                    if (jQuery('.wphp-product-option-' + i).length > 0 ) {
                        jQuery('.wphp-product-option-' + i).val(0);
                    }    
                }
                var $this = jQuery(this);
                setTimeout(function() {
                    $this.closest('div.body').next().find('div.calculationContainer')
                    jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
                    jQuery('.wphp-quantity-sec').addClass('wphp-display-none');
                    var attributes = mapAttributes();
                    var selectedAttribute = $this.data('select-id');
                    var data = mapData(attributes, selectedAttribute);
                    check_quantity_toggle(selectedAttribute);
                    showLoader(selectedAttribute);
                    sendRequest(data, selectedAttribute);
                }, 300);
                    
            });
        }
    } else {
        var attributes = mapAttributes();
        var data = mapData(attributes, '');
        jQuery('.wphp-loader').removeClass('wphp-display-none');
        sendRequest(data, '');
    }

    jQuery(document).on('change', '.quantityRadio', function () {
        getMapDataWithSelectedAttribute(false);
        updatePrice();
    });
    jQuery(document).on('change', '.wphp_design', function () {
        updatePriceWithGraphic();
    });
    jQuery('#wphp_custom_height').change(function (e) {  
        e.preventDefault();
        evaluateWidthHeight();
    });
    jQuery('#wphp_custom_width').change(function (e) {  
        e.preventDefault();
        evaluateWidthHeight();
    });

    $('.wphp_product_options').keyup(function() {
        var numbers = jQuery(this).val();
        jQuery(this).val(numbers.replace(/\D/, ''));
        var multiply = 0;
        var index = 0;
        jQuery(".wphp_product_options").each(function(){
            var value = jQuery(this).val();
            value = (value == '') ? 0 : value;
            if (index == 0) {
                multiply = value;
            } else {
                multiply = parseFloat(multiply) * parseFloat(value);
            }
            index++;
        });
        var total = getCustomSizeTotal(multiply); 
        jQuery(".wphp-custom-total").html(format(total));
    });

    jQuery('#wphp_service_level').on('change', function () {
        is_delivery_method_changed = true;
        resetPrice()
        jQuery(".wphp-loader").removeClass("wphp-display-none");
        jQuery(".wphp-quantity-sec").addClass("wphp-display-none");
        jQuery(".wphp-product-file-upload").addClass(
           "wphp-display-none"
         );
        jQuery("#wphp-add-to-cart-button").prop("disabled", true);
   
        var [data,selectedAttribute]=getMapDataWithSelectedAttribute();
        if (selectedAttribute == '' || selectedAttribute == null) {
            selectedAttribute = jQuery(".wphp-product-option-selector:last").data('select-id');
        }
        sendRequest(data, selectedAttribute);
    });
    fileUploader(".wphp_product_file_upload",'wphp_product_file_uploaded_path');
    var cartItemsCount = jQuery('#wphp_cart_items_count').val();
    if (cartItemsCount > 0) {
        for (var i = 0; i < cartItemsCount; i++) {
            fileUploaderCartSection(".wphp_cart_file_upload_" + i);
        }
    }

    jQuery('.wphp_file_upload_remove').click(function () {
        var cartItemKey = jQuery(this).data('cart-item-key');
        var fileComponentId = jQuery(this).data('file-component-id');
        addHelloPrintToCallQueue(function () {
            jQuery.ajax({
                method: "POST",
                url: wphp_ajax.ajax_url,
                data: {
                    action: 'remove_wphp_cart_file',
                    cart_item_key: cartItemKey,
                    file_component_id: fileComponentId,
                    _ajax_nonce: wphp_ajax_nonce.value
                },
                success: function (serverResponse) {
                    location.reload();
                }
            })
        });
    });

    jQuery('.wphp_artwork_file_remove').click(function () {
        var id = jQuery(this).data('id');
        var itemId = jQuery(this).data('itemid');
        var orderId = jQuery(this).data('orderid');
        addHelloPrintToCallQueue(function () {
            jQuery.ajax({
                method: "POST",
                url: wphp_ajax.ajax_url,
                data: {
                    action: 'wphp_delete_artwork_of_order',
                    id: id,
                    item_id: itemId,
                    order_id: orderId,
                    _ajax_nonce: wphp_ajax_nonce.value
                },
                success: function (serverResponse) {
                    location.reload();
                }
            })
        });
    });

    jQuery('.wphp-file-upload-button').on('click', function () {
        var uploadedFilesElement = $(this).parent();
        var cartItemKey = uploadedFilesElement.find('.data-item-key');
        var form_data = new FormData();
        var uploadedfiles = uploadedFilesElement.find("[name='wphp_product_file_upload[]']");
        jQuery.each(jQuery(uploadedfiles).prop('files'), function (i, file) {
            form_data.append(i, file);
        });
        form_data.append('action', 'wphp_upload_cart_file');
        form_data.append('cart_item_key', cartItemKey.val());
        form_data.append('_ajax_nonce', wphp_ajax_nonce.value);
        jQuery.ajax({
            method: "POST",
            url: wphp_ajax.ajax_url,
            contentType: false,
            processData: false,
            data: form_data,
            success: function (response) {
                location.reload();
            }
        })
    });
    jQuery('#wphp-add-to-cart-button').click(function (e) {  
        if($(this).data("testmode")){
            alert("Warning: The Helloprint plugin is currently set in 'test' mode. Please set to 'production' in plugin settings")
        }
    });
    // helloprint upload modal commenting for now as should be attached to an endpoint
    function startModal(modalId) {
       const modal = document.getElementById("wphp-pop-up-modal");
       if(modal){
            modal.classList.add('wphp-show-modal');
            modal.addEventListener('click', (e) => {
            if (e.target.id == modalId || e.target.className == 'wphp-close-button'){
                e.preventDefault();
                modal.classList.remove('wphp-show-modal');
            }
        });
       }
     }

     let infoIcon =jQuery(".wphp-info-icon");
     if (infoIcon.length) {
        infoIcon.on("click",function() {
            startModal("wphp-pop-up-modal");
        });
     }
     if($('.wphp_artwork_file_upload').length>0)
     {
        $('.wphp_artwork_file_upload').each(function(){
            var uploder = $(this);
            var item_id = uploder.data("itemid");
            fileUploaderOrderDetailSection(".wphp_artwork_file_upload_" + item_id, item_id);
        })
        
     }
        
    $('.wphp-product-options-apparel-size').on("change", debounce(function (e) {
        jQuery("#wphp-appreal-size-nonstandard-error").hide();
        apparelSizeProcess();
    }, 500));
    
});

debounce = function (func, wait, immediate) {
    var timeout, result;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) result = func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) result = func.apply(context, args);
        return result;
    };
};

function apparelSizeProcess(trigger){
    var total = 0;
    jQuery(".wphp-product-options-apparel-size").each(function(){
        var value = parseInt(jQuery(this).val());
        if(isNaN(value)){
            value = 0;
        }
        total += value;
    });
    jQuery("#wphp-product-total-custom-quantity").html(total);
    jQuery("#wphp-product-total-appreal-size").val(total);
    var emptySelector = jQuery(".wphp-product-option-selector option[value='0']:selected");
    if (emptySelector.length == 0 ) {
        if (total > 0 || trigger == "deeplink") {
            jQuery("#wphp-appreal-size-nonstandard-error").hide();
            var attributes = mapAttributes();
            if (trigger == "deeplink") {
                var selectedAttribute = deeplink_getSelectedAttribute();
            } else {
                var selectedAttribute = currentSelectedAttribute;
            }
            var data = mapData(attributes, selectedAttribute);
            check_quantity_toggle(selectedAttribute);
            sendRequest(data, selectedAttribute);
            deepink_create();
        }
        else{
            deepink_create();
            jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
            jQuery('#wphp-add-to-cart-button').prop("disabled", true);
            resetPrice()
        }
    } else {
        if(jQuery(".wphp-product-selector").length>1){
            jQuery('.wphp-loader').removeClass('wphp-display-none');
            var attributes = mapAttributes();
            if (trigger == "deeplink") {
                var selectedAttribute = deeplink_getSelectedAttribute();
            } else {
                var selectedAttribute = currentSelectedAttribute;
            }
            var data = mapData(attributes, selectedAttribute);
            check_quantity_toggle(selectedAttribute);
            sendRequest(data, selectedAttribute);
            deepink_create();
            
        }
    }

}

function deeplink_updateOptions(){
    console.log(attrOpts);
    jQuery(".wphp-product-option-selector ").each(function(){
        var attr = jQuery(this).attr("data-select-id");
        jQuery(this).children("option").each(function(){
            let optVal = jQuery(this).val()
            if(optVal != 0 && attrOpts[attr] != undefined && attrOpts[attr][optVal] == undefined){
               jQuery('[data-select-id="'+attr+'"] option[value="'+optVal+'"]').remove();
            } else if(optVal != 0 && typeof(attrOpts[attr]) == typeof(undefined)) {
                jQuery('[data-select-id="'+attr+'"] option[value="'+optVal+'"]').addClass("wphp-display-none");
            } else {
                jQuery('[data-select-id="'+attr+'"] option[value="'+optVal+'"]').removeClass("wphp-display-none");
            }
        });
    });
    if (jQuery('div.wphp-size-sec').length) {
        updateSizeImgOption()
    }
    if(jQuery('div.wphp-color-sec').length){
        updateColorImgOption()
    }
}
function deepink_create() {
    let attributes = mapAttributes();
    for (const key of Object.keys(attributes)) {
        let attr = key;
        let attrValue = attributes[key];
        if(attr == 'wphp_options') {
            jQuery.each(attributes[key], function(i, val){
                if(val != 0 && val != null){
                    urlParams.set("print_custom_" + i, val);
                }
            });
        }
        else if(urlParams.has(attr) && attrValue!=0 && attrValue != null){
            urlParams.set(attr, attrValue);
        }else if(urlParams.has(attr) && attrValue==0 && attrValue != null){
            urlParams.delete(attr);
        }else if (attrValue!=0 && attrValue != null){
            urlParams.set(attr, attrValue);
        }
    }

    if (jQuery(".wphp-product-country-selector").length > 0) {
        urlParams.set('destination_country', jQuery(".wphp-product-country-selector").val());
    }
    var queryString = urlParams.toString();
    if(queryString != ''){
        window.history.replaceState(null, null, `?${queryString}`);
    }else{
        window.history.replaceState({}, '', location.pathname);
    }
}
function deeplink_getSelectedAttribute() {
    let data_position = 0;
    for(var [key, value] of urlParams.entries()) {
        var new_data_position = jQuery('[data-select-id="'+key+'"]').attr("data-position");
        if (new_data_position > data_position) {
            data_position = new_data_position
        }
    }
    currentSelectedAttribute = jQuery('[data-position='+data_position+']').attr('data-select-id');  
    return jQuery('[data-position='+data_position+']').attr('data-select-id');
}
function deeplink_checks() {
    if (jQuery('.wphp-product-selector').length > 0) {
        let sizeImgVal = jQuery('.wphp-sizeRadio:checked').val();
        let colorImgVal = jQuery('.wphp-colorRadio:checked').val();
        if (jQuery('.wphp-product-option-selector').length == jQuery(".wphp-product-option-selector option[value='0']:selected").length) {
            if(!sizeImgVal && !colorImgVal){
                if (jQuery(".wphp-product-country-selector").length > 0) {
                    wphp_load_with_destination_country();
                }
                return false;
            }
        }   
    }
    if (urlParams != ''){ 
        if(productWithCountrySelector){
            for(var [key, value] of urlParams.entries()) {
                if (key == 'destination_country') {
                    jQuery(".wphp-product-country-selector").val(value);
                }
            }
        }
        if(productWithApparealSize){
            for(var [key, value] of urlParams.entries()) {
                if (jQuery("input#"+key).length) {
                    jQuery("input#"+key).val(value);
                }
                if (jQuery("input#"+key+"_hidden").length) {
                    jQuery("input#"+key+"_hidden").val(value);
                }
            }
            apparelSizeProcess('deeplink');
            return false;
        }
        let attributes = mapAttributes();
        var selectedAttribute = deeplink_getSelectedAttribute();
        var data = mapData(attributes, selectedAttribute);
        check_quantity_toggle(selectedAttribute);
        sendRequest(data, selectedAttribute);
    } else {
        if (jQuery(".wphp-product-country-selector").length > 0) {
            wphp_load_with_destination_country();
        }
    }
}
function check_quantity_toggle(selectedAttribute){
    let attributes = mapAttributes();
    let showQnt = true;
    let selectedAttrVal = jQuery('[data-select-id="'+selectedAttribute+'"]').val();
    let selectedDataPos = jQuery('[data-select-id="'+selectedAttribute+'"]').attr("data-position");
    let lastDataPos = 0;
        for (var key of Object.keys(attributes)) {
            var new_data_position = jQuery('[data-select-id="'+key+'"]').attr("data-position");
            if (new_data_position > lastDataPos) {
                lastDataPos = new_data_position;
            }
        }
    if (Object.keys(attributes).length === 0) {
        showQnt = false;
    }else{
        for (const key in attributes) {
            if (attributes[key] == '0' && key.indexOf("appreal") < 0) {
                showQnt = false;
                break;
            }
            //Check if height/width key/value exists
            if(typeof attributes[key] === 'object' && attributes[key] !== null) {
                if(attributes[key]['height'] == '' || attributes[key]['width' == '']){
                    showQnt = false;
                }
            }
        }
        if(selectedDataPos && showQnt && selectedAttrVal != 0 && selectedDataPos == lastDataPos){
            showQnt = true;
        } else if(selectedDataPos && selectedDataPos != lastDataPos){
            showQnt = false;
        }
    }
    if (showQnt && !productWithApparealSize) {
        jQuery('.wphp-loader').removeClass('wphp-display-none');
    }
    if(showQnt && productWithApparealSize && selectedAttribute){
        jQuery('.wphp-quantity-sec').removeClass('wphp-display-none')
    }
}
function selectProductQuantity(quantity) {
    if (global_lazy_loading == true) {
        return false;
    }
    var deliveryOption = jQuery('#wphp_service_level');
    var oldDeliveryOptionValue = deliveryOption.val();
    jQuery('#wphp_service_level').find('option').remove();
    if (oldDeliveryOptionValue != '0' || oldDeliveryOptionValue === null) {
        deliveryOption.find('option').remove();
        jQuery(jQuery(window.serviceLevelDays).get().reverse()).each(function (key, item) {
            if (item) {
                var label = item.label;
                var option = label.charAt(0).toUpperCase() + label.slice(1);
                deliveryOption.append(new Option(option, item.value));
            }
        });
    }
    jQuery(".quantityRadio[value='" + quantity + "'").prop("checked", true);
    
    jQuery("#wphp_service_level option[value='" + oldDeliveryOptionValue + "'").prop('selected', true);

    var days_for_you = 0;
    if (typeof(window.serviceLabelObj) != typeof(undefined)) {
        days_for_you = window.serviceLabelObj.find(function (sLevel) {
            if (sLevel.value == oldDeliveryOptionValue) {
                return sLevel.days;
            }
        });
        if (typeof(days_for_you) != typeof(undefined) && typeof(days_for_you.days) != typeof(undefined)) {
            days_for_you = days_for_you.days;
        }
    }
    jQuery("#wphp_total_delivery_days_including_buffer").val(days_for_you);
}
function mapAttributes(reset_price_table = true) {
    if (reset_price_table == true) {
        jQuery("#wphp-hidden-current-pricing-page").val(1);
    }
    selectProductQuantity();
    var attributes = {};
    var attributesText = {};
    var attributextTextLabels = {};
    if (jQuery(".wphp_product_options_hidden").length) {
        var options = [];
        jQuery('.wphp_product_options_hidden').each(function () {
            options[jQuery(this).attr('name')] = jQuery(this).val();
            attributesText[jQuery(this).attr('name')] = jQuery(this).val();
            attributextTextLabels[jQuery(this).data('label')] = jQuery(this).val();
        });
        attributes.wphp_options = Object.assign({}, options);
    }
    if (jQuery('.wphp-img-selector').length) {
        const imgSelector = document.querySelectorAll('.wphp-img-selector');
        for (const elem of imgSelector) {

            if (elem.classList.contains('wphp-size-sec')){
                console.log("size");
                let sizeChecked = jQuery(".wphp-sizeRadio:checked");
                let sizeRadios = jQuery('.wphp-sizeRadio');
                if (sizeChecked.length) {
                    attributes[sizeChecked.data('select-id')] = sizeChecked.val();
                    attributesText[sizeChecked.data('select-id')] = sizeChecked.data('text');
                    attributextTextLabels[sizeChecked.data('label')] = sizeChecked.data('text');
                } else {
                    attributes[sizeRadios.data('select-id')] = 0;
                    attributesText[sizeRadios.data('select-id')] = '';
                    attributextTextLabels[sizeRadios.data('label')] = '';
                }
            }

            else if (elem.classList.contains('wphp-color-sec')) {
                console.log("color");
                let colorRadios = jQuery('.wphp-colorRadio');
                let colorChecked = jQuery(".wphp-colorRadio:checked");
                if (colorChecked.length) {
                    attributes[colorChecked.data('select-id')] = colorChecked.val();
                    attributesText[colorChecked.data('select-id')] = colorChecked.data('text');
                    attributextTextLabels[colorChecked.data('label')] = colorChecked.data('text');
                } else {
                    attributes[colorRadios.data('select-id')] = 0;
                    attributesText[colorRadios.data('select-id')] = '';
                    attributextTextLabels[colorChecked.data('label')] = '';
                }
            }

            else if (elem.classList.contains('wphp-print-position-sec')){
                console.log("Printpos");
                let printPositionChecked = jQuery(".wphp-printPositionRadio:checked");
                let printPositionRadios = jQuery('.wphp-printPositionRadio');
                if (printPositionChecked.length) {
                    attributes[printPositionChecked.data('select-id')] = printPositionChecked.val();
                    attributesText[printPositionChecked.data('select-id')] = printPositionChecked.data('text');
                    attributextTextLabels[printPositionChecked.data('label')] = printPositionChecked.data('text');
                } else {
                    attributes[printPositionRadios.data('select-id')] = 0;
                    attributesText[printPositionRadios.data('select-id')] = '';
                    attributextTextLabels[printPositionRadios.data('label')] = '';
                }
            }
        }
    }

    jQuery('.wphp-product-option-selector').each(function () {
        attributes[jQuery(this).data('select-id')] = jQuery(this).val();
        attributesText[jQuery(this).data('select-id')] = jQuery(this).find('option:selected').text();
        attributextTextLabels[jQuery(this).data('label')] = jQuery(this).find('option:selected').text();
    });
    if (jQuery('.wphp-product-options-apparel-size').length > 0) {
        jQuery('.wphp-product-options-apparel-size').each(function () {
            attributes[jQuery(this).attr("id")] = jQuery(this).val();
            attributesText[jQuery(this).attr("name")] = jQuery(this).val();
            attributextTextLabels[jQuery(this).data('label')] = jQuery(this).val();
        });
    }
    
    jQuery('#wphp_product_options').val(JSON.stringify(attributesText));
    jQuery('#wphp_product_options_labels').val(JSON.stringify(attributextTextLabels));
    return attributes;
}
function mapData(attributes, selectedAttribute = '') {
    return {
        'action': 'get_wphp_variant_filter',
        'product_id': jQuery('#product_id').val(),
        'wphp_external_product_id': jQuery('#wphp_external_product_id').val(),
        'wphp_product_quantity': (jQuery("#wphp-product-total-appreal-size").length > 0) ? jQuery("#wphp-product-total-appreal-size").val() : jQuery(".quantityRadio:checked").val(),
        'wphp_appreal_quantity': (jQuery("#wphp-product-total-appreal-size").length > 0) ? jQuery("#wphp-product-total-appreal-size").val() : -1,
        'delivery_type': jQuery('#wphp_service_level').val(),
        'wphp_selected_attribute': selectedAttribute,
        'wphp_attributes': attributes,
        '_ajax_nonce':wphp_ajax_nonce.value
    }
}
function sendRequest(data, selectedAttribute = '', is_lazy_loading = false) {
    global_lazy_loading = is_lazy_loading;
    currentSelectedAttribute = selectedAttribute;
    jQuery(".wphp-combination-not-found").addClass("wphp-display-none");
    if (jQuery(".wphp-product-country-selector").length > 0) {
        data.destination_country = jQuery(".wphp-product-country-selector").val();
    }
    if (jQuery("#pp_main_btn_sec").length > 0 && jQuery("#wphp-add-to-cart-button").length > 0) {
        jQuery("#pp_main_btn_sec").hide();
    }
    var total = +jQuery("#wphp-product-total-appreal-size").val();
    var emptySelector = jQuery(".wphp-product-option-selector option[value='0']:selected");

    if (!is_lazy_loading) {
        jQuery('#wphp-add-to-cart-button').prop("disabled", true);
        jQuery('#wphp-btn-confirm').prop("disabled", true);
        jQuery('.wphp-options').attr('disabled', 'disabled');
        disableImages();
    }

    data.pricing_quantity_page = jQuery("#wphp-hidden-current-pricing-page").val();
    data.lazy_loading = is_lazy_loading;
    if (is_lazy_loading == true && is_delivery_method_changed == true) {
        return false;
    }
    jQuery.post(wphp_ajax.ajax_url, data, function (response) {
        if (response.success == true) {
            if (response.data) {
                if(is_delivery_method_changed == true && response.data.serviceLevel.length <= 0) {
                    setTimeout(function(){
                        sendRequest(data, selectedAttribute, is_lazy_loading);
                    }, 500);
                    
                    return false;
                }
                hideLoader(currentSelectedAttribute);
                if (!is_lazy_loading) {
                    jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
                }
                window.attrOpts = response.data.attributeOptions
                if (typeof(response.data.current_pricing_page) != typeof(undefined)) {
                    if (response.data.current_pricing_page == 1) {
                        window.quotes = response.data.quotes;
                    } else {
                        jQuery.each(response.data.quotes, function(key, singlequote) {
                            window.quotes.push(singlequote);
                        });
                    }
                } else {
                    window.quotes = response.data.quotes;
                }
                window.serviceLabelObj = response.data.serviceLevel;
                window.site_country = response.data.site_country;
                window.currency = response.data.site_currency;
                window.serviceLevel = Object.keys(serviceLabelObj).map(function (key, index) {
                    return serviceLabelObj[key].value;
                });
                window.serviceLevelDays = Object.keys(serviceLabelObj).map(function (key, index) {
                    return serviceLabelObj[key];
                });
                jQuery('#wphp_product_tax_rate_input').val(response.data.tax_rate);
                if (!is_lazy_loading) {
                    if (response.data.quotes.length <= 0 && response.data.variants.length <= 0 && typeof(response.data.combination_not_found) != typeof(undefined)) {
                        if (response.data.combination_not_found) {

                            jQuery(".wphp-combination-not-found-message").html(response.data.combination_not_found_msg);
                            jQuery(".wphp-combination-not-found").removeClass("wphp-display-none");
                            jQuery(".wphp-loader").addClass("wphp-display-none");
                            jQuery(".wphp-pricing-table-loader").addClass("wphp-display-none");
                            if (jQuery(".wphp-size-quantity").length <= 0) {
                                jQuery(".wphp-quantity-sec").addClass("wphp-display-none");
                            }
                        } else {
                            jQuery(".wphp-combination-not-found").addClass("wphp-display-none");
                        }
                    } else {
                        jQuery(".wphp-combination-not-found").addClass("wphp-display-none");
                    }
                }
                if (response.data.variants && !is_lazy_loading) {
                    jQuery.each(response.data.variants, function (index, value) {
                        var ifnameexists = false;
                        jQuery.each(value, function (k, vals) {
                            if(typeof(vals.name) != typeof(undefined)) {
                                ifnameexists = true;
                            }
                        });
                        if (ifnameexists) {
                            var option = jQuery('#wphp_product_option_' + index);
                            var oldOptionValue = option.val();
                            option.find('option').remove();

                            
                            option.append(new Option(jQuery('#wphp_select_one').val(), '0'));
                            jQuery.each(value, function (key, val) {
                                option.append(new Option(val.name, key));
                            });
                            var selectedPosition = jQuery('#wphp_product_option_' + selectedAttribute).data('position');
                            if (option.attr('data-position') <= selectedPosition) {
                                jQuery("#wphp_product_option_" + index + " option[value='" + oldOptionValue + "'").prop('selected', true);
                            }
                        }
                        
                    });
                }

                deepink_create();

                if(jQuery('div.wphp-color-sec').length && updateCount == 1 && !is_lazy_loading){
                    var selectedDataPos = 1;
                    if (selectedAttribute != '') {
                       selectedDataPos = jQuery('[data-select-id="'+selectedAttribute+'"]').attr("data-position");  
                    }
                    let sizeDataPos = jQuery('.wphp-sizeRadio').attr("data-position");
                    let colorDataPos = jQuery('.wphp-colorRadio').attr("data-position");
                    if(selectedDataPos < colorDataPos){
                        updateColorImgOption()
                    } else if (selectedDataPos < sizeDataPos){
                        updateSizeImgOption()
                    }
                }
                updatePrintAreaImgOption();
                if (attrOpts && updateCount==0 ) {
                    deeplink_updateOptions();
                    updateCount++;  
                    
                }
                if (jQuery('.wphp-product-option-selector').length == jQuery(".wphp-product-option-selector option[value='0']:selected").length) {   
                    if (jQuery(".wphp-product-country-selector").length > 0) {
                        updateCount = 0;
                    }
                }

                    var quantity_grp = jQuery('.wphp-quantity-grp');
                    var quantityOption = jQuery('.quantityRadio:checked');
                    var oldQuantityValue = quantityOption.val();
                    var current_pricing_page = response.data.current_pricing_page;
                    var is_next_pricing_page_available = response.data.is_next_pricing_page;
                    var next_pricing_page = response.data.next_pricing_page;

                    jQuery('.wphp-quantity-wrp a.wphp-showmore').attr("is_next_available", is_next_pricing_page_available);
                    jQuery('#wphp-hidden-current-pricing-page').val(next_pricing_page);
                    if (oldQuantityValue != '0') {
                        if (current_pricing_page == 1) {
                            quantity_grp.empty();
                        }
                        jQuery(response.data.quantities).each(function (key, item) {
                            jQuery('.wphp-loader').addClass('wphp-display-none');
                            jQuery(".wphp-pricing-table-loader").addClass("wphp-display-none");
                            jQuery('.wphp-quantity-sec').removeClass('wphp-display-none')
                            jQuery('.wphp-product-file-upload').removeClass('wphp-display-none');
                            jQuery.each(item, function (key, val) {
                                var formattedPrice = (jQuery(".wphp_product_price_exclude_tax").length > 0) ? val.centAmountExclTax : val.centAmountInclTax;
                                if (jQuery("[name='wphp_product_quantity'][value='"+val.quantity+"']").length <= 0) {
                                    quantity_grp.append(
                                        `<div>
                                            <input class="quantityRadio" type="radio" data-key="${key}" name="wphp_product_quantity" value="${val.quantity}">
                                            <label for="html">${val.quantity}</label>
                                            <span class="pricee"><span>${formattedPrice}</span>
                                            </span>
                                        </div>`
                                    );
                                }
                                
                        });
                    });
                    if (!is_lazy_loading) {
                        jQuery('.wphp-quantity-grp').find('div:first-child input').prop("checked", true)
                    }
                    // Quantity Show more button script
                    jQuery('.wphp-quantity-grp').css('max-height','')
                    var qGrpHeight = jQuery('.wphp-quantity-grp').height();
                    var qHeight = jQuery('.wphp-quantity-grp div').height();
                    var maxHeight = qHeight*7;
                    
                    if (is_next_pricing_page_available == true) {
                            jQuery('.wphp-quantity-wrp a.wphp-showmore').attr("style","display:block");
                            jQuery('.wphp-quantity-wrp a').css('visibility','visible');
                            jQuery(".wphp-quantity-wrp").addClass("less");
                            jQuery(".wphp-quantity-wrp").removeClass("more");
                            jQuery('.wphp-quantity-grp').css('max-height', '');
                            
                    } else {
                            if (current_pricing_page > 1 && qGrpHeight > maxHeight) {
                                if(jQuery('.wphp-quantity-wrp a.wphp-showmore').is(":hidden")) {
                                    jQuery(".wphp-quantity-wrp").removeClass("less");
                                    jQuery(".wphp-quantity-wrp").addClass("more");
                                    jQuery('.wphp-quantity-grp').css('max-height', '');
                                    jQuery('.wphp-quantity-wrp a').css('visibility','visible');
                                } else {
                                    jQuery(".wphp-quantity-wrp").addClass("less");
                                    jQuery(".wphp-quantity-wrp").removeClass("more");
                                    jQuery('.wphp-quantity-grp').css('max-height', maxHeight+'px');
                                    jQuery('.wphp-quantity-wrp a').css('visibility','visible');
                                }
                                
                            } else {
                                jQuery('.wphp-quantity-wrp a').css('visibility','hidden');
                                jQuery('.wphp-quantity-wrp a.wphp-showmore').attr("style","display:none!important");
                            }
                    }
                    
                    jQuery('.wphp-quantity-wrp a.wphp-show-less-pricing-btn').unbind().click(function () {
                        wphp_toggle_price_quantity();
                    });

                    jQuery('.wphp-quantity-wrp a.wphp-show-more-pricing-btn').unbind().click(function () {
                        var next_available = jQuery(this).attr("is_next_available");
                        if (next_available === 'true' || next_available === true) {
                            jQuery('.wphp-quantity-wrp a.wphp-showmore').attr("style","display:block");
                            jQuery(".wphp-pricing-table-loader").removeClass("wphp-display-none");
                            jQuery('.wphp-quantity-wrp').toggleClass('less more');
                            jQuery('.wphp-quantity-wrp a').css('visibility','hidden');
                            jQuery('.wphp-quantity-grp').css('max-height','')
                        } else {
                            wphp_toggle_price_quantity();
                        }
                        
                    });
                }
                if (!is_lazy_loading && response.data.current_pricing_page == 1) {
                    selectProductQuantity(jQuery('.quantityRadio:checked').val());
                }

                resetPrice()
                
                if (jQuery('.quantityRadio:checked').val()) {

                let oldQntFound = false;
                if (oldQuantityValue) {
                    var find = window.quotes.find(function (quote) {
                        if (oldQuantityValue == quote.quantity && jQuery('#wphp_service_level').val() == quote.serviceLevel) {
                            oldQntFound = true;
                            quantity = oldQuantityValue;
                            return quote;
                        }
                    })
                }
                if(!oldQntFound){
                    quantity = jQuery('.quantityRadio:checked').val();
                    var find = window.quotes.find(function (quote) {
                        if (quantity == quote.quantity && jQuery('#wphp_service_level').val() == quote.serviceLevel) {
                            return quote;
                        }
                    });
                }
                var taxIncl = jQuery('#wphp_tax_incl').val();
                
                if (find) {
                    jQuery("#wphp_currency").val(response.data.site_currency);
                    jQuery("#wphp_country").val(response.data.site_country);
                    jQuery("#wphp_thousand_separator").val(response.data.thousand_separator);
                    formattedPriceFromAjax(find.prices.centAmountExclTax/100,'.wphp_product_price_exclude_tax');
                    formattedPriceFromAjax(find.prices.centAmountInclTax/100,'.wphp_product_price');
                    productWithApparealSize ? formattedPriceFromAjax(find.prices.centAmountInclTax/100,'.wphp-apparealsize-price') : '';
                    jQuery('#hello_product_sku').val(find.sku);
                    jQuery('#hello_product_variant_key').val(find.variantKey);
                    jQuery('.wphp_product_variant_key').html(find.variantKey);
                    jQuery('#wphp_product_excl_tax_price_input').val(find.prices.centAmountExclTax / 100);
                    jQuery('#wphp_product_incl_tax_price_input').val(find.prices.centAmountInclTax / 100);
                    if(taxIncl){
                        jQuery('#wphp_product_price_input').val(find.prices.centAmountInclTax / 100);
                    }else{
                        jQuery('#wphp_product_price_input').val(find.prices.centAmountExclTax / 100);
                    }
                    jQuery('#wphp-add-to-cart-button').removeAttr('disabled');
                    jQuery('.wphp-product-file-upload').removeClass('wphp-display-none');
                    jQuery('.wphp_product_price_exclude_tax_without_margin').html(find.prices.origcentAmountExclTax / 100);
                    jQuery('.wphp_product_price_without_margin').html(find.prices.origcentAmountInclTax / 100);
                    var exclTaxOption = jQuery('#wphp_product_show_only_incl_vat_input').val();
                    if(exclTaxOption==1){
                        var designPriceExclPrice = parseFloat(jQuery('#wphp-design-price-hidden').val());
                        jQuery('.wphp-design-price-label').hide();
                        var designPriceInclTax = designPriceExclPrice * (1+(response.data.tax_rate/100));
                        formattedPriceFromAjax(designPriceInclTax,'.wphp-design-incl-tax-price-label');
                    }
                    updatePriceWithGraphic();
                }
                jQuery('.wphp_product_currency').html(" (" + response.data.currency + ")");
                }
                if (typeof oldQuantityValue != 'undefined') {
                    jQuery(document).find(".wphp-quantity-grp input[value='" + oldQuantityValue + "'").prop("checked", true)
                }
                jQuery('.wphp-options').removeAttr('disabled');
                jQuery('#wphp-btn-confirm').removeAttr("disabled");
                enableImages();
                // Non standard size-quantity error show
                if (productWithApparealSize && total > 0 && response.data.min_max_qty_message != '' && emptySelector.length == 0 && jQuery("#wphp-appreal-size-nonstandard-error").length > 0){
                    var minMaxQnt = response.data.min_max_qty_message.match(/\d+/g);;
                    var minQnt = +minMaxQnt[0]
                    var MaxQnt = +minMaxQnt[1]
                    if (total < minQnt || total > MaxQnt ) {
                        jQuery("#wphp-appreal-size-nonstandard-error").show();
                        jQuery('#wphp-add-to-cart-button').prop("disabled", true);
                        resetPrice()
                    } else if(emptySelector.length == 0 && total > 0 && response.data.quantities.length <= 0) {
                        jQuery("#helloprint-appreal-size-nonstandard-error").show();
                    } else {
                        jQuery("#wphp-appreal-size-nonstandard-error").hide();
                    }
                }

                else if (productWithApparealSize && emptySelector.length == 0 && total > 0 && response.data.quantities.length <= 0){
                    jQuery("#wphp-appreal-size-nonstandard-error").show();
                    jQuery('#wphp-add-to-cart-button').prop("disabled", true);
                    resetPrice()
                }
                else{
                    jQuery("#wphp-appreal-size-nonstandard-error").hide();
                }
                if(response.data.min_max_qty_message.length > 0 && !is_lazy_loading){
                    jQuery("#wphp-min_max_message_div").html(response.data.min_max_qty_message);
                }
                // initilaize pitchprint if design id matches and pitchprint plugin activated
                var pitchprint_api_key = "";
                var pitch_print_design_id = "";
                if (typeof(response.data.pitch_print_design_id) != undefined) {
                    pitch_print_design_id = response.data.pitch_print_design_id;
                }
                if (typeof(response.data.pitch_print_api_key) != undefined) {
                    pitchprint_api_key = response.data.pitch_print_api_key;
                }
                initilialize_wphp_pitchprint(pitchprint_api_key, pitch_print_design_id);

                console.log("Next pricing page  :: " + response.data.is_next_pricing_page);
                is_delivery_method_changed = false;
                if (response.data.is_next_pricing_page === true) {
                    console.log(" inside if statement");
                    sendRequest(data, currentSelectedAttribute, true);
                }
            }
        }
    });
}

var ajaxCallQueue = [];
var ajaxLock = false;

function addHelloPrintToCallQueue(callbackFn) {
    if (ajaxLock == false) {
        callbackFn(() => { runQue() });
        ajaxLock = true;
    } else {
        ajaxCallQueue.push(callbackFn);
    }
}
function runQue() {
    if (ajaxCallQueue.length) {
        let callbackFn = ajaxCallQueue.pop();
        callbackFn(() => { runQue() });
    } else {
        ajaxLock = false;
    }
}
function fileUploader(name,hiddenFilePathFieldName) {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginPdfPreview
    );
    FilePond.create(
        document.querySelector(name),
        {
           acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword',
                'application/x-zip-compressed', 'application/octet-stream', 'application/postscript'],
            maxFileSize: wphp_file_max_upload_size.maxSize,
        }
    );
    FilePond.setOptions({
        labelIdle: wphp_ajax_translate.labelIdle,
        labelInvalidField: wphp_ajax_translate.labelInvalidField,
        labelFileWaitingForSize: wphp_ajax_translate.labelFileWaitingForSize,
        labelFileSizeNotAvailable: wphp_ajax_translate.labelFileSizeNotAvailable,
        labelFileLoading: wphp_ajax_translate.labelFileLoading,
        labelFileLoadError: wphp_ajax_translate.labelFileLoadError,
        labelFileProcessing: wphp_ajax_translate.labelFileProcessing,
        labelFileProcessingComplete: wphp_ajax_translate.labelFileProcessingComplete,
        labelFileProcessingAborted: wphp_ajax_translate.labelFileProcessingAborted,
        labelFileProcessingError: wphp_ajax_translate.labelFileProcessingError,
        labelFileProcessingRevertError: wphp_ajax_translate.labelFileProcessingRevertError,
        labelFileRemoveError: wphp_ajax_translate.labelFileRemoveError,
        labelTapToCancel: wphp_ajax_translate.labelTapToCancel,
        labelTapToRetry: wphp_ajax_translate.labelTapToRetry,
        labelTapToUndo: wphp_ajax_translate.labelTapToUndo,
        labelButtonRemoveItem: wphp_ajax_translate.labelButtonRemoveItem,
        labelButtonAbortItemLoad: wphp_ajax_translate.labelButtonAbortItemLoad,
        labelButtonRetryItemLoad: wphp_ajax_translate.labelButtonRetryItemLoad,
        labelButtonAbortItemProcessing: wphp_ajax_translate.labelButtonAbortItemProcessing,
        labelButtonUndoItemProcessing: wphp_ajax_translate.labelButtonUndoItemProcessing,
        labelButtonRetryItemProcessing: wphp_ajax_translate.labelButtonRetryItemProcessing,
        labelButtonProcessItem: wphp_ajax_translate.labelButtonProcessItem,
        maxFileSize: wphp_file_max_upload_size.maxSize,
        labelMaxFileSize: wphp_ajax_translate.labelMaxFileSize,
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                var inputFieldName=fieldName.replace('[]','');
                const request = new XMLHttpRequest();
                addHelloPrintToCallQueue(function (que) {
                    var formData = new FormData();
                    formData.append(fieldName, file, file.name);
                    formData.append('action', 'wphp_upload_cart_file');
                    formData.append('no_cart', 1);
                    formData.append('_ajax_nonce', wphp_ajax_nonce.value);
                    request.open('POST', wphp_ajax.ajax_url);
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            var parsedResponse = JSON.parse(request.responseText);
                            if(parsedResponse.success == false){
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            }else{
                                if (parsedResponse && parsedResponse.data) {
                                    for (let imgDetails of parsedResponse.data) {
                                        jQuery("<input>").attr({
                                            name: `${hiddenFilePathFieldName}[]`,
                                            id: `file-${imgDetails.file_name}`,
                                            type: "hidden",
                                            value: `${JSON.stringify(imgDetails)}`
                                        }).appendTo(`#${inputFieldName}`);
                                    }
                                    load(JSON.stringify(parsedResponse.data));
                                }
                            }
                        } else {
                            if (request.responseText != '0') {
                                var parsedResponse = JSON.parse(request.responseText);
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            } else {
                                alert(wphp_ajax_translate.labelMaxFileSize);
                                error(wphp_ajax_translate.labelMaxFileSize);
                            }
                            //error('oh no Filepond error');
                        }
                        que();
                    };
                    request.send(formData);
                });
                return {
                    abort: () => {
                        console.log("aborted");
                        request.abort();
                        abort();
                    },
                };

            },
            revert: (uniqueFileId, load, error) => {
                var json = JSON.parse(uniqueFileId)
               // console.log(json);
                if (json) {
                    jQuery.ajax({
                        method: "POST",
                        url: wphp_ajax.ajax_url,
                        data: {
                            action: 'remove_wphp_product_file',
                            wphp_file: json[0].file_path,
                            _ajax_nonce: wphp_ajax_nonce.value
                        },
                        success: function (serverResponse) {
                            jQuery(`[id='file-${json[0].file_name}']`).remove()
                        }
                    })
                }
                error('oh my goodness');
                load();
            }
        },
        oninitfile(){
            jQuery("#wphp-add-to-cart-button").prop("disabled", true);
        },
        onprocessfileabort(file){
            if (totalFilesSelected <= 1 ) {
                jQuery("#wphp-add-to-cart-button").prop("disabled", false);
            }
        },
        onprocessfileprogress(file, progress){
            jQuery("#wphp-add-to-cart-button").prop("disabled", true);
        },
        onprocessfiles(){
            jQuery("#wphp-add-to-cart-button").prop("disabled", false);
        },
        onupdatefiles(files){
            window.totalFilesSelected = files.length;
        }
    });
}
function fileUploaderCartSection(name) {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation
    );
    const pond = FilePond.create(
        document.querySelector(name),
        {
           acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword',
           'application/x-zip-compressed', 'application/octet-stream',  'application/postscript'],
            maxFileSize: wphp_file_max_upload_size.maxSize,
        }
    );
    FilePond.setOptions({
        labelIdle: wphp_ajax_translate.labelIdle,
        labelInvalidField: wphp_ajax_translate.labelInvalidField,
        labelFileWaitingForSize: wphp_ajax_translate.labelFileWaitingForSize,
        labelFileSizeNotAvailable: wphp_ajax_translate.labelFileSizeNotAvailable,
        labelFileLoading: wphp_ajax_translate.labelFileLoading,
        labelFileLoadError: wphp_ajax_translate.labelFileLoadError,
        labelFileProcessing: wphp_ajax_translate.labelFileProcessing,
        labelFileProcessingComplete: wphp_ajax_translate.labelFileProcessingComplete,
        labelFileProcessingAborted: wphp_ajax_translate.labelFileProcessingAborted,
        labelFileProcessingError: wphp_ajax_translate.labelFileProcessingError,
        labelFileProcessingRevertError: wphp_ajax_translate.labelFileProcessingRevertError,
        labelFileRemoveError: wphp_ajax_translate.labelFileRemoveError,
        labelTapToCancel: wphp_ajax_translate.labelTapToCancel,
        labelTapToRetry: wphp_ajax_translate.labelTapToRetry,
        labelTapToUndo: wphp_ajax_translate.labelTapToUndo,
        labelButtonRemoveItem: wphp_ajax_translate.labelButtonRemoveItem,
        labelButtonAbortItemLoad: wphp_ajax_translate.labelButtonAbortItemLoad,
        labelButtonRetryItemLoad: wphp_ajax_translate.labelButtonRetryItemLoad,
        labelButtonAbortItemProcessing: wphp_ajax_translate.labelButtonAbortItemProcessing,
        labelButtonUndoItemProcessing: wphp_ajax_translate.labelButtonUndoItemProcessing,
        labelButtonRetryItemProcessing: wphp_ajax_translate.labelButtonRetryItemProcessing,
        labelButtonProcessItem: wphp_ajax_translate.labelButtonProcessItem,
        maxFileSize: wphp_file_max_upload_size.maxSize,
        labelMaxFileSize: wphp_ajax_translate.labelMaxFileSize,
        server: {
            url: wphp_ajax.ajax_url,
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                const request = new XMLHttpRequest();
                addHelloPrintToCallQueue(function (que) {
                    var uploadedFilesElement = jQuery('.upload_container_button');
                    var cartItemKey = '';
                    var formData = new FormData();
                    formData.append(fieldName, file, file.name);
                    formData.append('action', 'wphp_upload_cart_file');
                    formData.append('_ajax_nonce', wphp_ajax_nonce.value);
                   
                    uploadedFilesElement.find('.wphp-cart-file-upload').each((index, elem) => {
                        
                        if (elem.innerText.includes(file.name)) {
                            cartItemDiv = elem.nextElementSibling;
                            var cartDataArray = JSON.parse(cartItemDiv.getAttribute('data-arraydata')) || [];
                            if ( !cartDataArray.includes(file.name) ) {
                                cartItemKey = cartItemDiv.getAttribute('data-cart-item-key');
                                cartDataArray.push(file.name);
                                cartItemDiv.setAttribute('data-arraydata', JSON.stringify(cartDataArray));
                            }
                        }
                    });

                    formData.append('cart_item_key', cartItemKey);
                    request.open('POST', wphp_ajax.ajax_url);
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            // Check for file validation
                            var parsedResponse = JSON.parse(request.responseText);
                            if(parsedResponse.success == false){
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            }else{
                                if (parsedResponse && parsedResponse.data){
                                    load(JSON.stringify(parsedResponse.data));
                                }
                            }
                        } else {
                            if (request.responseText != '0') {
                                var parsedResponse = JSON.parse(request.responseText);
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            } else {
                                alert(wphp_ajax_translate.labelMaxFileSize);
                                error(wphp_ajax_translate.labelMaxFileSize);
                            }
                        }
                        que();
                    };
                    request.send(formData);
                });
                return {
                    abort: () => {
                        request.abort();
                        abort();
                    },
                };

            },
            revert: (uniqueFileId, load, error) => {
                // console.log("in reverty", ajaxLock, uniqueFileId);
                var json = JSON.parse(uniqueFileId)

                jQuery.ajax({
                    method: "POST",
                    url: wphp_ajax.ajax_url,
                    data: {
                        action: 'remove_wphp_cart_file',
                        cart_item_key: json.cart_item_key,
                        file_component_id: json.file_component_id,
                        _ajax_nonce: wphp_ajax_nonce.value
                    },
                    success: function (serverResponse) {
                    }
                })
                error('oh my goodness');
                load();
            },
            remove: (source, load, error) => {
                error('oh my goodness');
                load();
            },
        },
    });
}

function formatCurrency(country, currency, number)
{
    return new Intl.NumberFormat(country, { style: 'currency', currency: currency }).format(number)
}

function updatePriceWithGraphic() {
    if (jQuery(".wphp_design").length <= 0) {
        return false;
    }
    var designprice = parseFloat(jQuery(".wphp_design:checked").val());
    var exclTaxOption = jQuery('#wphp_product_show_only_incl_vat_input').val();
    var taxRate = parseFloat(jQuery('#wphp_product_tax_rate_input').val());
    if (designprice > 0) {
        if (exclTaxOption == 0) {
            var realPriceExclTax = parseFloat(jQuery('#wphp_product_excl_tax_price_input').val());
            if(realPriceExclTax >= 0){
                var priceExclTax = parseFloat(realPriceExclTax + parseFloat(designprice));
                formattedPriceFromAjax(priceExclTax,'.wphp_product_graphic_service_price_excl_tax');
                formattedPriceFromAjax(realPriceExclTax,'.wphp_product_original_price_excl_tax','(');
                formattedPriceFromAjax(designprice,'.wphp_product_design_artwork_excl_price',' + ',')');
                jQuery(".wphp_product_graphic_service_price_excl_tax").show();
                jQuery(".wphp_product_original_price_excl_tax").show();
                jQuery(".wphp_product_design_artwork_excl_price").show();
                jQuery(".wphp_product_price_exclude_tax").hide();
            }
            
        }
        var realprice = parseFloat(jQuery('#wphp_product_incl_tax_price_input').val());
        if (realprice >= 0) {
            var designPriceInclTax = designprice* (1+(taxRate/100));
            var price = parseFloat(realprice + parseFloat(designPriceInclTax));
            formattedPriceFromAjax(price,'.wphp_product_price_including_graphic_service');
            formattedPriceFromAjax(realprice,'.wphp_product_original_price','(');
            formattedPriceFromAjax(designPriceInclTax,'.wphp_product_design_artwork_price',' + ',')');
            jQuery(".wphp_product_price_including_graphic_service").show();
            jQuery(".wphp_product_original_price").show();
            jQuery(".wphp_product_design_artwork_price").show();
            jQuery(".wphp_product_price").hide();
        }
    } else {
        jQuery(".wphp_product_price_including_graphic_service").hide();
        jQuery(".wphp_product_original_price").hide();
        jQuery(".wphp_product_design_artwork_price").hide();
        jQuery(".wphp_product_price").show();
        if (exclTaxOption == 0) {
            jQuery(".wphp_product_graphic_service_price_excl_tax").hide();
            jQuery(".wphp_product_original_price_excl_tax").hide();
            jQuery(".wphp_product_design_artwork_excl_price").hide();
            jQuery(".wphp_product_price_exclude_tax").show();
        }
    }
}

function setupAjaxForFormatPrice(amount)
{
    return {
        'action': 'get_wphp_product_formatted_price',
        'price_amount': amount,
        '_ajax_nonce': wphp_ajax_nonce.value
    }
}


function formattedPriceFromAjax(amount,elementToUpdate,prependElem='',appendElem='')
{
    data = setupAjaxForFormatPrice(amount);
    jQuery.post(wphp_ajax.ajax_url, data, function (response) {
        var formattedPrice;
        
        var currency = jQuery("#wphp_currency").val();
        if(response.success == true){
            formattedPrice = response.data;
        }else{
            var country = jQuery("#wphp_country").val();
            formattedPrice = formatCurrency(country,currency,amount);
        }
        jQuery(elementToUpdate).html(prependElem+formattedPrice+appendElem);
        if (elementToUpdate == '.wphp-apparealsize-price') {
            let price_per_piece = Math.round(amount/quantity * 100) / 100;
            data.price_amount = price_per_piece;
            jQuery.post(wphp_ajax.ajax_url, data, function (singleres) {
                if (singleres.data) {
                    jQuery('.wphp-apparealsize-perpiece').html(singleres.data);
                } else {
                    jQuery('.wphp-apparealsize-perpiece').html(currency + '&nbsp;' + price_per_piece);
                }
            });
        }
    });
}

function getCustomSizeTotal(multiply) {
    var helloprintProductUnit = jQuery(".wphp-product-unit").val();
    var helloprintProductDim = jQuery(".wphp-product-dim").val();
    var total = 0;
    switch (helloprintProductUnit) {
      case "mm":
        total = parseFloat(multiply) / 100;
        if (helloprintProductDim === "m2") {
          total = parseFloat(total) / 10000;
        }
        break;

      case "cm":
        total = parseFloat(multiply) / 10000;
        break;
    }
    return total;
}

function getMinMaxSize(sizeAttribute) {
     var helloprintProductMin = jQuery(`.wphp-product-${sizeAttribute}-min`
     ).val();
     var helloprintProductMax = jQuery(`.wphp-product-${sizeAttribute}-max`
     ).val();
    return [helloprintProductMin,helloprintProductMax];
}
function updatePrice() {
  quantity = jQuery(".quantityRadio:checked").val();
  var find = window.quotes.find(function (quote) {
    if (
      quantity == quote.quantity &&
      jQuery("#wphp_service_level").val() == quote.serviceLevel
    ) {
      return quote;
    }
  });
  var formattedExclPrice = formatCurrency(
    window.site_country,
    window.currency,
    find.prices.centAmountExclTax / 100
  );
    var formattedInclPrice = formatCurrency(
        window.site_country,
        window.currency,
        find.prices.centAmountInclTax / 100);
  jQuery(".wphp_product_price_exclude_tax").html(formattedExclPrice);
  jQuery(".wphp_product_price").html(formattedInclPrice);
  jQuery('#wphp_product_price_input').val(parseFloat(find.prices.centAmountInclTax) / 100);
  jQuery('#wphp_product_incl_tax_price_input').val(parseFloat(find.prices.centAmountInclTax) / 100);
  jQuery('#wphp_product_excl_tax_price_input').val(parseFloat(find.prices.centAmountExclTax) / 100);
  if (typeof(find.prices.origcentAmountExclTax) != typeof(undefined)) {
    jQuery('.wphp_product_price_without_margin').html(parseFloat(find.prices.origcentAmountInclTax) / 100);
    jQuery('.wphp_product_price_exclude_tax_without_margin').html(parseFloat(find.prices.origcentAmountExclTax) / 100);
  }
  updatePriceWithGraphic();
}

function getMapDataWithSelectedAttribute(resetpricetable = true) {
    var attributes = mapAttributes(resetpricetable);
    var selectedAttribute = currentSelectedAttribute;
    var data = mapData(attributes, selectedAttribute);
    return [data, selectedAttribute];
}

function resetPrice(){
    jQuery('#wphp_product_price_input').val('');
    jQuery('#wphp_product_incl_tax_price').val('');
    jQuery('#wphp_product_incl_tax_price_input').val('');
    jQuery('#wphp_product_excl_tax_price_input').val('');
    jQuery('.wphp_product_price').html('');
    jQuery('.wphp_product_price_including_graphic_service').html('');
    jQuery('.wphp_product_original_price').html('');
    jQuery('.wphp_product_design_artwork_price').html('');
    jQuery('.wphp_product_price_exclude_tax').html('');
    jQuery('.wphp_product_currency').html('');
    jQuery('#hello_product_variant_key').val();
     if (productWithApparealSize) {
        jQuery('.wphp-apparealsize-perpiece').html('');
        jQuery('.wphp-apparealsize-price').html('');
    }
}

function evaluateWidthHeight()
{
    if(jQuery('#wphp_custom_width').val()!=''&&(jQuery('#wphp_custom_height').val()!='')){
        var multiply = 0;
        var index = 0;
        jQuery(".wphp_product_options").each(function(){
            var nameAttr = jQuery(this).attr("name");
            nameAttr = nameAttr.replace(/wphp_options|\[|\]/g,'');
            var value = parseFloat(jQuery(this).val());
            if (index == 0) {
                multiply = value;
            } else {
                multiply = parseFloat(multiply) * parseFloat(value);
            }
            var [min, max] = getMinMaxSize(nameAttr);
            if (!jQuery.isNumeric(value) || value == '' || value < parseFloat(min) || value > parseFloat(max)) {
                alert(
                  `${nameAttr} option cannot be empty and should be numeric value between ${min} and ${max}.`
                );
                const params = new URLSearchParams(window.location.search)
                if (params.get('print_custom_'+nameAttr) !== '') {
                    jQuery('#wphp_custom_'+nameAttr).val(params.get('print_custom_'+nameAttr));
                }
                return false;
                
            }
            var $id = jQuery(this).attr("id");
            jQuery("#" + $id + "_hidden").val(value);
            index++;

        });
        var total = getCustomSizeTotal(multiply); 
        jQuery(".wphp-custom-total").html(format(total));
        if (jQuery(".wphp-product-option-selector option[value='0']:selected").length == 0) {
            jQuery('.wphp-quantity-sec').addClass('wphp-display-none');
            jQuery('.wphp-product-file-upload').addClass('wphp-display-none');
            resetPrice()
            var attributes = mapAttributes();
            var selectedAttribute = jQuery(".wphp-product-option-selector:last").data("select-id");
            var data = mapData(attributes, selectedAttribute);
            check_quantity_toggle(selectedAttribute);
            sendRequest(data, selectedAttribute);
        } else {
            deepink_create();
        }
    }else{
        // Empty
    }
}

function fileUploaderOrderDetailSection(name, item_id) {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation
    );
    FilePond.create(
        document.querySelector(name),
        {
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif'],
        }
    );
    FilePond.setOptions({
        labelIdle: wphp_ajax_translate.labelIdle,
        labelInvalidField: wphp_ajax_translate.labelInvalidField,
        labelFileWaitingForSize: wphp_ajax_translate.labelFileWaitingForSize,
        labelFileSizeNotAvailable: wphp_ajax_translate.labelFileSizeNotAvailable,
        labelFileLoading: wphp_ajax_translate.labelFileLoading,
        labelFileLoadError: wphp_ajax_translate.labelFileLoadError,
        labelFileProcessing: wphp_ajax_translate.labelFileProcessing,
        labelFileProcessingComplete: wphp_ajax_translate.labelFileProcessingComplete,
        labelFileProcessingAborted: wphp_ajax_translate.labelFileProcessingAborted,
        labelFileProcessingError: wphp_ajax_translate.labelFileProcessingError,
        labelFileProcessingRevertError: wphp_ajax_translate.labelFileProcessingRevertError,
        labelFileRemoveError: wphp_ajax_translate.labelFileRemoveError,
        labelTapToCancel: wphp_ajax_translate.labelTapToCancel,
        labelTapToRetry: wphp_ajax_translate.labelTapToRetry,
        labelTapToUndo: wphp_ajax_translate.labelTapToUndo,
        labelButtonRemoveItem: wphp_ajax_translate.labelButtonRemoveItem,
        labelButtonAbortItemLoad: wphp_ajax_translate.labelButtonAbortItemLoad,
        labelButtonRetryItemLoad: wphp_ajax_translate.labelButtonRetryItemLoad,
        labelButtonAbortItemProcessing: wphp_ajax_translate.labelButtonAbortItemProcessing,
        labelButtonUndoItemProcessing: wphp_ajax_translate.labelButtonUndoItemProcessing,
        labelButtonRetryItemProcessing: wphp_ajax_translate.labelButtonRetryItemProcessing,
        labelButtonProcessItem: wphp_ajax_translate.labelButtonProcessItem,
        server: {
            url: wphp_ajax.ajax_url,
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                const request = new XMLHttpRequest();
                addHelloPrintToCallQueue(function (que) {
                    var uploadedFilesElement = jQuery(".filepond--file-wrapper").closest('.wphp_upload_artwork_container');
                    var orderId = uploadedFilesElement.find('.wphp_order_id');
                    var itemId = uploadedFilesElement.find('.wphp_item_id');
                    var formData = new FormData();
                    formData.append(fieldName, file, file.name);
                    formData.append('action', 'wphp_save_artwork_of_order');
                    formData.append('order_id', orderId.val());
                    formData.append('item_id', itemId.val());
                    formData.append('_ajax_nonce', wphp_ajax_nonce.value);
                    request.open('POST', wphp_ajax.ajax_url);
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            // Check for file validation
                            var parsedResponse = JSON.parse(request.responseText);
                            if(parsedResponse.success == false){
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            }else{
                                if (parsedResponse && parsedResponse.data){
                                    load(JSON.stringify(parsedResponse.data));
                                }
                                location.reload();
                            }
                        } else {
                            error('oh no Filepond error');
                        }
                        que();
                    };
                    request.send(formData);
                });
                return {
                    abort: () => {
                        request.abort();
                        abort();
                    },
                };

            },
        },
    });
}

function updateSizeImgOption() {
    if (jQuery('div.wphp-size-sec').length) {
        jQuery(".wphp-sizeRadio").each(function () {
            let optVal = jQuery(this).val();
            if (typeof (attrOpts['size']) != typeof (undefined)) {
                if (optVal != 0 && attrOpts['size'][optVal] == undefined) {
                    jQuery(this).parent().addClass('wphp-display-none');
                } else if (optVal != 0 && optVal == attrOpts['size'][optVal]) {
                    jQuery(this).parent().removeClass('wphp-display-none');
                }
            } else {
               jQuery(this).parent().addClass('wphp-display-none');
            }
        });
    }
}

function updateColorImgOption() {
    if (jQuery('div.wphp-color-sec').length) {
        jQuery(".wphp-colorRadio").each(function(){
            let optVal = jQuery(this).val()
            if(typeof(attrOpts['colours']) == typeof(undefined) || (optVal != 0 && attrOpts['colours'][optVal] == undefined)){
                jQuery(this).parent().addClass('wphp-display-none');
            }else if(optVal != 0 && optVal == attrOpts['colours'][optVal]){
                jQuery(this).parent().removeClass('wphp-display-none');
            }
        })
    }
}

function initilialize_wphp_pitchprint(api_key, design_id)
{
    if (jQuery("#_w2p_set_option").length > 0) {
        if (jQuery("#_w2p_set_option").val() != "" ) {
            jQuery("#wphp_old_w2p_set_option").val(jQuery("#_w2p_set_option").val());
        }
        jQuery("#_w2p_set_option").val("");
    }
    var old_design_id = jQuery("#wphp_old_pitchprint_design_id").val();
    var original_product_image = jQuery("#wphp_original_product_image").html();
    var pitchprint_product_image = jQuery("#wphp_pitchprint_product_image").html();
    if (typeof(api_key) == "undefined" || api_key == '' || design_id == '' || typeof(design_id) == "undefined") {
        if (jQuery("#wphp-add-to-cart-button").is(":hidden")) {
            jQuery("#wphp-add-to-cart-button").show();
            if (jQuery("#wphp-add-to-cart-button").is(':disabled')) {
                jQuery("#wphp-add-to-cart-button").attr("disabled", "disabled");
            }
        }
        jQuery("#pp_main_btn_sec").hide();
        if (jQuery(".woocommerce-product-gallery--with-images").length > 0 && jQuery('.woocommerce-product-gallery--with-images > .ppc-img-width')) {
            jQuery("#wphp_pitchprint_product_image").html(jQuery(".woocommerce-product-gallery--with-images").html());
        }
        if (original_product_image != "") {
            jQuery(".woocommerce-product-gallery--with-images").html(original_product_image);
        }
        return false;
    } else if(design_id == old_design_id && old_design_id != "") {
        jQuery("#_w2p_set_option").val(jQuery("#wphp_old_w2p_set_option").val());
        jQuery("#pp_main_btn_sec").show();
        var original_image = jQuery("#wphp_original_product_image").html();
        if (pitchprint_product_image != "") {
            jQuery(".woocommerce-product-gallery--with-images").html(pitchprint_product_image);
            setTimeout(function(){
                if (jQuery("a[rel='prettyPhoto[product-gallery]']").length > 0) {
                    jQuery("a[rel='prettyPhoto[product-gallery]']").prettyPhoto();
                }
            }, 1000);
        }
        setTimeout(function(){
            if (jQuery("#wphp-add-to-cart-button").is(":hidden")) {
                jQuery("#wphp-add-to-cart-button").show();
                if (jQuery("#wphp-add-to-cart-button").is(':disabled')) {
                    jQuery("#wphp-add-to-cart-button").attr("disabled", "disabled");
               }
            }
        }, 1500);
    } else {
        jQuery("#wphp_old_pitchprint_design_id").val(design_id);
        jQuery("#wphp_pitchprint_product_image").html("");
        if (original_product_image != "") {
            jQuery(".woocommerce-product-gallery--with-images").html(original_product_image);
        }
        var original_image = jQuery("#wphp_original_product_image").html();
        if (original_image == "") {
            if (jQuery(".woocommerce-product-gallery--with-images").length > 0) {
                jQuery("#wphp_original_product_image").html(jQuery(".woocommerce-product-gallery--with-images").html());
            }
        }
        var langcode = wphp_pitch_print_settings.language;
        langArr = langcode.split("_");
        langcode = langArr[0];
        console.log(langcode);
        jQuery("#pp_main_btn_sec").show();
        var ppclient = new PitchPrintClient({
            apiKey: api_key,	//Kindly provide your own APIKey
            designId: design_id,	//Change this to your designId
            custom: true,
            pluginRoot: '/' + wphp_ajax.pitchprint_root_url + '/',
            mode: "new",
            isvx: true,
            client: 'wp',
            createButtons: true,
            langCode: langcode,
            product: {
                id: jQuery("#wphp_hidden_product_id").val(),
                name: jQuery("#wphp_hidden_product_name").val()
            },
        });
    }
    setTimeout(function(){
        if (jQuery("#wphp-add-to-cart-button").is(":hidden")) {
            jQuery("#wphp-add-to-cart-button").show();
            if (jQuery("#wphp-add-to-cart-button").is(':disabled')) {
                jQuery("#wphp-add-to-cart-button").attr("disabled", "disabled");
           }
        }
    }, 1500);
   
    
}


function wphp_handle_cart_pitchprint()
{
    if (jQuery("tr.woocommerce-cart-form__cart-item").length <= 0) {
        return false;
    }
    jQuery(".woocommerce-cart-form tr.woocommerce-cart-form__cart-item").each(function(){
        var tr = jQuery(this);
        var length = tr.find(".product-quantity").find(".quantity.hidden").length;
        if (length > 0) {
            tr.find(".product-name").find(".pp-cart-label").closest("dt").hide();
            tr.find(".product-name").find(".pp-cart-data").closest("dd").hide();
        }

    });
}

function updatePrintAreaImgOption() {
    if(jQuery('div.wphp-print-position-sec').length ){
        jQuery(".wphp-printPositionRadio").each(function(){
            let optVal = jQuery(this).val();
            if(typeof(attrOpts['printposition']) == typeof(undefined) || (optVal != 0 && attrOpts['printposition'][optVal] == undefined)){
                jQuery(this).parent().addClass('wphp-display-none');
            }else if(optVal != 0 && optVal == attrOpts['printposition'][optVal]){
                jQuery(this).parent().removeClass('wphp-display-none');
            }
        })
    }
}

function disableImages() {
    jQuery('.wphp-printPositionRadio').attr('disabled', 'disabled');
    var images = jQuery('.wphp-color-sec,.wphp-print-position-sec,.wphp-size-sec').find('img');
    jQuery.each(images,function () {
        jQuery(this).css('opacity', '.1');
    });
}

function enableImages() {
    jQuery('.wphp-printPositionRadio').removeAttr('disabled');
    var images = jQuery('.wphp-color-sec,.wphp-print-position-sec,.wphp-size-sec').find('img');
    jQuery.each(images,function () {
        jQuery(this).css('opacity', '');
    });
}

function showLoader(attribute) {
    jQuery(`#wphp-${attribute}-spinner`).removeClass('wphp-display-none');
}

function hideLoader(attribute) {
    jQuery(`#wphp-${attribute}-spinner`).addClass('wphp-display-none');
}

function wphp_load_with_destination_country()
{
    var attributes = mapAttributes();
    var selectedAttribute = jQuery(".wphp-product-option-selector:first").data('select-id');
    if (jQuery('div.wphp-size-sec').length) {
        var selectedAttribute = "size";
    }
    jQuery(".wphp-product-option-selector").each(function(){
        if (jQuery(this).find(":selected").val() != 0) {
            selectedAttribute = jQuery(this).data('select-id');
        }
    });
    var data = mapData(attributes, selectedAttribute);
    check_quantity_toggle(selectedAttribute)
    sendRequest(data, selectedAttribute);
}


function wphp_toggle_price_quantity()
{
    var qHeight = jQuery('.wphp-quantity-grp div').height();
    var maxHeight = qHeight*7;
    jQuery('.wphp-quantity-wrp').toggleClass('less more');
    jQuery('.wphp-quantity-wrp a').css('visibility','visible');
    if(jQuery('.wphp-quantity-wrp').hasClass('less')){
        jQuery('.wphp-quantity-grp').css('max-height', maxHeight+'px');
    }
    else if(jQuery('.wphp-quantity-wrp').hasClass('more')){
        jQuery('.wphp-quantity-grp').css('max-height','')
    }
}



