jQuery(document).ready(function () {
    var productTable = jQuery('#tbl-bulk-product-list').DataTable({
        "columnDefs": [{
            "targets": 0,
            "orderable": false
        }]
    });
    var productTablePage = 0;

    jQuery("#wphp-filter-category").change(function (e) {
        var val = jQuery(this).val();
        productTable.column(".wphp-product-category").search(val, true, false, false).draw();        
    });

    jQuery(document).on('click', '.paginate_button ', function () {
        // Reset checkboxes when a page is changed
        jQuery(".all-wphp-products").prop("checked", false);
        jQuery(".select-wphp-product").prop("checked", false);
        productTablePage = productTable.page.info().page;
        productTable.page(productTable.page.info().page).draw('page');
    });
    
    jQuery(document).on('change', '.all-wphp-products', function () {
        productTable.page(productTablePage).draw('page');
        if(this.checked) {
          jQuery(".select-wphp-product").prop("checked", true);
        } else {
            jQuery(".select-wphp-product").prop("checked", false);
        }
        updateHPTotalCount();
    });

    jQuery(document).on('change', '.select-wphp-product', function() {
        if (jQuery(".select-wphp-product:checked").length == jQuery(".select-wphp-product").length) {
            jQuery(".all-wphp-products").prop("checked", true);
        } else {
            jQuery(".all-wphp-products").prop("checked", false);
        }
        updateHPTotalCount();
    });

    const bulkModal = new bootstrap.Modal('#wphp-category-modal', {backdrop: 'static', keyboard: false})
    jQuery('#wphp-product-add-to-cat').click(function() {
        var checkedlength = jQuery(".select-wphp-product:checked").length;
        if (checkedlength <= 0) {
            var emptyMessage = jQuery("#hidden_helloprint_product_empty_messge_div").val();
            alert(emptyMessage);
            return false;
        }else{
            jQuery("#helloprint_product_modal_count").html(checkedlength);
            bulkModal.show();
        }
    });

    jQuery('#wphp-category-modal').on('hidden.bs.modal', function (e) {
        jQuery("#wphp-modal-submit-bulk-import-product").removeAttr("disabled");
        jQuery("#wphp-modal-submit-bulk-import-product").removeClass("wphp-spin");
        jQuery("#wphp-bulk-import-status-message").html('');
        jQuery("#wphp-bulk-import-category-details-link").html("");
        jQuery("#wphp-bulk-import-modal-fields").show();
        jQuery("#wphp-modal-submit-bulk-import-product").show();
        jQuery("#wphp-wocommerce-category").val('').trigger("change");
        jQuery("#helloprint_modal_product_margin").val('');
        jQuery("#wphp-bulk-import-job-ids").val('');
        jQuery("#wphp-bulk-new-import-counts").val('');
        jQuery("#wphp-bulk-remaining-import-counts").val('');
        jQuery("#wphp-bulk-import-confirmation-email-message").html('');
        jQuery("#wphp-bulk-import-donot-close-message").html('');
        jQuery("#helloprint_bulk-new-import").html('');
        jQuery("#helloprint_bulk-duplicate-products").html('');
        jQuery("#helloprint_bulk-success-products-message").html('');
        jQuery("#helloprint_bulk-failed-products").html('');
        jQuery("#helloprint_bulk-progree-bar").hide();
        jQuery("#helloprint_bulk_import-progress-bar").css("width", "0%");
        jQuery("#helloprint_bulk_import-progress-bar").html("0%");
        jQuery("#helloprint_bulk-success-products-message").html("");
        jQuery("#helloprint_bulk_import-progress-bar").removeClass("hp-bar-success");
        jQuery("[name='helloprint_product_margin_option'][value='1']").trigger("click");
        jQuery("#helloprint_modal_product_margin").val("");
        if(typeof("hp_bulk_interval") != typeof(undefined)) {
            console.log("Interval cleared 61");
            clearInterval(hp_bulk_interval);
        }
    });
    if (jQuery("#helloprint_modal_product_margin").length > 0) {
        showHideProductMargin();
        jQuery("input[name='helloprint_product_margin_option']").change(function(e) {
            showHideProductMargin();
        });
    }
} );

function updateHPTotalCount()
{
    var totalchecked = jQuery(".select-wphp-product:checked").length;
    jQuery("#hp_total_selected").html(totalchecked);
}

function submitHelloPrintBulkImport()
{
    jQuery("#wphp-bulk-import-status-message").html("");
    var woocoom_cat = jQuery("#wphp-wocommerce-category").val();
    var margin = jQuery("#helloprint_modal_product_margin").val();
    var marginOption = jQuery("input[name='helloprint_product_margin_option'][value='1']").prop("checked");
    var products = [];
    if (woocoom_cat == '') {
        var emptyCatMessage = jQuery("#hidden_helloprint_cat_empty_messge_div").val();
        jQuery("#wphp-bulk-import-status-message").html(emptyCatMessage);
        return false;
    }

    if( margin != '' && (parseInt(margin) < 0 || parseInt(margin) > 99)) {
        var invalidMessage = jQuery("#hidden_helloprint_invalid_margin_div").val();
        jQuery("#wphp-bulk-import-status-message").html(invalidMessage);
        return false;
    }

    if(margin == null || margin == ''){
        margin = 0;
    }

    jQuery(".select-wphp-product:checked").each(function(){
        var pslug = jQuery(this).val();
        var pname = jQuery(this).data("name");
        products.push({
            name: pname,
            slug: pslug
        });
    });

    jQuery("#wphp-modal-submit-bulk-import-product").attr("disabled", "disabled");
    jQuery("#wphp-modal-submit-bulk-import-product").addClass("wphp-spin");
    var data = {
            'action': 'bulk_import_helloprint_products',
            'products': products,
            'margin': margin,
            'category': woocoom_cat,
            'marginOption': marginOption,
            '_ajax_nonce': helloprint_ajax_nonce.value
        };

    jQuery.post(ajaxurl, data, function (response) {
        jQuery("#wphp-modal-submit-bulk-import-product").removeAttr("disabled");
        jQuery("#wphp-modal-submit-bulk-import-product").removeClass("wphp-spin");
        jQuery(".all-wphp-products").prop("checked", false);
        jQuery(".select-wphp-product").prop("checked", false);
        if (!jQuery("#wphp-category-modal").hasClass("show")) {
            return false;
        }
        if (response.data.success == true) {
            if (response.data.difference > 0) {
                var message_to_display = "<b>" + response.data.close_message + "</b><br/>"+
                response.data.email_confirm + 
                "<br/><br/>" + response.data.category_link_html;
                jQuery("#wphp-bulk-import-confirmation-email-message").html('<div class="alert alert-info">' + message_to_display + '</div>');
                //jQuery("#wphp-bulk-import-donot-close-message").html('<h5>' + response.data.close_message + '</h5>');
                jQuery("#wphp-bulk-import-job-ids").val(response.data.jobs);
                jQuery("#wphp-bulk-new-import-counts").val(response.data.difference);
                jQuery("#wphp-bulk-remaining-import-counts").val(response.data.difference);

                window.hp_bulk_interval = setInterval(helloprint_bulk_recursion_call, 2000);
                jQuery("#helloprint_bulk_import-progress-bar").removeClass("hp-bar-success");
                jQuery("#helloprint_bulk_import-progress-bar").css("width", "0%");
                jQuery("#helloprint_bulk_import-progress-bar").html("0/" + response.data.difference);
                jQuery("#helloprint_bulk-progree-bar").show();
            } else {
                jQuery("#wphp-bulk-import-confirmation-email-message").html('');
                jQuery("#wphp-bulk-import-donot-close-message").html('');
                jQuery("#wphp-bulk-import-confirmation-email-message").html('');
                jQuery("#wphp-bulk-import-job-ids").val("");
                jQuery("#wphp-bulk-new-import-counts").val(response.data.difference);
                jQuery("#wphp-bulk-remaining-import-counts").val(response.data.difference);
                jQuery("#wphp-bulk-import-category-details-link").html('<div class="alert alert-info"> '+ response.data.category_link_html + '</div>');
                jQuery("#helloprint_bulk-progree-bar").hide();
                
                jQuery("#helloprint_bulk-new-import").html(response.data.import_message);
            }
            jQuery("#helloprint_bulk-success-products-message").html("");
            jQuery("#helloprint_bulk-failed-products").html("");
            var duplicate_message = (response.data.duplicate_message != "") ? response.data.duplicate_message : "";
            jQuery("#helloprint_bulk-duplicate-products").html(duplicate_message);
            //jQuery("#wphp-bulk-import-status-message").html('<div class="alert alert-success">' + response.data.message + '</div>');
            jQuery("#wphp-bulk-import-modal-fields").hide();
            jQuery("#wphp-modal-submit-bulk-import-product").hide();
            
            window.onbeforeunload = function (e) {
                 e = e || window.event;
                 
                if (e) {
                    e.returnValue = response.data.close_message;
                }
                return response.data.close_message;
            };
        } else {
            jQuery("#wphp-bulk-import-status-message").html('<div class="alert alert-danger">' + response.data.message + '</div>');
            
        }
    });

}
function showHideProductMargin()
{
    var enabled = jQuery("input[name='helloprint_product_margin_option'][value='1']").prop("checked");
    if (enabled) {
        jQuery(".helloprint_modal_product_margin_field").hide();
    } else {
        jQuery(".helloprint_modal_product_margin_field").show();
    }
}


function helloprint_bulk_recursion_call()
{
    var remaining = jQuery("#wphp-bulk-remaining-import-counts").val();
    var job_ids = jQuery("#wphp-bulk-import-job-ids").val();
    if (remaining <= 0 || job_ids == "") {
        jQuery("#helloprint_bulk_import-progress-bar").css("width", "0%");
        jQuery("#helloprint_bulk_import-progress-bar").html("0%");
        jQuery("#helloprint_bulk-progree-bar").hide();
        jQuery("#helloprint_bulk-success-products-message").html("");
        if(typeof("hp_bulk_interval") != typeof(undefined)) {
            console.log("Interval cleared 192");
            clearInterval(hp_bulk_interval);
        }
    }
    var data = {
        'action': 'wphp_check_remaining_imports',
        'job_ids': job_ids,
        '_ajax_nonce': helloprint_ajax_nonce.value
    };
    jQuery.post(ajaxurl, data, function (response) {
        if (response.data.success == true) {
            jQuery("#helloprint_bulk-new-import").html("");
            jQuery("#helloprint_bulk_import-progress-bar").css("width", response.data.percentage + "%");
            jQuery("#helloprint_bulk_import-progress-bar").html(response.data.total_success+"/"+response.data.total_jobs);
            var alert_class = (response.data.percentage == 100) ? "success" : "info";
            if (alert_class == "success") {
                jQuery("#helloprint_bulk_import-progress-bar").addClass("hp-bar-success");
                jQuery("#helloprint_bulk-success-products-message").html('<div class="alert alert-' + alert_class + '">' + response.data.success_message + '</div>');
            } else {
                jQuery("#helloprint_bulk-success-products-message").html("");
            }
            var remaining_jobs = response.data.total_jobs - (response.data.total_success + response.data.total_failed);
            jQuery("#wphp-bulk-remaining-import-counts").val(remaining_jobs);
            if (response.data.total_failed > 0) {
                var failed_html = response.data.failure_message;
                var failed_jobs = response.data.failed_jobs;
                if (failed_jobs.length > 0) {
                    failed_html += '<br/><ol type="1">';
                    jQuery.each(failed_jobs, function( index, job ) {
                        failed_html += '<li>' + job.product_id + '</li>';
                    });
                    failed_html += '</ol>';
                }
                jQuery("#helloprint_bulk-failed-products").html('<div class="alert alert-warning">' + failed_html + '<div>');
            } else {
                jQuery("#helloprint_bulk-failed-products").html("");
            }
            if (remaining_jobs <= 0) {
                if(typeof("hp_bulk_interval") != typeof(undefined)) {
                    console.log("Interval cleared 225");
                    clearInterval(hp_bulk_interval);
                }
            }
            var job_ids = jQuery("#wphp-bulk-import-job-ids").val();
            if (remaining_jobs > 0 && job_ids == "") {
                jQuery("#helloprint_bulk_import-progress-bar").css("width", "0%");
                jQuery("#helloprint_bulk_import-progress-bar").html("0%");
                jQuery("#helloprint_bulk-success-products-message").html("");
            }
        }
    });
}