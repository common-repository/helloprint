<?php

/** 
 * @package HelloPrint
 */

namespace HelloPrint\Inc\Base;

use HelloPrint\Inc\Base\Controllers\BaseController;

class Activate extends BaseController
{

	public static function activate()
	{
		global $wpdb;
		$env_mode = get_option("helloprint_env_mode");
		if (!$env_mode) {
			add_option('helloprint_env_mode', 1);
		}
		// $charset_collate = $wpdb->get_charset_collate();

		// if ($installed_ver != $this->version) {
		// 	update_option('helloprint_db_version', $this->version);
		// }

		$activate = new \HelloPrint\Inc\Base\Activate();
		$activate->init_helloprint_translation_db();
		$activate->init_helloprint_order_presets_db();
		$activate->init_helloprint_bulk_import_queue_db();
		$activate->init_helloprint_pitchprint_db();
		$activate->init_helloprint_pricing_tiers_db();
		flush_rewrite_rules();
	}

	// Initialize DB Tables
	public function init_helloprint_translation_db()
	{
		// WP Globals
		global $table_prefix, $wpdb;

		// Translation Table
		$translationsTable = $table_prefix . 'helloprint_translations';

		// Create Translations Table if not exist
		if ($wpdb->get_var("show tables like '$translationsTable'") != $translationsTable) {

			// Query - Create Table
			$sql = "CREATE TABLE `$translationsTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `string` varchar(500) NOT NULL, ";
			$sql .= " `translation` varchar(500) NOT NULL, ";
			$sql .= " PRIMARY KEY `translation_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			// Create Table
			dbDelta($sql);
		}
	}

	// Initialize DB Tables
	public function init_helloprint_order_presets_db()
	{
		// WP Globals
		global $table_prefix, $wpdb;

		// Order Presets Table
		$orderPresetsTable = $table_prefix . 'helloprint_order_presets';

		// Create Presets Table if not exist
		if ($wpdb->get_var("show tables like '$orderPresetsTable'") != $orderPresetsTable) {

			// Query - Create Table
			$sql = "CREATE TABLE `$orderPresetsTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `order_preset_name` varchar(500) NOT NULL, ";
			$sql .= " `helloprint_item_sku` varchar(500) NOT NULL, ";
			$sql .= " `helloprint_variant_key` varchar(500) NOT NULL, ";
			$sql .= " `default_service_level` varchar(500) NOT NULL, ";
			$sql .= " `default_quantity` int(11) DEFAULT 0, ";
			$sql .= " `file_name` varchar(500) NULL, ";
			$sql .= " `file_url` varchar(500) NULL, ";
			$sql .= " PRIMARY KEY `order_preset_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			// Create Table
			dbDelta($sql);
		}

		$available_options = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = '$orderPresetsTable' AND column_name = 'available_options'");

		if (empty($available_options)) {
			$wpdb->query("ALTER TABLE $orderPresetsTable ADD available_options TEXT DEFAULT '' AFTER default_quantity, ADD default_options TEXT DEFAULT '' AFTER available_options");
		}

		$product_type = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = '$orderPresetsTable' AND column_name = 'product_type'");
		if (empty($product_type)) {
			$wpdb->query("ALTER TABLE $orderPresetsTable ADD product_type VARCHAR(30) DEFAULT 'non_hp' AFTER id,ADD helloprint_product_id VARCHAR(300) DEFAULT '' AFTER default_quantity");
		}

		// Order Line Item Presets Table
		$orderLineItemPresetsTable = $table_prefix . 'helloprint_order_line_item_presets';

		// Create Order Line Item Preset Table if not exist
		if ($wpdb->get_var("show tables like '$orderLineItemPresetsTable'") != $orderLineItemPresetsTable) {

			// Query - Create Table
			$sql = "CREATE TABLE `$orderLineItemPresetsTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `order_id` int(11) NULL, ";
			$sql .= " `line_item_id` int(11) NULL, ";
			$sql .= " `preset_id` int(11) NULL, ";
			$sql .= " `service_level` varchar(500) NOT NULL, ";
			$sql .= " `quantity` int(11) DEFAULT 0, ";
			$sql .= " PRIMARY KEY `order_preset_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			// Create Table
			dbDelta($sql);
		}

		$item_line_options = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = '$orderLineItemPresetsTable' AND column_name = 'options'");

		if (empty($item_line_options)) {
			$wpdb->query("ALTER TABLE $orderLineItemPresetsTable ADD options TEXT DEFAULT '' AFTER quantity");
		}


		// Order Line Item File Presets Table
		$orderLineFilePresetsTable = $table_prefix . 'helloprint_order_line_preset_files';

		// Create Order Line Item File Preset Table if not exist
		if ($wpdb->get_var("show tables like '$orderLineFilePresetsTable'") != $orderLineFilePresetsTable) {

			// Query - Create Table
			$sql = "CREATE TABLE `$orderLineFilePresetsTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `order_id` int(11) NULL, ";
			$sql .= " `line_item_id` int(11) NULL, ";
			$sql .= " `file_url` varchar(500) NOT NULL, ";
			$sql .= " PRIMARY KEY `order_preset_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			// Create Table
			dbDelta($sql);
		}

		// Order Line Item Public File Table
		$orderLineFilePublicTable = $table_prefix . 'helloprint_order_line_public_files';

		// Create Order Line Item File Public Table if not exist
		if ($wpdb->get_var("show tables like '$orderLineFilePublicTable'") != $orderLineFilePublicTable) {

			// Query - Create Table
			$sql = "CREATE TABLE `$orderLineFilePublicTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `order_id` int(11) NULL, ";
			$sql .= " `line_item_id` int(11) NULL, ";
			$sql .= " `public_file_url` varchar(500) NOT NULL, ";
			$sql .= " PRIMARY KEY `public_file_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			// Create Table
			dbDelta($sql);
		}
	}

	// Initialize DB Tables
	public function init_helloprint_bulk_import_queue_db()
	{
		// WP Globals
		global $table_prefix, $wpdb;

		// Bulk import queue Table
		$bulkImportQueueTable = $table_prefix . 'helloprint_bulk_import_queues';

		// Create Bulk import queue Table if not exist
		if ($wpdb->get_var("show tables like '$bulkImportQueueTable'") != $bulkImportQueueTable) {

			// Query - Create Table
			$sql = "CREATE TABLE `$bulkImportQueueTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `category` int(11) NOT NULL, ";
			$sql .= " `product_id` varchar(500) NOT NULL default '', ";
			$sql .= " `imported` boolean NOT NULL default false, ";
			$sql .= " `attempts` int(11) NOT NULL default 0, ";
			$sql .= " `date` DATE, ";
			$sql .= " PRIMARY KEY `bulk_import_queue_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			// Create Table
			dbDelta($sql);
		}
	}

	// Initialize DB Tables

	public function init_helloprint_pitchprint_db()
	{
		// WP Globals
		global $table_prefix, $wpdb;
		// Translation Table
		$pitchPrintTable = $table_prefix . 'helloprint_pitch_prints';
		// Create Translations Table if not exist
		if ($wpdb->get_var("show tables like '$pitchPrintTable'") != $pitchPrintTable) {
			// Query - Create Table
			$sql = "CREATE TABLE `$pitchPrintTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `name` varchar(500) NOT NULL, ";
			$sql .= " `pitchprint_design_id` varchar(500) NOT NULL, ";
			$sql .= " `hp_variant_key` varchar(500) NOT NULL, ";
			$sql .= " PRIMARY KEY `hp_pitchprint_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			// Create Table
			dbDelta($sql);
		}
	}

	// Initialize DB Tables

	public function init_helloprint_pricing_tiers_db()
	{
		// WP Globals
		global $table_prefix, $wpdb;
		// Pricing Tiers Table
		$pricingTiersTable = $table_prefix . 'helloprint_pricing_tiers';
		// Create Pricing Tiers Table if not exist
		if ($wpdb->get_var("show tables like '$pricingTiersTable'") != $pricingTiersTable) {
			// Query - Create Table
			$sql = "CREATE TABLE `$pricingTiersTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `name` varchar(500) NOT NULL, ";
			$sql .= " `default_markup` int(11) NOT NULL DEFAULT 0, ";
			$sql .= " `enable_scaling` int(1) NOT NULL DEFAULT 0, ";
			$sql .= " PRIMARY KEY `hp_pricing_tier_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			// Create Table
			dbDelta($sql);
		}

		$tier_type = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = '$pricingTiersTable' AND column_name = 'tier_type'");

		if (empty($tier_type)) {
			$wpdb->query("ALTER TABLE $pricingTiersTable ADD tier_type varchar(500) DEFAULT 'margin' AFTER name");
		}

		// Scaled Pricing Tiers Table
		$scaledPricingTable = $table_prefix . 'helloprint_scaled_pricing';
		// Create Scaled Pricing Table if not exist
		if ($wpdb->get_var("show tables like '$scaledPricingTable'") != $scaledPricingTable) {
			// Query - Create Table
			$sql = "CREATE TABLE `$scaledPricingTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `pricing_tier_id` int(11) NULL, ";
			$sql .= " `price` DOUBLE(11, 2) NOT NULL DEFAULT 0, ";
			$sql .= " `margin` int(11) NOT NULL DEFAULT 0, ";
			$sql .= " PRIMARY KEY `hp_scaled_pricing_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			// Create Table
			dbDelta($sql);
		}

		// Helloprint product Pricing Table
		$helloprintProductPricingTable = $table_prefix . 'helloprint_product_pricing_tier';
		// Create Scaled Pricing Table if not exist
		if ($wpdb->get_var("show tables like '$helloprintProductPricingTable'") != $helloprintProductPricingTable) {
			// Query - Create Table
			$sql = "CREATE TABLE `$helloprintProductPricingTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `product_id` int(11) NULL, ";
			$sql .= " `pricing_tier_id` int(11) NULL, ";
			$sql .= " `profile` VARCHAR(500) NULL, ";
			$sql .= " PRIMARY KEY `hp_product_pricing_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			// Include Upgrade Script
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			// Create Table
			dbDelta($sql);
		}
	}
}
