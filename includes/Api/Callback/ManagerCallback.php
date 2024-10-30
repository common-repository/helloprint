<?php

/** 
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Api\Callback;

use HelloPrint\Inc\Base\Controllers\BaseController;

class ManagerCallback extends BaseController
{

    public function settingSectionManager()
    {
        echo '<p>' . wp_kses(_translate_helloprint('Manage your settings here', 'helloprint'), true) . '</p>';
    }

    public function textField($args)
    {
        $name = $args['label_for'];
        $option_name = $args['option_name'];
        $classes = $args['class'];
        $value = esc_attr(get_option($option_name));
        if (empty($value)) {
            if (!empty($args["default"])) {
                $value = $args["default"];
            }
        }
        $extraMessage = '';

        if ($option_name == 'helloprint_api_key') {
            $errorMessageArr = get_settings_errors('api-key-error-msg');
            if (!empty($errorMessageArr[0]['message'])) {
                $extraMessage .= "<br/><label class='text-danger'>" . $errorMessageArr[0]['message'] . '</label>';
            }
            if (!empty($errorMessageArr[0]['message']) || empty($value)) {
                $extraMessage .= "<br/> <label class='text-info'>" . wp_kses(_translate_helloprint("Send us an email at ", "helloprint"), true). " <a href='mailto:api@helloprint.com'>api@helloprint.com</a> ". wp_kses(_translate_helloprint('get your api key', 'helloprint'), true) . "</label>";
            }
            if (!empty(get_settings_errors('api-error-wrong-value')[0]['message'])) {
                $value = get_settings_errors('api-error-wrong-value')[0]['message'];
            }
        }
        echo '<div class = "' . esc_attr($classes) . '"><input type="text" class="regular-text" id="' . esc_attr($name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '">' . wp_kses($extraMessage, true) . '</div>';
    }

    public function checkbox($args)
    {
        $name = $args['label_for'];
        $option_name = $args['option_name'];
        $classes = $args['class'] ?? '';
        $tooltip_title = $args['data_title'] ?? '';
        $value = esc_attr(get_option($option_name));
        echo '<div  class = "' . esc_attr($classes) . '"><input type="checkbox" class="regular-text" id="' . esc_attr($name) . '" name="' . esc_attr($option_name) . '"' . ($value ? 'checked' : '') . '>';
        if (!empty($tooltip_title)) {
            echo '<div class="wphp-tooltip-text aa"><span class="dashicons dashicons-info"></span>
                    <span class="wphp-tooltiptext">' . esc_html($tooltip_title) .'</span>
                    </div>';
        }
        echo '</div>';
    }
    public function selectOption($args)
    {
        $name = $args['label_for'];
        $option_name = $args['option_name'];
        $option_values = $args['option_values'];
        $default = !empty($args['default']) ? $args['default'] : '';
        $classes = $args['class'] ?? '';
        $value = esc_attr(get_option($option_name));
        if (empty($value) && !empty($default)) {
            $value = $default;
        }
        echo '<div class = "' . esc_attr($classes) . '">';
        echo '<select id="' . esc_attr($name) . '" class="select short wphp-select2" name="' . esc_attr($option_name) . '">';
        foreach ($option_values as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ';
            if ($key === $value) {
                echo ' selected="selected" ';
            }
            echo '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
    }

    public function fileInput($args)
    {
        $name = $args['label_for'];
        $option_name = $args['option_name'];
        $url = get_option($option_name . "_url");
        $classes = $args['class'] ?? '';
        $accepts = $args['accepts'] ?? '/*';
        $invalidMessaage = $args['invalid_message'] ?? '';
        $onchange = !empty($args['onchange']) ? ' onchange="' . $args['onchange'] . '()" ' : '';
        $value = esc_attr(get_option($option_name));
        echo '<div class = "' . esc_attr($classes) . '"><input type="file" class="regular-text" id="' . esc_attr($name) . '" name="' . esc_attr($option_name) . '" ' . esc_attr($onchange) . ' ></div>';
        if (!empty($url)) {
            echo '<div class="file-div">';
            echo '<input type="hidden" id="helloprint_hidden_invalid_file_message" value="' . esc_attr($invalidMessaage) . '"/>';
            echo '<img style="max-height:100px;" src="' . esc_url($url) . '" /> <br/>';
            echo '<input accept="' . esc_attr($accepts) . '" type="hidden" name="' . esc_attr($option_name) . '_old" value="' . esc_attr($url) . '" />';
            echo '<a  class="wphp-setting-remove-file" href="#">Remove </a>';
            echo '</div>';
        }
    }

    public function numberField($args)
    {
        $name = $args['label_for'];
        $option_name = $args['option_name'];
        $classes = isset($args['class']) ? $args['class'] : '';
        $min = isset($args['min']) ? $args['min'] : '';
        $max = isset($args['max']) ? $args['max'] : '';
        $step = isset($args['step']) ? $args['step'] : '';
        $width = isset($args['width']) ? $args['width'] : '';
        $value = esc_attr(get_option($option_name));
        if (empty($value)) {
            if (!empty($args["default"])) {
                $value = $args["default"];
            } else if (isset($args["default"]) && $args["default"] === '0') {
                $value = 0;
            }
        }
        echo '<div class = "' . esc_attr($classes) . '"><input type="number" class="regular-text" id="' . esc_attr($name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" step="' . esc_attr($step) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" width="' . esc_attr($width) . '"></div>';
    }
}
