<?php

/**
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Base;

use HelloPrint\Inc\Base\Controllers\BaseController;

class SettingLink extends BaseController
{

    public function register()
    {
        add_filter("plugin_action_links_$this->plugin", array($this, 'settings_link'));
    }

    public function settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=helloprint">' . wp_kses(_translate_helloprint('Settings', 'helloprint'), true) . '</a>';
        array_push($links, $settings_link);
        return $links;
    }
}
