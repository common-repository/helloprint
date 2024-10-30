<?php

/**
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Pages;

use HelloPrint\Inc\Api\SettingApi;
use HelloPrint\Inc\Api\Callback\AdminCallback;
use HelloPrint\Inc\Api\Callback\ManagerCallback;
use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Base\Controllers\Admin\LanguageTranslatorController;
use HelloPrint\Inc\Base\Controllers\Admin\BulkProductController;
use HelloPrint\Inc\Base\Controllers\Admin\OrderPresetController;
use HelloPrint\Inc\Base\Controllers\Admin\PitchPrintController;
use HelloPrint\Inc\Base\Controllers\Admin\PricingTierController;

class Dashboard extends BaseController
{
    public $setting;
    public $callbacks;
    public $translator;
    public $tiers;
    public $manager;
    public $pages = array();
    public $subPages = array();
    public $bulkProduct;
    public $orderPreset;
    public $pitchPrint;

    public function register()
    {
        $this->setting = new SettingApi();
        $this->callbacks = new AdminCallback();
        $this->translator = new LanguageTranslatorController();
        $this->manager = new ManagerCallback();
        $this->bulkProduct = new BulkProductController;
        $this->orderPreset = new OrderPresetController;
        $this->pitchPrint = new PitchPrintController;
        $this->tiers = new PricingTierController;
        $this->setPages();
        $this->setSubPages();
        $this->setSettings();
        $this->setSections();
        $this->setFields();
        $this->setting->addPages($this->pages)
            ->withSubPage(_translate_helloprint('Settings', 'helloprint'))
            ->addsubPages($this->subPages)->register();
    }

    public function setPages()
    {
        $this->pages = [
            [
                'page_title' => _translate_helloprint('HelloPrint', 'helloprint'),
                'menu_title' => _translate_helloprint('HelloPrint', 'helloprint'),
                'capability' => 'manage_options',
                'menu_slug' => 'helloprint',
                'callback' => array($this->callbacks, 'dashboard'),
                'icon_url' => 'dashicons-printer',
                'position' => 25
            ]
        ];
    }
    public function setSubPages()
    {

        $this->subPages = [

            [
                'parent_slug' => 'helloprint',
                'page_title' => wp_kses(_translate_helloprint('Translations', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Translations', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'language-translate.php',
                'callback' => array($this->translator, 'helloprint_language_translator'),
            ],
            [
                'parent_slug' => 'helloprint',
                'page_title' => wp_kses(_translate_helloprint('New Translation', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('New Translation', 'helloprint'), false),
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
            [
                'parent_slug' => 'helloprint',
                'page_title' => wp_kses(_translate_helloprint('Bulk Add Products', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Bulk Add Products', 'helloprint'), false),
                'capability' => 'edit_products',
                'menu_slug' => 'add-bulk-products.php',
                'callback' => array($this->bulkProduct, 'helloprint_bulk_product_lists')
            ],
            [
                'parent_slug' => 'helloprint',
                'page_title' => wp_kses(_translate_helloprint('Order Presets', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Order Presets', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'helloprint-order-presets',
                'callback' => array($this->orderPreset, 'helloprint_order_presets'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('New Order Preset', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('New Order Preset', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'add-order-preset',
                'callback' => array($this->orderPreset, 'new_helloprint_order_preset'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('Edit Order Preset', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Edit Order Preset', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'edit-order-preset',
                'callback' => array($this->orderPreset, 'edit_helloprint_order_preset'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('Delete Order Preset', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Delete Order Preset', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'delete-helloprint-order-preset',
                'callback' => array($this->orderPreset, 'delete_helloprint_order_preset'),
            ],
            [
                'parent_slug' => 'helloprint',
                'page_title' => wp_kses(_translate_helloprint('Pricing Tiers', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Pricing Tiers', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'helloprint-pricing-tiers',
                'callback' => array($this->tiers, 'helloprint_pricing_tiers'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('New Pricing Tier', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('New Pricing Tier', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'add-hp-pricing-tier',
                'callback' => array($this->tiers, 'new_helloprint_pricing_tier'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('Edit Pricing Tier', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Edit Pricing Tier', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'edit-hp-pricing-tier',
                'callback' => array($this->tiers, 'edit_helloprint_pricing_tier'),
            ],
            [
                'parent_slug' => null,
                'page_title' => wp_kses(_translate_helloprint('Delete Pricing Tier', 'helloprint'), false),
                'menu_title' => wp_kses(_translate_helloprint('Delete Pricing Tier', 'helloprint'), false),
                'capability' => 'manage_options',
                'menu_slug' => 'delete-helloprint-pricing-tier',
                'callback' => array($this->tiers, 'delete_helloprint_pricing_tier'),
            ],
        ];
        if ($this->pitchPrint->is_plugin_activated()) {
            $this->subPages[] = [
                'parent_slug' => 'helloprint',
                'page_title' => _translate_helloprint('Pitchprint', 'helloprint'),
                'menu_title' => _translate_helloprint('Pitchprint', 'helloprint'),
                'capability' => 'manage_options',
                'menu_slug' => 'hp-pitch-print',
                'callback' => array($this->pitchPrint, 'all_pitch_print_links'),
                'position' => 111
            ];
            $this->subPages[] = [
                'parent_slug' => 'hp-pitch-print',
                'page_title' => _translate_helloprint('New Pitchprint', 'helloprint'),
                'menu_title' => _translate_helloprint('New', 'helloprint'),
                'capability' => 'manage_options',
                'menu_slug' => 'new-hp-pitch-print',
                'callback' => array($this->pitchPrint, 'new_pitch_print_form'),
            ];
            $this->subPages[] = [
                'parent_slug' => null,
                'page_title' => _translate_helloprint('Edit Pitchprint', 'helloprint'),
                'menu_title' => _translate_helloprint('Edit', 'helloprint'),
                'capability' => 'manage_options',
                'menu_slug' => 'edit-hp-pitch-print',
                'callback' => array($this->pitchPrint, 'edit_pitch_print_form'),
            ];
            $this->subPages[] = [
                'parent_slug' => null,
                'page_title' => 'delete_helloprint_language_translator',
                'menu_title' => _translate_helloprint('Delete', 'helloprint'),
                'capability' => 'manage_options',
                'menu_slug' => 'delete-new-hp-pitch-print',
                'callback' => array($this->pitchPrint, 'delete_pitch_print'),
            ];
        }
    }

    public function setSettings()
    {
        $args = [
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_api_key',
                'callback' => array($this->callbacks, 'helloPrintSettingOptionsGroup')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_env_mode',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_switch_icon',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_switch_color_icon',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_switch_print_position_icon',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_show_prices_incl_vat_only',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_product_upload_file',
                'callback' => array($this->callbacks, 'selectOptionSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_override_icon',
                'callback' => array($this->callbacks, 'OverrideIconFile')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_global_product_margin',
                'callback' => array($this->callbacks, 'globalProductMarginSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_global_product_markup',
                'callback' => array($this->callbacks, 'globalProductMarkupSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_enable_global_graphic_design',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_global_graphic_design_price',
                'callback' => array($this->callbacks, 'inputNumberSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_order_prefix',
                'callback' => array($this->callbacks, 'inputSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_automatic_send_order',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_quantity_pricing_limit',
                'callback' => array($this->callbacks, 'inputNumberSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_shipping_email',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_delivery_buffer_days',
                'callback' => array($this->callbacks, 'inputNumberSanitize')
            ],
            [
                'option_group' => 'helloprint_setting_options_group',
                'option_name' => 'helloprint_disable_hp_order_change_status_email',
                'callback' => array($this->callbacks, 'checkboxSanitize')
            ],
            

        ];
        $this->setting->setSettings($args);
    }

    public function setSections()
    {
        $args = [
            [
                'id' => 'setting_helloprint_api_key_section',
                'title' => '',
                'callback' => array($this->manager, 'settingSectionManager'),
                'page' => 'helloprint'
            ],
        ];
        $this->setting->setSections($args);
    }

    public function setFields()
    {
        $args = [
            [
                'id' => 'helloprint_api_key',
                'title' => wp_kses(_translate_helloprint('Api Key', 'helloprint'), false),
                'callback' => array($this->manager, 'textField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_api_key',
                    'label_for' => 'helloprint_api_key',
                    'class' => 'example-class'
                )
            ],
            [
                'id' => 'helloprint_env_mode',
                'title' => wp_kses(_translate_helloprint('Test Mode', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_env_mode',
                    'label_for' => 'helloprint_env_mode'
                )
            ],
            [
                'id' => 'helloprint_switch_icon',
                'title' => wp_kses(_translate_helloprint('Show Size Images', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_switch_icon',
                    'label_for' => 'helloprint_switch_icon'
                )
            ],
            [
                'id' => 'helloprint_switch_color_icon',
                'title' => wp_kses(_translate_helloprint('Show Color Images', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_switch_color_icon',
                    'label_for' => 'helloprint_switch_color_icon'
                )
            ],
            [
                'id' => 'helloprint_switch_print_position_icon',
                'title' => wp_kses(_translate_helloprint('Show Print Position', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_switch_print_position_icon',
                    'label_for' => 'helloprint_switch_print_position_icon'
                )
            ],
            [
                'id' => 'helloprint_show_prices_incl_vat_only',
                'title' => wp_kses(_translate_helloprint('Show Prices Incl Vat Only', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_show_prices_incl_vat_only',
                    'label_for' => 'helloprint_show_prices_incl_vat_only',
                    'class' => 'wphp-has-tooltip',
                    'title' => esc_attr(_translate_helloprint('WooCommerce settings will override HelloPrint tax settings', 'helloprint')),
                    'data_title' => esc_attr(_translate_helloprint('WooCommerce settings will override HelloPrint tax settings', 'helloprint'))
                ),
            ],
            [
                'id' => 'helloprint_product_upload_file',
                'title' => wp_kses(_translate_helloprint('Product Upload File', 'helloprint'), false),
                'callback' => array($this->manager, 'selectOption'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_product_upload_file',
                    'label_for' => 'helloprint_product_upload_file',
                    'option_values' => [
                        'show_on_both_pages' => wp_kses(_translate_helloprint("Show upload on product and cart", 'helloprint'), false),
                        'show_on_cart_only' => wp_kses(_translate_helloprint("Show upload on cart only", 'helloprint'), false),
                        'show_on_product_only' => wp_kses(_translate_helloprint("Show upload on product only", 'helloprint'), false),
                        'no_option' => wp_kses(_translate_helloprint("No option for uploading", 'helloprint'), false)
                    ],
                    'default' => 'show_on_both_pages'
                )
            ],
            [
                'id' => 'helloprint_override_icon',
                'title' => wp_kses(_translate_helloprint('Loading Icon (85x85px)', 'helloprint'), false),
                'callback' => array($this->manager, 'fileInput'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_override_icon',
                    'label_for' => 'helloprint_override_icon',
                    'onchange' => 'validateHelloPrintOverrideImageFile',
                    'accepts' => '.png, .PNG, .jpg, .JPG, .jpeg, .JPEG, .gif, .GIF',
                    'invalid_message' => wp_kses(_translate_helloprint('Only gif, jpg/jpeg and gif images are allowed', 'helloprint'), false)
                )
            ],
            [
                'id' => 'helloprint_global_product_margin',
                'title' => wp_kses(_translate_helloprint('Global Margin %', 'helloprint'), false),
                'callback' => array($this->manager, 'numberField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_global_product_margin',
                    'label_for' => 'helloprint_global_product_margin',
                    'step' => '1',
                    'min' => '0',
                    'max' => '99',
                )
            ],
            [
                'id' => 'helloprint_global_product_markup',
                'title' => wp_kses(_translate_helloprint('Global Markup %', 'helloprint'), false),
                'callback' => array($this->manager, 'numberField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_global_product_markup',
                    'label_for' => 'helloprint_global_product_markup',
                    'step' => '1',
                    'min' => '0',
                    'max' => '',
                )
            ],
            [
                'id' => 'helloprint_enable_global_graphic_design',
                'title' => wp_kses(_translate_helloprint('Enable Graphic Design', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_enable_global_graphic_design',
                    'label_for' => 'helloprint_enable_global_graphic_design'
                )
            ],
            [
                'id' => 'helloprint_global_graphic_design_price',
                'title' => wp_kses(_translate_helloprint('Global Design Fee', 'helloprint'), false),
                'callback' => array($this->manager, 'numberField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_global_graphic_design_price',
                    'label_for' => 'helloprint_global_graphic_design_price',
                    'default' => '0',
                    'step' => '1',
                    'min' => '0',
                    'max' => '99',
                )
            ],
            [
                'id' => 'helloprint_order_prefix',
                'title' => wp_kses(_translate_helloprint('Order Prefix', 'helloprint'), false),
                'callback' => array($this->manager, 'textField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_order_prefix',
                    'label_for' => 'helloprint_order_prefix',
                    'class' => 'example-class',
                    "default" => "wp-"
                )
            ],
            [
                'id' => 'helloprint_automatic_send_order',
                'title' => wp_kses(_translate_helloprint('Enable Automatic HP Order', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_automatic_send_order',
                    'label_for' => 'helloprint_automatic_send_order'
                )

            ],

            [
                'id' => 'helloprint_quantity_pricing_limit',
                'title' => wp_kses(_translate_helloprint('Pricing Limit on Products', 'helloprint'), false),
                'callback' => array($this->manager, 'numberField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_quantity_pricing_limit',
                    'label_for' => 'helloprint_quantity_pricing_limit',
                    'class' => 'example-class',
                    "default" => "14",
                    "min" => 2
                )
            ],
            [
                'id' => 'helloprint_shipping_email',
                'title' => wp_kses(_translate_helloprint('Disable sending shipping address email to Helloprint', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_shipping_email',
                    'label_for' => 'helloprint_shipping_email'
                )
            ],

            [
                'id' => 'helloprint_delivery_buffer_days',
                'title' => wp_kses(_translate_helloprint('Delivery Buffer Days', 'helloprint'), false),
                'callback' => array($this->manager, 'numberField'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_delivery_buffer_days',
                    'label_for' => 'helloprint_delivery_buffer_days',
                    'class' => 'example-class',
                    "default" => "0",
                    "min" => 0
                )
            ],
            [
                'id' => 'helloprint_disable_hp_order_change_status_email',
                'title' => wp_kses(_translate_helloprint('Disable email on HP Order status change', 'helloprint'), false),
                'callback' => array($this->manager, 'checkbox'),
                'page' => 'helloprint',
                'section' => 'setting_helloprint_api_key_section',
                'args' => array(
                    'option_name' => 'helloprint_disable_hp_order_change_status_email',
                    'label_for' => 'helloprint_disable_hp_order_change_status_email'
                )
            ],
        ];
        $this->setting->setFields($args);
    }
}
