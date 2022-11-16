<?php
/*
 * Plugin Name: WooCommerce Deliverr Integration
 * Plugin URI: https://wpplease.com
 * Description: Adds Woocommerce Deliverr integration
 * Author: Muhammad Arslan
 * Author URI: https://wpplease.com
 * Version: 1.0.0
 */
require_once "ars-utilmethods.php";
require_once "ars-deliverr-api.php";
require_once "ars-deliverr-shippping.php";


$settings_delivrr = get_option("woocommerce_deliverr_settings");
$seller_id="";

if(isset($settings_delivrr["seller_id"]))
{
	$seller_id=$settings_delivrr["seller_id"];
}

wp_enqueue_style('ars_fasttag_css', plugin_dir_url(__FILE__) . 'css/wc-deliverr-integration.css',array("jquery"),time());
wp_enqueue_script('ars_fasttag_plugin_js', plugin_dir_url(__FILE__) . 'js/wc-deliverr-integration.js',array("jquery"),time());
wp_localize_script( 'ars_fasttag_plugin_js', 'DeliverrAPI',
		array( 
			'seller_id' => $seller_id
		)
	);

wp_enqueue_script('ars_fast_tag_js', 'https://fast-tags.deliverr.com/web/main.js',array(),time());


function disable_shipping_calc_on_cart( $show_shipping ) {
    if( is_cart() ) {
        return false;
    }
    return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );

//add_action( 'woocommerce_before_shipping_calculator', 'wcdi_before_shipping_calc_cart_page');
function wcdi_before_shipping_calc_cart_page()
{
 		 $skus      = array(); // Initializing                    
                    foreach ( WC()->cart->get_cart() as $cart_item )
                    {
                        
                        $product = wc_get_product($cart_item["product_id"]);
                        $skus[]=$product->get_sku();
                    }

        $settings_delivrr = get_option("woocommerce_deliverr_settings");
        if(is_array($settings_delivrr) && isset($settings_delivrr["display_product_page"]) && $settings_delivrr["display_product_page"]=="yes")
        {
            $seller_id= isset($settings_delivrr["seller_id"])?"data-sellerid='".$settings_delivrr["seller_id"]."'":"";
            
            ?>            
	<style>
		.dlvrtg10{width:50px !important}
		.woocommerce-shipping-destination{
			display:none !important;
		}
</style>
                <deliverr-tag-extended <?php echo $seller_id ?>  data-skus='<?php echo json_encode($skus);?>' ></deliverr-tag-extended> 
            <?php
        }
}

//if(wcdi_is_display_product_page())
//{
    add_action( 'woocommerce_after_add_to_cart_form', 'wcdi_ars_fast_tag_single_product');
    //add_action( 'woocommerce_after_add_to_cart_button', 'wcdi_ars_fast_tag_single_product');
    
    function wcdi_ars_fast_tag_single_product()
    {
        global $product;
        $sku= $product->get_sku();
 		$price = $product->get_price();
		$cart_amount = WC()->cart->cart_contents_total;
			if(!$cart_amount)
			{
				$cart_amount=0;
			}
        $sku_array = array($sku);
        $sku_array = json_encode($sku_array);

        $settings_delivrr = get_option("woocommerce_deliverr_settings");
        if(is_array($settings_delivrr) && isset($settings_delivrr["display_product_page"]) && $settings_delivrr["display_product_page"]=="yes")
        {
            $seller_id= isset($settings_delivrr["seller_id"])?"data-sellerid='".$settings_delivrr["seller_id"]."'":"";
            
			
			
			
            ?>            
	<style>
		.dlvrtg10{width:65px !important}
</style>
                <deliverr-tag-extended data-price="<?php echo $price; ?>" data-cart-size="<?php echo $cart_amount;?>" <?php echo $seller_id ?>  data-skus='<?php echo $sku_array;?>' ></deliverr-tag-extended> 
            <?php
        }
        
        
        

        
    }
//}


//add_action( 'woocommerce_after_shop_loop_item', 'woo_show_excerpt_shop_page', 5 );
add_action( 'woocommerce_loop_add_to_cart_link', 'woo_show_excerpt_shop_page', 10,3 );
function woo_show_excerpt_shop_page($add_to_cart_html, $product, $args)
{
		global $product;
        $sku= $product->get_sku();
		$price = $product->get_price();
		$cart_amount = WC()->cart->cart_contents_total;
			if(!$cart_amount)
			{
				$cart_amount=0;
			}
        $sku_array = array($sku);
        $sku_array = json_encode($sku_array);
		$before="";

        $settings_delivrr = get_option("woocommerce_deliverr_settings");
        if(is_array($settings_delivrr) && isset($settings_delivrr["display_product_page"]) && $settings_delivrr["display_product_page"]=="yes")
        {
            $seller_id= isset($settings_delivrr["seller_id"])?"data-sellerid='".$settings_delivrr["seller_id"]."'":"";
            
            $before="
			<style>
		.dlvrtg10{width:65px !important}
</style>
                <deliverr-tag-extended data-price='".$price."' data-cart-size='".$cart_amount."' ".$seller_id."   data-skus='".$sku_array."' ></deliverr-tag-extended> 
			";
	
            
        }
	
	

	return $before . $add_to_cart_html ;
}

   add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

add_filter( 'woocommerce_package_rates' , 'xa_sort_shipping_services_by_cost', 10, 2 );
function xa_sort_shipping_services_by_cost( $rates, $package ) 
{

 uasort( $rates, function($a, $b) {
        //return $b->get_cost() > $a->get_cost();
        return $b->get_id() < $a->get_id();
    });

    return $rates;

}