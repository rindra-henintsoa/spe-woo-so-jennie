<?php
namespace Specifs\Module\Coffret;

/**
 * Class gerant les spécifiques sur le gestion des coffret(écrin)
 */
class CoffretModule
{
    public function runHooks(){
        add_action( 'woocommerce_cart_loaded_from_session', array($this, "reorderCoffretPositInCart"));

        //eviter la redirection vers le fiche de l'écrin après ajout d'écrin
        add_filter( 'woocommerce_add_to_cart_redirect', 'wp_get_referer', 50 );
    }

    /**
     * On test si le produit est déjà dans le panier
     *
     * @return bool
     */
    public static function productIsInCart($product_id){
        global $woocommerce;

        foreach($woocommerce->cart->get_cart() as $key => $val ) {
            $_product = $val['data'];
            if($product_id == $_product->get_id() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * ajout des produits écrins à la fin de la listing des produits ajoutés le panier
     * */
    public function reorderCoffretPositInCart()
    {
        $category_terms    = 70; // Here set your category terms (can be names, slugs or Ids) //@todo constante
        $items_in_category = $other_items = array(); // Initizlizing

        // Assign each item in a different array depending if it belongs to defined category terms or not
        foreach ( WC()->cart->cart_contents as $key => $item ) {
            if( has_term( $category_terms, 'product_cat', $item['product_id'] ) ) {
                $items_in_category[ $key ] = $item;
            } else {
                $other_items[ $key ] = $item;
            }
        }

        // Set back merged items arrays with the items that belongs to a category at the end
        WC()->cart->cart_contents = array_merge( $other_items, $items_in_category );
    }
}