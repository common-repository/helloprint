<?php

namespace HelloPrint\Inc\Base\Controllers\Admin;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use HelloPrint\Inc\Services\ProductService;
use HelloPrint\Inc\Services\FileUploadService;
use HelloPrint\Inc\Base\Controllers\BaseController;
use HelloPrint\Inc\Services\HelloPrintApiService;
use HelloPrint\Inc\Services\InputProcessService;


class OrderController extends BaseController
{
    public function register()
    {
        //add_action('admin_post_nopriv_complete_order_from_helloprint_callback', array($this, 'update_order_status'), 10);
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'add_order_status_to_order_notes'));

        /*add_action('woocommerce_process_shop_order_meta', array($this, 'update_order_item_presets'), 10, 2);*/

        add_action('woocommerce_after_order_itemmeta', array($this, 'action_woocommerce_admin_order_item_values'), 10, 3);

        add_action('wp_ajax_remove_helloprint_order_file', array($this, 'remove_helloprint_order_file'));
        add_action('wp_ajax_nopriv_remove_helloprint_order_file', array($this, 'remove_helloprint_order_file'));

        add_action('wp_ajax_helloprint_upload_order_item_file', array($this, 'upload_order_item_file'));
        add_action('wp_ajax_nopriv_helloprint_upload_order_item_file', array($this, 'upload_order_item_file'));

        add_filter('woocommerce_order_item_add_line_buttons', array($this, 'add_product_button'));
        add_filter('woocommerce_admin_order_data_after_shipping_address', array($this, 'helloprint_product_order_modal'));


        add_action('wp_ajax_helloprint_get_product_for_order', array($this, 'get_product_for_order'));
        add_action('wp_ajax_nopriv_helloprint_get_product_for_order', array($this, 'get_product_for_order'));

        add_action('wp_ajax_helloprint_get_product_attribute_for_order', array($this, 'get_product_attribute_for_order'));
        add_action('wp_ajax_nopriv_helloprint_get_product_attribute_for_order', array($this, 'get_product_attribute_for_order'));

        add_action('wp_ajax_helloprint_add_order_item', array($this, 'add_order_item'));
        add_action('wp_ajax_nopriv_helloprint_add_order_item', array($this, 'add_order_item'));

        // add rest api url to handle the callback
        add_action('rest_api_init', function () {
            register_rest_route('helloprint/v1', '/complete_order_from_helloprint_callback',  array(
                'methods' => ['GET', 'POST'],
                'callback' => array($this, 'update_order_status'),
                'permission_callback' => '__return_true'
            ));
        });

        add_action('add_meta_boxes', array($this, 'add_helloprint_submit_option'));
        add_action('woocommerce_order_item_line_item_html', array($this, 'helloprint_order_item_option'), 10, 2);

        add_filter('woocommerce_order_number', array($this, 'change_woocommerce_order_number'));
        add_filter( 'woocommerce_order_item_get_formatted_meta_data', array($this, 'hide_my_item_meta'), 20, 2 );
    
        if (get_option("helloprint_disable_hp_order_change_status_email")) {
            add_action("woocommerce_email", array($this, "prevent_order_status_change_email"));
        }
    }

    public function update_order_status()
    {
        try {
            if (true === WP_DEBUG) {
                error_log("Helloprint APi callback comes.");
            }
            $logger = wc_get_logger();
            $context = array('source' => 'wphp-callback-log');
            $request = file_get_contents('php://input');
            $data = json_decode($request, true);
            if (true === WP_DEBUG) {
                error_log("Helloprint APi callback data :: " . \json_encode($data));
            }
            $logger->info("Helloprint API callback data :: " .  wp_json_encode($data), $context);
            if (empty($data["data"]) && !empty($data['orderReferenceId'])) {
                $data["data"] = $data;
            }
            $oReferenceId = isset($data['data']['orderReferenceId']) ? $data['data']['orderReferenceId'] : '';
            if (empty($data) || empty($data['data']) || empty($oReferenceId)) {
                return false;
            }
            if (!empty($oReferenceId)) {
                $order_id_array = explode("-", $oReferenceId);
                $order_id = $order_id_array[(count($order_id_array) - 1)];
            } else {
                $order_id = $oReferenceId;
            }
            $helloprint_order_prefix = get_option("helloprint_order_prefix", "wp-");
            $order_id = str_replace($helloprint_order_prefix, '', $order_id);
            $order_id = str_replace('wp-', '', $order_id);
            $order = wc_get_order($order_id);
            if (empty($order) || $order == false) {
                $order_id = str_replace($helloprint_order_prefix, '', $oReferenceId);
                $order_id = str_replace('wp-', '', $order_id);
                $order = wc_get_order($order_id);
                if (empty($order) || $order == false) {
                    return false;
                }
            }
            $order_status = $order->get_meta('helloprint_order_status', true);
            $request_id = !empty($order_status['request_id']) ? $order_status['request_id'] : "";
            $logger = wc_get_logger();
            $context = array('source' => 'helloprint');
            $orderItems = $data['data']['orderItems'];
            if ($data['data']['status'] == 'ORDER_CREATED') {
                $this->update_order_item_status($order, $orderItems, true);
                if (!empty($order_status)) {
                    update_post_meta($order_id, 'helloprint_order_status', [
                        'request_id' => $request_id,
                        'status' => !empty($data['data']['orderStatus']) ? sanitize_text_field($data['data']['orderStatus']) : sanitize_text_field($data['data']['status']),
                        'order_id' => sanitize_text_field($data['data']['orderId'])
                    ]);
                } else {
                    add_post_meta($order_id, 'helloprint_order_status', [
                        'request_id' => $request_id,
                        'status' => !empty($data['data']['orderStatus']) ? sanitize_text_field($data['data']['orderStatus']) : sanitize_text_field($data['data']['status']),
                        'order_id' => sanitize_text_field($data['data']['orderId'])
                    ]);
                }
                
                $order_status = $order->get_meta('helloprint_order_status', true);
                $logger->info("Order Created :: " . \json_encode($order_status), $context);
                return wp_send_json_success($order_status);
            } else if ($data['data']['status'] == 'SHIPPED') {
                if (!empty($order_status)) {
                    update_post_meta($order_id, 'helloprint_order_status', [
                        'request_id' => $request_id,
                        'status' => !empty($data['data']['orderStatus']) ? sanitize_text_field($data['data']['orderStatus']) : sanitize_text_field($data['data']['status']),
                        'order_id' => sanitize_text_field($data['data']['orderId'])
                    ]);
                } else {
                    add_post_meta($order_id, 'helloprint_order_status', [
                        'request_id' => $request_id,
                        'status' => !empty($data['data']['orderStatus']) ? sanitize_text_field($data['data']['orderStatus']) : sanitize_text_field($data['data']['status']),
                        'order_id' => sanitize_text_field($data['data']['orderId'])
                    ]);
                }
    
                $this->update_order_after_shipped($order, $data, $request_id);
                $order_status = $order->get_meta('helloprint_order_status', true);
                $logger->info("Shipped :: " . \json_encode($order_status), $context);
                return wp_send_json_success($order_status);
            } else {
                $this->update_order_item_status($order, $orderItems);
                if (isset($data['data']['status']) && isset($data['data']['orderId'])) {
                    if (!empty($order_status)) {
                        update_post_meta($order_id, 'helloprint_order_status', [
                            'request_id' => $request_id,
                            'status' => !empty($data['data']['orderStatus']) ? sanitize_text_field($data['data']['orderStatus']) : sanitize_text_field($data['data']['status']),
                            'order_id' => sanitize_text_field($data['data']['orderId'])
                        ]);
                    } else {
                        add_post_meta($order_id, 'helloprint_order_status', [
                            'request_id' => $request_id,
                            'status' => !empty($data['data']['orderStatus']) ? sanitize_text_field($data['data']['orderStatus']) : sanitize_text_field($data['data']['status']),
                            'order_id' => sanitize_text_field($data['data']['orderId'])
                        ]); 
                    }
                    $order_status = $order->get_meta('helloprint_order_status', true);
                    $logger->info(\json_encode($order_status), $context);
                    return wp_send_json_success($order_status);
                } else {
                    if (!empty($order_status)) {
                        update_post_meta($order_id, 'helloprint_order_status', [
                            'request_id' => $request_id,
                            'status' => 'FAILED_TO_CREATE_ORDER_IN_HELLOPRINT',
                            "message" => !empty($data["data"]['message']) ? $data["data"]['message'] : "",
                            'order_id' => '',
                        ]);
                    } else {
                        add_post_meta($order_id, 'helloprint_order_status', [
                            'request_id' => $request_id,
                            'status' => 'FAILED_TO_CREATE_ORDER_IN_HELLOPRINT',
                            "message" => !empty($data["data"]['message']) ? $data["data"]['message'] : "",
                            'order_id' => '',
                        ]);
                    }
                    $order = wc_get_order($order_id);
                    $order_status = $order->get_meta('helloprint_order_status', true);
                    $logger->info("Failed :: " . \json_encode($order_status), $context);
                    return wp_send_json_success($order_status);
                }
            }
            return wp_send_json_error();
        } catch (\Exception $e) {
            $full_error_msg = $e->getCode() . " : " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile();
            error_log($full_error_msg);
        }
        
    }

    public function add_order_status_to_order_notes($order)
    {
        $order_status = $order->get_meta('helloprint_order_status', true);
        if ($order_status) {
            echo '<div id="my_custom_checkout_field"><h3>' . wp_kses(_translate_helloprint('HelloPrint Order Details', 'helloprint'), true) . '</h3>';
            if (isset($order_status['status'])) {
                echo '<p>' . wp_kses(_translate_helloprint('Order Status', 'helloprint'), true) . ': ' . wp_kses(_translate_helloprint(str_replace('_', ' ', esc_attr($order_status['status'])), 'helloprint'), true) . '</p>';
            } else {
                echo '<p>' . wp_kses(_translate_helloprint('Order Status', 'helloprint'), true) . ': ' . wp_kses(_translate_helloprint('FAILED TO CREATE ORDER', 'helloprint'), true) . '</p>';
            }
            if (isset($order_status['message']) && !empty($order_status['message'])) {
                echo '<p>' . wp_kses(_translate_helloprint('Message', 'helloprint'), true) . ': ' . esc_html($order_status['message']) . '</p>';
            }
            if (isset($order_status['request_id'])) {
                echo '<p>' . wp_kses(_translate_helloprint('Request ID', 'helloprint'), true) . ': ' . esc_html($order_status['request_id']) . '</p>';
            }
            if (isset($order_status['order_id'])) {
                echo '<p>' . wp_kses(_translate_helloprint('Order ID', 'helloprint'), true) . ': ' . esc_html($order_status['order_id']) . '</p>';
            }
            echo '</div>';
        }
    }

    public  function action_woocommerce_admin_order_item_values($item_id, $item, $product)
    {
        if (empty($product) || empty($item_id) || empty($item)) {
            return true;
        }
        global $wpdb;
        $order_id = $item['order_id'];
        $order = wc_get_order($order_id);
        $helloprint_order_id = '';
        $order_status = $order->get_meta('helloprint_order_status', true);
        $organization = $order->get_meta('organization', true);
        
        if (!empty($order_status['order_id'])) {
            $helloprint_order_id = $order_status['order_id'];
        }
        if ($product->get_type() == 'helloprint_product') {
            if (!empty($item['helloprint_product_setup'])) {
                $product_setup = $item['helloprint_product_setup'];
                if (!is_array($product_setup)) {
                    $product_setup = json_decode($product_setup, true);
                }
                $sku = $product_setup['sku'];
                $this->copy_files_for_old_datas($product_setup, $item_id, $order_id);
                $this->load_preset_files($wpdb, $sku, $item_id, $order_id, $helloprint_order_id, true);
            }
        } else {
            $sku = $product->get_sku();

            $this->load_preset_files($wpdb, $sku, $item_id, $order_id, $helloprint_order_id, false);
        }
    }

    public function remove_helloprint_order_file()
    {
        check_ajax_referer('wphp-plugin-nonce');
        $item_id = sanitize_text_field(wp_unslash($_POST['item_id']));
        $fileKey = sanitize_text_field(wp_unslash($_POST['fileKey']));

        $custom_field = wc_get_order_item_meta($item_id, 'helloprint_product_setup', true);
        if (!is_array($custom_field)) {
            $custom_field = json_decode($custom_field, true);
        }
        $uploads_files = $custom_field['uploaded_files'];

        if (count($uploads_files) > 1) {
            unset($uploads_files[$fileKey]);
        } else {
            $uploads_files[0]['file_name'] = '';
            $uploads_files[0]['file_path'] = '';
        }

        $custom_field['uploaded_files'] = $uploads_files;
        $td = wc_update_order_item_meta($item_id, 'helloprint_product_setup', json_encode($custom_field));

        wp_send_json_success(array(
            'success' => true
        ));
    }

    public function upload_order_item_file() {
        check_ajax_referer( 'wphp-plugin-nonce' );
        global $wpdb;
        $fileUploadTable = $wpdb->prefix . 'helloprint_order_line_preset_files';
        $item_id = isset( $_POST['item_id'] ) ? sanitize_text_field( wp_unslash( $_POST['item_id'] ) ) : '';
        if ( '' != $item_id ) {
            $files = isset( $_POST['data'] ) ?
            ( isset( $_POST['data']['helloprint_order_item_file_upload'] ) ?
                array_map( 'sanitize_text_field', wp_unslash( $_POST['data']['helloprint_order_item_file_upload'] ) )
                : array() )
                : array();
            $custom_field = wc_get_order_item_meta( $item_id, 'helloprint_product_setup', true );
            $order_id = wc_get_order_id_by_order_item_id($item_id);
     
            if ( !is_array( $custom_field ) ) {
                $custom_field = json_decode( $custom_field, true );
            }
            $uploads_files = $custom_field['uploaded_files'];
            if ( !empty( $files ) && count( $files ) > 0 ) {
                foreach ( $files as $fileUploads ) {
                    $fileUploads = ltrim( $fileUploads, "[" );
                    $fileUploads = rtrim( $fileUploads, "]" );
                    $filesArray = json_decode(stripslashes( $fileUploads ), true );
                    if (is_string($fileUploads) && !isset( $filesArray )) {
                        $file_url = $fileUploads;
                        $file_name = '';
                    } else {
                        $file_name = ($filesArray['file_name']) ?? '';
                        $file_url = ($filesArray['file_path']) ?? '';
                    }
                    if (!empty($file_url)) {
                        $file_url = sanitize_url(wp_unslash($file_url));
                        $uploads_files[] = [
                            'file_name' => sanitize_file_name(wp_unslash($file_name)),
                            'file_path' => $file_url];
                            if (!empty($file_url)) {
                                $insertQuery = "INSERT INTO $fileUploadTable(order_id,line_item_id,file_url) VALUES('$order_id','$item_id', '$file_url')";
                                $wpdb->query($insertQuery);
                            }
                    }
                }
                $custom_field['uploaded_files'] = $uploads_files;   
                $td = wc_update_order_item_meta($item_id, 'helloprint_product_setup', json_encode($custom_field));
            }
            return wp_send_json_success(['files'=>$files,'download'=>esc_html(_translate_helloprint('Download', 'helloprint')),'remove'=>esc_html(_translate_helloprint('Remove', 'helloprint'))]);
        }
    }

    private function update_item_files($item_id, $file_name, $file_path)
    {
        $custom_field = wc_get_order_item_meta($item_id, 'helloprint_product_setup', true);
        if (!is_array($custom_field)) {
            $custom_field = json_decode($custom_field, true);
        }
        $uploads_files = $custom_field['uploaded_files'];

        $uploads_files[] = ['file_name' => $file_name, 'file_path' => $file_path];
        $custom_field['uploaded_files'] = $uploads_files;
        $td = wc_update_order_item_meta($item_id, 'helloprint_product_setup', json_encode($custom_field));
    }

    /**
    * Check if HPOS enabled.
    */
    function is_wc_order_hpos_enabled() {
        return function_exists( 'wc_get_container' ) ? 
                wc_get_container()
                    ->get( CustomOrdersTableController::class )
                    ->custom_orders_table_usage_is_enabled() 
                : false;
    }


    public function add_helloprint_submit_option()
    {
        global $post;
        // Get an instance of the WC_Order Object
        $order = wc_get_order($post->ID);
        $ifShipped = false;
        $ifAlreadyCreated = false;
        
        if ($order) {
            $order_status = $order->get_meta('helloprint_order_status', true);
        }
        if (isset($order_status['status']) && $order_status['status'] == 'SHIPPED') {
            //$ifShipped = true;
        }
        if (!empty($order_status['order_id'])) {
            //$ifAlreadyCreated = true;
        }
        if (!$ifShipped && !$ifAlreadyCreated) {

            $screen = $this->is_wc_order_hpos_enabled()
                ? wc_get_page_screen_id( 'shop-order' )
                : 'shop_order';


            add_meta_box(
                'submit_order_action',
                wp_kses(_translate_helloprint('Helloprint Action', 'helloprint'), false),
                array($this, '_render_submit_order_box'),
                $screen, // shop_order is the post type of the admin order page
                'side', // change to 'side' to move box to side column
                'core' // priority (where on page to put the box)
            );
        }
    }

    private function update_order_item_status($order, $orderItems, $new_entry = false)
    {
        if (!empty($orderItems)) {
            foreach ($order->get_items() as $ik => $item) {
                $item_id = $item->get_id();
                foreach ($orderItems as $oI) {
                    $oikey = $oI["itemReferenceId"];
                    $oi_array = explode("-", $oikey);
                    $oi_item_id = $oi_array[count($oi_array) - 1];
                    if ($oi_item_id == $item_id) {
                        if (!empty($oI['trackingUrls'])) {
                            $td = wc_update_order_item_meta($item_id, 'Tracking Url', implode(",", $oI['trackingUrls']));
                        }
            
                        if (!empty($oI['itemStatus'])) {
                            $tsd = wc_update_order_item_meta($item_id, 'Item Status', $oI['itemStatus']);
                        }
                        $item_id = !empty($oI["itemId"]) ? $oI["itemId"] : "";
                        if ($new_entry) {
                            $item->add_meta_data('helloprint_product_order_status', [
                                'item_id' => $item_id,
                                'item_reference_id' => $oikey,
                                'item_status' => $oI['itemStatus'],
                            ]);
                        }
                    }
                    
                }
            }
        }
        
    }

    public function update_order_after_shipped($order, $data, $requestId)
    {
        // Get and Loop Over Order Items
        $orderItems = $data['data']['orderItems'];
        $order_id = $order->get_id();
        $this->update_order_item_status($order, $orderItems);
        
        $order->set_status('completed');
        $order->save();
        update_post_meta($order_id, 'helloprint_order_status', [
            'request_id' => $requestId,
            'status' => !empty($data['data']['orderStatus']) ? $data['data']['orderStatus'] : $data['data']['status'],
            'order_id' => $data['data']['orderId']
        ]);
        $disable_email = get_option("helloprint_disable_hp_order_change_status_email");
        if ($disable_email) {
            return false;
        }
        $this->_send_customer_email($order_id, $data);
    }

    private function _send_customer_email($order_id = null, $data = [])
    {
        // Get the user ID from an Order ID
        $user_id = get_post_meta($order_id, '_customer_user', true);

        // Get an instance of the WC_Customer Object from the user ID
        $customer = new \WC_Customer($user_id);
        $username = ""; // Get username
        $user_email = ""; // Get account email
        $first_name = "";
        $last_name = "";
        $display_name = "";
        if (!empty($customer)) {
            $username     = $customer->get_username(); // Get username
            $user_email   = $customer->get_email(); // Get account email
            $first_name   = $customer->get_first_name();
            $last_name    = $customer->get_last_name();
            $display_name = $customer->get_display_name();
        }

        $itemDetails = $data['data']['orderItems'];


        $to = $user_email;
        $subject = !empty($data['data']['message']) ? wp_kses(_translate_helloprint($data['data']['message'], 'helloprint'), false) : wp_kses(_translate_helloprint('Order Shipped', 'helloprint'), false);
        $body = wp_kses(_translate_helloprint('Hi', 'helloprint'), true) . " " . $display_name . ', <br/><br/>';
        $body .= wp_kses(_translate_helloprint('Your order has been shipped', 'helloprint'), true) . '.<br/>';
        $body .= '<h4>' . wp_kses(_translate_helloprint('Items', 'helloprint'), true) . '</h4>';
        foreach ($itemDetails as $oItem) {
            $body .= '<p>' . wp_kses(_translate_helloprint('Item ID', 'helloprint'), true) . ': ' . esc_attr($oItem['itemId']) . '</p>';
            /*if (!empty($oItem['trackingUrls'])) {
                $body .= '<p>' . wp_kses(_translate_helloprint('Tracking Urls', 'helloprint'), true) . ': ' . implode(",", $oItem['trackingUrls']) . '</p>';
            }*/
            $body .= '<hr/>';
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (!empty($to)) {
            wp_mail($to, $subject, $body, $headers);
        }
    }

    public function _render_submit_order_box()
    {
        esc_html(load_template("$this->plugin_path/templates/admin/submit-order.php", true));
    }

    public function add_product_button()
    {
        esc_html(load_template("$this->plugin_path/templates/admin/orders/add-product-button.php", true));
    }

    public function helloprint_product_order_modal()
    {
        $product_options[0] = esc_html(_translate_helloprint('Select One', 'helloprint'));
        $product_options = array_merge($product_options, (new HelloPrintApiService())->getAllProducts());
        $data = [
            'selectProducts' => $product_options
        ];
        esc_html(load_template("$this->plugin_path/templates/admin/orders/add-product-modal.php", true, $data));
    }

    public function get_product_attribute_for_order()
    {
        check_ajax_referer( 'wphp-plugin-nonce' );
        $product_external_id = sanitize_text_field(wp_unslash($_POST['product_id']));
        $response = (new HelloPrintApiService())->getProductDetailForSelectOptions($product_external_id);
        if (isset($response['attributes'])) {
            foreach ($response['attributes'] as $key => $attribute) {
                $response['attributes'][$key]['name'] = wp_kses(_translate_helloprint($attribute['name'], 'helloprint'), true);
            }
            $width = false;
            $height = false;
            $customSize = false;
            $customSizeHtml = '';
            $customQuantityHtml = '';
            foreach ($response['options'] as $option) {
                $option['code'] == 'width' ? $width = true : '';
                $option['code'] == 'height' ? $height = true : '';
                if ($width && $height) {
                    $customSize = true;
                    break;
                }
            }
            $quantitySearch = array_search('apparelSize', array_column($response['options'], 'code'));
            $quantitySearch !== false ? $customQuantity = true : $customQuantity = false;
            if ($customSize || $customQuantity) {
                $data['product_options'] = $response['options'];
            }
            if ($customSize) {
                $data['size_exists'] = array_search('size', array_column($response['attributes'], 'id')) !== false;
                ob_start();
                esc_html(load_template("$this->plugin_path/templates/wp/inc/custom-size.php", true, $data));
                $customSizeHtml = ob_get_clean();
            }
            if ($customQuantity) {
                ob_start();
                esc_html(load_template("$this->plugin_path/templates/wp/inc/size-selector.php", true, $data));
                $customQuantityHtml = ob_get_clean();
            }
            $newArr  = array_merge(
                $response,
                [
                    'helloprint_external_product_id' => $product_external_id,
                    'customSize' => $customSize,
                    'customSizeHtml' => $customSizeHtml,
                    'customQuantity' => $customQuantity,
                    'customQuantityHtml' => $customQuantityHtml
                ]
            );
            wp_send_json_success($newArr);
        } else {
            wp_send_json_success([
                'message' => esc_html(_translate_helloprint('Product Attributes not found', 'helloprint'))
            ]);
        }
    }

    public function add_order_item()
    {
        check_ajax_referer( 'wphp-plugin-nonce' );
        global $wpdb;
        
        if (
            isset($_POST['helloprint_admin_order']) &&
            rest_sanitize_boolean($_POST['helloprint_admin_order']) == true
        ) {
            $productService = new ProductService();
            $helloprintProductKey = sanitize_text_field(wp_unslash($_POST['helloprint_external_product_id']));
            $helloprintProductName = sanitize_text_field(wp_unslash($_POST['helloprint_external_product_name']));
            $productId = $productService->getProductIdFromMetaKey($helloprintProductKey);
            if ($productId == null) {
                $productId = $productService->createHelloprintProduct($helloprintProductKey, $helloprintProductName);
                (new ProductController)->save_helloprint_product_panel($productId);
            }
            $product = wc_get_product($productId);

            if (!empty($product) && $product->get_type() == 'helloprint_product') {
                $customOptions = sanitize_text_field(wp_unslash(json_encode($_POST['helloprint_product_custom_options'])));
               
                $apprealSizeOptions = sanitize_text_field(wp_unslash(json_encode($_POST['helloprint_appreal_size_options'])));
                $order_notes = [];
                $added_items = [];
                $uploaded_file = [];
                $order_id  = sanitize_text_field(wp_unslash($_POST['order_id']));
                $order = wc_get_order($order_id);
                $offerPrice = (float)sanitize_text_field(wp_unslash($_POST['offer_price']));
                $inputProcessService = new InputProcessService();
                $values = [];
                $attributeOptionValues = [];
                $upload_file = [];
                $helloprint_uploaded_files = array_map('sanitize_text_field',wp_unslash($_POST['helloprint_product_file_upload']));

                $item_id = $order->add_product($product, 1, array('order' => $order));
                if ($helloprint_uploaded_files) {
                    $fileUploadTable = $wpdb->prefix . 'helloprint_order_line_preset_files';
                    foreach ($helloprint_uploaded_files as $fileUploads) {
                        $fileUploads = json_decode(stripslashes($fileUploads), true);
                        
                        if (!empty($fileUploads) && !empty($fileUploads[0]['file_path'])) {
                            array_push($uploaded_file, array_map('sanitize_text_field',wp_unslash($fileUploads[0])));
                            $file_name = (sanitize_text_field(wp_unslash($fileUploads[0]['file_path']))) ?? '';
                            $file_url = (sanitize_text_field(wp_unslash($fileUploads[0]['file_path']))) ?? '';
                            if (!empty($file_url)) {
                                $insertQuery = "INSERT INTO $fileUploadTable(order_id,line_item_id,file_url) VALUES('$order_id','$item_id', '$file_url')";
                                $wpdb->query($insertQuery);
                            }
                        }
                    }
                }
                $item = apply_filters('woocommerce_ajax_order_item', $order->get_item($item_id), $item_id, $order, $product);
                $added_items[$item_id] = $item;
                $order_notes[$item_id] = $product->get_formatted_name();

                $item->set_subtotal($offerPrice);
                $item->set_total($offerPrice);
                do_action('woocommerce_ajax_add_order_item_meta', $item_id, $item, $order);
                $attributeOptions = $inputProcessService->process_product_setup_json(sanitize_text_field(wp_unslash($_POST['helloprint_product_options_labels'])));
                $attributeOptionValues['appreal_size'] = [];
                $sizeLabel = '';
                foreach ($attributeOptions ?? [] as $kkey => $opt) {
                    if (str_starts_with(strtolower($kkey), 'appreal_size[')) {
                        if ($opt > 0) {
                            $keyString = $kkey . "=" . $opt;
                            parse_str($keyString, $result);
                            foreach ($result['appreal_size'] as $key => $optionss) {
                                if (!isset($attributeOptionValues['appreal_size'][$key])) {
                                    $attributeOptionValues['appreal_size'][$key] = [];
                                }
                                foreach ($optionss as $jk => $val) {
                                    $attributeOptionValues['appreal_size'][$key][$jk] = $val;
                                }
                            }
                        }
                        unset($attributeOptions[$kkey]);
                    } else {
                        $valueName = esc_html(_translate_helloprint(wc_clean($opt), 'helloprint'));
                        $valueKey =  esc_html(_translate_helloprint(ucfirst($kkey), 'helloprint'));
                        $attributeOptionValues[$valueKey] = $valueName;
                        array_push($values, [
                            $valueKey => $valueName
                        ]);
                        $item->add_meta_data($valueKey, $valueName);
                    }
                }
                foreach ($attributeOptionValues as $key => $attributeOption) {
                    if ($key == 'appreal_size') {
                        foreach ($attributeOptionValues[$key] as $innerKey => $flattened) {
                            array_walk($flattened, function (&$value, $key) {
                                $value = "{$key}:{$value}";
                            });
                            $imp  = implode(', ', $flattened);
                            $item->add_meta_data($innerKey, '[' . $imp . ']');
                        }
                    }
                }
                $helloprint_product_setup = [
                    'sku' => sanitize_text_field(wp_unslash($_POST['helloprint_product_sku'])),
                    'helloprint_variant_key' => sanitize_text_field(wp_unslash($_POST['helloprint_product_variant_key'])),
                    'quantity' => sanitize_text_field(wp_unslash($_POST['helloprint_product_quantity'])),
                    'delivery_option' => sanitize_text_field(wp_unslash($_POST['helloprint_service_level'])),
                    'product_price' => sanitize_text_field(wp_unslash($_POST['offer_price'])),
                    'options' => $attributeOptionValues,
                    'uploaded_files' => $uploaded_file,
                    'custom_options' => $customOptions,
                    'appreal_size_options' => $apprealSizeOptions,
                    'want_graphic_design' => false
                ];
                $item->add_meta_data('helloprint_product_setup', json_encode($helloprint_product_setup));
                $item->add_meta_data(esc_html(_translate_helloprint('Quantity', 'helloprint')), $inputProcessService->process_quantity(sanitize_text_field(wp_unslash($_POST['helloprint_product_quantity']))));
                $item->add_meta_data(esc_html(_translate_helloprint('Delivery option', 'helloprint')), esc_html(_translate_helloprint(ucfirst($inputProcessService->process_service_level($_POST['helloprint_service_level'])), 'helloprint')));

                $item->calculate_taxes();
                $item->save();

                $order = wc_get_order($order_id);
                $order->calculate_totals();
                $order->add_order_note(sprintf(__('Added line items: %s', 'woocommerce'), implode(', ', $order_notes)), false, true);
                $order->save();
                do_action('woocommerce_ajax_order_items_added', $item, wc_get_order($order_id));

                $data = get_post_meta($order_id);

                // Get HTML to return 

                ob_start();
                include $this->plugin_path . '../woocommerce/includes/admin/meta-boxes/views/html-order-items.php';
                $items_html = ob_get_clean();
                ob_start();
                $notes = wc_get_order_notes(array('order_id' => $order_id));
                include $this->plugin_path . '../woocommerce/includes/admin/meta-boxes/views/html-order-notes.php';
                $notes_html = ob_get_clean();

                wp_send_json_success([
                    'item_added' => true,
                    'html'       => $items_html,
                    'notes_html' => $notes_html,
                ]);
            }
            wp_send_json_success(['item_added' => false]);
        }
        wp_send_json_success(['item_added' => false]);
    }

    public function update_order_item_presets($order_id, $order)
    {
        if (!empty($_POST['order_item_preset'])) {
            $order_presets = (sanitize_text_field(wp_unslash($_POST['order_item_preset']))) ?? [];
            $service_levels = (sanitize_text_field(wp_unslash($_POST['order_item_preset_service_level']))) ?? [];
            $quantities = (sanitize_text_field(wp_unslash($_POST['order_item_preset_quantity']))) ?? [];
            global $wpdb;
            $tableName = $wpdb->prefix . 'helloprint_order_line_item_presets';
            foreach ($order_presets as $id => $preset) {
                $id = sanitize_text_field(wp_unslash($id));
                $preset = sanitize_text_field(wp_unslash($preset));
                $slevel = sanitize_text_field(wp_unslash($service_levels[$id]));
                $qty = (int)sanitize_text_field(wp_unslash($quantities[$id]));
                $deleteQuery = "DELETE from $tableName where line_item_id = $id";
                $wpdb->query($deleteQuery);
                if (!empty($preset) && $preset != 0) {
                    $insertQuery = "INSERT INTO $tableName(order_id,line_item_id,preset_id,service_level,quantity) VALUES('$order_id','$id', '$preset', '$slevel', $qty)";
                    $wpdb->query($insertQuery);
                }
            }
        }
    }

    private function load_preset_files($wpdb, $sku, $item_id, $order_id, $helloprint_order_id, $is_hp_product = false)
    {
        $tableName = $wpdb->prefix . 'helloprint_order_presets';
        $presetFileTable = $wpdb->prefix . 'helloprint_order_line_preset_files';
        $presetPublicFileTable = $wpdb->prefix . 'helloprint_order_line_public_files';
        $product_type = "non_hp";
        $query = "SELECT id,order_preset_name,helloprint_item_sku,helloprint_variant_key,default_service_level,default_quantity,file_name,file_url from $tableName where (helloprint_item_sku = '$sku' or helloprint_item_sku = '') and product_type = '$product_type' ORDER BY id DESC";
        $allavailablePresets = $wpdb->get_results($query);
        if (!$is_hp_product && count($allavailablePresets) <= 0) {
            return false;
        }
        $presetPublicFiles = $wpdb->get_results("Select * FROM $presetPublicFileTable WHERE line_item_id='$item_id'");
        $publicFileUrl = ($presetPublicFiles[0]->public_file_url) ?? '';
        $all_hp_presets = [];
        $helloprint_prefer_file = wc_get_order_item_meta($item_id, "helloprint_preset_prefer_files", true);
        $item = \WC_Order_Factory::get_order_item($item_id);
        $product_id = $item->get_product_id();
        $item_quantity = $item->get_quantity();
        if ($is_hp_product) {
            $wphp_product_id = get_post_meta($product_id, "helloprint_external_product_id", true);
            $query = "SELECT id,file_name,file_url from $tableName where product_type = 'hp' and helloprint_product_id='$wphp_product_id' ";
            $all_hp_presets = $wpdb->get_results($query);
            
            foreach ($all_hp_presets as $key => &$a_preset) {
                if (strpos($a_preset->file_url, "//") !== FALSE) {
                    unset($all_hp_presets[$key]);
                    if (empty($publicFileUrl)) {
                        $publicFileUrl = $a_preset->file_url;
                    }
                }
            }
        }

        $all_hp_presets = array_values($all_hp_presets);


        if (empty($helloprint_prefer_file)) {
            $helloprint_prefer_file = "upload_files";
        }
        
        $line_item_tableName = $wpdb->prefix . 'helloprint_order_line_item_presets';

        $lineItemPreset = $wpdb->get_results("SELECT * from $line_item_tableName where line_item_id = '$item_id'");
        $presetFiles = $wpdb->get_results("Select * FROM $presetFileTable WHERE line_item_id='$item_id'");
        $lineItemPreset = ($lineItemPreset[0]) ?? [];

        
        if (empty($helloprint_prefer_file) && !empty($all_hp_presets) && empty($presetFiles) && empty($publicFileUrl)) {
            $helloprint_prefer_file = "hp_preset_artwork";
        }
        
        $data_to_send = [
            'sku' => $sku,
            'item_id' => $item_id,
            'all_available_presets' => $allavailablePresets,
            'line_item_preset' => $lineItemPreset,
            'preset_files' => !empty($presetFiles) ? $presetFiles : [],
            'external_file_url' => $publicFileUrl,
            'order_id' => $order_id,
            'helloprint_order_id' => $helloprint_order_id,
            "is_hp_product" => $is_hp_product,
            "all_hp_presets" => $all_hp_presets,
            "helloprint_prefer_file" => $helloprint_prefer_file,
            "order_item_quantity" => $item_quantity
        ];
        load_template("$this->plugin_path/templates/admin/order-item-presets.php", false, $data_to_send);
    }

    // before files were stored as item meta data with keys helloprint_product_setup and in separate files table both 
    // but now saved in only separate files table, so copying the item meta data to files tables for old datas
    private function copy_files_for_old_datas($product_setup, $item_id, $order_id)
    {
        global $wpdb;
        $artworkFileTable = $wpdb->prefix . 'helloprint_order_line_preset_files';
        $artworks = $wpdb->get_results("Select * FROM $artworkFileTable WHERE line_item_id='$item_id'");
        if (empty($artworks)) {
            if (!empty($product_setup['uploaded_files'])) {
                $uploadedFiles = $product_setup['uploaded_files'];
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = json_decode($uploadedFiles, true);
                }
                foreach ($uploadedFiles as $fileUploads) {
                    if (!empty($fileUploads['file_path'])) {
                        $file_name = (sanitize_text_field(wp_unslash($fileUploads['file_name']))) ?? '';
                        $file_url = (sanitize_text_field(wp_unslash($fileUploads['file_path']))) ?? '';
                        if (!empty($file_name) && !empty($file_url)) {
                            $old_artworks = $wpdb->get_results("Select * FROM $artworkFileTable WHERE order_id='$order_id' and file_url='$file_url'");
                            if (empty($old_artworks)) {
                                $insertQuery = "INSERT INTO $artworkFileTable(order_id,line_item_id,file_url) VALUES('$order_id','$item_id', '$file_url')";
                                $wpdb->query($insertQuery);
                            }
                        }
                    }
                }

                $product_setup['uploaded_files'] = [];
                wc_update_order_item_meta($item_id, 'helloprint_product_setup', json_encode($product_setup));
            }
        }
    }

    public function helloprint_order_item_option($item_id, $item)
    {
        $product = $item->get_product();
        if (is_admin() && !empty($product) && method_exists($product, "get_type") && $product->get_type() == 'helloprint_product') {
            $data['item_id'] = $item_id;
            load_template("$this->plugin_path/templates/admin/order-item-prevent-edit.php", false, $data);
        }
    }

    public function change_woocommerce_order_number($order_id)
    {
        if (!is_numeric($order_id)) {
            $order_id_array = explode("-", $order_id);
            $order_id = (int) $order_id_array[count($order_id_array) - 1];
        }       
        $order = wc_get_order($order_id);
        $organization_prefix_id = '';
        if ($order) {
            $organization = $order->get_meta('organization', true);
            if (is_array($organization)) {
                $organization = $organization[0];
            }
            if (!empty($organization)) {
                $organization_prefix_id = $organization . "-";
            }
        }
        $prefix = get_option("helloprint_order_prefix", "wp-");
        $new_order_id = $prefix . $organization_prefix_id . $order_id;
        return $new_order_id;
    }

    public function hide_my_item_meta($meta_data, $item)
    {
        $new_meta = array();
        foreach ( $meta_data as $id => $meta_array ) {
            // We are removing the meta with the key 'Tracking Url' from the whole array.
            if ( 'Tracking Url' === $meta_array->key && !is_admin()) { continue; }
            $new_meta[ $id ] = $meta_array;
        }
        return $new_meta;
    }

    public function prevent_order_status_change_email($email_class)
    {
        // Completed order emails
        remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );

    }
    
}
