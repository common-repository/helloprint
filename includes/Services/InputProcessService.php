<?php

namespace HelloPrint\Inc\Services;

class InputProcessService
{
    public function process_product_external_key($data)
    {
        return sanitize_key($data);
    }

    public function process_quantity($data)
    {
        $new_data = sanitize_text_field($data);
        if (!is_numeric($new_data)) {
            return 0;
        }
        return $data;
    }

    public function process_service_level($data)
    {
        return sanitize_text_field($data);
    }

    public function process_product_setup_json($data)
    {
        $decodedJson = json_decode(stripslashes($data), true);

        if (!is_array($decodedJson)) {
            return array();
        }
        
        $setup = array();
        foreach ($decodedJson as $key => $value) {
                $setup[sanitize_key($key)] = sanitize_text_field($value);
        }

        return $decodedJson;
    }
}
