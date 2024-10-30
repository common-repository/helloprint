<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Exception;
use HelloPrint\Inc\Base\Controllers\BaseController;

class PitchPrintController extends BaseController
{
    private $tableName = '';
    private $wpdb;

    private function setTable()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $this->wpdb->prefix . 'helloprint_pitch_prints';
    }

    public function all_pitch_print_links()
    {
        $s = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : "";

        $post_per_page = 20;
        $pagenum = isset($_GET['paged']) ? (int)sanitize_text_field(wp_unslash($_GET['paged'])) : 1;

        $this->setTable();
        $query = "SELECT id,name,pitchprint_design_id,hp_variant_key from $this->tableName";
        if (!empty($s)) {
            $query .= " WHERE ((`name` like '%" . $s . "%') OR (`pitchprint_design_id` like '%" . $s . "%') OR (`hp_variant_key` like '%" . $s . "%')) ";
        }

        //Get total number of results
        $results = $this->wpdb->get_results($query);
        $totals = $this->wpdb->num_rows;

        $page = ($pagenum - 1);
        $query .= " ORDER BY id DESC LIMIT $post_per_page OFFSET " . $page * $post_per_page;
        $pitchprints = $this->wpdb->get_results($query);

        $num_of_pages = ceil($totals / $post_per_page);
        $page_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;', 'aag'),
            'next_text' => __('&raquo;', 'aag'),
            'total' => $num_of_pages,
            'current' => $pagenum
        ));

        require_once "$this->plugin_path/templates/admin/pitch-print/all-pitch-prints.php";
    }

    public function new_pitch_print_form()
    {
        if (isset($_POST['action'])) {
            $this->setTable();
            $name = sanitize_text_field(wp_unslash($_POST['name']));
            $design_id = sanitize_text_field(wp_unslash($_POST['design_id']));
            $variant_key = sanitize_text_field(wp_unslash($_POST['variant_key']));
            $this->wpdb->query("INSERT INTO $this->tableName(name,pitchprint_design_id,hp_variant_key) VALUES('$name','$design_id','$variant_key')");
            add_helloprint_flash_notice(esc_attr(_translate_helloprint("New Pitchprint Added Successfully")), "success");
            return wp_redirect('admin.php?page=hp-pitch-print');
        }

        require_once "$this->plugin_path/templates/admin/pitch-print/new-pitch-print.php";
    }

    public function edit_pitch_print_form()
    {
        $this->setTable();
        if (isset($_POST['action']) && isset($_POST['id'])) {
            $id = sanitize_text_field(wp_unslash($_POST['id']));
            $name = sanitize_text_field(wp_unslash($_POST['name']));
            $design_id = sanitize_text_field(wp_unslash($_POST['design_id']));
            $variant_key = sanitize_text_field(wp_unslash($_POST['variant_key']));
            $this->wpdb->query("UPDATE $this->tableName SET name='$name',pitchprint_design_id='$design_id',hp_variant_key='$variant_key' WHERE id='$id'");
            add_helloprint_flash_notice(esc_attr(_translate_helloprint("Pitchprint Updated Successfully")), "success");
            return wp_redirect('admin.php?page=hp-pitch-print');
        }

        $id = absint($_GET['id']);
        $name = '';
        $design_id = '';
        $variant_key = '';
        $result = $this->wpdb->get_results("SELECT * FROM $this->tableName WHERE id='$id'");
        foreach ($result as $print) {
            $name = $print->name;
            $design_id = $print->pitchprint_design_id;
            $variant_key = $print->hp_variant_key;
        }

        require_once "$this->plugin_path/templates/admin/pitch-print/edit-pitch-print.php";
    }

    public function delete_pitch_print()
    {
        $this->setTable();
        $del_id = (int)sanitize_text_field(wp_unslash($_GET['id']));
        $this->wpdb->query("DELETE FROM $this->tableName WHERE id='$del_id'");
        add_helloprint_flash_notice(esc_attr(_translate_helloprint("Pitchprint deleted Successfully")), "success");
        return wp_redirect('admin.php?page=hp-pitch-print');
    }

    public function is_plugin_activated()
    {
        require_once $this->plugin_path . "includes/Base/Functions.php";
        return \hp_is_pitchprint_plugin_active();
    }
}
