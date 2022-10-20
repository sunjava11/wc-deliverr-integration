<?php
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function ars_Deliverr_Shipping_method() {
        if ( ! class_exists( 'Ars_Deliverr_Shipping_method' ) ) {
            class Ars_Deliverr_Shipping_method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'deliverr'; 
                    $this->method_title       = __( 'Deliverr Shipping', 'deliverr' );  
                    $this->method_description = __( 'Fast Tag Shipping with Deliverr', 'deliverr' ); 
 
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'US', // Unites States of America
                        'CA' // Canada                        
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Deliverr Shipping', 'deliverr' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'deliverr' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', 'deliverr' ),
                          'default' => 'yes'
                          ),
 
               /*      'title' => array(
                        'title' => __( 'Title', 'deliverr' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', 'deliverr' ),
                          'default' => __( 'Deliverr Shipping', 'deliverr' )
                     ),
                 */    
                     'seller_id' => array(
                        'title' => __( 'Seller Id', 'deliverr' ),
                          'type' => 'text',
                          'description' => __( 'Deliverr Seller Id', 'deliverr' ),
                          'default' => __( '', 'deliverr' )
                     ),

                     'display_product_page' => array(
                        'title' => __( 'Display FastTag on product page', 'deliverr' ),
                          'type' => 'checkbox',
                          'description' => __( 'Check to display on product page', 'deliverr' ),
                          'default' => __( 'yes', 'deliverr' )
                     ),
                     

                    /* 'display_arrive_by' => array(
                        'title' => __( 'Display arrive by text', 'deliverr' ),
                        'type' => 'checkbox',
                        'description' => __( 'Enable this option to show "Arrives by Wednesday, October 19th"', 'deliverr' ),
                        'default' => 'yes'
                        ),
 */
                          'shipping_fee' => array(
                            'title' => __( 'Shipping Fee ($)', 'deliverr' ),
                              'type' => 'number',
                              'description' => __( 'Shipping Fee', 'deliverr' ),
                              'default' => 0
                          ),

                          'free_shipping_over' => array(
                            'title' => __( 'Free Shipping Over ($)', 'deliverr' ),
                              'type' => 'number',
                              'description' => __( 'Enter just the dollar amount without currency symbol', 'deliverr' ),
                              'default' => 10
                          ),

                     );

                     
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package=array() ) {
                    
                    $items=array();
                    $skus      = array(); // Initializing                    
                    foreach ( WC()->cart->get_cart() as $cart_item )
                    {
                        
                        $product = wc_get_product($cart_item["product_id"]);
                        $skus[]=$product->get_sku();
                    }

                    
                    
                    
                    $sinalite_product_id = 0;
                    $shippingInfoObj = new stdClass();
                    $shippingInfoObj->destination=new stdClass();


                    $shippingInfoObj->destination->street1=  $package[ 'destination' ][ 'address_1' ];
                    $shippingInfoObj->destination->street2=  $package[ 'destination' ][ 'address_2' ];
                    $shippingInfoObj->destination->state=  $package[ 'destination' ][ 'state' ];
                    $shippingInfoObj->destination->city=  $package[ 'destination' ][ 'city' ];
                    $shippingInfoObj->destination->zip=  $package[ 'destination' ][ 'postcode' ];
                    $shippingInfoObj->destination->country=  $package[ 'destination' ][ 'country' ];
                    
                    $shippingInfoObj->sellerId=$this->settings["seller_id"];
                    $shippingInfoObj->skus=$skus;
                   
                    $ars_Sinalite_API = new ars_Deliverr_API();
                    $res = $ars_Sinalite_API->get_deliverr_shipping($shippingInfoObj);
                    
                    if(!is_null($res))
                    {
						$counter=1;
                        
                        $display_arrive_by= isset($this->settings['display_arrive_by'])?$this->settings['display_arrive_by']:false;
                        $title = isset($this->settings['title'])?$this->settings['title']:"";
                        $shippingFee = floatVal($this->settings['shipping_fee']);
                        $free_shipping_over = floatVal($this->settings['free_shipping_over']);
                        $cart_total= floatVal(WC()->cart->get_cart_contents_total());
                        $free_shipping="";

                        if($cart_total>$free_shipping_over  )
                        {
                            $shippingFee=0.00;
                            $free_shipping=" - Free Shipping";
                        }

                        
                            //var_dump($r);
                          /*  $rate = array(
                                'id' => $res->code,
                                'label' => $title." "."  ". ($display_arrive_by=="yes"?" - ".$res->arrivesBy:"")." ".$free_shipping ,
                                'cost' => $shippingFee
                            );
         */
						$rate = array(
                                'id' => "0__deliver",
                                'label' => $res->name,
                                'cost' => $shippingFee,
								'meta_data'      => array("original_value"=>$res->name)
                            );
						
                            $this->add_rate( $rate );
							$counter++;
                                               
                    }                    
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'ars_Deliverr_Shipping_method' );
 
    function add_Ars_Deliverr_Shipping_method( $methods ) {
        $methods[] = 'Ars_Deliverr_Shipping_method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_Ars_Deliverr_Shipping_method' );
 

   

}