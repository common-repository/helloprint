<?php

/**
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Pages;

use HelloPrint\Inc\Api\SettingApi;
use HelloPrint\Inc\Base\Controllers\BaseController;

use HelloPrint\Inc\Base\Controllers\Admin\LanguageTranslatorController;

class LanguageTranslator extends BaseController
{
    public $setting;
    public $translator;
    public $pages = array();
    public $subPages = array();
    
    public function register()
    {
        $this->setting = new SettingApi();
        $this->translator = new LanguageTranslatorController();
        $this->setPages();
        $this->setSubPages();
        $this->setting->addPages($this->pages)
            ->withSubPage(wp_kses(_translate_helloprint('Lists', 'helloprint'), false))
            ->addsubPages($this->subPages)->register();
    }

    public function setPages()
    {
        $this->pages = [
            [
                'page_title' => wp_kses(_translate_helloprint('Language Translations', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Language Translations', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'language-translate.php',
                'callback' => array($this->translator, 'helloprint_language_translator'),
                'icon_url' => 'dashicons-translation',
                'position' => 111
            ]
        ];
    }
    public function setSubPages()
    {

        $this->subPages = [
            [
                'parent_slug' => 'language-translate.php',
                'page_title' => wp_kses(_translate_helloprint('New Translation', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('New', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'add-language-translation.php',
                'callback' => array($this->translator, 'new_helloprint_language_translator'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('Edit Translation', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Edit', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'edit-language-translation.php',
                'callback' => array($this->translator, 'edit_helloprint_language_translator'),
            ],
            [
                'parent_slug' => null,
                'page_title' => 'delete_helloprint_language_translator',
                'menu_title' => wp_kses(_translate_helloprint('Delete', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'delete-language-translation.php',
                'callback' => array($this->translator, 'delete_helloprint_language_translator'),
            ],
        ];
    }
}
