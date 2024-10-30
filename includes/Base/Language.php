<?php

add_action('init', '_create_translation_db_if_not_exists');
add_action('init', 'load_helloprint_plugin_textdomain');

if (!function_exists('_create_translation_db_if_not_exists')) {
	function _create_translation_db_if_not_exists()
	{
		$activate = new HelloPrint\Inc\Base\Activate();
		$activate->init_helloprint_translation_db();
		$activate->init_helloprint_order_presets_db();
		$activate->init_helloprint_bulk_import_queue_db();
		$activate->init_helloprint_pitchprint_db();
		$activate->init_helloprint_pricing_tiers_db();
	}
}

if (!function_exists("_translate_helloprint")) {
	function _translate_helloprint($string = '', $option = '')
	{
		if (empty($string)) return '';
		if (is_array($string)) return $string;
		
		$string = wp_specialchars_decode(esc_html(trim($string)), ENT_QUOTES);

		if ($option == 'helloprint') {
			$str = trim(filter_var($string, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH));
			global $wpdb;
			$table = $wpdb->prefix . 'helloprint_translations';
			$query = 'SELECT translation from ' . $table . ' where string="' . $str . '"';
			$results = $wpdb->get_results($query);

			if (empty($results)) {
				return wp_specialchars_decode(esc_html(__($string, 'helloprint')), ENT_QUOTES);
			}
			require_once "Marked.php";
			$marked = new Marked\Marked();
			$marked->setOptions(['gfm' => true, 'headerIds' => true]);

			$text = $results[0]->translation;
			$text = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $text);
			$returnHtml = $marked($text, function($err, $html) {
				if($err) {
					throw new Exception($err);
				}
				return  $html;
			});
			return $returnHtml;
		}

		return wp_specialchars_decode(esc_html(__($string, 'helloprint')), ENT_QUOTES);
	}
}


if (!function_exists("load_helloprint_plugin_textdomain")) {
	function load_helloprint_plugin_textdomain()
	{
		$domain = 'helloprint';
		$pluginLanguageDir = dirname(plugin_basename(__FILE__)) . '/../../languages/';
		$mo_file = WP_PLUGIN_DIR . '/' . $pluginLanguageDir . $domain . '-' . get_locale() . '.mo';
		$flag = load_textdomain($domain, $mo_file);
	}
}
