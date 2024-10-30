<?php

/** 
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Base\Controllers;

class BaseController
{
    public  $plugin_path;
    public  $plugin_url;
    public  $plugin;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 3));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__, 3));
        $this->plugin = plugin_basename(dirname(__FILE__, 4)) . '/helloprint.php';
    }

    public function returnStatusJson(int $status)
    {
        $return = array(
            'status' => $status
        );
        wp_send_json($return);

        wp_die();
    }
    public function returnJson(array $arary)
    {
        wp_send_json($arary);
        wp_die();
    }
}
