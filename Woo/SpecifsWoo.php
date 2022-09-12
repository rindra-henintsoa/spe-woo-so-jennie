<?php
use Mediapilote\Wordpress\Theme;

namespace Specifs\Woo;

/**
 * Class gerant les spécifiques sur woocommerce
 */
class SpecifsWoo
{
    // id de la catégorie de produit coffret
    const ID_CAT_COFFRET = 70;

    // Max input Qty value
    const INPUT_QTY_MAX_VALUE = 10;
    
    /**
     * Run specifs dev 
     */
    public function runSpe(){
        // replaceWooLoginBtnText
        add_filter( 'gettext', array($this, "replaceWooLoginBtnText"), 10, 3 );

        // changeAddToCartSuccessMsg
        add_filter ( 'wc_add_to_cart_message_html', array($this, "changeAddToCartSuccessMsg"), 10, 2 );

        // ajaxifyCartTotal
        add_filter('woocommerce_add_to_cart_fragments', array($this, "ajaxifyCartTotal"));

        // addProductCapacityToCart
        add_action('woocommerce_before_calculate_totals', array($this, "addProductCapacityToCart"), 10, 1);

        // addCheckoutBtnBeforeCart
        add_action( 'woocommerce_before_cart', array($this, "addCheckoutBtnBeforeCart"));

        // cartQuantityDropdownJS
        add_action( 'woocommerce_after_cart', array($this, "cartQuantityDropdownJS") );
       
        // setQtyInputMaxValue
        add_filter( 'woocommerce_quantity_input_args', array($this, "setQtyInputMaxValue"), 10, 2 );
    }

    /**
     * remplace le texte sur le boutton de connexion dans la page mon compte
     * 
     * @return string $translated_text
     * */
    public function replaceWooLoginBtnText($translated_text, $text, $domain){
        if ( ! is_user_logged_in() && is_account_page() ) {
            $original_text = 'Log in';

            if ( $text === $original_text )
                $translated_text = esc_html__('Connexion', $domain );
        }

        return $translated_text;
    }

    /**
     * Personnalisation du message d'ajout panier pour les produits écrins
     * 
     * @return string $message
     * */
    public function changeAddToCartSuccessMsg($message, $products){
        foreach( $products as $product_id => $quantity ){
            if( has_term( array(self::ID_CAT_COFFRET), 'product_cat', $product_id ) ){
                $added_text = __('Votre écrin a bien été ajouté au panier!','woocommerce');
                $message    = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_page_permalink( 'cart' ) ), esc_html__( 'Voir le panier', 'woocommerce' ), esc_html( $added_text ) );
            }else{
                $added_text = __('Votre produit a bien été ajouté au panier!','woocommerce');
                $message    = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_page_permalink( 'cart' ) ), esc_html__( 'Voir le panier', 'woocommerce' ), esc_html( $added_text ) );
            }
        }
        return $message;
    }

    /**
     * Mise à jour en ajax du total du panier
     * 
     * @return html $fragments
     * */
    public function ajaxifyCartTotal($fragments){
        global $woocommerce;
        $cartUrl = wc_get_cart_url();
        $fragments['a.cart-mini-contents'] = '<a class="cart-mini-contents" href="' . $cartUrl . '">
                        <span class="count">' . $woocommerce->cart->cart_contents_count . '</span>
                    </a>';
        return $fragments;
    }

    /**
     * on rajoute l'attribut de contenance sur le nom du produit
     * */
    public function addProductCapacityToCart($cart){
        if (is_admin() && !defined('DOING_AJAX'))
        return;

        // Required since Woocommerce version 3.2 for cart items properties changes
        if (did_action('woocommerce_before_calculate_totals') >= 2)
            return;

        // Parcourir les articles du panier
        foreach ($cart->get_cart() as $cart_item) {
            // Get the product name and the product ID
            $product_name               = $cart_item['data']->get_name();
            $product_default_attributes = $cart_item['data']->get_default_attributes();

            if ($product_default_attributes) {
                // Définir le nouveau nom du produit
                $cart_item['data']->set_name($product_name . ' <span>' . $product_default_attributes['contenance'] . '</span>');
            }
        }
    }

    /**
     * Ajoutez le bouton "Commander" au-dessus du tableau du contenu du panier
     * */
    public function addCheckoutBtnBeforeCart()
    { ?>
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button button alt wc-forward">
            <?php esc_html_e('Commander', 'woocommerce'); ?>
        </a>
    <?php
    }

    /**
     * Script jQuery pour la liste déroulante des quantités
     */
    public  function cartQuantityDropdownJS() {
        ?>
            <script type="text/javascript">
                jQuery( function($){
                    $(document.body).on('change blur', 'form.woocommerce-cart-form .quantity select', function(e){
                        var t = $(this), q = t.val(), p = t.parent();
                        $(this).parent().find('input').val($(this).val());
                        console.log($(this).parent().find('input').val());
                    });
                });
            </script>
        <?php
    }

    /**
     * Restreindre la quantité maximale de produits à 10
     *
     * @return array
     */
    public function setQtyInputMaxValue( $args, $product ) {
        $args['max_value'] = self::INPUT_QTY_MAX_VALUE;

        return $args;
    }
}
