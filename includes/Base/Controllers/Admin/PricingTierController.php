<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Exception;
use HelloPrint\Inc\Base\Controllers\BaseController;

class PricingTierController extends BaseController
{
    private $tierTableName = '', $scaledPricingTableName = '';
    private $wpdb;

    private function setTable()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tierTableName = $this->wpdb->prefix . 'helloprint_pricing_tiers';
        $this->scaledPricingTableName = $this->wpdb->prefix . 'helloprint_scaled_pricing';
    }

    public function helloprint_pricing_tiers()
    {
        $s = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : "";

        $post_per_page = 20;
        $pagenum = isset($_GET['paged']) ? (int)sanitize_text_field(wp_unslash($_GET['paged'])) : 1;

        $this->setTable();
        $query = "SELECT id,name,tier_type,default_markup,enable_scaling from $this->tierTableName";
        if (!empty($s)) {
            $query .= " WHERE `name` like '%" . $s . "%' ";
        }

        //Get total number of results
        $results = $this->wpdb->get_results($query);
        $totals = $this->wpdb->num_rows;

        $page = ($pagenum - 1);
        $query .= " ORDER BY id DESC LIMIT $post_per_page OFFSET " . $page * $post_per_page;
        $tiers = $this->wpdb->get_results($query);

        $num_of_pages = ceil($totals / $post_per_page);
        $page_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;', 'aag'),
            'next_text' => __('&raquo;', 'aag'),
            'total' => $num_of_pages,
            'current' => $pagenum
        ));

        require_once "$this->plugin_path/templates/admin/pricing-tiers/lists.php";
    }

    public function new_helloprint_pricing_tier()
    {
        if (isset($_POST['action'])) {
            $this->setTable();
            $name = sanitize_text_field(wp_unslash($_POST['name']));
            $default_markup = sanitize_text_field(wp_unslash($_POST['default_markup']));
            $tier_type = sanitize_text_field(wp_unslash($_POST['tier_type']));
            $enable_scaling = sanitize_text_field(wp_unslash($_POST['enable_scaling']));
            if ($enable_scaling != 1) {
                $enable_scaling = 0;
            }
            $this->wpdb->query("INSERT INTO $this->tierTableName(name,tier_type,default_markup,enable_scaling) VALUES('$name','$tier_type',$default_markup,$enable_scaling)");

            if ($enable_scaling == 1) {
                $tier_id = $this->wpdb->insert_id;
                $scaled_prices = array_map('sanitize_text_field', $_POST['scaled_price']);
                $scaled_margins = array_map('sanitize_text_field', $_POST['scaled_margin']);
                foreach ($scaled_prices as $k => $sp) {
                    $sp = !empty($sp) ? $sp : 0;
                    $sm = !empty($scaled_margins[$k]) ? $scaled_margins[$k] : 0;
                    $this->wpdb->query("INSERT INTO $this->scaledPricingTableName(pricing_tier_id,price,margin) VALUES('$tier_id',$sp,$sm)");
                }
            }
            add_helloprint_flash_notice(wp_kses(_translate_helloprint("New Pricing Tier Added Successfully", "helloprint"), false), "success");
            return wp_redirect('admin.php?page=helloprint-pricing-tiers');
        }
        // scripts for price tier add/edit
        wp_enqueue_script('wphp-admin-price-tier', $this->plugin_url . 'assets/admin/js/price-tier.js', [], _get_helloprint_version());

        $currency_symbol = get_woocommerce_currency_symbol();
        $action = "add";
        $id = "";
        $name = '';
        $tier_type = "margin";
        $default_markup = '';
        $enable_scaling = "";
        $action_to_perform = "create";
        require_once "$this->plugin_path/templates/admin/pricing-tiers/add-edit.php";
    }

    public function edit_helloprint_pricing_tier()
    {
        $this->setTable();
        if (isset($_POST['action']) && isset($_POST['id'])) {
            $this->setTable();
            $id = sanitize_text_field(wp_unslash($_POST['id']));
            $name = sanitize_text_field(wp_unslash($_POST['name']));
            $tier_type = sanitize_text_field(wp_unslash($_POST['tier_type']));
            $default_markup = sanitize_text_field(wp_unslash($_POST['default_markup']));
            $enable_scaling = sanitize_text_field(wp_unslash($_POST['enable_scaling']));
            if ($enable_scaling != 1) {
                $enable_scaling = 0;
            }
            $this->wpdb->query("DELETE FROM $this->scaledPricingTableName WHERE pricing_tier_id='$id'");
            $this->wpdb->query("UPDATE $this->tierTableName SET name='$name',tier_type='$tier_type',default_markup=$default_markup,enable_scaling=$enable_scaling WHERE id='$id'");

            if ($enable_scaling == 1) {
                $tier_id = $id;
                $scaled_prices = array_map('sanitize_text_field', $_POST['scaled_price']);
                $scaled_margins = array_map('sanitize_text_field', $_POST['scaled_margin']);
                foreach ($scaled_prices as $k => $sp) {
                    $sp = !empty($sp) ? $sp : 0;
                    $sm = !empty($scaled_margins[$k]) ? $scaled_margins[$k] : 0;
                    $this->wpdb->query("INSERT INTO $this->scaledPricingTableName(pricing_tier_id,price,margin) VALUES('$tier_id',$sp,$sm)");
                }
            }
            add_helloprint_flash_notice(wp_kses(_translate_helloprint("Pricing Tier Updated Successfully", "helloprint"), false), "success");
            return wp_redirect('admin.php?page=helloprint-pricing-tiers');
        }

        $id = absint($_GET['id']);
        $name = '';
        $default_markup = '';
        $enable_scaling = "";
        $tier_type = "margin";
        $scalings = [];
        $result = $this->wpdb->get_results("SELECT * FROM $this->tierTableName WHERE id='$id'");
        foreach ($result as $pricing) {
            $id = $pricing->id;
            $name = $pricing->name;
            $default_markup = $pricing->default_markup;
            $enable_scaling = $pricing->enable_scaling;
            $tier_type = $pricing->tier_type;
        }
        if ($enable_scaling == 1) {
            $scalings = $this->wpdb->get_results("SELECT * FROM $this->scaledPricingTableName WHERE pricing_tier_id='$id' ORDER BY id ASC");
        }
        // scripts for price tier add/edit
        wp_enqueue_script('wphp-admin-price-tier', $this->plugin_url . 'assets/admin/js/price-tier.js', [], _get_helloprint_version());

        $currency_symbol = get_woocommerce_currency_symbol();
        $action = "edit";
        $action_to_perform = "update";
        require_once "$this->plugin_path/templates/admin/pricing-tiers/add-edit.php";
    }

    public function delete_helloprint_pricing_tier()
    {
        $this->setTable();
        $helloprintProductPricingTable = $this->wpdb->prefix . 'helloprint_product_pricing_tier';

        $del_id = (int)sanitize_text_field(wp_unslash($_GET['id']));
        $this->wpdb->query("DELETE FROM $this->tierTableName WHERE id='$del_id'");
        $this->wpdb->query("DELETE FROM $this->scaledPricingTableName WHERE pricing_tier_id='$del_id'");
        $this->wpdb->query("DELETE FROM $helloprintProductPricingTable WHERE pricing_tier_id='$del_id'");
        add_helloprint_flash_notice(wp_kses(_translate_helloprint("Pricing Tier Deleted Successfully", "helloprint"), false), "success");
        return wp_redirect('admin.php?page=helloprint-pricing-tiers');
    }
}
