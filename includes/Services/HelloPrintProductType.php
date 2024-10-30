<?php

class HelloPrintProductType extends WC_Product
{
    public function __construct($product)
    {
        $this->product_type = 'helloprint_product';
        parent::__construct($product);
        $this->is_in_stock = true;
        $this->price = 100;
        $this->regular_price = 120;
    }
}
