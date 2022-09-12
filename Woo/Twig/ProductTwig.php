<?php

namespace Specifs\Woo\Twig;

class ProductTwig
{
    //
    public $wooProduct = null;

    /**
     * init
     */
    public function __construct($wooProduct)
    {
        $this->wooProduct = $wooProduct;
    }

    /**
     * get pourcentage
     *
     * @return void
     */
    public function get_percentage_remise()
    {
        $percentage = false;

        //pourcent remise
        if (
            $this->wooProduct->get_regular_price()
            && $this->wooProduct->get_regular_price() != $this->wooProduct->get_price()
        ) {
            $percentage = round((($this->wooProduct->get_regular_price() - $this->wooProduct->get_price()) / $this->wooProduct->get_regular_price()) * 100);
        }

        //
        return $percentage;
    }

    /**
     * usine
     */
    static public function make($product)
    {
        //
        $obj = new ProductTwig($product);
        return $obj;
    }
}
