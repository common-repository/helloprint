<?php

/**
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Base;

use HelloPrint\Inc\Services\TranslationService;
use HelloPrint\Inc\Base\Controllers\BaseController;

class Enqueue extends BaseController
{
    public function register()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueue'));
    }

    public function enqueue()
    {
        $version = $this->getVersion();
        wp_enqueue_style('wphp-wp-styles-filepond-plugin-pdf-preview', $this->plugin_url . 'assets/wp/css/filepond-plugin-pdf-preview.min.css', [], $version);
        wp_enqueue_style('wphp-wp-styles-filepond', $this->plugin_url . 'assets/wp/css/filepond.css', [], $version);
        wp_enqueue_style('wphp-wp-styles-filepond-plugin-image-preview', $this->plugin_url . 'assets/wp/css/filepond-plugin-image-preview.css', [], $version);
        wp_enqueue_style('wphp-wp-styles', $this->plugin_url . 'assets/wp/css/wphp-custom.css', [], $version);
        wp_enqueue_style('wphp-wp-styles-image-uploader', $this->plugin_url . 'assets/wp/css/image-uploader.css', [], $version);
        wp_enqueue_script('wphp-wp-js-image-uploader', $this->plugin_url . 'assets/wp/js/image-uploader.js', array('jquery'), $version);
        wp_enqueue_script('wphp-wp-js-image-uploader', $this->plugin_url . 'assets/wp/js/image-uploader.js', array('jquery'), $version);

        wp_enqueue_script('wphp-wp-js-filepond', $this->plugin_url . 'assets/wp/js/filepond.js', array('jquery'), $version);
        wp_enqueue_script('wphp-wp-js-filepond-plugin-pdf-preview', $this->plugin_url . 'assets/wp/js/filepond-plugin-pdf-preview.min.js', array('jquery'), $version);
        wp_enqueue_script('wphp-wp-js-filepond-plugin-file-validate-type', $this->plugin_url . 'assets/wp/js/filepond-plugin-file-validate-type.js', array('jquery'), $version);
        wp_enqueue_script('wphp-wp-js-filepond-plugin-image-preview', $this->plugin_url . 'assets/wp/js/filepond-plugin-image-preview.js', array('jquery'), $version);
        wp_enqueue_script('wphp-wp-js-filepond-plugin-image-exif-orientation', $this->plugin_url . 'assets/wp/js/filepond-plugin-image-exif-orientation.js', array('jquery'), $version);
        wp_enqueue_script('wphp-wp-js', $this->plugin_url . 'assets/wp/js/wphp-custom.js', array('jquery'), $version);
        wp_localize_script('wphp-wp-js', 'wphp_ajax', array('ajax_url' => admin_url('admin-ajax.php'), "pitchprint_root_url" => \hp_is_pitchprint_plugin_active('root_url')));
        wp_localize_script('wphp-wp-js', 'wphp_file_max_upload_size', ['maxSize' => \helloprint_get_max_file_upload_size()]);
        wp_localize_script('wphp-wp-js', 'wphp_ajax_nonce', ['value' => wp_create_nonce('wphp-plugin-nonce')]);
        wp_localize_script('wphp-wp-js', 'wphp_pitch_print_settings', ['language' => get_locale()]);
        wp_localize_script('wphp-wp-js', 'wphp_ajax_translate', (new TranslationService())->getAllTexts());
    }

    public function adminEnqueue()
    {
        $version = $this->getVersion();
        wp_enqueue_style('helloprint-admin-styles-filepond-plugin-pdf-preview', $this->plugin_url . 'assets/wp/css/filepond-plugin-pdf-preview.min.css', [], $version);
        wp_enqueue_style('helloprint-admin-styles-filepond', $this->plugin_url . 'assets/wp/css/filepond.css', [], $version);
        wp_enqueue_style('helloprint-admin-styles-filepond-plugin-image-preview', $this->plugin_url . 'assets/wp/css/filepond-plugin-image-preview.css', [], $version);

        wp_enqueue_style('helloprint-admin-select2', $this->plugin_url . 'assets/admin/css/select2.css', [], $version);
        wp_enqueue_style('helloprint-admin-styles', $this->plugin_url . 'assets/admin/css/style.css', [], $version);

        wp_enqueue_style('helloprint-admin-styles-image-uploader', $this->plugin_url . 'assets/admin/css/image-uploader.css', [], $version);
        wp_enqueue_style('helloprint-admin-styles-toast-ui', $this->plugin_url . 'assets/admin/css/plugins/tu-editor.min.css', [], $version);


        wp_enqueue_script('helloprint-admin-js-filepond', $this->plugin_url . 'assets/wp/js/filepond.js', array('jquery'), $version);
        wp_enqueue_script('helloprint-admin-js-filepond-plugin-pdf-preview', $this->plugin_url . 'assets/wp/js/filepond-plugin-pdf-preview.min.js', array('jquery'), $version);
        wp_enqueue_script('helloprint-admin-js-filepond-plugin-file-validate-type', $this->plugin_url . 'assets/wp/js/filepond-plugin-file-validate-type.js', array('jquery'), $version);
        wp_enqueue_script('helloprint-admin-js-filepond-plugin-image-preview', $this->plugin_url . 'assets/wp/js/filepond-plugin-image-preview.js', array('jquery'), $version);
        wp_enqueue_script('helloprint-admin-js-filepond-plugin-image-exif-orientation', $this->plugin_url . 'assets/wp/js/filepond-plugin-image-exif-orientation.js', array('jquery'), $version);
        wp_enqueue_script('helloprint-admin-image-uploader-js', $this->plugin_url . 'assets/admin/js/image-uploader.js', [], $version);

        wp_enqueue_script('helloprint-admin-select2js', $this->plugin_url . 'assets/admin/js/select2.min.js', [], $version);
        wp_enqueue_script('helloprint-admin-js', $this->plugin_url . 'assets/admin/js/helloprint-admin.js', [], $version);
        wp_localize_script('helloprint-admin-js', 'helloprint_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_script('helloprint-admin-preset-js', $this->plugin_url . 'assets/admin/js/helloprint-admin-preset.js', [], $version);
        
        wp_enqueue_script('helloprint-admin-js-toastui', $this->plugin_url . 'assets/admin/js/plugins/tu-editor-all.min.js', array('jquery'), $version);
        
        wp_localize_script('helloprint-admin-js', 'helloprint_ajax_translate',(new TranslationService())->getAllTextsForAdmin());
        wp_localize_script('helloprint-admin-js', 'helloprint_file_max_upload_size', ['maxSize' => \helloprint_get_max_file_upload_size()]);
        
        wp_localize_script('helloprint-admin-js', 'helloprint_ajax_nonce', ['value' => wp_create_nonce('wphp-plugin-nonce')]);
    }

    public function getVersion()
    {
        global $wp_version;
        $version = $wp_version;
        $plugin_base_arr = explode('/', plugin_basename(__FILE__));
        if (isset($plugin_base_arr)) {
            $base_name = $plugin_base_arr[0];
            foreach (\get_plugins() as $key => $value) {
                if (strpos($key, $base_name) === 0) {
                    $version = $value['Version'];
                }
            }
        }
        return $version;
    }
}
