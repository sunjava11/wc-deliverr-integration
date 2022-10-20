<?php

class ars_Deliverr_API
{
    private $ars_base_url;
    private $ars_client_id;
    private $ars_client_secret;

    function __construct()
    {
        $this->ars_base_url = "https://fast-tag.deliverr.com/v1/service-rate";            
    }

   

    public function get_deliverr_shipping($shippingInfoObj)
    {
      
        $payload = json_encode( $shippingInfoObj);

        

        $ch = curl_init( $this->ars_base_url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );        
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $result = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($result);
        
        return $result;
    }

    public function get_sinalite_price($productId,$attributesArray)
    {
        $token = $this->ars_GetToken();
        $obj = new stdClass();
        $obj->productOptions = $attributesArray;

        $products =  wp_remote_post($this->ars_base_url . "/price/" . $productId . "/en_us", array(
            "headers" => array(
                'Content-type' => 'application/json',
                'Authorization:' => 'Bearer ' . $token,
            ),
            "body" => json_encode($obj)
        ));

        
        if (!is_wp_error($products)) {
            $returnObj = json_decode($products["body"]);
            return $returnObj;
        }

        return null;
    }

    public function ars_GetProductOptions($productId, $token)
    {

        $products =  wp_remote_get($this->ars_base_url . "/product/" . $productId . "/en_us", array(
            "headers" => array(
                'Content-type' => 'application/json',
                'Authorization:' => 'Bearer ' . $token,
            ),
        ));

        $products["body"] = json_decode($products["body"]);
        $uniqueGroups = array();
        
        if (!is_wp_error($products)) {

            $util= new ars_UtilMethod();
            $uniqueGroups= $util->group_by("group",$products["body"][0]);

            if($uniqueGroups==false)
            {
                return false;
            }
            
        }

        return $uniqueGroups;
    }


    public function ars_GetProducts()
    {
        $arsWCObj = new ars_WC_Product();
        $token = $this->ars_GetToken();


        $products =  wp_remote_get($this->ars_base_url . "/product", array(
            "headers" => array(
                'Content-type' => 'application/json',
                'Authorization:' => 'Bearer ' . $token,
            )
        ));

        $products["body"] = json_decode($products["body"]);

        if (!is_wp_error($products)) {
            $counter = 0;
            foreach ($products["body"] as $p) {
                // if($counter==100)
                // {
                //     break;
                // }

                $arsWCObj->ars_create_product($p);
                $counter++;
            }
        }
    }
}
