<?php



namespace HelloPrint\Inc\Services;

class ProductPriceService
{

    public function getProductPriceMargin($product_id, $price, $divide_by_hundred = false)
    {
        $price_to_pass = ($divide_by_hundred) ? round($price) / 100 : $price;
        $product_margin = $this->getProductMargin($product_id, $price_to_pass);
        $product_margin_markup = get_post_meta($product_id, 'helloprint_markup_margin', true);
        if ($product_margin_markup == "markup") {
            if ($product_margin > 0) {
                return  round((float)$price * (1 + ($product_margin/100)));
            }
            return round($price);
        }
        $markup = (100 - (float)$product_margin) / 100;
        return ($markup > 0) ? round((float)$price / $markup) : round($price);
        //return round((($product_margin + 100) / 100) * $price);
    }

    public function getProductMargin($product_id, $price = null)
    {
        $product_margin_markup = get_post_meta($product_id, 'helloprint_markup_margin', true);
        if ($product_margin_markup == "markup") {
            $product_margin_option = get_post_meta($product_id, 'helloprint_product_markup_option', true);
        } else {
            $product_margin_option = get_post_meta($product_id, 'helloprint_product_margin_option', true);
        }
        if (empty($product_margin_option)) {
            $product_margin_option = 0;
        }
        $product_margin_option = (int)$product_margin_option;
        if ($product_margin_option == 0) {
            $user = wp_get_current_user();
    		$roles = ( array ) $user->roles;
            $margin_options = _helloprint_get_pricing_tiers_info($product_id, $roles, 0);
            if (!$margin_options["is_scaling"]) {
                return $margin_options["default_margin"];
            } else {
                return _helloprint_calculate_scaled_margin($price, $margin_options["scaled_margins"], $margin_options["default_margin"]);
            }
        } else if ($product_margin_option == 2) {
            if ($product_margin_markup == "markup") {
				$product_margin = get_post_meta($product_id, 'helloprint_product_markup', true);
			} else {
				$product_margin = get_post_meta($product_id, 'helloprint_product_margin', true);
			}
        } else {
            $product_margin = ($product_margin_markup == "markup") ? $this->getGlobalMarkup() : $this->getGlobalMargin();
        }
        return (float)$product_margin;
    }

    public function getGlobalMargin()
    {
        $margin = get_option('helloprint_global_product_margin', 0);
        if (empty($margin) || $margin == '' || $margin == null) {
            $margin = 0;
        }
        return $margin;
    }

    public function getGlobalMarkup()
    {
        $markup = get_option('helloprint_global_product_markup', 0);
        if (empty($markup) || $markup == '' || $markup == null) {
            $markup = 0;
        }
        return $markup;
    }
}
