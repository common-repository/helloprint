<?php

/**
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Base;

use HelloPrint\Inc\Base\Controllers\BaseController;

class Notice extends BaseController
{

    public function register()
    {
        add_action('admin_notices', array($this, 'wphp_apikey_admin_notice'));
        add_action('admin_notices', array($this, 'display_helloprint_flash_notices'));

        add_action('admin_notices', array($this, 'display_helloprint_disable_cron_notices'));
        add_action('admin_notices', array($this, 'display_helloprint_folder_permissions_notices'));
    }

    public function wphp_apikey_admin_notice()
    {
        if (!empty(get_option('helloprint_api_key', true))) {
            return true;
        }

        load_template("$this->plugin_path/templates/admin/apikey-notice.php", true);
    }

    public function display_helloprint_flash_notices()
    {
        $notices = get_option("helloprint_flash_notices", array());

        // Iterate through our notices to be displayed and print them.
        $iteration = 1;
        foreach ($notices as $notice) {

            printf(
                '<div id="notice-%1s-setting_notice_%4$s" class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                esc_attr($notice['type']),
                esc_attr($notice['dismissible']),
                esc_attr($notice['notice']),
                $iteration
            );
            $iteration++;
        }

        // Now we reset our options to prevent notices being displayed forever.
        if (!empty($notices)) {
            delete_option("helloprint_flash_notices", array());
        }
    }

    public function display_helloprint_disable_cron_notices()
    {
        if (defined('DISABLE_WP_CRON') && (DISABLE_WP_CRON == true)) {
            printf(
                '<div id="notice-%1s-setting_notice_%4$s" class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                'error',
                false,
                esc_html(_translate_helloprint('DISABLE_WP_CRON config setting set to true, please change to false otherwise some features may not work as expected', 'helloprint')),
                1
            );
        }
        
    }

    public function display_helloprint_folder_permissions_notices()
    {
        $upload_dir = wp_upload_dir();
        $permissions = fileperms($upload_dir['path']);
        $perm_value = sprintf("%o", $permissions);
        $perm = substr($perm_value, -3);
        if ($perm != '755' && $perm != '777' && $perm != '775') {
            printf(
                '<div id="notice-%1s-setting_notice_%4$s" class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                'error',
                false,
                esc_html(_translate_helloprint('Uploads folder permissions are ' . $perm . ' please change to 755 otherwise some features may not work as expected', 'helloprint')),
                1
            );
        }
    }

}
