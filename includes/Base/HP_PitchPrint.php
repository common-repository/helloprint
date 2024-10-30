<?php

class HP_PitchPrint
{

    private $wpdb, $table_name;

    function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name =  $this->wpdb->prefix . 'pitchprint_projects';
    }

    private function pitchprint_insert_before_cart($productId)
    {
        if (isset($_COOKIE['pitchprint_sessId'])) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $sessId = $_COOKIE['pitchprint_sessId'];
            $value = wp_kses($_POST['_w2p_set_option'], false);
            // Delete old
            $this->wpdb->delete($this->table_name, array('id' => $sessId, 'product_id' => $productId));

            // Insert new
            $date = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);
            $sql = "INSERT INTO `$this->table_name` VALUES ('$sessId', $productId, '$value', '$date')";

            dbDelta($sql);
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['pp_projects'])) {
                $_SESSION['pp_projects'] = array();
                $_SESSION['pp_projects'][$productId] = array();
            } else if (!isset($_SESSION['pp_projects'][$productId])) {
                $_SESSION['pp_projects'][$productId] = array();
            }

            $_SESSION['pp_projects'][$productId] = $_POST['_w2p_set_option'];
        }
    }

    public function handle_HP_pitch_print_cart(&$cart_item_data, $product_id)
    {
        $this->pitchprint_insert_before_cart($product_id);
        $_projects = $this->getPitchPrintProjectData($product_id);
        if (isset($_projects)) {
            if (isset($_projects[$product_id])) {
                $cart_item_data['_w2p_set_option'] = $_projects[$product_id];
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (!isset($_SESSION['pp_cache'])) $_SESSION['pp_cache'] = array();
                $opt_ = json_decode(rawurldecode($_projects[$product_id]), true);
                if ($opt_['type'] === 'p') $_SESSION['pp_cache'][$opt_['projectId']] = $_projects[$product_id] . "";
                if (isset($_SESSION['pp_projects']) && isset($_SESSION['pp_projects'][$product_id]))
                    unset($_SESSION['pp_projects'][$product_id]);
                else {
                    $sessId = isset($_COOKIE['pitchprint_sessId']) ? $_COOKIE['pitchprint_sessId'] : false;
                    if (!$sessId) return false;
                    $this->wpdb->delete($this->table_name, array('id' => $sessId, 'product_id' => $product_id));
                }
                $this->clear_pitch_print_data($product_id);
            }
        }
    }

    private function getPitchPrintProjectData($product_id = null)
    {
        if (!$product_id) {
            global $post;
            $product_id = $post->ID;
        }
        $_projects = array();
        if (isset($_COOKIE['pitchprint_sessId'])) {
            $sessId = $_COOKIE['pitchprint_sessId'];
            $sql = "SELECT `value` FROM `$this->table_name` WHERE `product_id` = $product_id AND `id` = '$sessId';";
            $results = $this->wpdb->get_results($sql);
            if (count($results))
                $_projects[$product_id] = $results[0]->value;
        } else {
            if (!session_id() && !headers_sent()) session_start();
            if (isset($_SESSION['pp_projects']))
                $_projects =  $_SESSION['pp_projects'];
        }
        return $_projects;
    }

    private function clear_pitch_print_data($productId)
    {
        if (isset($_COOKIE['pitchprint_sessId'])) {
            $this->clearPitchPrintProjects($productId);
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['pp_projects'])) {
                if (isset($_SESSION['pp_projects'][$productId])) {
                    unset($_SESSION['pp_projects'][$productId]);
                } elseif (isset($_COOKIE['pitchprint_sessId'])) {
                    $this->clearPitchPrintProjects($productId);
                }
            }
        }
    }

    private function clearPitchPrintProjects($productId)
    {
        $sessId = $_COOKIE['pitchprint_sessId'];
        $this->wpdb->delete($this->table_name, array('id' => $sessId, 'product_id' => $productId));
    }
}
