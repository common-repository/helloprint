
pageWithApparealSize = false;
var needRecalculation = true;
jQuery(document).ready(function ($) {

    jQuery(".helloprint_order_preset_public_url").on("keyup, change", function () {
        var $this = jQuery(this);
        hp_show_hide_preset_page_file_upload($this);
    });

    if(jQuery(".helloprint_order_preset_public_url").length > 0 && jQuery(".helloprint_order_preset_public_url").val() != "") {
        var $this = jQuery(".helloprint_order_preset_public_url");
        hp_show_hide_preset_page_file_upload($this);
    }
    
    if (jQuery("#wphp-markdown-editor").length > 0) {
        const Editor = toastui.Editor;
        const editor = new Editor({
            el: document.querySelector('#wphp-markdown-editor'),
            height: '500px',
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            initialValue: jQuery("#wphp-markdown-textarea").val(),
            toolbarItems: [
                ['heading', 'bold', 'italic', 'strike'],
                ['hr', 'quote'],
                ['ul', 'ol', 'task', 'indent', 'outdent'],
                ['table', 'link',  'scrollSync']
              ],
            events: {
                change : function(change){
                        var markdown = editor.getMarkdown();
                        jQuery("#wphp-markdown-textarea").val(markdown);
                }
            }
        });
        editor.getMarkdown();
    }

    jQuery('._tax_status_field').parent().addClass('show_if_helloprint_product');
    wphp_get_product_details();
    jQuery("#product-type").on("change", function () {
        var type = jQuery(this);
        if (type.val() == 'helloprint_product') {
            wphp_product_details();
        }
    });

    jQuery("#helloprint_external_product_id").on("change", function () {
        wphp_product_details();
        wphp_get_product_details();
    });

    jQuery(document).on('click','.wphp-order-remove-file',function (e) {
        e.preventDefault();
        if (!confirm("Are you sure, you want to remove this file ?")) {
            return false;
        }
        var fileKey = jQuery(this).data('file-key');
        var item_id = jQuery(this).data('item_id');
        var data = {
            'action': 'remove_helloprint_order_file',
            'fileKey': fileKey,
             'item_id': item_id,
             '_ajax_nonce': helloprint_ajax_nonce.value
        }
        jQuery.post(ajaxurl, data, function (response) {
            if (response.success = true) {
                location.reload();
            }
        });
    });


    jQuery('.wphp-file-upload-button').on('click', function () {
        var uploadedFilesElement = jQuery(this).parent();
        var item_id = uploadedFilesElement.find('.data-item-id');
        var form_data = new FormData();
        var uploadedfiles = uploadedFilesElement.find("[name='helloprint_order_item_file_upload[]']");
        jQuery.each(jQuery(uploadedfiles).prop('files'), function (i, file) {
            form_data.append(i, file);
        });
        form_data.append('action', 'helloprint_upload_order_item_file');
        form_data.append('item_id', item_id.val());
        form_data.append('_ajax_nonce', helloprint_ajax_nonce.value);

        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            contentType: false,
            processData: false,
            data: form_data,
            success: function (response) {
                location.reload();
            }
        })
    });


    jQuery(".wphp_order_item_file").each(function () {
        jQuery(this).imageUploader({
            imagesInputName: 'helloprint_order_item_file_upload',
            extensions: ['.jpg', '.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.pdf', '.PDF', '.tiff', '.TTF', '.tif'],
            mimes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif'],
        });
    });


    jQuery(".wphp-select2").select2({
        width: 'resolve'
    });
    jQuery("#wphp-wocommerce-category").select2({
        dropdownParent: jQuery('#wphp-category-modal')
    });

    if (jQuery("form[name='wphp-settings']").length > 0) {
        jQuery("form[name='wphp-settings']").attr("enctype", "multipart/form-data");
    }

    if (jQuery(".wphp-setting-remove-file").length > 0) {
        jQuery(".wphp-setting-remove-file").click(function (e) {
            e.preventDefault();
            jQuery(this).closest(".file-div").remove();
        })
    }

    if (jQuery(".helloprint_product_graphic_design_price_field").length > 0) {
        showHideGraphicDesignPrice();
        jQuery("select[name='helloprint_product_graphic_design_fee']").change(function (e) {
            showHideGraphicDesignPrice();
        });

    }
    if (jQuery(".helloprint_pricing_tier_field").length > 0) {
        showHideProductMargin();
        jQuery("select[name='helloprint_product_margin_option']").change(function (e) {
            showHideProductMargin();
        });
    }


    if (jQuery(".helloprint_order_preset_file").length > 0) {
        /*jQuery('.helloprint_order_preset_file').imageUploader({
            imagesInputName: 'helloprint_order_preset_file_upload',
            extensions: ['.jpg', '.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.pdf', '.PDF', '.tiff', '.TTF', '.tif'],
            mimes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif'],
            maxFiles: 1
        });*/
        fileUploader(".helloprint_order_preset_file", 'helloprint_order_preset_file_upload', '', '', 0);
    }

    if (jQuery(".helloprint_order_preset_file_each").length > 0) {
        jQuery(".helloprint_order_preset_file_each").each(function () {
            var item_id = jQuery(this).closest("td").find(".helloprint_item_id").val();
            var id = jQuery(this).data("id");
            fileUploader("." + id, 'helloprint_order_preset_file_upload', '', '', 0);
        });
    }

   /* if (jQuery("#helloprint_order_preset_variant_key").val() != '') {
        var selectedServ = jQuery("#helloprint_preset_default_service_level").val();
        var selectedQtys = jQuery("#helloprint_preset_default_quantity").val();
        helloprint_preset_load_quantities(selectedServ, selectedQtys);
    }*/
    jQuery("#helloprint_order_preset_variant_key").keyup(function () {
        helloprint_preset_load_quantities();
    });

    jQuery(".wphp-admin-preset-remove").click(function () {
        jQuery(".wphp-preset-file-remove-div").html('<input type="hidden" name="remove_preset_file" value="1" />');
        showHidePresetPublicUrl(jQuery(".wphp-order-preset-file-upload-page"));
    });

    if (jQuery(".helloprint_preset_order_item_preset").length > 0) {
        jQuery(".helloprint_preset_order_item_preset").change(function(){
            var $this = jQuery(this);
            helloprint_lists_from_presets($this);
        });

        jQuery(".wphp-item-preset-show-btn").click(function(){
            var $this = jQuery(this);
            $this.next(".wphp-order-item-preset-div").toggle();
        });
    }


    jQuery(document).on('click',".wphp-item-preset-show-btn",function () {
        var $this = jQuery(this);
        $this.next(".wphp-order-item-preset-div").toggle();
    });
    jQuery(document).on('change','.helloprint_preset_order_item_preset',function () {
        var $this = jQuery(this);
        helloprint_lists_from_presets($this);
        var presetSelect = $this.closest('table').find(".helloprint_preset_order_item_preset");
        presetSelect.select2();
        var serviceSelect = $this.closest('table').find(".helloprint_preset_order_item_service_level");
        serviceSelect.select2();
        serviceSelect.attr('disabled', true);
        var quantitySelect = $this.closest('table').find(".helloprint_preset_order_item_quantity");
        quantitySelect.select2();
        quantitySelect.attr('disabled', true);
   
    });

    jQuery(".wphp-submit-wphp-order").click(function (e) {
        e.preventDefault();
        if (!helloprint_admin_confirm_recalculate()) {
            return false;
        }
        var $this = jQuery(this);
        $this.attr("disabled", "disabled");
        var form = $this.closest("form");
        jQuery("select[name='wc_order_action']").val('send_to_helloprint');
        form.prepend('<input type="hidden" name="wc_order_action" value="send_to_helloprint"/>');
        form.trigger("submit");
    });

    jQuery(document).on('click', '.add-wphp-product-to-order-item', function (e) {
        e.preventDefault();
        showHelloprintAddOrderItemModal();
    });

    jQuery('.wphp-close').click(function (e) {
        e.preventDefault();
        hideHelloprintAddOrderItemModal();
        hideAttributeAndQuantites();
        resetOrderForm();
    });
    if (jQuery(".helloprint_filepond_order_item_product_file_upload").length > 0) {
        fileUploader(".helloprint_filepond_order_item_product_file_upload", 'helloprint_product_file_uploaded_path');
    }
    window.onclick = function (event) {
        if (event.target == document.getElementById("wc-order-modal-add-wphp-products")) {
            hideHelloprintAddOrderItemModal();
        }
    }
    jQuery('#add-wphp-product-item').select2();

    jQuery('#add-wphp-product-item').on("select2:select", function (e) {
        hideAttributeAndQuantites();
        jQuery('.wphp-order-service-quantity').hide();
        var selectHtml = jQuery('.wphp-order-product-attributes');
        var customSizeHtml = jQuery('.wphp-product-custom-size');
        customSizeHtml.html('');
        var customQuantityHtml = jQuery('.wphp-product-custom-quantity');
        customQuantityHtml.html('');
        var product_id = this.value;
        if (product_id == 0) {
            selectHtml.html('<h3>' + helloprint_ajax_translate.invalid_product_please_select_another_product + '</h3>');
        } else {
            var product_text = jQuery("#add-wphp-product-item").find(":selected").text();
            jQuery.ajax({
                method: "POST",
                url: helloprint_ajax.ajax_url,
                data: {
                    product_id: product_id,
                    action: 'helloprint_get_product_attribute_for_order',
                    '_ajax_nonce': helloprint_ajax_nonce.value,
                },
                success: function (response) {
                    if (response.success == false) {
                        selectHtml.html('<h3>' + helloprint_ajax_translate.something_went_wrong + '</h3>');
                    } else {
                        if (response.data.attributes.length <= 0) {
                            selectHtml.html('<h3>' + helloprint_ajax_translate.temporarily_unavailable + '</h3>');
                        } else {
                            var select = '';
                            var label = '';
                            var finalHtml = '';
                            var selectOptions = '';
                            jQuery.each(response.data.attributes, function (key, item) {
                                label = '<label for="helloprint_product_option_' + item.id + '">' + item.name + '</label>'
                                select = label + '<select class="wphp-product-option-selector wphp-options  wphp-product-selector   wphp-product-option-' + key + '" id="helloprint_product_option_' + item.id + '" name="helloprint_product_option_' + item.id + '" data-position="' + key + '" data-select-id="' + item.id + '"  data-itteration="' + key + '" data-label="' + item.name + '" data-iteration="' + key + '">';
                                selectOptions = '<option value="0">' + helloprint_ajax_translate.select_one + '</option>';
                                jQuery.each(item.options, function (optionKey, optionItem) {
                                    selectOptions += '<option value="' + optionKey + '">' + optionItem.name + '</option>';
                                });
                                finalHtml += select + selectOptions + '</select>';
                                selectOptions = '';
                            });
                            jQuery('#helloprint_product_id').val(product_id);
                            jQuery('#helloprint_attributes_count').val(response.data.attributes.length);
                            jQuery('#helloprint_external_product_id').val(response.data.helloprint_external_product_id);
                            jQuery('#helloprint_external_product_text').val(product_text);
                            jQuery('.wphp-order-product-attributes').show();
                            selectHtml.html(finalHtml);
                            if (response.data.customSize) {
                                customSizeHtml.html(response.data.customSizeHtml);
                            }
                            if (response.data.customQuantity) {
                                pageWithApparealSize = true;
                                jQuery('.wphp-default-quantity').hide();
                                jQuery('.wphp-product-custom-quantity').hide();
                                customQuantityHtml.html(response.data.customQuantityHtml);
                            } else {
                                jQuery('.wphp-default-quantity').show();
                            }
                            jQuery.fn.getHelloprintAttributes();
                        }
                    }
                }
            });
        }
    });

    jQuery.fn.getHelloprintAttributes = function () {
        if (parseInt(jQuery('#helloprint_attributes_count').val()) > 0) {
            var optionslength = parseInt(jQuery('#helloprint_attributes_count').val());
            for (index = 0; index < optionslength; index++) {
                jQuery('.wphp-product-option-' + index).on('change', function () {
                    var it = jQuery(this).data("iteration");
                    for (i = it + 1; i < optionslength; i++) {
                        if (jQuery('.wphp-product-option-' + i).length > 0) {
                            jQuery('.wphp-product-option-' + i).val(0);
                        }
                    }
                    var $this = jQuery(this);
                    setTimeout(function () {
                        jQuery('.wphp-quantity-sec').addClass('wphp-display-none');
                        var attributes = mapAttributes();
                        var selectedAttribute = $this.data('select-id');
                        var data = mapData(attributes, selectedAttribute);
                        sendRequest(data, selectedAttribute);
                    }, 300);

                });
            }
        }
    }

    jQuery(document).on('change', '#helloprint_product_quantity', function () {
        getMapDataWithSelectedAttribute();
        updatePrice();
    });


    jQuery('#helloprint_service_level').on('change', function () {
        var [data, selectedAttribute] = getMapDataWithSelectedAttribute();
        sendRequest(data, selectedAttribute);
    });
    
    jQuery('#woocommerce-order-items').on('click', 'a.delete-order-item', function () {
        needRecalculation = true; 
    });

    jQuery(document.body).on('order-totals-recalculate-success', function () {
        needRecalculation = false; 
    });
    
    jQuery('#wphp-add-product-to-order-btn').click(function () {
        var fileUploads = [];
        var helloprint_custom_options = [];
        var helloprint_appreal_size_options = [];
        jQuery("input[name='helloprint_filepond_order_item_product_file_upload[]']").each(function () {
            fileUploads.push(jQuery(this).val());
        });
        if (jQuery(".helloprint_product_options_hidden").length) {
            var options = [];
            jQuery('.helloprint_product_options_hidden').each(function () {
                options[jQuery(this).attr('name')] = jQuery(this).val();
            });
            helloprint_custom_options = Object.assign({}, options);
        }
        if (jQuery(".wphp-product-options-apparel-size").length) {
            var options = [];
            jQuery('.wphp-product-options-apparel-size').each(function () {
                var name = jQuery(this).attr('name');
                let pattern = /(appreal_size\[)/g;
                name = name.replace(pattern, '');
                options[name] = jQuery(this).val();
            });
            helloprint_appreal_size_options = Object.assign({}, options);
        }
        jQuery.ajax({
            method: "POST",
            url: helloprint_ajax.ajax_url,
            data: {
                action: 'helloprint_add_order_item',
                order_id: jQuery('#post_ID').val(),
                helloprint_admin_order: true,
                helloprint_product_quantity: jQuery('#helloprint_product_quantity').val(),
                helloprint_service_level: jQuery('#helloprint_service_level').val(),
                helloprint_product_options: jQuery('#helloprint_product_options').val(),
                helloprint_product_sku: jQuery('#helloprint_product_sku').val(),
                helloprint_product_variant_key: jQuery('#helloprint_product_variant_key').val(),
                helloprint_product_options_labels: jQuery('#helloprint_product_options_labels').val(),
                offer_price: jQuery('#helloprint_product_excl_tax_price_input').val(),
                helloprint_external_product_id: jQuery('#helloprint_product_id').val(),
                helloprint_external_product_name: jQuery('#helloprint_external_product_text').val(),
                product_type: 'helloprint_product',
                helloprint_product_margin_option: 1,
                helloprint_product_upload_file: '',
                helloprint_product_margin: 0,
                helloprint_product_graphic_design_fee: 0,
                helloprint_product_show_icon: '',
                helloprint_switch_color_icon: '',
                original_post_status: 'auto-draft',
                helloprint_product_file_upload: fileUploads,
                helloprint_product_custom_options: helloprint_custom_options,
                helloprint_appreal_size_options: helloprint_appreal_size_options,
                '_ajax_nonce': helloprint_ajax_nonce.value,
            },
            success: function (response) {
                if (response.success && response.data.item_added) {
                    jQuery('#woocommerce-order-items').find('.inside').empty();
                    jQuery('#woocommerce-order-items').find('.inside').append(response.data.html);
                    // Update notes.
                    if (response.data.notes_html) {
                        jQuery('ul.order_notes').empty();
                        jQuery('ul.order_notes').append(jQuery(response.data.notes_html).find('li'));
                    }
                    jQuery('.wphp-order-product-attributes').hide();
                    jQuery('.wphp-order-service-quantity').hide();
                    jQuery('.wphp-product-file-upload').hide();
                    jQuery('.wphp-admin-offer-price').hide();
                    jQuery('.helloprint_product_price_exclude_tax').html('');
                    jQuery('.helloprint_product_price').html('');
                    jQuery('.helloprint_product_price_exclude_tax_without_margin').html('');
                    jQuery('.helloprint_product_price_without_margin').html('');
                    jQuery('#wphp-add-product-to-order-btn').attr('disabled', 'disabled');
                    jQuery('.helloprint_product_price_exclude_tax').html('');
                    jQuery('#add-wphp-product-item').val(0).trigger('change');
                    jQuery('.wphp-product-custom-size').html('');
                    jQuery('.wphp-product-custom-quantity').html('');
                    FilePond.destroy(document.querySelector('.helloprint_order_preset_file_each'));
                    createFilePond('.helloprint_order_preset_file_each');
                    orderPresetFileUploader();
                    FilePond.destroy(document.querySelector('.helloprint_filepond_order_item_product_file_upload'));
                    createFilePond('.helloprint_filepond_order_item_product_file_upload');
                    orderItemsFileUploder();
                    needRecalculation = true;
                } else {
                    window.alert(response.data.error);
                }
            },
            complete: function () {
                window.wcTracks.recordEvent('order_edit_add_products', {
                    order_id: jQuery('#post_ID').val(),
                    status: $('#order_status').val()
                });
            },
            dataType: 'json'
        });

        hideHelloprintAddOrderItemModal();
    });

    jQuery(document).on('click','.wphp-submit-order-item-preset',function (e) {
        e.preventDefault();
        var $this = jQuery(this);
        var parent = $this.closest(".wphp-list-table");
        var url = parent.find(".helloprint_artwork_external_url").val();
        parent.find(".wphp-invalid-external-url").hide();
        if (jQuery.trim(url) != '' && !validURL(url)) {
            parent.find(".wphp-invalid-external-url").show();
            return false;
        }
        var flag = true;
        var indexed_array = {};
        var total_appreal_size_quantity = 0;

        jQuery.map(parent.find('select, textarea, input').serializeArray(), function (n, i) {
            var name = n['name'];
            if (name.toLowerCase().indexOf("[") >= 0) {
                name = name.substr(0, name.indexOf('['));
                if (typeof (indexed_array[name]) == typeof (undefined)) {
                    indexed_array[name] = [];
                }
                if (name != "custom_options" && name != "appreal_sizes") {
                    indexed_array[name].push(n['value']);
                }
            } else {
                indexed_array[n['name']] = n['value'];
            }
        });

        jQuery(".helloprint_custom_option_input_preset_field").each(function(){
            var value = jQuery(this).val();
            var name = jQuery(this).data("name");
            var keytype = jQuery(this).data("keytype");
            var keyvalue = jQuery(this).data("keyvalue");
            if (jQuery(this).hasClass("helloprint_preset_appreal_size") && parseFloat(value) > 0) {
                total_appreal_size_quantity += parseFloat(value);
            }
            if (jQuery(this).attr("required") != typeof(undefined)) {
                
                var required = jQuery(this).attr("required");
                if ((required == true || required == "required" ) && jQuery.trim(value) == "") {
                    jQuery(this).focus();
                    flag = false;
                    return false;
                }
            }
            if (jQuery(this).attr("min") != typeof(undefined)) {
                var min = parseFloat(jQuery(this).attr("min"));
                if (parseFloat(value) < min) {
                    jQuery(this).focus();
                    alert("Min :: " + min);
                    flag = false;
                    return false;
                }
            }

            if (jQuery(this).attr("max") != typeof(undefined)) {
                var max = parseFloat(jQuery(this).attr("max"));
                if (parseFloat(value) > max) {
                    alert("Max :: " + max);
                    jQuery(this).focus();
                    flag = false;
                    return false;
                }
            }
            if (typeof(keytype) != typeof(undefined) && typeof(keyvalue) != typeof(undefined)) {
                if (typeof(indexed_array[name]) == typeof(undefined)) {
                    indexed_array[name] = [];
                }
                if (typeof(indexed_array[name][keytype]) == typeof(undefined)) {
                    indexed_array[name][keytype] = [];
                }
                indexed_array[name][keytype][keyvalue] = value;
            } else {
                if (typeof(indexed_array[name]) == typeof(undefined)) {
                    indexed_array[name] = [];
                }
                indexed_array[name][keytype] = value;
            }
        });
        if (typeof(indexed_array["custom_options"]) != typeof(undefined)) {
            indexed_array["custom_options"] = Object.assign({}, indexed_array["custom_options"]);
        }
        if (typeof(indexed_array["appreal_sizes"]) != typeof(undefined)) {
            if (typeof(indexed_array["appreal_sizes"]["quantity"]) != typeof(undefined)) {
                indexed_array["appreal_sizes"]["quantity"] = Object.assign({}, indexed_array["appreal_sizes"]["quantity"]);
            }
            if (typeof(indexed_array["appreal_sizes"]["men"]) != typeof(undefined)) {
                indexed_array["appreal_sizes"]["men"] = Object.assign({}, indexed_array["appreal_sizes"]["men"]);
            }
            if (typeof(indexed_array["appreal_sizes"]["women"]) != typeof(undefined)) {
                indexed_array["appreal_sizes"]["women"] = Object.assign({}, indexed_array["appreal_sizes"]["women"]);
            }
            indexed_array["appreal_sizes"] = Object.assign({}, indexed_array["appreal_sizes"]);
        }

        if (jQuery(".helloprint_preset_appreal_size").length > 0 && total_appreal_size_quantity <= 0) {
            flag = false;
            alert(jQuery("#helloprint_hidden_preset_one_appreal_msg").val());
            jQuery(".helloprint_preset_appreal_size:first").focus();
            return false;
        }
        
        if (flag == false) {
            return false;
        }
        var data = {
            action: "helloprint_save_order_item_presets",
            data: indexed_array,
            _ajax_nonce: helloprint_ajax_nonce.value
        };
        jQuery.post(ajaxurl, data, function (response) {
            if ((response.success = true)) {
                parent.parent().find(".wphp-preset-message-div").html(response.data.message);
                setTimeout(function () { 
                    parent.parent().find(".wphp-preset-message-div").html('');
                },5000);
                downloadLabel = response.data.download;
                removeLabel = response.data.remove;
                $div = parent.find('tr.wphp-preset-file-uplaoder td div.wphp-old-artworks');
                response.data.files.forEach(function (item) {
                    fileName = item.file_name;
                    filePath = item.file_path;
                                                            
                    $html = `<div class="wphp-single-preset-file">`;
                    $html += `<input type="hidden" name="helloprint_order_preset_file_upload[]" value="${filePath}"><span></span>${fileName}<br>`;
                    $html += `<a target="_blank" href="${filePath}" class="btn btn-sm btn-outline-primary"> ${downloadLabel} </a>&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;`;
                    $html += `<a href="#" class="wphp-admin-itemline-preset-remove btn btn-sm btn-outline-danger">${removeLabel}</a><br>`;
                    $html += `</div>`;
                    $div.append($html);
                    id = parent.find('.helloprint_order_preset_file_each').attr('id');
                    FilePond.destroy(document.querySelector(`.${id}`));
                    createFilePond(`.${id}`);
                    orderPresetFileUploader();
                });
                artworkListDiv = parent.find('.wphp-artworks-lists');
                $div.append(artworkListDiv.html())
                artworkListDiv.html('');

                if (jQuery(".helloprint_prefer_files-div").length > 0) {
                    jQuery(".wphp-order-item-preset-div ").each(function(){
                        _helloprint_show_hide_authoritive_files(jQuery(this));
                    });
                }
            }
        });

    })

    jQuery(document).on("click", ".wphp-admin-itemline-preset-remove", function (e) {
        e.preventDefault();
        var parenttr = jQuery(this).closest(".wphp-preset-file-uplaoder");
        var parentdiv = jQuery(this).closest(".wphp-order-item-preset-div")
        jQuery(this).closest(".wphp-single-preset-file").remove();
        showHideArtworkExternalUrl(parenttr);
        _helloprint_show_hide_authoritive_files(parentdiv);
    });


    if (jQuery(".helloprint_order_item_file_each").length > 0) {
        orderItemsFileUploder();
    }

    jQuery(document).on('click','.wphp-submit-order-items',function (e) {
        e.preventDefault();
        var $this = jQuery(this);
        var parent = $this.closest(".wphp-single-order-item-file");
        var item_id = parent.find(".data-item-id").val();
        var indexed_array = {};

        jQuery.map(parent.find('select, textarea, input').serializeArray(), function (n, i) {
            var name = n['name'];
            if (name.toLowerCase().indexOf("[") >= 0) {
                name = name.substr(0, name.indexOf('['));
                if (typeof (indexed_array[name]) == typeof (undefined)) {
                    indexed_array[name] = [];
                }
                indexed_array[name].push(n['value']);
            } else {
                indexed_array[n['name']] = n['value'];
            }
        });
        var data = {
            action: "helloprint_upload_order_item_file",
            data: indexed_array,
            item_id: item_id,
            _ajax_nonce: helloprint_ajax_nonce.value
        };
        jQuery.post(ajaxurl, data, function (response) {
            if ((response.success = true)) {
                downloadLabel = response.data.download;
                removeLabel = response.data.remove;
                response.data.files.forEach(function(item) {
                    
                    item = JSON.parse(item);
                    fileName = item[0].file_name;
                    filePath = item[0].file_path;
                    $div = parent.find('table tr td span.order-files-div:last');
                    file_key = 1;
                    if ($div.length>0) {
                        file_key = $div.find('a.wphp-order-remove-file').attr('data-file-key');
                    } else {
                        $div = parent.find('table tr td h4');
                    }
                    $html = `<br/><span class="order-files-div" >`;
                    $html += `<span></span>${fileName}<br>`;
                    $html += `<a download="" href="${filePath}" class="btn btn-sm btn-outline-primary">${downloadLabel}</a>&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;`; 
                    $html += `<a href="#" data-file-key="${file_key}" data-item_id="${item_id}" class="wphp-order-remove-file btn btn-sm btn-outline-danger">${removeLabel}</a><br>`;
                    $html += `</span>`;	
                    $($html).insertAfter($div);
                    FilePond.destroy(document.querySelector('.helloprint_order_preset_file_each'));
                    createFilePond('.helloprint_order_preset_file_each');
                    orderPresetFileUploader();
                });
            }
        });

    })

    jQuery(".helloprint_artwork_external_url").on("keyup, change", function () {
        var $this = jQuery(this);
        showHideArtworkUpload($this);
    })

    jQuery('#helloprint_product_limit_variant_key').change(function () {
        toggleSKUTextarea();
    });

    if (jQuery('#helloprint_product_limit_variant_key').length > 0) {
        toggleSKUTextarea();
    }

    if (jQuery(".wphp-preset-file-uplaoder").length > 0) {
        allArtworksCheckUploads();
    }

    if (jQuery(".helloprint_artwork_external_url").length > 0) {
        jQuery(".helloprint_artwork_external_url").each(function () {
            var $this = jQuery(this);
            showHideArtworkUpload($this);
        });
    }
    jQuery(document).on("change", '.wphp-product-options-apparel-size', debounce(function (e) {
        jQuery("#wphp-appreal-size-nonstandard-error").hide();
        addProductOptionCustomApparelSizeTotalQuantity();
    }, 500));

    hp_show_margin_markup_product();
    showHideHPProductMarkup();

    jQuery(document).on("change", "[name='helloprint_markup_margin']", function(){
        hp_show_margin_markup_product();
    });

    jQuery(document).on("change", "#helloprint_product_markup_option", function(){
        showHideHPProductMarkup();
    })
});

const format = (num, decimals) => num.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});
oldCustomOptions = {};

jQuery(document).on('change', '#helloprint_custom_height', function (e) {
    e.preventDefault();
    evaluateWidthHeight();
});
jQuery(document).on('change', '#helloprint_custom_width', function (e) {
    e.preventDefault();
    evaluateWidthHeight();
});
jQuery(document).on('keyup', '.helloprint_product_options', function () {
    var numbers = jQuery(this).val();
    jQuery(this).val(numbers.replace(/\D/, ''));
    var multiply = 0;
    var index = 0;
    jQuery(".helloprint_product_options").each(function () {
        var value = jQuery(this).val();
        var id = jQuery(this).attr('id');
        oldCustomOptions[id] = value;
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

function wphp_product_details() {
    var original_post_status = jQuery("#original_post_status").val();
    if (original_post_status != 'auto-draft') {
        return true;
    }
    var product_id = jQuery("#helloprint_external_product_id").val();
    var name = jQuery("#helloprint_external_product_id option:selected").text();
    name = jQuery.trim(name.substring(0, name.indexOf('[')));
    jQuery("#title-prompt-text").addClass("screen-reader-text");
    jQuery("input[name='post_title']").val(name);
    if (jQuery(".wp-block-post-title").length > 0) {
        wp.data.dispatch('core/editor').editPost({ title: name })
    }
}

function validateHelloPrintOverrideImageFile() {
    var fileName = document.getElementById("helloprint_override_icon").value;
    var idxDot = fileName.lastIndexOf(".") + 1;
    var extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
    if (extFile == "jpg" || extFile == 'gif' || extFile == "jpeg" || extFile == "png") {
        //TO DO
    } else {
        var invalid_message = jQuery("#helloprint_hidden_invalid_file_message").val();
        document.getElementById("helloprint_override_icon").value = null;
        alert(invalid_message);
    }
}

function showHideGraphicDesignPrice() {
    const enabled = Number(jQuery("select[name='helloprint_product_graphic_design_fee']").val());
    if (enabled == 1) {
        jQuery(".helloprint_product_graphic_design_price_field").show();
    } else {
        jQuery(".helloprint_product_graphic_design_price_field").hide();
    }
}

function showHideProductMargin() {
    const enabled = Number(jQuery("select[name='helloprint_product_margin_option']").val());
    if (enabled == 1) {
        jQuery(".helloprint_product_margin_field").hide();
        jQuery(".helloprint_user_roles_for_price_field").hide();
        jQuery(".helloprint_pricing_tier_field").hide();

    } else if(enabled == 2) {
        jQuery(".helloprint_product_margin_field").show();
        jQuery(".helloprint_user_roles_for_price_field").hide();
        jQuery(".helloprint_pricing_tier_field").hide();
    } else {
        jQuery(".helloprint_product_margin_field").hide();
        jQuery(".helloprint_user_roles_for_price_field").show();
        jQuery(".helloprint_pricing_tier_field").show();
    }
}
function wphp_get_product_details() {
    var data = {
        action: "get_helloprint_product_detail",
        product_id: jQuery("#helloprint_external_product_id").val(),
        _ajax_nonce: helloprint_ajax_nonce.value
    };
    jQuery.post(ajaxurl, data, function (response) {
        if (response.success == true) {
            var size_exists = false;
            if (response.data.length > 0 && response.data.attributes.length > 0) {
                size_exists = response.data.attributes.some(element => element.id === 'size');
            }
            if (size_exists) {
                jQuery(".helloprint_product_show_icon_field_div").removeClass(
                    "hidden"
                );
            } else {
                jQuery(".helloprint_product_show_icon_field_div").addClass(
                    "hidden"
                );
            }
        }
    });
}


/*function helloprint_preset_load_quantities(service = '', qtys = '') {
    var variant_key = jQuery("#helloprint_order_preset_variant_key").val();
    if (variant_key == '') {
        return false;
    }
    var data = {
        action: "get_helloprint_preset_load_quantities",
        variant_key: variant_key,
    };
    jQuery.post(ajaxurl, data, function (response) {
        var serviceLevelOpt = jQuery("#helloprint_preset_default_service_level");
        var quantityOpt = jQuery("#helloprint_preset_default_quantity");
        serviceLevelOpt.find("option").remove();
        quantityOpt.find("option:gt(0)").remove();

        if (response.success == true) {
            if (response.data.quantities !== "undefined") {
                jQuery.each(response.data.quantities, function (key, val) {
                    quantityOpt.append(new Option(val, val));
                });
            }
            if (response.data.service_levels) {
                jQuery.each(response.data.service_levels, function (key, val) {
                    serviceLevelOpt.append(new Option(val, key));
                });
            }

            if (response.data.sku) {
                 jQuery("#helloprint_item_sku").val(response.data.sku);
            }

            if (service != '' || qtys != '') {
                setTimeout(function () {
                    serviceLevelOpt.val(service).trigger("change");
                    quantityOpt.val(qtys).trigger("change");
                }, 500);
            }
        }
    });
}*/

function helloprint_lists_from_presets(element, service = '', qtys = '') {
    var preset_id = element.val();
    var variant_key = jQuery('option:selected', element).data("variantkey");
    var serviceLevelOpt = element.closest('table').find(".helloprint_preset_order_item_service_level");
    var quantityOpt = element.closest('table').find(".helloprint_preset_order_item_quantity");
    var order_item_quantity = element.closest('table').find(".helloprint_order_item_quantity").val();

    serviceLevelOpt.find("option").remove().trigger("change");
    element.closest('table').find(".wphp-artworks-lists").html('');
    quantityOpt.find("option:gt(0)").remove().trigger("change");
    element.closest(".wphp-list-table").find(".wphp-load-selected-variantkey-div").html(variant_key);
    var item_id = element.closest(".wphp-list-table").find(".helloprint_order_hidden_itemid").val();
    var is_hp = element.closest('table').find(".helloprint_order_hidden_is_hp_product").val();

    if (is_hp != true && is_hp != 1) {
        if (preset_id == "") {
            element.closest("table").find(".hp-preset-file-related-class").hide();
        } else {
            element.closest("table").find(".hp-preset-file-related-class").show();
        }
    }
   
    var data = {
        action: "get_helloprint_preset_load_details_from_preset",
        preset_id: preset_id,
        item_id: item_id
    };
    jQuery.post(ajaxurl, data, function (response) {


        if ((response.success = true)) {
            if (response.data.quantities) {
                jQuery.each(response.data.quantities, function (key, val) {
                    quantityOpt.append(new Option(val, val));
                });
            }
            if (response.data.service_levels) {
                jQuery.each(response.data.service_levels, function (key, val) {
                    serviceLevelOpt.append(new Option(val, key));
                });
            }
            if (is_hp != true && is_hp != 1) {
                var find = false;
                quantityOpt.find("option").each(function(){
                    if (jQuery(this).val() == order_item_quantity) {
                        find = true;
                    }
                });
                //if(!quantityOpt.find("option:contains('" + order_item_quantity  + "')").length){
                if(!find) {
                    setTimeout(function () {
                        quantityOpt.find("option:gt(0)").remove().trigger("change");
                        console.log("Length :: 908");
                        console.log(element.closest("table").find(".quantity-not-found-warning").length);
                        if (element.closest("table").find(".quantity-not-found-warning").length <=0){
                            quantityOpt.after("<div class='notice notice-warning quantity-not-found-warning'>Quantity not found</div>");
                        }
                        quantityOpt.val(order_item_quantity).trigger("change");
                    }, 1000);
                } else {
                    setTimeout(function () {
                        quantityOpt.val(order_item_quantity).trigger("change");
                    }, 500);
                }
            } else {
                if (service != '' || qtys != '') {
                    setTimeout(function () {
                        quantityOpt.val(qtys).trigger("change");
                    }, 500);
                } else {
                    setTimeout(function () {
                        quantityOpt.val(response.data.quantity).trigger("change");
                    }, 500);
                }
            }
            if (service != '' || qtys != '') {
                setTimeout(function () {
                    serviceLevelOpt.val(service).trigger("change");
                }, 500);
            } else {
                setTimeout(function () {
                    serviceLevelOpt.val(response.data.service_level).trigger("change");
                }, 500);
            }

            var file_urls = '';
            if (response.data.file_url != '') {
                var file_full_url = response.data.file_url;
                if (file_full_url.indexOf("//") >= 0) {
                    element.closest('table').find(".helloprint_artwork_external_url").val(file_full_url).trigger("keyup");
                } else {
                    file_urls += '<div class="wphp-single-preset-file" >';
                    file_urls += '<input type="hidden" name="helloprint_order_preset_file_upload[]" value="' + response.data.file_url + '" />';
                    file_urls += '<span>' + response.data.file_name + '</span><br/>';
                    file_urls += '<a target="_blank" href="' + response.data.file_full_path + '" class=" btn btn-sm btn-outline-primary">' + response.data.download_text + '</a>' + '&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;' +
                        '<a href="#" class="wphp-admin-itemline-preset-remove btn btn-sm btn-outline-danger">' + response.data.remove_text + '</a>'
                    file_urls += '</div>';
                }

            }
            if (jQuery('.wphp-old-artworks').children().length > 0) {
                element.closest('table').find(".wphp-artworks-lists").html('');
            } else {
                element.closest('table').find(".wphp-artworks-lists").html(file_urls);
            }

            _helloprint_custom_sizes_for_preset([], preset_id, item_id, element);
        }
    });
}

function helloprint_preset_load_quantities(service = '', qtys = '') {
    var variant_key = jQuery("#helloprint_order_preset_variant_key").val();
    jQuery(".helloprint_preset_custom_size_tr").html("");
    if (variant_key == '') {
        return false;
    }
    var data = {
        action: "get_helloprint_preset_load_quantities",
        variant_key: variant_key,
        _ajax_nonce: helloprint_ajax_nonce.value
    };
    jQuery.post(ajaxurl, data, function (response) {
        var serviceLevelOpt = jQuery("#helloprint_preset_default_service_level");
        var quantityOpt = jQuery("#helloprint_preset_default_quantity");
        serviceLevelOpt.find("option").remove();
        quantityOpt.find("option:gt(0)").remove();

        if (response.success == true) {
            if (response.data.quantities !== "undefined") {
                jQuery.each(response.data.quantities, function (key, val) {
                    quantityOpt.append(new Option(val, val));
                });
            }
            if (response.data.service_levels) {
                jQuery.each(response.data.service_levels, function (key, val) {
                    serviceLevelOpt.append(new Option(val, key));
                });
            }

            /*if (response.data.sku) {
                 jQuery("#helloprint_item_sku").val(response.data.sku);
            }*/
            jQuery("#helloprint_pod_available_options").val(JSON.stringify(response.data.options));
            var preset_id = jQuery("#helloprint_hidden_pod_id").val();
            var return_variant_key = response.data.variant_key;
            if (return_variant_key == jQuery("#helloprint_order_preset_variant_key").val()) {
                _helloprint_custom_sizes_for_preset(response.data.options, preset_id, "", null, return_variant_key);
            } else {
                jQuery(".helloprint_preset_custom_size_tr").html("");
            }
            
            if (service != '' || qtys != '') {
                setTimeout(function () {
                    serviceLevelOpt.val(service).trigger("change");
                    quantityOpt.val(qtys).trigger("change");
                }, 500);
            }
        }
    });
}
function helloprint_lists_from_presets(element, service = '', qtys = '') {
    var preset_id = element.val();
    var variant_key = jQuery('option:selected', element).data("variantkey");
    var serviceLevelOpt = element.closest('table').find(".helloprint_preset_order_item_service_level");
    var quantityOpt = element.closest('table').find(".helloprint_preset_order_item_quantity");
    var order_item_quantity = element.closest('table').find(".helloprint_order_item_quantity").val();
    element.closest("table").find(".quantity-not-found-warning").remove();
    serviceLevelOpt.find("option").remove().trigger("change");
    element.closest('table').find(".wphp-artworks-lists").html('');
    quantityOpt.find("option:gt(0)").remove().trigger("change");
    var item_id = element.closest(".wphp-list-table").find(".helloprint_order_hidden_itemid").val();
    element.closest(".wphp-list-table").find(".wphp-load-selected-variantkey-div").html(variant_key);
    var is_hp = element.closest('table').find(".helloprint_order_hidden_is_hp_product").val();
    if (is_hp != true && is_hp != 1) {
        if (preset_id == "") {
            element.closest("table").find(".hp-preset-file-related-class").hide();
        } else {
            element.closest("table").find(".hp-preset-file-related-class").show();
        }
    }
    if (preset_id == '') {
        element.closest('table').find(".helloprint_order_item_preset_custom_options").html("");
        element.closest('table').find(".helloprint_preset_quantity_select_td").show();
        return false;
    }
    var data = {
        action: "get_helloprint_preset_load_details_from_preset",
        preset_id: preset_id,
        item_id: item_id,
        _ajax_nonce: helloprint_ajax_nonce.value
    };
    jQuery.post(ajaxurl, data, function (response) {


        if ((response.success = true)) {
            if (response.data.quantities) {
                jQuery.each(response.data.quantities, function (key, val) {
                    quantityOpt.append(new Option(val, val));
                });
            }
            if (response.data.service_levels) {
                jQuery.each(response.data.service_levels, function (key, val) {
                    serviceLevelOpt.append(new Option(val, key));
                });
            }

            if (is_hp != true && is_hp != 1) {
                var find = false;
                quantityOpt.find("option").each(function(){
                    if (jQuery(this).val() == order_item_quantity) {
                        find = true;
                    }
                });
                //if(!quantityOpt.find("option:contains('" + order_item_quantity  + "')").length){
                if(!find) {
                    setTimeout(function () {
                        quantityOpt.find("option:gt(0)").remove().trigger("change");
                        console.log("Length :: 1068");
                        console.log(element.closest("table").find(".quantity-not-found-warning").length);
                        if (element.closest("table").find(".quantity-not-found-warning").length <=0){
                            quantityOpt.after("<div class='notice notice-warning quantity-not-found-warning'>Quantity not found</div>");
                        }
                        quantityOpt.val(order_item_quantity).trigger("change");
                    }, 1000);
                } else {
                    setTimeout(function () {
                        quantityOpt.val(order_item_quantity).trigger("change");
                    }, 500);
                }
            } else {
                if (service != '' || qtys != '') {
                    setTimeout(function () {
                        quantityOpt.val(qtys).trigger("change");
                    }, 500);
                } else {
                    setTimeout(function () {
                        quantityOpt.val(response.data.quantity).trigger("change");
                    }, 500);
                }
            }
            if (service != '' || qtys != '') {
                setTimeout(function () {
                    serviceLevelOpt.val(service).trigger("change");
                }, 500);
            } else {
                setTimeout(function () {
                    serviceLevelOpt.val(response.data.service_level).trigger("change");
                }, 500);
            }

            var file_urls = '';
            if (response.data.file_url != '') {
                var file_full_url = response.data.file_url;
                if (file_full_url.indexOf("//") >= 0) {
                    element.closest('table').find(".helloprint_artwork_external_url").val(file_full_url).trigger("change");
                } else {
                    file_urls += '<div class="wphp-single-preset-file" >';
                    file_urls += '<input type="hidden" name="helloprint_order_preset_file_upload[]" value="' + response.data.file_url + '" />';
                    file_urls += '<span>' + response.data.file_name + '</span><br/>';
                    file_urls += '<a target="_blank" href="' + response.data.file_full_path + '" class=" btn btn-sm btn-outline-primary">' + response.data.download_text + '</a>' + '&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;' +
                        '<a href="#" class="wphp-admin-itemline-preset-remove btn btn-sm btn-outline-danger">' + response.data.remove_text + '</a>'
                    file_urls += '</div>';
                }

            }
            element.closest('table').find(".wphp-artworks-lists").html(file_urls);
        }

        serviceLevelOpt.attr('disabled', false);
        quantityOpt.attr('disabled', false);

        _helloprint_custom_sizes_for_preset([], preset_id, item_id, element);
    });
}

function _helloprint_admin_show_hide_graphic() {
    if (jQuery("#helloprint_enable_global_graphic_design").length > 0) {
        var input = jQuery("#helloprint_enable_global_graphic_design");
        var checked = input.prop("checked");
        if (checked) {
            input.closest("tr").next("tr").show();
        } else {
            input.closest("tr").next("tr").hide();
        }
    }
}

function mapData(attributes, selectedAttribute = '') {
    return {
        'action': 'get_wphp_variant_filter',
        'product_id': 0,
        'wphp_external_product_id': jQuery('#helloprint_external_product_id').val(),
        'wphp_product_quantity': jQuery('#helloprint_product_quantity').val(),
        'wphp_appreal_quantity': (jQuery("#wphp-product-total-appreal-size").length > 0) ? jQuery("#wphp-product-total-appreal-size").val() : -1,
        'delivery_type': jQuery('#helloprint_service_level').val(),
        'wphp_selected_attribute': selectedAttribute,
        'wphp_attributes': attributes,
        '_ajax_nonce': helloprint_ajax_nonce.value,
    }
}

function mapAttributes() {
    selectProductQuantity();
    var attributes = {};
    var attributesText = {};
    var attributextTextLabels = {};
    if (jQuery(".helloprint_product_options_hidden").length) {
        var options = [];
        jQuery('.helloprint_product_options_hidden').each(function () {
            options[jQuery(this).attr('name')] = jQuery(this).val();
            attributesText[jQuery(this).attr('name')] = jQuery(this).val();
            attributextTextLabels[jQuery(this).data('label')] = jQuery(this).val();
        });
        attributes.helloprint_options = Object.assign({}, options);
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

    jQuery('#helloprint_product_options').val(JSON.stringify(attributesText));
    jQuery('#helloprint_product_options_labels').val(JSON.stringify(attributextTextLabels));
    return attributes;
}

function sendRequest(data, selectedAttribute = '') {
    hideAttributeAndQuantites();
    var total = +jQuery("#wphp-product-total-appreal-size").val();
    var emptySelector = jQuery(".wphp-product-option-selector option[value='0']:selected");
    jQuery.post(helloprint_ajax.ajax_url, data, function (response) {
        if (response.success == true) {
            if (response.data) {
                window.attrOpts = response.data.attributeOptions
                window.quotes = response.data.quotes;
                window.serviceLabelObj = response.data.serviceLevel;
                window.serviceLevel = Object.keys(serviceLabelObj).map(function (key, index) {
                    return serviceLabelObj[key].value;
                });
                window.serviceLevelDays = Object.keys(serviceLabelObj).map(function (key, index) {
                    return serviceLabelObj[key];
                });
                jQuery('#helloprint_product_tax_rate_input').val(response.data.tax_rate);
                if (response.data.variants) {
                    jQuery.each(response.data.variants, function (index, value) {
                        var option = jQuery('#helloprint_product_option_' + index);
                        var oldOptionValue = option.val();
                        option.find('option').remove();
                        option.append(new Option(helloprint_ajax_translate.select_one, '0'));
                        jQuery.each(value, function (key, val) {
                            option.append(new Option(val.name, key));
                        });
                        var selectedPosition = jQuery('#helloprint_product_option_' + selectedAttribute).data('position');
                        if (option.attr('data-position') <= selectedPosition) {
                            jQuery("#helloprint_product_option_" + index + " option[value='" + oldOptionValue + "'").prop('selected', true);
                        }
                    });
                }

                jQuery('#helloprint_product_quantity').find('option').remove();
                var quantitySelect = jQuery('#helloprint_product_quantity');
                jQuery(response.data.quantities).each(function (key, item) {
                    jQuery.each(item, function (key, val) {
                        quantitySelect.append(new Option(val.quantity, val.quantity));
                    });
                });

                var oldQuantityValue = quantitySelect.find('selected').val();
                selectProductQuantity(oldQuantityValue);
                jQuery('.helloprint_product_price').html('');
                jQuery('#helloprint_product_variant_key').val();
                if (jQuery('.wphp-product-custom-quantity').length > 0 && response.data.variants.length == 0 && response.data.min_max_qty_message != '') {
                    jQuery('.wphp-product-custom-quantity').show();
                } else {
                    jQuery('.wphp-product-custom-quantity').hide();
                }
                if (jQuery('#helloprint_product_quantity option:first').val()) {
                    let oldQntFound = false;
                    if (oldQuantityValue) {
                        var find = window.quotes.find(function (quote) {
                            if (oldQuantityValue == quote.quantity && jQuery('#helloprint_service_level').val() == quote.serviceLevel) {
                                oldQntFound = true;
                                quantity = oldQuantityValue;
                                return quote;
                            }
                        })
                    }
                    if (!oldQntFound) {
                        quantity = jQuery('#helloprint_product_quantity option:first').val();
                        var find = window.quotes.find(function (quote) {
                            if (quantity == quote.quantity && jQuery('#helloprint_service_level').val() == quote.serviceLevel) {
                                return quote;
                            }
                        });
                    }
                    jQuery('.helloprint_product_price_exclude_tax').html('');
                    jQuery('.helloprint_product_price').html('');
                    jQuery('.helloprint_product_price_exclude_tax_without_margin').html('');
                    jQuery('.helloprint_product_price_without_margin').html('');
                    jQuery('.helloprint_product_excl_tax_price_input').val('');
                    var taxIncl = jQuery('#helloprint_tax_incl').val();
                    if (find) {
                        formattedPriceFromAjax(find.prices.centAmountExclTax / 100, '.helloprint_product_price_exclude_tax');
                        formattedPriceFromAjax(find.prices.centAmountInclTax / 100, '.helloprint_product_price');
                        formattedPriceFromAjax(find.prices.origcentAmountExclTax / 100, '.helloprint_product_price_exclude_tax_without_margin');
                        if (pageWithApparealSize) {
                            formattedPriceFromAjax(find.prices.centAmountInclTax / 100, '.wphp-apparealsize-price')
                            let amount = find.prices.centAmountInclTax / 100;
                            let price_per_piece = amount / jQuery("#helloprint_product_quantity").val();
                            formattedPriceFromAjax(price_per_piece, '.wphp-apparealsize-perpiece')
                        }
                        formattedPriceFromAjax(find.prices.origcentAmountInclTax / 100, '.helloprint_product_price_without_margin');
                        jQuery('#helloprint_product_sku').val(find.sku);
                        jQuery('#helloprint_product_variant_key').val(find.variantKey);
                        jQuery('#helloprint_product_excl_tax_price_input').val(find.prices.centAmountExclTax / 100);
                        jQuery('#helloprint_product_incl_tax_price_input').val(find.prices.centAmountInclTax / 100);
                        if (taxIncl) {
                            jQuery('#helloprint_product_price_input').val(find.prices.centAmountInclTax / 100);
                        } else {
                            jQuery('#helloprint_product_price_input').val(find.prices.centAmountExclTax / 100);
                        }
                        // jQuery('#wphp-add-product-to-order-btn').removeAttr('disabled');
                        // jQuery('.wphp-product-file-upload').removeClass('wphp-display-none');
                        jQuery('.helloprint_product_price_exclude_tax_without_margin').html(find.prices.origcentAmountExclTax / 100);
                        jQuery('.helloprint_product_price_without_margin').html(find.prices.origcentAmountInclTax / 100);
                        var exclTaxOption = jQuery('#helloprint_product_show_only_incl_vat_input').val();
                        if (exclTaxOption == 1) {
                            var designPriceExclPrice = parseFloat(jQuery('#wphp-design-price-hidden').val());
                            jQuery('.wphp-design-price-label').hide();
                            var designPriceInclTax = designPriceExclPrice * (1 + (response.data.tax_rate / 100));
                            formattedPriceFromAjax(designPriceInclTax, '.wphp-design-incl-tax-price-label');
                        }
                        jQuery('.wphp-order-service-quantity').show();
                        jQuery('.wphp-product-file-upload').show();
                        jQuery('#wphp-add-product-to-order-btn').removeAttr('disabled');
                        jQuery('.wphp-admin-offer-price').show();
                    }
                }
                // Non standard size-quantity error show
                if (pageWithApparealSize && total > 0 && response.data.min_max_qty_message != '' && emptySelector.length == 0 && jQuery("#wphp-appreal-size-nonstandard-error").length > 0) {
                    var minMaxQnt = response.data.min_max_qty_message.match(/\d+/g);
                    var minQnt = +minMaxQnt[0];
                    var MaxQnt = +minMaxQnt[1];
                    if (total < minQnt || total > MaxQnt) {
                        jQuery("#wphp-appreal-size-nonstandard-error").show();
                        jQuery('#wphp-add-product-to-order-btn').prop("disabled", true);
                        resetPrice()
                    } else {
                        jQuery("#wphp-appreal-size-nonstandard-error").hide();
                    }
                }
                else if (pageWithApparealSize && emptySelector == 0 && total > 0 && response.data.quantities.length <= 0) {
                    jQuery("#wphp-appreal-size-nonstandard-error").show();
                    jQuery('#wphp-add-product-to-order-btn').prop("disabled", true);
                    resetPrice()
                }
                else {
                    jQuery("#wphp-appreal-size-nonstandard-error").hide();
                }
                if (response.data.min_max_qty_message.length > 0) {
                    jQuery("#wphp-min_max_message_div").html(response.data.min_max_qty_message);
                }
                if (window.quotes.length == 0) {
                    jQuery('.wphp-order-service-quantity').hide();
                }

                jQuery('.wphp-options').removeAttr('disabled');

            }
        }
    });
}


function selectProductQuantity(oldQuantity = '') {
    var deliveryOption = jQuery('#helloprint_service_level');
    var oldDeliveryOptionValue = deliveryOption.val();
    if (oldDeliveryOptionValue != '0') {
        deliveryOption.find('option').remove();
        jQuery(jQuery(window.serviceLevelDays).get().reverse()).each(function (key, item) {
            if (item) {
                var label = item.label;
                var option = label.charAt(0).toUpperCase() + label.slice(1);
                deliveryOption.append(new Option(option, item.value));
            }
        });
    }

    jQuery("#helloprint_product_quantity option[value='" + oldQuantity + "'").prop('selected', true);
    jQuery("#helloprint_service_level option[value='" + oldDeliveryOptionValue + "'").prop('selected', true);
}

function getMapDataWithSelectedAttribute() {
    var attributes = mapAttributes();
    var selectedAttribute = jQuery(this).data("select-id");
    var data = mapData(attributes, selectedAttribute);
    return [data, selectedAttribute];
}

function formattedPriceFromAjax(amount, elementToUpdate, prependElem = '', appendElem = '') {
    data = setupAjaxForFormatPrice(amount);
    jQuery.post(helloprint_ajax.ajax_url, data, function (response) {
        var formattedPrice;
        if (response.success == true) {
            formattedPrice = response.data;
        } else {
            formattedPrice = amount;
        }
        jQuery(elementToUpdate).html(prependElem + formattedPrice + appendElem);
    });
}


function setupAjaxForFormatPrice(amount) {
    return {
        'action': 'get_helloprint_product_formatted_price',
        'price_amount': amount,
        '_ajax_nonce': helloprint_ajax_nonce.value
    }
}

function updatePrice() {
    quantity = jQuery("#helloprint_product_quantity").val();
    var find = window.quotes.find(function (quote) {
        if (quantity == quote.quantity &&
            jQuery("#helloprint_service_level").val() == quote.serviceLevel
        ) {
            return quote;
        }
    });
    formattedPriceFromAjax(find.prices.centAmountExclTax / 100, '.helloprint_product_price_exclude_tax');
    formattedPriceFromAjax(find.prices.centAmountInclTax / 100, '.helloprint_product_price');
    formattedPriceFromAjax(find.prices.origcentAmountExclTax / 100, '.helloprint_product_price_exclude_tax_without_margin');
    formattedPriceFromAjax(find.prices.origcentAmountInclTax / 100, '.helloprint_product_price_without_margin');
    jQuery('#helloprint_product_price_input').val(parseFloat(find.prices.centAmountInclTax) / 100);
    jQuery('#helloprint_product_incl_tax_price_input').val(parseFloat(find.prices.centAmountInclTax) / 100);
    jQuery('#helloprint_product_excl_tax_price_input').val(parseFloat(find.prices.centAmountExclTax) / 100);
}

function showHelloprintAddOrderItemModal() {
    jQuery('#helloprint_product_excl_tax_price_input').attr('type', 'number');
    jQuery('#wc-order-modal-add-wphp-products').show();
}

function hideHelloprintAddOrderItemModal() {
    jQuery('#helloprint_product_excl_tax_price_input').attr('type', 'hidden');
    jQuery('#wc-order-modal-add-wphp-products').hide();
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


function hideAttributeAndQuantites() {
    jQuery('.wphp-options').attr('disabled', 'disabled');
    jQuery('#wphp-btn-confirm').prop("disabled", true);
    jQuery('.wphp-product-file-upload').hide();
    jQuery('.wphp-admin-offer-price').hide();
    jQuery('.helloprint_product_price_exclude_tax_without_margin').html('');
    jQuery('.helloprint_product_price_without_margin').html('');
    jQuery('#wphp-add-product-to-order-btn').attr('disabled', 'disabled');
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

function evaluateWidthHeight() {
    if (jQuery('#helloprint_custom_width').val() != '' && (jQuery('#helloprint_custom_height').val() != '')) {
        var multiply = 0;
        var index = 0;
        jQuery(".helloprint_product_options").each(function () {
            var nameAttr = jQuery(this).attr("name");
            nameAttr = nameAttr.replace(/helloprint_options|\[|\]/g, '');
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
                var oldValue = jQuery("#" + jQuery(this).attr("id") + "_hidden").val();
                jQuery('#helloprint_custom_' + nameAttr).val(oldValue);
                return false;

            }
            var $id = jQuery(this).attr("id");
            jQuery("#" + $id + "_hidden").val(value);
            index++;

        });
        var total = getCustomSizeTotal(multiply);
        jQuery(".wphp-custom-total").html(format(total));
        if (jQuery(".wphp-product-option-selector option[value='0']:selected").length == 0) {
            var attributes = mapAttributes();
            var selectedAttribute = jQuery(".wphp-product-option-selector:last").data("select-id");
            var data = mapData(attributes, selectedAttribute);
            sendRequest(data, selectedAttribute);
        } else {
            // Empty
        }
    } else {
        // Empty
    }
}

function getMinMaxSize(sizeAttribute) {
    var helloprintProductMin = jQuery(`.wphp-product-${sizeAttribute}-min`
    ).val();
    var helloprintProductMax = jQuery(`.wphp-product-${sizeAttribute}-max`
    ).val();
    return [helloprintProductMin, helloprintProductMax];
}

function resetCustomHeightWidth() {
    jQuery(".wphp-product-unit").val('');
    jQuery(".wphp-product-dim").val('');
    jQuery("#helloprint_custom_width").val('');
    jQuery("#helloprint_custom_height").val('');
    jQuery(".wphp-custom-total").html('')
}

function fileUploader(name, hiddenFilePathFieldName, item_id = '', hp_action = '', refresh = 0) {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginPdfPreview
    );

    const pond01 = FilePond.create(
        document.querySelector(name),
        {
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword',
           'application/x-zip-compressed', 'application/octet-stream', 'application/postscript'],
            maxFileSize: helloprint_file_max_upload_size.maxSize,
        }
    );
    FilePond.setOptions({
        labelIdle: helloprint_ajax_translate.labelIdle,
        labelInvalidField: helloprint_ajax_translate.labelInvalidField,
        labelFileWaitingForSize: helloprint_ajax_translate.labelFileWaitingForSize,
        labelFileSizeNotAvailable: helloprint_ajax_translate.labelFileSizeNotAvailable,
        labelFileLoading: helloprint_ajax_translate.labelFileLoading,
        labelFileLoadError: helloprint_ajax_translate.labelFileLoadError,
        labelFileProcessing: helloprint_ajax_translate.labelFileProcessing,
        labelFileProcessingComplete: helloprint_ajax_translate.labelFileProcessingComplete,
        labelFileProcessingAborted: helloprint_ajax_translate.labelFileProcessingAborted,
        labelFileProcessingError: helloprint_ajax_translate.labelFileProcessingError,
        labelFileProcessingRevertError: helloprint_ajax_translate.labelFileProcessingRevertError,
        labelFileRemoveError: helloprint_ajax_translate.labelFileRemoveError,
        labelTapToCancel: helloprint_ajax_translate.labelTapToCancel,
        labelTapToRetry: helloprint_ajax_translate.labelTapToRetry,
        labelTapToUndo: helloprint_ajax_translate.labelTapToUndo,
        labelButtonRemoveItem: helloprint_ajax_translate.labelButtonRemoveItem,
        labelButtonAbortItemLoad: helloprint_ajax_translate.labelButtonAbortItemLoad,
        labelButtonRetryItemLoad: helloprint_ajax_translate.labelButtonRetryItemLoad,
        labelButtonAbortItemProcessing: helloprint_ajax_translate.labelButtonAbortItemProcessing,
        labelButtonUndoItemProcessing: helloprint_ajax_translate.labelButtonUndoItemProcessing,
        labelButtonRetryItemProcessing: helloprint_ajax_translate.labelButtonRetryItemProcessing,
        labelButtonProcessItem: helloprint_ajax_translate.labelButtonProcessItem,
        maxFileSize: helloprint_file_max_upload_size.maxSize,
        labelMaxFileSize: helloprint_ajax_translate.labelMaxFileSize,
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                var inputFieldName = fieldName.replace('[]', '');
                const request = new XMLHttpRequest();
                addHelloPrintToCallQueue(function (que) {
                    var formData = new FormData();
                    formData.append(fieldName, file, file.name);
                    if (fieldName == 'helloprint_filepond_order_item_product_file_upload[]') {
                        formData.append('action', 'helloprint_upload_cart_file');
                        formData.append('no_cart', 1);
                    } else {
                        formData.append('action', 'helloprint_upload_preset_file');
                    }
                    formData.append('_ajax_nonce', helloprint_ajax_nonce.value); 
                    request.open('POST', helloprint_ajax.ajax_url);
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            var parsedResponse = JSON.parse(request.responseText);
                            if (parsedResponse.success == false) {
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            } else {
                                if (parsedResponse && parsedResponse.data) {
                                    for (let imgDetails of parsedResponse.data) {
                                        jQuery("<input>").attr({
                                            name: `${hiddenFilePathFieldName}[]`,
                                            id: `file-${imgDetails.file_name}`,
                                            type: "hidden",
                                            value: `${JSON.stringify(imgDetails)}`
                                        }).appendTo(`#${inputFieldName}`);
                                        if (fieldName != 'helloprint_filepond_order_item_product_file_upload[]') {
                                            setTimeout(function () {
                                                var tr = jQuery(`[value='[${JSON.stringify(imgDetails)}]']`);

                                                _helloprint_only_show_authoritive_files(tr.closest(".wphp-order-item-preset-div"));
                                                tr = tr.closest(".wphp-list-table").find(".wphp-preset-file-uplaoder");
                                                showHideArtworkExternalUrl(tr);
                                            }, 500);
                                        }
                                        

                                    }

                                    setTimeout(function(){
                                        if (jQuery(".helloprint_order_preset_public_url").length > 0) {
                                            showHidePresetPublicUrl(jQuery(".wphp-order-preset-file-upload-page"));
                                        }
                                    }, 700);
                                    load(JSON.stringify(parsedResponse.data));
                                }
                            }
                        } else {
                            if (request.responseText != '0') {
                                var parsedResponse = JSON.parse(request.responseText);
                                alert(parsedResponse.data.message);
                                error(parsedResponse.data.message);
                            } else {
                                alert(helloprint_ajax_translate.labelMaxFileSize);
                                error(helloprint_ajax_translate.labelMaxFileSize);
                            }
                        }
                        que();
                    };
                    request.send(formData);
                });
                return {
                    abort: () => {
                        abort();
                        request.onloadend = function () {
                            if (request && request.responseText) {
                                responseText = JSON.parse(request.responseText);
                                if (responseText && responseText.data && responseText.data[0] && responseText.data[0].file_path) {
                                    console.log(responseText.data[0].file_path);
                                    if (name != 'helloprint_filepond_order_item_product_file_upload') {
                                        var ajaxAction = 'remove_helloprint_preset_file';
                                    } else {
                                        var ajaxAction = 'remove_helloprint_product_file';
                                    }
                                    if (jQuery(".helloprint_prefer_files-div").length > 0) {
                                        jQuery(".wphp-order-item-preset-div ").each(function(){
                                            _helloprint_show_hide_authoritive_files(jQuery(this));
                                        });
                                    }
                                    jQuery.ajax({
                                        method: "POST",
                                        url: helloprint_ajax.ajax_url,
                                        data: {
                                            action: ajaxAction,
                                            helloprint_file: responseText.data[0].file_path,
                                            _ajax_nonce: helloprint_ajax_nonce.value
                                        }
                                    })
                                }
                            }
                        }
                    },
                };

            },
            revert: (uniqueFileId, load, error) => {
                var json = JSON.parse(uniqueFileId);
                if (json) {
                    if (name != 'helloprint_filepond_order_item_product_file_upload') {
                        var ajaxAction = 'remove_helloprint_preset_file';
                    } else {
                        var ajaxAction = 'remove_helloprint_product_file';
                    }
                    jQuery.ajax({
                        method: "POST",
                        url: helloprint_ajax.ajax_url,
                        data: {
                            action: ajaxAction,
                            helloprint_file: json[0].file_path,
                            _ajax_nonce: helloprint_ajax_nonce.value
                        },
                        success: function (serverResponse) {
                            jQuery(`[id='file-${json[0].file_name}']`).remove();
                            if (jQuery(".helloprint_prefer_files-div").length > 0) {
                                jQuery(".wphp-order-item-preset-div ").each(function(){
                                    _helloprint_show_hide_authoritive_files(jQuery(this));
                                });
                            }
                            if (name != 'helloprint_filepond_order_item_product_file_upload') {
                                setTimeout(function () {
                                    allArtworksCheckUploads();
                                }, 500);
                            }

                            setTimeout(function(){
                                if (jQuery(".helloprint_order_preset_public_url").length > 0) {
                                    showHidePresetPublicUrl(jQuery(".wphp-order-preset-file-upload-page"));
                                }
                            }, 300);
                        }
                    })
                }
                error('oh my goodness');
                load();
            }
        },
    });
}

function fileUploaderItems(name,hiddenFilePathFieldName, item_id, hp_action = '', refresh = 0) {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation
    );
    const pond11 = FilePond.create(
        document.querySelector(name),
        {
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword',
           'application/x-zip-compressed', 'application/octet-stream', 'application/postscript'],
        }
    );
    FilePond.setOptions({
        labelIdle: helloprint_ajax_translate.labelIdle,
        labelInvalidField: helloprint_ajax_translate.labelInvalidField,
        labelFileWaitingForSize: helloprint_ajax_translate.labelFileWaitingForSize,
        labelFileSizeNotAvailable: helloprint_ajax_translate.labelFileSizeNotAvailable,
        labelFileLoading: helloprint_ajax_translate.labelFileLoading,
        labelFileLoadError: helloprint_ajax_translate.labelFileLoadError,
        labelFileProcessing: helloprint_ajax_translate.labelFileProcessing,
        labelFileProcessingComplete: helloprint_ajax_translate.labelFileProcessingComplete,
        labelFileProcessingAborted: helloprint_ajax_translate.labelFileProcessingAborted,
        labelFileProcessingError: helloprint_ajax_translate.labelFileProcessingError,
        labelFileProcessingRevertError: helloprint_ajax_translate.labelFileProcessingRevertError,
        labelFileRemoveError: helloprint_ajax_translate.labelFileRemoveError,
        labelTapToCancel: helloprint_ajax_translate.labelTapToCancel,
        labelTapToRetry: helloprint_ajax_translate.labelTapToRetry,
        labelTapToUndo: helloprint_ajax_translate.labelTapToUndo,
        labelButtonRemoveItem: helloprint_ajax_translate.labelButtonRemoveItem,
        labelButtonAbortItemLoad: helloprint_ajax_translate.labelButtonAbortItemLoad,
        labelButtonRetryItemLoad: helloprint_ajax_translate.labelButtonRetryItemLoad,
        labelButtonAbortItemProcessing: helloprint_ajax_translate.labelButtonAbortItemProcessing,
        labelButtonUndoItemProcessing: helloprint_ajax_translate.labelButtonUndoItemProcessing,
        labelButtonRetryItemProcessing: helloprint_ajax_translate.labelButtonRetryItemProcessing,
        labelButtonProcessItem: helloprint_ajax_translate.labelButtonProcessItem,
        server: {
            url: ajaxurl,
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                const request = new XMLHttpRequest();
                addHelloPrintToCallQueue(function (que) {
                    var uploadedFilesElement = jQuery('.upload_container_button');
                    var cartItemKey = uploadedFilesElement.find('.data-item-key');
                    var formData = new FormData();
                    formData.append(fieldName, file, file.name);
                    formData.append("item_id", item_id)
                    formData.append('action', 'helloprint_upload_order_item_file');
                    formData.append('_ajax_nonce', helloprint_ajax_nonce.value);
                     
                    request.open('POST', ajaxurl);
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            // Check for file validation
                            location.reload();
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
            revert: (uniqueFileId, load, error) => {
                // console.log("in reverty", ajaxLock, uniqueFileId);
                var json = JSON.parse(uniqueFileId)

                jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'remove_helloprint_cart_file',
                        cart_item_key: json.cart_item_key,
                        file_component_id: json.file_component_id,
                        _ajax_nonce: helloprint_ajax_nonce.value
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

 function validURL(myURL) {
    var pattern = /^(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?/;
    return pattern.test(myURL);
}

function showHideArtworkUpload(element) {
    setTimeout(function () {
        var external_url = element.val();
        if (jQuery.trim(external_url) == '') {
            _helloprint_show_hide_authoritive_files(element.closest(".wphp-order-item-preset-div"));
            element.closest(".wphp-list-table")
                .find(".wphp-order-preset-file-upload")
                .removeClass("wphp-clicked-not-allowed");
        } else {
            _helloprint_only_show_authoritive_files(element.closest(".wphp-order-item-preset-div"));
            element.closest(".wphp-list-table")
                .find(".wphp-order-preset-file-upload")
                .addClass("wphp-clicked-not-allowed");
        }
        
    }, 300);
}

function showHideArtworkExternalUrl(element) {
    setTimeout(function () {
        var parent = element.closest(".wphp-list-table");
        var count = element.find('[name="helloprint_order_preset_file_upload[]"]').length;
        if (count == 1) {
            var value = element.find('[name="helloprint_order_preset_file_upload[]"]').val();
            if (value == '') {
                count = 0;
            }
        }
        if (count > 0) {
            parent.find(".helloprint_artwork_external_url")
                .attr("disabled", "disabled");
        } else {
            parent.find(".helloprint_artwork_external_url")
                .removeAttr("disabled");
        }
    }, 300);

}

function allArtworksCheckUploads() {
    jQuery(".wphp-preset-file-uplaoder").each(function () {
        var $this = jQuery(this);
        showHideArtworkExternalUrl($this);
    });
}


function toggleSKUTextarea() {
    if (jQuery('#helloprint_product_limit_variant_key').find('option:selected').val() == 1) {
        jQuery('#helloprint_product_sku_div').css('display', '');
    } else {
        jQuery('#helloprint_product_sku_div').css('display', 'none');
    }
}
function addProductOptionCustomApparelSizeTotalQuantity() {
    var total = 0;
    jQuery(".wphp-product-options-apparel-size").each(function () {
        var value = parseInt(jQuery(this).val());
        if (isNaN(value)) {
            value = 0;
        }
        total += value;
    });
    jQuery("#wphp-product-total-custom-quantity").html(total);
    jQuery("#wphp-product-total-appreal-size").val(total);
    var emptySelector = jQuery(".wphp-product-option-selector option[value='0']:selected");
    if (emptySelector.length == 0) {
        if (total > 0) {
            jQuery("#wphp-appreal-size-nonstandard-error").hide();
            jQuery("#helloprint_product_quantity").val(total);
            var attributes = mapAttributes();
            var selectedAttribute = jQuery(".wphp-product-option-selector:last").data("select-id");
            var data = mapData(attributes, selectedAttribute);
            sendRequest(data, selectedAttribute);
        }
        else {
            jQuery('#helloprint_service_level').find('option').remove();
            jQuery('.wphp-product-file-upload').hide();
            jQuery('#wphp-add-product-to-order-btn').prop("disabled", true);
            resetPrice()
        }
    }
}

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

function resetPrice() {
    jQuery('.helloprint_product_price_without_margin').html('');
    jQuery('.helloprint_product_price_exclude_tax_without_margin').html('');
    jQuery('.helloprint_product_currency').html('');
    jQuery('#hello_product_variant_key').val();
    jQuery('#helloprint_product_excl_tax_price_input').val('');
    jQuery('.wphp-admin-offer-price').hide();
    if (pageWithApparealSize) {
        jQuery('.wphp-apparealsize-perpiece').html('');
        jQuery('.wphp-apparealsize-price').html('');
    }
}


function orderItemsFileUploder() {
    jQuery(".helloprint_order_item_file_each").each(function () {
        var item_id = jQuery(this).data("itemid");
        var id = jQuery(this).data("id");
        fileUploader("." + id, 'helloprint_order_item_file_upload', item_id, 'helloprint_upload_order_item_file', 1);
    });
}

function orderPresetFileUploader(params) {
    jQuery(".helloprint_order_preset_file_each").each(function () {
        var id = jQuery(this).data("id");
        fileUploader("." + id, 'helloprint_order_preset_file_upload', '', '', 0);
    });
}

function createFilePond(name) {
    return FilePond.create(
        document.querySelector(name),
        {
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'image/tiff', 'image/tif'],
            maxFileSize: helloprint_file_max_upload_size.maxSize,
        }
    );
}

function resetOrderForm() {
    FilePond.destroy(document.querySelector('.helloprint_filepond_order_item_product_file_upload'));
    createFilePond('.helloprint_filepond_order_item_product_file_upload');
    orderItemsFileUploder();
    jQuery('#add-wphp-product-item').val(0).trigger('change');
    jQuery('.wphp-order-product-attributes').html('');
    jQuery('.wphp-order-service-quantity').hide();
    jQuery('.wphp-product-file-upload').hide();
    jQuery('.wphp-product-custom-size').html('');
    jQuery('.wphp-product-custom-quantity').html('');
}


jQuery(document).on('FilePond:addfile', '.wphp-order-preset-file-upload', function (e) {
    jQuery(this).closest(".wphp-list-table").find('.wphp-submit-order-item-preset').attr('disabled', true);
});

jQuery(document).on('FilePond:processfile','.wphp-order-preset-file-upload', function (e) {
    jQuery(this).closest(".wphp-list-table").find('.wphp-submit-order-item-preset').attr('disabled', false);
});

jQuery(document).on('FilePond:removefile','.wphp-order-preset-file-upload', function (e) {
    jQuery(this).closest(".wphp-list-table").find('.wphp-submit-order-item-preset').attr('disabled', false);
});


jQuery(document).on('FilePond:addfile','.wphp-product-file-upload', function (e) {
    jQuery('#wphp-add-product-to-order-btn').attr('disabled', true);
});

jQuery(document).on('FilePond:processfile','.wphp-product-file-upload', function (e) {
    jQuery('#wphp-add-product-to-order-btn').attr('disabled', false);
});

jQuery(document).on('FilePond:removefile','.wphp-product-file-upload', function (e) {
    jQuery('#wphp-add-product-to-order-btn').attr('disabled', false);
});

jQuery(document).on('click','.save_order', function (e) {
    if (!helloprint_admin_confirm_recalculate()) {
        return false;
    }
});
function helloprint_admin_confirm_recalculate()
{
    if (needRecalculation && jQuery(".calculate-action").length > 0) {
        if (!confirm(helloprint_ajax_translate.insure_order_has_calculated)) {
            return false;
        } else {
            needRecalculation = false;
            return true;
        }
    }
    return true;
}

function _helloprint_custom_sizes_for_preset(options = [], preset_id = "", line_item_id = "", element = null, return_variant_key = "")
{
    var data = {
        action: "get_helloprint_preset_get_custom_size",
        _ajax_nonce: helloprint_ajax_nonce.value,
        options: JSON.stringify(options),
        preset_id: preset_id,
        line_item_id: line_item_id
    };
    jQuery.post(ajaxurl, data, function (response) {
        if (element == null) {
            if (return_variant_key == jQuery("#helloprint_order_preset_variant_key").val()) {
                jQuery(".helloprint_preset_custom_size_tr").html(response.data.html);
                jQuery("#helloprint_pod_available_option_type").val("");
                if (typeof(response.data.type) != typeof(undefined)) {
                    jQuery("#helloprint_pod_available_option_type").val(response.data.type);
                }
                if (typeof(response.data.type) == typeof(undefined) || response.data.type != "appreal_sizes") {
                    jQuery("#helloprint_preset_quantity_tr").show();
                } else {
                    jQuery("#helloprint_preset_quantity_tr").hide();
                    
                }
            } else {
                jQuery(".helloprint_preset_custom_size_tr").html("");
            }
            
        } else {
            var table = element.closest("table");
            table.find(".helloprint_order_item_preset_custom_options").html(response.data.html);

            if (typeof(response.data.type) == typeof(undefined) || response.data.type != "appreal_sizes") {
                table.find(".helloprint_preset_quantity_select_td").show();
            } else {
                table.find(".helloprint_preset_quantity_select_td").hide();
            }

            table.find(".helloprint_preset_option_type").val("");
            if (typeof(response.data.type) != typeof(undefined)) {
                table.find(".helloprint_preset_option_type").val(response.data.type);
            }
        }
        
    });
}

jQuery(document).on("submit", "#helloprint_add_edit_preset_form", function(event){
    if (jQuery(".helloprint_preset_appreal_size").length > 0) {
        var total_appreal_size_quantity = 0;
        jQuery(".helloprint_preset_appreal_size").each(function(){
            var value = parseFloat(jQuery(this).val());
            if (value > 0) {
                total_appreal_size_quantity += value;
            }
        });

        if (total_appreal_size_quantity <= 0) {
            alert(jQuery("#helloprint_hidden_preset_one_appreal_msg").val());
            jQuery(".helloprint_preset_appreal_size:first").focus();
            event.preventDefault();
            return false;
        }
    }
});




function showHideHPProductMarkup() {
    const enabled = Number(jQuery("select[name='helloprint_product_markup_option']").val());
    if (enabled == 1) {
        jQuery(".helloprint_product_markup_field").hide();
        jQuery(".helloprint_user_roles_for_price_markup_field").hide();
        jQuery(".helloprint_pricing_tier_markup_field").hide();

    } else if(enabled == 2) {
        jQuery(".helloprint_product_markup_field").show();
        jQuery(".helloprint_user_roles_for_price_markup_field").hide();
        jQuery(".helloprint_pricing_tier_markup_field").hide();
    } else {
        jQuery(".helloprint_product_markup_field").hide();
        jQuery(".helloprint_user_roles_for_price_markup_field").show();
        jQuery(".helloprint_pricing_tier_markup_field").show();
    }
}

function hp_show_margin_markup_product()
{
    var option = jQuery("[name='helloprint_markup_margin']:checked").val();
    if (option == "markup") {
        jQuery("#wphp-product-add-edit-margin-div").hide();
        jQuery("#wphp-product-add-edit-markup-div").show();
    } else {
        jQuery("#wphp-product-add-edit-margin-div").show();
        jQuery("#wphp-product-add-edit-markup-div").hide();
    }
}

function hp_show_hide_preset_page_file_upload(element)
{
    setTimeout(function () {
        var external_url = element.val();
        if (jQuery.trim(external_url) == '') {
            element.closest("table")
                .find(".wphp-order-preset-file-upload-page")
                .removeClass("wphp-clicked-not-allowed");
        } else {
            element.closest("table")
                .find(".wphp-order-preset-file-upload-page")
                .addClass("wphp-clicked-not-allowed");
        }
        
    }, 300);
}

function showHidePresetPublicUrl(element) {
    setTimeout(function () {
        var parent = element.closest("table");
        var count = jQuery('[name="helloprint_order_preset_file_upload"]').length;
        if (count == 1) {
            var value = jQuery('[name="helloprint_order_preset_file_upload"]').val();
            if (value == '') {
                count = 0;
            }
        }
        if (count > 0) {
            jQuery(".helloprint_order_preset_public_url")
                .attr("disabled", "disabled");
        } else {
            jQuery("#order_preset_public_url")
                .removeAttr("disabled");
        }
    }, 200);

}