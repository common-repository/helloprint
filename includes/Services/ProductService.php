<?php

namespace HelloPrint\Inc\Services;

class ProductService
{
    public function createHelloprintProduct($helloprint_extrenal_id, $productName)
    {
        $objProduct = new \WC_Product();
        $objProduct->set_name($productName);
        $objProduct->product_type = 'helloprint_product';
        $new_product_id = $objProduct->save();
        return $new_product_id;
    }


    public function getProductIdFromMetaKey($metaValue)
    {
        $args = array(
            'post_type' => 'product',
            'status' => 'publish',
            'ignore_sticky_posts' => 1,
            'meta_key' => 'helloprint_external_product_id',
            'meta_value' => $metaValue,
            'meta_compare' => '='
        );
        $my_query = new \WP_Query($args);
        $productId = null;
        if ($my_query->have_posts()) {
            foreach ($my_query as $post) {
                $my_query->the_post();
                $productId = get_the_ID();
                break;
            }
        }
        return $productId;
    }
}
