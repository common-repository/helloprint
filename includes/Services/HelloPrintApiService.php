<?php


namespace HelloPrint\Inc\Services;

use function Automattic\WooCommerce\GoogleListingsAndAds\Vendor\GuzzleHttp\json_encode;

class HelloPrintApiService
{

    private string $base_url, $api_key, $lang, $env_mode, $callback_url;

    public function __construct($api_key = '')
    {
        $language = get_locale();
        $langArr = explode("_", $language);
        $this->lang = !empty($langArr[0]) ? $langArr[0] : 'en';
        $env_mode = get_option('helloprint_env_mode', true);
        $this->env_mode = $env_mode ? 'test' : 'prod';
        $this->base_url = HELLOPRINT_API_URL;
        $api_key ? $this->api_key = $api_key : $this->api_key = get_option('helloprint_api_key', true);
    }

    public function get($url, $params = [])
    {
        global $wp_version;
        $response = wp_remote_get(
            $this->base_url . $url,
            [
                'timeout' => 45,
                'headers' => [
                    'x-api-key' => $this->api_key,
                    'x-api-source' => "wp" . $wp_version
                ],
                'body' => $params,
            ]
        );
        $this->updateLangFromResponse($response);
        return $response;
    }

    public function post($url, $params, $contentType = 'application/json')
    {
        global $wp_version;
        $headers = [
            'x-api-key' => $this->api_key,
            'x-api-source' => "wp" . $wp_version
        ];
        if (!empty($contentType)) {
            $headers['Content-Type'] = $contentType;
        }
        $response =  wp_remote_post($this->base_url . $url, [
            'timeout' => 45,
            'headers' => $headers,
            'body' => ($contentType == 'application/json') ? \json_encode($params) : $params,
            'method' => 'POST',
            'data_format' => 'body'
        ]);
        $this->updateLangFromResponse($response);
        return $response;
    }

    public function getResponseToJson($response)
    {
        $body = wp_remote_retrieve_body($response);
        if (is_wp_error($response)) {
            return [];
        }
        if (wp_remote_retrieve_response_code($response) == '403') {
            return [];
        }
        return json_decode($body, true);
    }

    public function getCategoriesForSelectOptions()
    {
        $response = $this->get('categories');
        $data = $this->getResponseToJson($response);
        if (is_array($data) && count($data) > 0) {
            foreach ($data['data'] as $category) {
                $categories[$category] = wp_kses(_translate_helloprint(ucfirst(str_replace(['-', '_'], ' ', $category)), 'helloprint'), false);
            }
            return $categories;
        }
        return [];
    }

    public function getCategoryProductForSelectOptions($categoryId)
    {
        $response = $this->get("categories/$categoryId");
        $data = $this->getResponseToJson($response);
        if ($data) {
            if (count($data) > 0) {
                foreach ($data['data']['products'] as $category) {
                    $categories[esc_attr($category['productKey'])] = wp_kses(_translate_helloprint($category['name'], 'helloprint'), false);
                }
                return $categories;
            }
        }
        return [];
    }

    public function getProductDetailForSelectOptions($productId)
    {
        $response = $this->get("products/$productId");
        $data = $this->getResponseToJson($response);
        if ($data) {
            if (count($data) > 0) {
                $attributes = [];
                $options = [];
                $i = 0;
                $available = false;
                $destinationCountries = [];
                if (isset($data['data'])) {
                    foreach ($data['data']['attributes'] as $attributeKey => $attribute) {
                        array_push($attributes, [
                            'id' => esc_attr($attributeKey),
                            'name' => isset($attribute['name'][$this->lang]) ? esc_attr($attribute['name'][$this->lang]) : esc_attr($attribute['name']['en'])
                        ]);
                        $options = [];
                        foreach ($attribute['values'] as $value) {
                            foreach ($value as $key => $innerValue) {
                                $key = esc_attr($key);
                                $options[$key]['name'] = isset($innerValue['name'][$this->lang]) ? esc_attr($innerValue['name'][$this->lang]) : esc_attr($innerValue['name']['en']);

                                // assign sub text
                                $options[$key]['subText'] = NULL;
                                if (isset($innerValue['subText'][$this->lang]) || isset($innerValue['subText']['en'])) {
                                    $options[$key]['subText'] = isset($innerValue['subText'][$this->lang]) ? esc_attr($innerValue['subText'][$this->lang]) : esc_attr($innerValue['subText']['en']);
                                }
                                // assign image
                                $options[$key]['image'] = NULL;
                                if (isset($innerValue['image'][$this->lang]['url']) || isset($innerValue['image']['en']['url'])) {
                                    $options[$key]['image'] = isset($innerValue['image'][$this->lang]['url']) ? esc_attr($innerValue['image'][$this->lang]['url']) : esc_attr($innerValue['image']['en']['url']);
                                }
                            }
                        }
                        $attributes[$i]['options'] = $options;
                        $i++;
                    }

                    $options = (isset($data['data']['options'])) ? $data['data']['options'] : [];
                    $available = isset($data['data']['available']) ? $data['data']['available'] : false;

                    if (isset($data['data']['destinationCountries']) && count($data['data']['destinationCountries'])) {
                        foreach ($data['data']['destinationCountries'] as $kk => $dest) {
                            if (isset($dest['name'][$this->lang]) || isset($dest['name']['en'])) {
                                array_push($destinationCountries, [
                                    'id' => esc_attr($kk),
                                    'name' => isset($dest['name'][$this->lang]) ? esc_attr($dest['name'][$this->lang]) : (isset($dest['name']['en']) ? esc_attr($dest['name']['en']) : ""),
                                    'flag_icon' => isset($dest['flagIcon']) ? esc_url($dest['flagIcon']) : "",
                                    'code' => isset($dest['isoCode']) ? esc_attr($dest['isoCode']) : ""
                                ]);
                            }
                        }
                    }
                }
                return ['attributes' => $attributes, 'options' => $options, 'available' => $available, 'destination_countries' => $destinationCountries];
            }
        }
        return [];
    }


    public function getProductVariantsFilter($externalProductId, $attributes, $productMargin, $deliveryType = '', $selectedAttribute = '', $productQuantity = '', $productId = null, $apprealQuantity = -1, $destination_country = '', $pricing_page = 1, $lazy_loading = false)
    {
        $product = wc_get_product($productId);
        if (!empty($product) && wc_tax_enabled() && get_option("woocommerce_tax_display_shop") == 'incl' && $product->get_tax_status() == 'taxable') {
            $tax = new \WC_Tax();
            $taxes = $tax->get_rates($product->get_tax_class());
            $rates = array_shift($taxes);
        }
        $taxRate = isset($rates['rate']) ? $rates['rate'] : 0;
        $selectedPosition = array_search($selectedAttribute, array_keys($attributes));
        $query = '';
        $originalAttributeOptions = $attributes['helloprint_options'];
        $originalAttributes =  $this->getProductDetailForSelectOptions($externalProductId);
        $oriAttrOptions = isset($originalAttributes['options']) ? $originalAttributes['options'] : [];
        $delivery_buffer_days = (get_option("helloprint_delivery_buffer_days") > 0 ) ? (int) get_option("helloprint_delivery_buffer_days") : 0;
        $newAttributes = [];
        $filterAttribute = [];
        $markup = ((100 - (float)$productMargin) / 100);
        $j = 0;
        $attributesKeys = [];
        $attributeOptions = [];
        $attributeOptionsK = [];
        $attributeFilters = [];
        $combination_not_found = false;
        $combination_not_found_msg = "";
        $per_page_pricing = get_option("helloprint_quantity_pricing_limit", 14);
        $start_pricing_from = ($pricing_page - 1) * $per_page_pricing;
        $pricing_last_index = $per_page_pricing + $start_pricing_from;
        if (isset($attributes['helloprint_options'])) {
            $customOptions = $attributes['helloprint_options'];
            unset($attributes['helloprint_options']);
        }

        if (isset($attributes['wphp_options'])) {
            if (empty($customOptions)) {
                $customOptions = $attributes['wphp_options'];
            }
            unset($attributes['wphp_options']);
        }
        $emptyAttr = true;
        foreach ($attributes as $vall) {
            if (!empty($vall) && ($vall != '0' || $vall != 0)) {
                $emptyAttr = false;
            }
        }
        $oriAttrs = isset($originalAttributes['attributes']) ? $originalAttributes['attributes'] : [];
        $attributesKeys = array_keys($attributes);
        $ifSelected = empty($oriAttrs) || !$emptyAttr;
        if (isset($attributes["undefined"])) {
            unset($attributes["undefined"]);
        }
        foreach ($attributes as $key => $attribute) {
            $j = array_search($key, $attributesKeys);
            if ($selectedAttribute) {
                if ($selectedPosition >= $j && $attribute != '0') {
                    $query = $query . 'attributes["' . $key . '"]="' . $attribute . '"&';
                    $filterAttribute[$key] = $attribute;
                } else {
                    array_push($newAttributes, $key);
                }
            } else {
                if ($attribute != '0') {
                    $filterAttribute[$key] = $attribute;
                    $query = $query . 'attributes["' . $key . '"]="' . $attribute . '"&';
                } else {
                    array_push($newAttributes, $key);
                }
            }
            if ($j > 0 && isset($attributeFilters[$j - 1])) {
                $attributeFilters[$j] = $attributeFilters[$j - 1];
            }
            if ($attribute != '0') {
                $attributeFilters[$j][$key] = $attribute;
            }
            //$attributesKeys[$j] = $key;
            //$j++;
        }

        if ($apprealQuantity >= 0) {
            foreach ($filterAttribute as $ky => $val) {
                if (str_starts_with($ky, 'appreal_')) {
                    unset($filterAttribute[$ky]);
                }
            }
        }
        if (empty($query)) {
            $query = 'null';
        }
        $variantQuery = [];
        if (!empty($destination_country)) {
            $variantQuery["destinationCountryCode"] = $destination_country;
        }
        $variantQuery["where"] = $query;
        
        $response = $this->get("products/$externalProductId/variants", $variantQuery);
        //print_r($response);
        $data = $this->getResponseToJson($response);
        //print_r($data);die();
        if ($data) {
            if (isset($data['data']) && is_countable($data['data']) && count($data['data']) > 0) {
                $variants = [];
                $quoteVariants = [];
                $originalSku = '';
                $newVariants = [];
                $i = 0;
                if (isset($data['data']['variantKey'])) {
                    $quoteVariants[$i]['variantKey'] = esc_attr($data['data']['variantKey']);
                }
                if (isset($data['data']['sku'])) {
                    $originalSku = esc_attr($data['data']['sku']);
                }
                foreach ($data['data'] as $variant) {
                    if (!empty($variant['attributes'])) {
                        if (count(array_diff($filterAttribute, $variant['attributes'])) == 0) {
                            if ($i == 0 && isset($variant['variantKey'])) {
                                $quoteVariants[$i]['variantKey'] = esc_attr($variant['variantKey']);
                            }
                            if ($i == 0 && isset($variant['sku'])) {
                                $originalSku = esc_attr($variant['sku']);
                            }
                            $i++;

                            foreach ($variant['attributes'] as $key => $attribute) {
                                if (in_array($key, $newAttributes)) {
                                    $variants[$key][$attribute] = esc_attr($attribute);
                                }
                            }
                        }

                        foreach ($variant['attributes'] as $key => $attribute) {
                            $arrKey = array_search($key, $attributesKeys, true);
                            $newAttrArray = isset($attributeFilters[$arrKey - 1]) ? $attributeFilters[$arrKey - 1] : null;
                            if ($arrKey > 0 && !empty($newAttrArray)) {
                                $flag = true;
                                foreach ($newAttrArray as $nKey => $att) {
                                    if (strtoupper(trim($variant['attributes'][$nKey])) !== strtoupper(trim($att))) {
                                        $flag = false;
                                    }
                                }
                                if ($flag == true) {
                                    $attributeOptionsK[$key][$attribute] = esc_attr($attribute);
                                }
                            } else {
                                $attributeOptionsK[$key][$attribute] = esc_attr($attribute);
                            }

                            if (!isset($attributeOptionsK[$key])) {
                                $attributeOptionsK[$key] = [];
                            }
                        }
                    } else {
                        if ($i == 0 && isset($variant['variantKey'])) {
                            $quoteVariants[$i]['variantKey'] = esc_attr($variant['variantKey']);
                        }
                        if ($i == 0 && isset($variant['sku'])) {
                            $originalSku = esc_attr($variant['sku']);
                        }
                    }
                }
                if (isset($originalAttributes['attributes'])) {
                    $originalAttributes = $originalAttributes['attributes'];
                }
                foreach ($originalAttributes as $orgKey => $originalAttribute) {
                    foreach ($variants as $key => $variant) {
                        if ($originalAttribute['id'] == $key) {
                            foreach ($originalAttribute['options'] as $optionKey => $orgOption) {
                                $search = array_search($optionKey, $variant);
                                if ($search) {
                                    if (isset($orgOption['name'])) {
                                        $orgOption['name'] = wp_kses(_translate_helloprint($orgOption['name'], 'helloprint'), false);
                                    }
                                    $newVariants[$key][$search] = $orgOption;
                                }
                            }
                        }
                    }

                    foreach ($attributeOptionsK as $key => $opt) {
                        if ($originalAttribute['id'] == $key) {
                            foreach ($originalAttribute['options'] as $optionKey => $orgOption) {
                                $search = array_search($optionKey, $opt);
                                if ($search) {
                                    $attributeOptions[$key][$search] = esc_attr($search);
                                }
                            }
                        }
                    }
                }
                $emptyService = empty($deliveryType);
                $deliveryType = empty($deliveryType) ? 'saver' : $deliveryType;
                if (!empty($customOptions)) {
                    $options = [];
                    foreach ($customOptions as $key => $opt) {
                        $options[] = ['code' => $key, 'value' => $opt];
                    }
                    $quoteVariants[0]['options'] = $options;
                }

                if ($apprealQuantity > 0) {
                    $quoteVariants[0]['quantity'] = $apprealQuantity;
                }
                $min_max_qty_message = '';
                $availableAllquantities = [];
                if (!empty($originalSku) && (count($newVariants) <= 0 || ($apprealQuantity >= 0 && !empty($oriAttrOptions)))) {
                    $responsevar = $this->get("products/$externalProductId/variants", ["sku" => $originalSku, 'includeAvailableQtys' => true]);
                    $datavar = $this->getResponseToJson($responsevar);
                    if (isset($datavar['data']['availableQtys']) && count($datavar['data']['availableQtys']) > 0) {
                        $availableAllquantities = array_column($datavar['data']['availableQtys'], 'quantity');
                        sort($availableAllquantities);
                        $qts = $datavar['data']['availableQtys'];
                        $count = count($datavar['data']['availableQtys']);
                        if (!empty($oriAttrOptions)) {
                            $max = max(array_column($qts, 'quantity'));
                            $min = min(array_column($qts, 'quantity'));
                            $min_max_qty_message = wp_kses(_translate_helloprint("Min:", 'helloprint'), false) . " " . $min . "/" . wp_kses(_translate_helloprint("Max:", 'helloprint'), false)  . " " . $max;
                        }
                        
                    }
                }
                $availableAllquantities = array_unique($availableAllquantities);
                $is_next_pricing_page = count($availableAllquantities) > $pricing_last_index;
                if ($lazy_loading === true || $lazy_loading === "true") {
                    $per_page_pricing = count($availableAllquantities);
                    $is_next_pricing_page = false;
                }
                $price_quantities_array = array_values(array_slice($availableAllquantities, $start_pricing_from, $per_page_pricing));
                if ($apprealQuantity > 0) {
                    $price_quantities_array = [$apprealQuantity];
                    $is_next_pricing_page = false;
                }
                $widthExists = false;
                if (!empty($originalAttributeOptions)) {
                    $widthExists = array_key_exists('width', $originalAttributeOptions);
                }
                $getQuantity = $widthExists ? (!empty($originalAttributeOptions['width']) && !empty($originalAttributeOptions['height'])) : true;
                if ($ifSelected && count($newVariants) <= 0 && $apprealQuantity <> 0 && $getQuantity) {
                    $res = $this->returnsQuotesServiceLevel($emptyService, $deliveryType, $quoteVariants, $destination_country, $price_quantities_array);
                    if (isset($res)) {
                        if (isset($res['data'])) {
                            if (count($res['data']['items']) > 0) {
                                $margin_options = _helloprint_get_pricing_tiers_info($productId);
                                $serviceLevel = [];
                                $quotes = [];
                                $quantities = [];
                                foreach ($res['data']['items'] as $variants) {
                                    foreach ($variants as $variant) {
                                        foreach ($variant as $item) {
                                            $quantities[$item['quantity']]['quantity'] = $item['quantity'];
                                            $originalCentAmountExclTax = (float)$item['prices']['centAmountExclTax'];
                                            $originalCentAmountInclTax = $originalCentAmountExclTax * (1 + ($taxRate / 100));
                                            
                                            $exclTaxWithMargin = $this->_calculate_price_after_markup($originalCentAmountExclTax, $margin_options);
                                            $quantities[$item['quantity']]['centAmountExclTax'] = wc_price(round($exclTaxWithMargin) / 100);
                                            $inclTaxWithMargin = $exclTaxWithMargin * (1 + ($taxRate / 100));
                                            $quantities[$item['quantity']]['centAmountInclTax'] = wc_price(round($inclTaxWithMargin) / 100);

                                                // set original value without margin  to test 
                                            $quantities[$item['quantity']]['originalcentAmtExclTax'] = wc_price($originalCentAmountExclTax / 100);
                                            $quantities[$item['quantity']]['originalcentAmountInclTax'] = wc_price($originalCentAmountInclTax / 100);
                                            
                                        }
                                    }
                                }
                                $attributePosition = array_search($selectedAttribute, array_column($originalAttributes, 'id'));
                                $attributesCount = count($attributes) - 1;
                                if (count($quantities) > 0) {
                                    asort($quantities);
                                    if ($productQuantity == '') {
                                        if ($selectedAttribute == '' || $attributePosition == $attributesCount) {
                                            $productQuantity = array_values($quantities)[0]['quantity'];
                                        }
                                    }
                                }
                                if (isset($quoteVariants[0]['serviceLevel'])) {
                                    unset($quoteVariants[0]['serviceLevel']);
                                }
                                if (!empty($price_quantities_array)) {
                                    $quoteVariants[0]['quantity'] = $price_quantities_array;
                                }
                                $quoteReqParams = ['items' => $quoteVariants];
                                if (!empty($destination_country)) {
                                    $quoteReqParams['destinationCountryCode'] = $destination_country;
                                }
                                $serviceLevelRes =  $this->post('quotes', $quoteReqParams);

                                $serviceLevelRes =  $this->getResponseToJson($serviceLevelRes);
                                if (isset($serviceLevelRes['data'])) {
                                    foreach ($serviceLevelRes['data']['items'] as $key => $variants) {
                                        foreach ($variants as $variantkey => $quoteDetails) {
                                            foreach ($quoteDetails as $itemKey => $item) {
                                                if ($item['serviceLevel'] && !empty($item['serviceLevel']) && $item['serviceLevel'] != null) {
                                                    $serviceLabelObj = [];
                                                    if (isset($item['times']) && !empty($item['times'])) {
                                                        $delivery_total_days = (int) $item['times']['minDeliveryDays'] + $delivery_buffer_days;
                                                        $serviceLabelObj = [
                                                            'label' => wp_kses(_translate_helloprint(ucwords($item['serviceLevel']), "helloprint"), true) . ' - ' . $delivery_total_days . ' ' . wp_kses(_translate_helloprint("Day(s)", 'helloprint'), true),
                                                            'value' => $item['serviceLevel'],
                                                            'days' => $delivery_total_days
                                                        ];
                                                    } else {
                                                        $serviceLabelObj = [
                                                            'label' => wp_kses(_translate_helloprint(ucwords($item['serviceLevel']), "helloprint"), true),
                                                            'value' => $item['serviceLevel'],
                                                            'days' => 0
                                                        ];
                                                    }

                                                    if (!in_array($serviceLabelObj, $serviceLevel) && (array_search($item['serviceLevel'], array_column($serviceLevel, 'value')) === FALSE)) {
                                                        array_push($serviceLevel, $serviceLabelObj);
                                                    }
                                                    if ($item['serviceLevel'] == $deliveryType) {
                                                        $quotes[] = $item;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    array_multisort(array_map(function ($element) {
                                        return $element['days'];
                                    }, $serviceLevel), SORT_NUMERIC, $serviceLevel);
                                }

                                foreach ($quotes as $key => $quote) {
                                    $centAmountExclTax = (float)$quote['prices']['centAmountExclTax'];
                                    $centAmountInclTax = $centAmountExclTax * (1 + ($taxRate / 100));
                                    $exclCentTaxWithMargin = $this->_calculate_price_after_markup($centAmountExclTax, $margin_options);
                                    $quotes[$key]['prices']['centAmountExclTax'] = round((float)$exclCentTaxWithMargin);
                                    $inclCentTaxWithMargin = $exclCentTaxWithMargin * (1 + ($taxRate / 100));
                                    $quotes[$key]['prices']['centAmountInclTax'] = round($inclCentTaxWithMargin);

                                        // set original value without margin  to test 
                                    $quotes[$key]['prices']['origcentAmountExclTax'] = round((float)$quote['prices']['centAmountExclTax']);
                                    $quotes[$key]['prices']['origcentAmountInclTax'] = round($centAmountInclTax);
                                    

                                    if (empty($quotes[$key]['serviceLevel']) || $quotes[$key]['serviceLevel'] == null) {
                                        unset($quotes[$key]);
                                        array_splice($quotes, 0, 0);
                                    }
                                }
                                if (empty($quotes)) {
                                    if (!isset($attributes[$selectedAttribute]) || empty($attributes[$selectedAttribute]) || $attributes[$selectedAttribute] === 0) {
                                        if (isset($attributeOptions[$selectedAttribute]) && empty($newVariants[$selectedAttribute])) {
                                            $newVariants[$selectedAttribute] = $attributeOptions[$selectedAttribute];
                                        }
                                    }
                                    if (empty($newVariants) && count($attributeOptions) == 1) {
                                        $newVariants = $attributeOptions;
                                    }
                                }
                                
                                if (empty($quotes) && empty($newVariants) && !empty($attributes) && $apprealQuantity <> 0) {
                                    $combination_not_found = true;
                                    if (!empty($destination_country)) {
                                        $combination_not_found_msg = wp_kses(_translate_helloprint("Selected combination is not available in ". $destination_country, "helloprint"), false);
                                    } else {
                                        $combination_not_found_msg = wp_kses(_translate_helloprint("Selected combination is not available.", "helloprint"), false);
                                    }
                                }

                                return [
                                    'quotes' => $quotes,
                                    'quantities' => $quantities,
                                    'currency' => $res['data']['currency'],
                                    'serviceLevel' => $serviceLevel,
                                    'variants' => $newVariants,
                                    'attributeOptions' => $attributeOptions,
                                    'tax_rate' => $taxRate,
                                    'min_max_qty_message' => !empty($min_max_qty_message) ? $min_max_qty_message : '',
                                    "combination_not_found" => $combination_not_found,
                                    "combination_not_found_msg" => $combination_not_found_msg,
                                    "is_next_pricing_page" => $is_next_pricing_page,
                                    "next_pricing_page" => ($is_next_pricing_page) ? ($pricing_page + 1) : $pricing_page,
                                    "current_pricing_page" => $pricing_page
                                ];
                            }
                        }
                    }
                }
                if (!isset($attributes[$selectedAttribute]) || empty($attributes[$selectedAttribute]) || $attributes[$selectedAttribute] === 0) {
                    if (isset($attributeOptions[$selectedAttribute]) && empty($newVariants[$selectedAttribute])) {
                        $newVariants[$selectedAttribute] = $attributeOptions[$selectedAttribute];
                    }
                }
                if (empty($newVariants) && count($attributeOptions) == 1) {
                    $newVariants = $attributeOptions;
                }
                if (empty($newVariants) && !empty($attributes) && $apprealQuantity <> 0) {
                    $combination_not_found = true;
                    if (!empty($destination_country)) {
                        $combination_not_found_msg = wp_kses(_translate_helloprint("Selected combination is not available in ". $destination_country, "helloprint"), false);
                    } else {
                        $combination_not_found_msg = wp_kses(_translate_helloprint("Selected combination is not available.", "helloprint"), false);
                    }
                }
                return [
                    'quotes' => [],
                    'quantities' => [],
                    'currency' => '',
                    'serviceLevel' => [],
                    'variants' => $newVariants,
                    'attributeOptions' => $attributeOptions,
                    'tax_rate' => $taxRate,
                    'min_max_qty_message' => !empty($min_max_qty_message) ? $min_max_qty_message : '',
                    "combination_not_found" => $combination_not_found,
                    "combination_not_found_msg" => $combination_not_found_msg,
                    "is_next_pricing_page" => false,
                    "next_pricing_page" => 1,
                    "current_pricing_page" => $pricing_page
                ];
            }
        }
        return [
            'quotes' => [],
            'quantities' => [],
            'currency' => '',
            'serviceLevel' => [],
            'variants' => [],
            'attributeOptions' => $attributeOptions,
            'tax_rate' => $taxRate,
            'min_max_qty_message' => !empty($min_max_qty_message) ? $min_max_qty_message : '',
            "is_next_pricing_page" => false,
            "next_pricing_page" => 1,
            "current_pricing_page" => $pricing_page
        ];
    }

    private function returnsQuotesServiceLevel($emptyService, &$deliveryType, $quoteVariants, $destination_country = '', $available_quantities = [])
    {

        $res =  $this->sendRequestToQuote($quoteVariants, $deliveryType, $destination_country, $available_quantities);
        if (empty($res['data']) && ($emptyService || $deliveryType == 'saver')) {
            if (!isset($res['data']) || empty($res['data'])) {
                $deliveryType = 'standard';
                $res =  $this->sendRequestToQuote($quoteVariants, $deliveryType, $destination_country, $available_quantities);
            }

            if (!isset($res['data']) || empty($res['data'])) {
                $deliveryType = 'express';
                $res =  $this->sendRequestToQuote($quoteVariants, $deliveryType, $destination_country, $available_quantities);
            }
        }
        return $res;
    }

    private function sendRequestToQuote($quoteVariants, $deliveryType, $destination_country = '', $available_quantities = [])
    {
        $quoteVariants[0]['serviceLevel'] = $deliveryType;
        if (!empty($available_quantities)) {
            $quoteVariants[0]['quantity'] = $available_quantities;
        }
        $quoteParams = [
            'items' => $quoteVariants
        ];
        if (!empty($destination_country)) {
            $quoteParams['destinationCountryCode'] = $destination_country;
        }
        
        $quoteRes =  $this->post('quotes', $quoteParams);
        return $this->getResponseToJson($quoteRes);
    }

    public function createOrder($order)
    {
        $logger = wc_get_logger();
        $order_json = json_decode($order, true);
        $orderItems = [];
        $helloprint_order_prefix = get_option("helloprint_order_prefix", "wp-");
        $organization = $order->get_meta('organization', true);
        if (is_array($organization)) {
            $organization = $organization[0];
        }
        if (!empty($organization)) {
            $helloprint_order_prefix .= $organization . "-";
        }
        foreach ($order->get_items() as $key => $item) {
            $itemId = (int)esc_attr($item->get_id());
            $item_quantity = $item->get_quantity();
            $helloprint_prefer_file = wc_get_order_item_meta($itemId, "helloprint_preset_prefer_files", true);
            $product = wc_get_product($item['product_id']);
            $product_variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';
            $item = json_decode($item['helloprint_product_setup'], true);
            $total_delivery_days = !empty($item["total_delivery_days"]) ? $item["total_delivery_days"] : '';
            $wphp_product_id = get_post_meta($product->get_id(), "helloprint_external_product_id", true);
            $fileItems = '';
            $zipname = 'wphp-' . $helloprint_order_prefix . $order->get_id() . '-' . $itemId . '.zip';
            $variantKey = ($item['helloprint_variant_key']) ?? '';
            $quantity = ($item['quantity']) ?? '';
            $serviceLevel = ($item['delivery_option']) ?? '';
            $sku = !empty(esc_attr($item['sku'])) ? esc_attr($item['sku']) : esc_attr($product->get_sku()); 
         
            // Check if product has variation.
            if ($product_variation_id) { 
                $variation_product = wc_get_product($product_variation_id);
                $sku = $variation_product->get_sku();  
            }
            global $wpdb;
            $preset_tableName = $wpdb->prefix . 'helloprint_order_presets';
            $line_item_tableName = $wpdb->prefix . 'helloprint_order_line_item_presets';
            $line_item_public_file_tableName = $wpdb->prefix . 'helloprint_order_line_public_files';
            $line_item_file_tableName = $wpdb->prefix . 'helloprint_order_line_preset_files';
            $lineItemPreset = $wpdb->get_results("SELECT $line_item_tableName.*, $preset_tableName.helloprint_variant_key, $preset_tableName.file_url  from $line_item_tableName INNER JOIN $preset_tableName ON $line_item_tableName.preset_id = $preset_tableName.id where $line_item_tableName.line_item_id = '$itemId' and ($preset_tableName.helloprint_item_sku = '$sku' OR $preset_tableName.helloprint_item_sku = '')");

            $publicPresetFiles = [];
            $lineItemPreset = ($lineItemPreset[0]) ?? [];

            if (empty($lineItemPreset) && !empty($sku)) {
                $lineItemPreset = $wpdb->get_results("SELECT $preset_tableName.*  from $preset_tableName where helloprint_item_sku = '$sku'");
                $lineItemPreset = ($lineItemPreset[0]) ?? [];
                if (!empty($lineItemPreset)) {
                    $lineItemPreset->service_level = $lineItemPreset->default_service_level;
                    $lineItemPreset->quantity = ($product->get_type() == 'helloprint_product') ? $lineItemPreset->default_quantity : $item_quantity;
                    $presetDefaultFile = $lineItemPreset->file_url;
                    $lineItemPreset->options = $lineItemPreset->default_options;
                } else {
                    $logger->info("Place order: No preset file for order item" .  wp_json_encode($product));
                }
            }
            if (!empty($lineItemPreset)) {
                if (isset($item['custom_options'])) {
                    unset($item['custom_options']);
                }
                if (isset($item['options']['appreal_size'])) {
                    unset($item['options']['appreal_size']);
                }
                if (!empty($lineItemPreset->helloprint_variant_key))
                    $variantKey = $lineItemPreset->helloprint_variant_key;
                if (!empty($lineItemPreset->service_level))
                    $serviceLevel = $lineItemPreset->service_level;
                if (!empty($lineItemPreset->quantity))
                    $quantity = $lineItemPreset->quantity;
                if (!empty($lineItemPreset->options)) {
                    $options_array = json_decode($lineItemPreset->options, true);
                    if (!empty($options_array["custom_options"])) {
                        $item['custom_options'] = $options_array["custom_options"];
                    } elseif (!empty($options_array["appreal_sizes"])) {
                        $item['options']['appreal_size'] = $options_array["appreal_sizes"];
                    }
                    
                } 
            }

            $homePath = ABSPATH;
            $baseFolder = $homePath . 'wp-content/uploads/print-tmp/';
            if (!file_exists($baseFolder)) {
                mkdir($baseFolder, 0755, true);
                $logger->info('Place order: folder created for zip file');
            }
            $zipPath = $baseFolder . $zipname;
            if (file_exists($zipPath)) {
                unlink($zipPath);
                $logger->info('Place order: Duplicate zip file deleted');
            }

            $zip = new \ZipArchive;
            $zip->open($zipPath, \ZIPARCHIVE::CREATE);
            $logger->info('Place order: Zip file opened');
            $fileCount = 0;
            $presetFiles = $wpdb->get_results("SELECT * from $line_item_file_tableName where line_item_id = '$itemId'");
            if (!empty($presetFiles) && (empty($helloprint_prefer_file) || $helloprint_prefer_file == "upload_files")) {
                foreach ($presetFiles as $key => $file) {
                    if ($file->file_url != '') {
                        if (strpos($file->file_url, "//") === FALSE) {
                            $fileUrl = $homePath . ltrim($file->file_url, "/");
                            $zip->addFile($fileUrl, basename($fileUrl));
                            $logger->info('Place order: ' . $file->file_url . " file added to zip");
                            $fileCount++;
                        } else {
                            $publicPresetFiles[] = $file->file_url;
                        }
                        
                    }
                }
            } else if(!empty($presetDefaultFile) && (empty($helloprint_prefer_file) || $helloprint_prefer_file == "upload_files")) {
                $fileUrl = $homePath . ltrim($presetDefaultFile, "/");
                $zip->addFile($fileUrl, basename($fileUrl));
                $fileCount++;
            }
            $is_preset_artwork = ((empty($helloprint_prefer_file) && $fileCount == 0) || $helloprint_prefer_file == "hp_preset_artwork");
            if (empty($lineItemPreset) && !empty($wphp_product_id) && $is_preset_artwork) {
                $hpPresets = $wpdb->get_results("SELECT * from $preset_tableName where product_type = 'hp' and helloprint_product_id='$wphp_product_id' ");
                if (!empty($hpPresets)) {
                    foreach ($hpPresets as $key => $file) {
                        if ($file->file_url != '') {
                            if (strpos($file->file_url, "//") === FALSE) {
                                $fileUrl = $homePath . ltrim($file->file_url, "/");
                                $zip->addFile($fileUrl, basename($fileUrl));
                                $logger->info('Place order: ' . $file->file_url . " file added to zip");
                                $fileCount++;
                            } else {
                                $publicPresetFiles[] = $file->file_url;
                            }
                        }
                        
                    }
                }
            }

            $zip->close();
            if ($fileCount > 0) {
                $fileItems = \get_site_url() . '/wp-content/uploads/print-tmp/' . $zipname;
                $logger->info('Place order: Zip file created');
            }

            $publicFiles = $wpdb->get_results("SELECT * from $line_item_public_file_tableName where line_item_id = '$itemId'");
            if (!empty($publicFiles[0]->public_file_url)) {
                if (!empty($fileItems)) {
                    $fileItems .= ", ";
                }
                $fileItems .= $publicFiles[0]->public_file_url;
            }

            if (!empty($publicPresetFiles)) {
                foreach ($publicPresetFiles as $p_file) {
                    if (!empty($fileItems)) {
                        $fileItems .= ", ";
                    }
                    $fileItems .= $p_file;
                }
            }
            
            $pp_item_string = \wc_get_order_item_meta($itemId, '_w2p_set_option', true);
            if (!empty($pp_item_string)) {
                $json = \urldecode($pp_item_string);
                $array = \json_decode($json, true);
                if (!empty($array['distiller']) && !empty($array['projectId'])) {
                    $pp_url = $array['distiller'] . "/?id=" . $array['projectId'];
                    if (!empty($fileItems)) {
                        $fileItems .= ", ";
                    }
                    $fileItems .= $pp_url;
                }
            }
            if (!empty($variantKey) && !empty($quantity)) {
                $orderItem = [
                    'itemReferenceId' => $helloprint_order_prefix . $order_json['id'] . '-' . $itemId,
                    'variantKey' => $variantKey,
                    'quantity' => $quantity,
                    'serviceLevel' => $serviceLevel,
                    'fileUrl' => $fileItems
                ];

                if (!empty($item['custom_options'])) {
                    $options = [];
                    if (!is_array($item['custom_options'])) {
                        $item['custom_options'] = json_decode($item['custom_options'], true);
                    }
                    if (is_array($item['custom_options'])) {
                        foreach ($item['custom_options'] as $key => $opt) {
                            $options[] = ['code' => strtolower($key), 'value' => $opt];
                        }
                    }
                    
                    $orderItem['options'] = $options;
                }

                if (!empty($item["destination_country"])) {
                    $orderItem["destination_country"] = $item["destination_country"];
                }

                if (!empty($total_delivery_days)) {
                    $orderItem["delivery_days"] = $total_delivery_days;
                }

                if (!empty($item['options']['appreal_size']) && is_array($item['options']['appreal_size'])) {
                    $apprealSizes = $item['options']['appreal_size'];
                    $finalSizesString = "";
                    $finalSizeArr = [];
                    foreach ($apprealSizes as $key => $opt) {
                        $finalSizesString .= strtolower($key) . "[";
                        $interArr = [];
                        foreach ($opt as $oKey => $oVal) {
                            if ($oVal > 0) {
                                $interArr[] = strtolower($oKey) . ":" . $oVal;
                                $finalSizeArr[] = [
                                    "quantity" => $oVal,
                                    "size" => strtoupper($oKey),
                                    "type" => (strtolower($key) == "quantity") ? "unisex" : strtolower($key)
                                ];
                            }
                        }
                        $finalSizesString .= implode("; ", $interArr);
                        $finalSizesString .= "]";
                    }
                    if (!isset($orderItem['options'])) {
                        $orderItem['options'] = [];
                    }
                    $appSize = ['code' => 'apparelSize', 'value' => $finalSizeArr];
                    $orderItem['options'][] = $appSize;
                }
                $logger->info('Place order: Order item detail: ' . wp_json_encode($orderItem));
                $orderItems[] = $orderItem;
            }
        }
        $shipOrbilling = $order_json['shipping']['first_name'] ? 'shipping' : 'billing';
        $callBackUrl = get_site_url() . '/?rest_route=/helloprint/v1/complete_order_from_helloprint_callback';
        $removeShippingEmailAddress = get_option("helloprint_shipping_email");
        
        $shippingDetails = [
            'companyName' => ($order_json[$shipOrbilling]['company']) ?? '',
            'firstName' => $order_json[$shipOrbilling]['first_name'],
            'lastName' => $order_json[$shipOrbilling]['last_name'],
            'addressLine1' => $order_json[$shipOrbilling]['address_1'],
            'addressLine2' => $order_json[$shipOrbilling]['address_2'],
            'postcode' => $order_json[$shipOrbilling]['postcode'],
            'city' => $order_json[$shipOrbilling]['city'],
            'country' => $order_json[$shipOrbilling]['country'],
            'phone' => !empty($order_json[$shipOrbilling]['phone']) ? $order_json[$shipOrbilling]['phone'] : (isset($order_json['billing']['phone']) ? $order_json['billing']['phone'] : ''),
            // 'email' => ($order_json['billing']['email']) ?? '',
        ];
        if(!$removeShippingEmailAddress){
            $shippingDetails['email']=($order_json['billing']['email']) ?? '';
        }
      
        $logger->info('Place order: Shipping detail: ' . wp_json_encode($shippingDetails));
        $order_reference_id = $helloprint_order_prefix . $order_json['id'];
        if (true === WP_DEBUG) {
            error_log("order Items details :: " . \json_encode($orderItems));
            error_log("shipping details :: " . \json_encode($shippingDetails));
            error_log("Order reference  id :: " . $order_reference_id);
        }
        if (empty($orderItems) || empty($order_json[$shipOrbilling])) {
            return ['status' => 'VALIDATION_ERROR'];
        }
        $logger->info('Place order: Order placed');

        $order_array = [
            'mode' => $this->env_mode,
            'orderReferenceId' => $order_reference_id,
            'shipping' => $shippingDetails,
            'orderItems' => $orderItems,
            'callbackUrls' => [
                $callBackUrl
            ]
        ];
        if (\is_plugin_active("woocommerce-gateway-purchase-order/woocommerce-gateway-purchase-order.php") && !empty(get_post_meta($order_json['id'], '_po_number', true))) {
            $order_array["metaData"] = [
                "purchaseOrder" => get_post_meta($order_json['id'], '_po_number', true)
            ];
        }
        if (true === WP_DEBUG) {
            error_log("Final Order Request :: " . \json_encode($order_array));
        }
        $logger->info('Place order: final order request : ' . wp_json_encode($order_array));
        return $this->post('orders', $order_array);
    }

    public function getProductDetailsForLoad($productId)
    {
        $response = $this->get("products/$productId");
        $data = $this->getResponseToJson($response);

        if ($data) {
            if (count($data) > 0 && !empty($data["data"])) {
                $returnData = [];
                $returnData['description'] = isset($data['data']['description'][$this->lang]) ? esc_attr($data['data']['description'][$this->lang]) : esc_attr($data['data']['description']['en']);
                $returnData['product_name'] = isset($data['data']['productName'][$this->lang]) ? esc_attr($data['data']['productName'][$this->lang]) : esc_attr($data['data']['productName']['en']);
                $returnData['preview_image'] = NULL;
                $returnData['gallery_images'] = NULL;
                if (!empty($data['data']['images'])) {
                    $returnData['preview_image'] = isset($data['data']['images'][0]['url']) ? esc_url($data['data']['images'][0]['url']) : NULL;
                    foreach ($data['data']['images'] as $k => $img) {
                        if (isset($img['url'])) {
                            $returnData['gallery_images'][$k] = esc_url($img['url']);
                        }
                    }
                }
                return $returnData;
            }
        }
        return [];
    }

    public function getOrderDetails($requestId)
    {
        $response = $this->get("orders/requestId=" . $requestId);
        $data = $this->getResponseToJson($response);
        return $data;
    }

    private function updateLangFromResponse($response)
    {
        if (!empty(wp_remote_retrieve_header($response, 'x-api-language-default'))) {
            $this->lang = wp_remote_retrieve_header($response, 'x-api-language-default') ? wp_remote_retrieve_header($response, 'x-api-language-default') : 'en';
        }
    }

    public function getAllProducts()
    {
        $response = $this->get("products");
        $data = $this->getResponseToJson($response);
        $products = [];
        if ($data) {
            if (count($data) > 0) {
                foreach ($data['data'] as $product) {
                    $productName = isset($product['productName'][$this->lang]) ? $product['productName'][$this->lang] : $product['productName']['en'];
                    $category = isset($product['categoryName'][$this->lang]) ? $product['categoryName'][$this->lang] : $product['categoryName']['en'];
                    $category = esc_attr(trim(ucwords(str_replace("-", " ", $category))));
                    if (!empty($productName)) {
                        $products[$category][esc_attr($product['productKey'])] = esc_attr($productName);
                    }
                }
                ksort($products);
                return $products;
            }
        }
        return [];
    }

    public function cancelOrder($orderId = '')
    {
        return $this->post('orders/' . $orderId . '/cancel', []);
    }

    public function getProductPdfTemplates($productId)
    {
        $response = $this->get("products/$productId/templates");
        $data = $this->getResponseToJson($response);
        if ($data) {
            if (count($data) > 0) {
                $templates = [];
                $i = 0;
                if (isset($data['data'])) {
                    foreach ($data['data'] as $key => $temp) {
                        $single_template = [];
                        $single_template['name'] = isset($temp['name'][$this->lang]) ? $temp['name'][$this->lang] : $temp['name']['en'];
                        if (!empty($temp['pdf']['url'])) {
                            $pdf_url = $temp['pdf']['url'];
                            $pdf_file_name = $temp['pdf']['fileName'];
                            if (is_array($pdf_url)) {
                                $pdf_url = isset($pdf_url[$this->lang]) ? $pdf_url[$this->lang] : $pdf_url['en'];
                            }
                            if (is_array($pdf_file_name)) {
                                $pdf_file_name = isset($pdf_file_name[$this->lang]) ? $pdf_file_name[$this->lang] : $pdf_file_name['en'];
                            }
                            $single_template['pdf']['url'] = esc_url($pdf_url);
                            $single_template['pdf']['file_name'] = esc_attr($pdf_file_name);
                        }

                        if (!empty($temp['indd']['url'])) {
                            $indd_url = $temp['indd']['url'];
                            $indd_file_name = $temp['indd']['fileName'];
                            if (is_array($indd_url)) {
                                $indd_url = isset($indd_url[$this->lang]) ? $indd_url[$this->lang] : $indd_url['en'];
                            }
                            if (is_array($indd_file_name)) {
                                $indd_file_name = isset($indd_file_name[$this->lang]) ? $indd_file_name[$this->lang] : $indd_file_name['en'];
                            }
                            $single_template['indd']['url'] = esc_url($indd_url);
                            $single_template['indd']['file_name'] = esc_attr($indd_file_name);
                        }
                        array_push($templates, $single_template);
                    }
                    return $templates;
                }
            }
        }

        return [];
    }

    public function validateApiKey($orderId = '')
    {
        return $this->get('identity?src=wordpress');
    }

    public function get_qts_and_levels($variant_key = '')
    {
        $serviceLevelRes =  $this->post('quotes', [
            'items' => [
                0 => [
                    'variantKey' => $variant_key
                ]
            ]
        ]);
        $quantities = [];
        $serviceLevels = [];
        $sku = '';
        $serviceLevelRes =  $this->getResponseToJson($serviceLevelRes);
        if (isset($serviceLevelRes['data'])) {
            foreach ($serviceLevelRes['data']['items'] as $key => $variants) {
                foreach ($variants as $variantkey => $quoteDetails) {
                    foreach ($quoteDetails as $itemKey => $item) {
                        if (!empty($item['serviceLevel'])) {
                            $serviceLevels[esc_attr($item['serviceLevel'])] =  esc_attr(ucwords($item['serviceLevel']));
                        }

                        if (!empty($item['quantity'])) {
                            $quantities[] =  esc_attr($item['quantity']);
                        }

                        if (!empty($item['sku'])) {
                            $sku =  esc_attr($item['sku']);
                        }
                    }
                }
            }
        }

        // to add options(custom sizes, appreal sizes etc) 
        $variant_arr = explode("~", $variant_key);
        $productId = ($variant_arr[0]) ?? "";
        $options = [];
        if (!empty($quantities)) {
            $product_res = $this->get("products/$productId");
            $data = $this->getResponseToJson($product_res);
        }
        $options = (isset($data['data']['options'])) ? $data['data']['options'] : [];

        return ['quantities' => array_unique($quantities), "options" => $options, 'service_levels' => array_unique($serviceLevels), 'sku' => $sku];
    }

    public function getAllSKUsByProductKey($externalProductId)
    {
        $response = $this->get("products/$externalProductId/variants", ["where" => 'attributes']);
        $data = $this->getResponseToJson($response);
        if ($data) {
            if (isset($data['data']) && is_countable($data['data']) && count($data['data']) > 0) {
                return $data['data'];                
            }
        }
        return [];
    }

    private function _calculate_price_after_markup($price, $margin_options = [])
    {
        if (!$margin_options["is_scaling"]) {
            $margin_percentage = $margin_options["default_margin"];
        } else {
            $margin_percentage = _helloprint_calculate_scaled_margin((round($price) / 100), $margin_options["scaled_margins"], $margin_options["default_margin"]);
        }
        if (!empty($margin_options["type"]) && $margin_options["type"] == "markup") {
            if ($margin_percentage > 0) {
               return  round((float)$price * (1 + ($margin_percentage/100)));
            }
            return round($price);
        } else {
            $markup = (100 - (float)$margin_percentage) / 100;
            return ($markup > 0) ? round((float)$price / $markup) : round($price);
        }
    }
}
