<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Exception;
use HelloPrint\Inc\Base\Controllers\BaseController;

class LanguageTranslatorController extends BaseController
{
    private $tableName = '';
    private $wpdb;

    private function setTable()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $this->wpdb->prefix . 'helloprint_translations';
    }

    public function helloprint_language_translator()
    {
        $s = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : "";

        $post_per_page = 20;
        $pagenum = isset($_GET['paged']) ? (int)sanitize_text_field(wp_unslash($_GET['paged'])) : 1;

        $this->setTable();
        $query = "SELECT id,string,translation from $this->tableName";
        if (!empty($s)) {
            $query .= " WHERE ((`string` like '%" . $s . "%') OR (`translation` like '%" . $s . "%')) ";
        }

        //Get total number of results
        $results = $this->wpdb->get_results($query);
        $totals = $this->wpdb->num_rows;

        $page = ($pagenum - 1);
        $query .= " ORDER BY id DESC LIMIT $post_per_page OFFSET " . $page * $post_per_page;
        $translations = $this->wpdb->get_results($query);

        $num_of_pages = ceil($totals / $post_per_page);
        $page_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;', 'aag'),
            'next_text' => __('&raquo;', 'aag'),
            'total' => $num_of_pages,
            'current' => $pagenum
        ));

        require_once "$this->plugin_path/templates/admin/translator/lists.php";
    }

    public function new_helloprint_language_translator()
    {
        if (isset($_POST['action'])) {
            $this->setTable();
            $string = sanitize_text_field(wp_unslash($_POST['string']));
            $translation = implode( "\n", array_map( 'sanitize_textarea_field', explode( "\n", $_POST['translation'])));
            $translation = str_replace(array("\r\n", "\r", "\n", "\\n"), "<br/>", $translation);
            $this->wpdb->query("INSERT INTO $this->tableName(string,translation) VALUES('$string','$translation')");

            return wp_redirect('admin.php?page=language-translate.php&success=added');
        }

        require_once "$this->plugin_path/templates/admin/translator/add.php";
    }

    public function edit_helloprint_language_translator()
    {
        $this->setTable();
        if (isset($_POST['action']) && isset($_POST['id'])) {
            $this->setTable();
            $id = sanitize_text_field(wp_unslash($_POST['id']));
            $string = sanitize_text_field(wp_unslash($_POST['string']));
            $translation = implode( "\n", array_map( 'sanitize_textarea_field', explode( "\n", $_POST['translation'])));
            $translation = str_replace(array("\r\n", "\r", "\n", "\\n"), "<br />", $translation);
            $this->wpdb->query("UPDATE $this->tableName SET string='$string',translation='$translation' WHERE id='$id'");
            return wp_redirect('admin.php?page=language-translate.php&success=updated');
        }

        $id = absint($_GET['id']);
        $string = '';
        $translation = '';
        $result = $this->wpdb->get_results("SELECT * FROM $this->tableName WHERE id='$id'");
        foreach ($result as $print) {
            $string = $print->string;
            $translation = $print->translation;
        }

        require_once "$this->plugin_path/templates/admin/translator/edit.php";
    }

    public function delete_helloprint_language_translator()
    {
        $this->setTable();
        $del_id = (int)sanitize_text_field(wp_unslash($_GET['id']));
        $this->wpdb->query("DELETE FROM $this->tableName WHERE id='$del_id'");
        return wp_redirect('admin.php?page=language-translate.php&success=deleted');
    }
}
