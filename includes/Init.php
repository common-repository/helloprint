<?php

/**
 * @package HelloPrint 
 *          
 */

namespace HelloPrint\Inc;

final class Init
{
    public static function get_services()
    {
        return [
            Base\Enqueue::class,
            Base\SettingLink::class,
            Base\Notice::class,
            Pages\Dashboard::class,
            Base\Controllers\Admin\ProductController::class,
            Base\Controllers\Admin\CartController::class,
            Base\Controllers\Admin\CheckoutController::class,
            Base\Controllers\Admin\FileUploadController::class,
            Base\Controllers\Admin\CartFileUploadController::class,
            Base\Controllers\Admin\OrderController::class,
            Base\Controllers\Admin\LanguageTranslatorController::class,
            Base\Controllers\Admin\CronController::class,
            Base\Controllers\Admin\BulkProductController::class,
            Base\Controllers\Admin\OrderPresetController::class,
        ];
    }

    /**
     * Loop throught the classes, initialze them, 
     * and call the register() method if it exists
     * @return 
     */
    public static function register_services()
    {
        foreach (self::get_services() as $class) {
            $service = self::instantiate($class);
            if (method_exists($service, 'register')) {
                $service->register();
            }
        }
    }

    /**
     * Initialize the class
     * @param class $class class from the services array
     * @return class instance new instance of the class
     */
    private static function instantiate($class)
    {
        return new $class();
    }
}
