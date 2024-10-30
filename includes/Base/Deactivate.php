<?php

/**
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Base;

class Deactivate
{
    public static function deactivate()
    {
        //self::helloprint_remove_translation_db();
        flush_rewrite_rules();
    }

    public function helloprint_remove_translation_db()
    {
        global $wpdb;

        $translationsTable = $wpdb->prefix . 'helloprint_translations';
        if ($wpdb->get_var("show tables like '$translationsTable'") == $translationsTable) {
            $sql = "DROP TABLE IF EXISTS $translationsTable";
            $wpdb->query($sql);
        }
    }
}
