<?php
class ars_UtilMethod
{
    public function group_by($key, $data) {
        $result = array();
    
        foreach($data as $val) {

            if(is_object($val))
            {
                $val = (array)$val;
            }
            
            // if(!is_array($val))
            // {
            //     return false;
            //     //echo $val;
            //     //die();
            // }
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                return false;
                $result[""][] = $val;
            }
        }
    
        return $result;
    }

    public function display_errors()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    public function var_dump($obj)
    {
        echo "<pre>";
        var_dump($obj);
    }
    public function print_r($obj)
    {
        echo "<pre>";
        print_r($obj);
    }
}