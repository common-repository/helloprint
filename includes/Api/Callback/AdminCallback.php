<?php

/** 
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Api\Callback;

use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\HelloPrintApiService;

class AdminCallback extends BaseController
{
    public function dashboard()
    {
        return require_once("$this->plugin_path/templates/admin/dashboard.php");
    }

    public function helloPrintSettingOptionsGroup($input)
    {
        if (empty($input)) {
            return $input;
        }
        $oldvalue = esc_attr(get_option('helloprint_api_key'));
        $response = (new HelloPrintApiService($input))->validateApiKey();
        if (is_wp_error($response)) {
            add_settings_error('api-key-error-msg', 500, wp_kses(_translate_helloprint('Server error! Request couldn\t be completed.', 'helloprint'), false));
            add_settings_error('api-error-wrong-value', 404, $input, 'hidden');
            set_transient('settings_errors', get_settings_errors(), 30);
            return $oldvalue;
        }
        if (wp_remote_retrieve_response_code($response) == '403') {
            add_settings_error('api-key-error-msg', 403, wp_kses(_translate_helloprint('Provided API key is incorrect.', 'helloprint'), false));
            add_settings_error('api-error-wrong-value', 404, $input, 'hidden');
            set_transient('settings_errors', get_settings_errors(), 30);
            return $oldvalue;
        }
        return $input;
    }

    public function checkboxSanitize($input)
    {
        return (isset($input) ? true : false);
    }

    public function selectOptionSanitize($input)
    {
        return trim($input);
    }


    public function OverrideIconFile($input)
    {
        if (!function_exists('wp_handle_upload')) require_once(ABSPATH . 'wp-admin/includes/file.php');
        if (!empty($_FILES['helloprint_override_icon']['name'])) {
            $file_name = sanitize_file_name(wp_unslash($_FILES['helloprint_override_icon']['name']));
            $_FILES['helloprint_override_icon']['name'] = uniqid('loading_icon-') . '.' . pathinfo($file_name)['extension'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload(wp_unslash($_FILES['helloprint_override_icon']), $upload_overrides);
            if ($movefile) {
                if (isset($movefile['error'])) {
                    print_r($movefile);
                    //return new WP_Error('upload_error', $movefile['error']);
                } else {
                    $this->add_or_update_option("helloprint_override_icon_url", $movefile['url']);
                }
            } else {
                echo wp_kses(_translate_helloprint("Possible file upload attack!", "helloprint"), false);
            }
        } else if (empty(sanitize_file_name(wp_unslash($_POST['helloprint_override_icon_old'])))) {
            $this->add_or_update_option("helloprint_override_icon_url", '');
        }
    }

    private function add_or_update_option($option = '', $value = '')
    {

        $oldfile = get_option($option);
        $name = delete_option($option);
        if (!empty($value)) {
            add_option($option, $value);
        }

        if (!empty($oldfile)) {
            $file = str_replace(get_site_url(), '', $oldfile);
            $file_path = get_home_path() . $file;
            if (file_exists($file_path)) {
                wp_delete_file($file_path);
            }
        }
    }

    public function globalProductMarginSanitize($input)
    {
        $input = esc_attr($input);
        if ($input < 0) {
            return add_settings_error('Margin Validation error', 422, wp_kses(_translate_helloprint('Margin should be greater or equal to 0', 'helloprint'), false));
        }
        if ($input > 99) {
            return add_settings_error('Margin Validation error', 422, wp_kses(_translate_helloprint('Margin should be less or equal to 99', 'helloprint'), false));
        }
        return trim($input);
    }

    public function inputNumberSanitize($input)
    {
        return trim($input);
    }

    public function inputSanitize($input)
    {
        return sanitize_text_field(wp_unslash($input));
    }

    public function globalProductMarkupSanitize($input)
    {
        $input = esc_attr($input);
        if ($input < 0) {
            return add_settings_error('Markup Validation error', 422, wp_kses(_translate_helloprint('Markup should be greater or equal to 0', 'helloprint'), false));
        }
        return trim($input);
    }
}
