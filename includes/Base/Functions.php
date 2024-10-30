<?php

/**
 * Output a select input box.
 *
 * @param array $field Data about the field to render.
 */
if (!function_exists('helloprint_wp_select')) {
	function helloprint_wp_select($field)
	{
		global $thepostid, $post;

		$thepostid = empty($thepostid) ? $post->ID : $thepostid;
		$field     = wp_parse_args(
			$field,
			array(
				'class'             => 'select short',
				'style'             => '',
				'wrapper_class'     => '',
				'value'             => get_post_meta($thepostid, $field['id'], true),
				'name'              => $field['id'],
				'desc_tip'          => false,
				'custom_attributes' => array(),
			)
		);

		$wrapper_attributes = array(
			'class' => $field['wrapper_class'] . " form-field {$field['id']}_field",
		);

		$label_attributes = array(
			'for' => $field['id'],
		);

		$field_attributes          = (array) $field['custom_attributes'];
		$field_attributes['style'] = $field['style'];
		$field_attributes['id']    = $field['id'];
		$field_attributes['name']  = $field['name'];
		$field_attributes['class'] = $field['class'];

		$tooltip     = !empty($field['description']) && false !== $field['desc_tip'] ? $field['description'] : '';
		$description = !empty($field['description']) && false === $field['desc_tip'] ? $field['description'] : '';
?>
		<p <?php echo wc_implode_html_attributes($wrapper_attributes); // WPCS: XSS ok. 
			?>>
			<label <?php echo wc_implode_html_attributes($label_attributes); // WPCS: XSS ok. 
					?>><?php echo wp_kses_post($field['label']); ?></label>
			<?php if ($tooltip) : ?>
				<?php echo wc_help_tip($tooltip); // WPCS: XSS ok. 
				?>
			<?php endif; ?>
			<select <?php echo wc_implode_html_attributes($field_attributes); // WPCS: XSS ok. 
					?>>
				<?php
				foreach ($field['options'] as $key => $value) {
					if (!is_array($value)) {
						echo '<option value="' . esc_attr($key) . '"' . esc_html(wc_selected($key, $field['value'])) . '>' . esc_html($value) . '</option>';
					} else {
						echo '<optgroup label="' . esc_attr($key) . '">';
						foreach ($value as $k => $val) {
							echo '<option value="' . esc_attr($k) . '"' . esc_html(wc_selected($k, $field['value'])) . '>' . esc_html($val) . '</option>';
						}
						echo '</optgroup>';
					}
				}
				?>
			</select>
			<?php if ($description) : ?>
				<span class="description"><?php echo wp_kses_post($description); ?></span>
			<?php endif; ?>
		</p>
<?php
	}
}


if (!function_exists('all_existing_wphp_product_ids')) {
	function all_existing_wphp_product_ids()
	{
		global $wpdb;
		$hp_ids = [];
		$table = $wpdb->prefix . 'postmeta';
		$query = 'SELECT meta_value from ' . $table . ' where meta_key="helloprint_external_product_id"';
		$results = $wpdb->get_results($query);
		foreach ($results as $res) {
			$hp_ids[] = $res->meta_value;
		}
		return $hp_ids;
	}
}


if (!function_exists("_helloprint_get_graphic_design_price")) {
	function _helloprint_get_graphic_design_price($productId = null)
	{
		$product_graphic_design_price = 0;
		$enable_product_design = get_post_meta($productId, 'helloprint_product_graphic_design_fee', true);
		if ($enable_product_design == 1) {
			$enable_product_design = true;
			$product_graphic_design_price = get_post_meta($productId, 'helloprint_product_graphic_design_price', true);
		} else if ($enable_product_design == -1) {
			$enable_product_design = false;
			$product_graphic_design_price = 0;
		} else {
			$enable_product_design = get_option("helloprint_enable_global_graphic_design");
			$product_graphic_design_price = ($enable_product_design) ? get_option("helloprint_global_graphic_design_price") : 0;
		}
		$enable_product_design = ($product_graphic_design_price > 0) ? $enable_product_design : false;
		return ['enabled' => $enable_product_design, 'price' => $product_graphic_design_price];
	}
}


if (!function_exists("helloprint_get_max_file_upload_size")) {
	function helloprint_get_max_file_upload_size()
	{
		$size = wp_max_upload_size();
		$base = log($size) / log(1024);
		$suffix = array("", "KB", "MB", "GB", "TB")[floor($base)];
		return pow(1024, $base - floor($base)) . $suffix;
	}
}

if (!function_exists("helloprint_get_checkout_country_code")) {
	function helloprint_get_checkout_country_code()
	{

		$customer = WC()->session->get('customer');
		$shipping_country = (!empty($customer['shipping_country'])) ? esc_html($customer['shipping_country']) : '';
		$billing_country = (!empty($customer['billing_country'])) ? esc_html($customer['billing_country']) : '';
		$current_user = wp_get_current_user();
		$returnCountry = '';
		if (!empty($shipping_country) || !empty($billing_country)) {
			$returnCountry = ($shipping_country) ?? $billing_country;
		} else if (!empty($current_user->billing_country)) {
			$returnCountry = $current_user->billing_country;
		} else {
			$countries_obj   = new WC_Countries();
			$returnCountry = $countries_obj->get_base_country();
		}
		return $returnCountry;
	}
}

if (!function_exists("add_helloprint_flash_notice")) {
	function add_helloprint_flash_notice($notice = "", $type = "warning", $dismissible = true)
	{
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option("helloprint_flash_notices", array());

		$dismissible_text = ($dismissible) ? "is-dismissible" : "";

		// We add our new notice.
		array_push($notices, array(
			"notice" => $notice,
			"type" => $type,
			"dismissible" => $dismissible_text
		));
		delete_option("helloprint_flash_notices");
		// Then we update the option with our notices array
		add_option("helloprint_flash_notices", $notices);
	}
}

if (!function_exists("helloprint_get_data_from_url")) {
	function helloprint_get_data_from_url($url = "")
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}

if (!function_exists("hp_is_pitchprint_plugin_active")) {
	function hp_is_pitchprint_plugin_active($response = "active")
	{
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$activePlugins = get_option('active_plugins', array());
		foreach ($activePlugins as $plugin) {
			if (strpos($plugin, "pitchprint.php") !== false) {
				if ($response == "root_url") {
					$pluginNameArray = explode("/", $plugin);
					return $pluginNameArray[0];
				}
				return true;
			}
		}
		return false;
	}
}

if (!function_exists("_get_helloprint_version")) {
	function _get_helloprint_version()
	{
		global $wp_version;
		$version = $wp_version;
		$plugin_base_arr = explode('/', plugin_basename(__FILE__));
		if (isset($plugin_base_arr)) {
			$base_name = $plugin_base_arr[0];
			foreach (\get_plugins() as $key => $value) {
				if (strpos($key, $base_name) === 0) {
					$version = $value['Version'];
				}
			}
		}
		return $version;
	}
}

if (!function_exists("_helloprint_calculate_scaled_margin")) {
	function _helloprint_calculate_scaled_margin($price = null, $scaled_margins = [], $default_margin = 0)
	{
		krsort($scaled_margins);
		foreach ($scaled_margins as $key => $margin) {
			if (floatval($price) >= floatval($key)) {
				return $margin;
			}
		}
		return $default_margin;
	}
}

if (!function_exists("_helloprint_global_margin")) {
	function _helloprint_global_margin($product_margin = 0)
	{
		if ($product_margin > 0) {
			return $product_margin;
		}
		$margin = get_option('helloprint_global_product_margin', 0);
		if (empty($margin) || $margin == '' || $margin == null) {
			$margin = 0;
		}
		return $margin;
	}
}

if (!function_exists("_helloprint_global_markup")) {
	function _helloprint_global_markup($product_markup = 0)
	{
		if ($product_markup > 0) {
			return $product_markup;
		}
		$markup = get_option('helloprint_global_product_markup', 0);
		if (empty($markup) || $markup == '' || $markup == null) {
			$markup = 0;
		}
		return $markup;
	}
}

if (!function_exists("_helloprint_get_pricing_tiers_info")) {
	function _helloprint_get_pricing_tiers_info($product_id = null, $roles = [], $product_margin_option = null, $margin_markup = "")
	{

		if (empty($margin_markup)) {
			$margin_markup = get_post_meta($product_id, 'helloprint_markup_margin', true);
		}
		
		$product_default_markup = get_post_meta($product_id, 'helloprint_product_markup', true);
		$product_default_margin = get_post_meta($product_id, 'helloprint_product_margin', true);
 		if ($product_margin_option == null) {
			if ($margin_markup == "markup") {
				$product_margin_option = get_post_meta($product_id, 'helloprint_product_markup_option', true);
				$product_default_markup_margin = $product_default_markup;
			} else {
				$product_margin_option = get_post_meta($product_id, 'helloprint_product_margin_option', true);
				$product_default_markup_margin = $product_default_margin;
			}
			
			if (empty($product_margin_option) && $product_margin_option != 0) {
				$product_margin_option = ($product_default_markup_margin > 0) ? 2 : 1;
			}
		}

		if ($product_margin_option == 1) {
			if ($margin_markup == "markup") {
				return ["is_scaling" => false, "default_margin" => _helloprint_global_markup(), "type" => "markup"];
			} else {
				return ["is_scaling" => false, "default_margin" => _helloprint_global_margin(), "type" => "margin"];
			}
		} else if ($product_margin_option == 2) {
			if ($margin_markup == "markup") {
				return ["is_scaling" => false, "default_margin" => $product_default_markup, "type" => "markup"];
			} else {
				return ["is_scaling" => false, "default_margin" => $product_default_margin, "type" => "margin"];
			}
			
		}
		global $wpdb;
		$productTierTableName = $wpdb->prefix . 'helloprint_product_pricing_tier';
		$product_tier = $wpdb->get_row("SELECT id,pricing_tier_id,profile from $productTierTableName WHERE product_id='$product_id'");
		if (empty($roles)) {
			$user = wp_get_current_user();
			$roles = (array) $user->roles;
		}
		if (!empty($product_tier) && (empty($product_tier->profile) || in_array($product_tier->profile, $roles))) {
			$tierTableName = $wpdb->prefix . 'helloprint_pricing_tiers';
			$tier_details = $wpdb->get_row("SELECT * from $tierTableName WHERE id='$product_tier->pricing_tier_id'");
			if (empty($tier_details)) {
				if ($margin_markup == "markup") {
					return ["is_scaling" => false, "default_margin" => _helloprint_global_markup($product_default_markup), "type" => "markup"];
				} else {
					return ["is_scaling" => false, "default_margin" => _helloprint_global_margin($product_default_margin), "type" => "margin"];
				}
			}
			if ($tier_details->enable_scaling == 1) {
				$tierScaleTableName = $wpdb->prefix . 'helloprint_scaled_pricing';
				$scaled_margins = $wpdb->get_results("SELECT * from $tierScaleTableName WHERE pricing_tier_id='$tier_details->id' ORDER BY price desc");
				$scaled_margins_array = [];
				foreach ($scaled_margins as $sc) {
					$scaled_margins_array[$sc->price] = $sc->margin;
				}
				return ["is_scaling" => true, "scaled_margins" => $scaled_margins_array, "default_margin" => $tier_details->default_markup, "type" => $margin_markup];
			}
			return ["is_scaling" => false, "default_margin" => $tier_details->default_markup, "type" => $margin_markup];
		} else {
			if ($margin_markup == "markup") {
				return ["is_scaling" => false, "default_margin" => _helloprint_global_markup($product_default_markup), "type" => "markup"];
			} else {
				return ["is_scaling" => false, "default_margin" => _helloprint_global_margin($product_default_margin), "type" => "margin"];
			}
		}
	}
}


