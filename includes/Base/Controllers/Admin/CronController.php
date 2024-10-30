<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\HelloPrintApiService;
use HelloPrint\Inc\Base\Controllers\Admin\OrderController;

class CronController extends BaseController
{
   public function register()
   {
      /*add_action('wp_ajax_helloprint_order_shipping_cron', array($this, 'order_shipping_cron'));
      add_action('helloprint_order_shipping_cron', array($this, 'order_shipping_cron'));

      if (!wp_next_scheduled('helloprint_order_shipping_cron')) {
         wp_schedule_event(time(), 'hourly', 'helloprint_order_shipping_cron');
      }*/
   }

   public function order_shipping_cron()
   {
      return true;
      // The query arguments
      $args = array(
         // WC orders post type
         'post_type'   => 'shop_order',
         // Only orders with status "processing" (others common status: 'wc-completed' or 'wc-processing')
         'post_status' => 'wc-processing',
         // all posts
         'numberposts' => -1,
      );

      // Get all incompleted orders
      $all_orders = get_posts($args);
      if (!empty($all_orders)) {
         // Going through each orders
         foreach ($all_orders as $order) {
            $orderDetails = wc_get_order($order->ID);
            $this->process_single_order($orderDetails);
         }
      }
   }

   private function process_single_order($order)
   {

      $helloprint_order = false;
      foreach ($order->get_items() as $item) {

         $product = wc_get_product($item['product_id']);

         if (!empty($product) && $product->get_type() == 'helloprint_product') {
            $helloprint_order = true;
            break;
         }
      }

      if ($helloprint_order == true) {
         //print_r("helloprint order" . $order->ID);die();
         $order_status = $order->get_meta('helloprint_order_status', true);
         if (isset($order_status['request_id']) && !empty($order_status['request_id'])) {
            $helloPrintService = new HelloPrintApiService();
            $helloprint_order_details = $helloPrintService->getOrderDetails($order_status['request_id']);
            // for testing the shipping this can be removed after testing starts
            $helloprint_order_details['data']['orderStatus'] = 'SHIPPED';
            $helloprint_order_details['data']['orderItems'][0]['trackingUrls'] = ['https://www.ups.com?parcel=ABC'];
            $helloprint_order_details['data']['orderItems'][0]['itemStatus'] = 'SHIPPED';

            // for testing the shipping this can be removed after testing ends
            if (isset($helloprint_order_details['data']['orderStatus']) && $helloprint_order_details['data']['orderStatus'] == 'SHIPPED') {
               $orderController = new OrderController();
               $orderController->update_order_after_shipped($order, $helloprint_order_details, $order_status['request_id']);
            }
         }
      }
   }
}
