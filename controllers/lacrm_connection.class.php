<?php

/**
 * Class LACRM Connection Class.
 * Class built to handle the connection to LACRM
 */

class Lacrm_connection {

    public $userCode;
    public $apiToken;
    public $functionName = "CreateContact";
    /**
     * Constructor
     */
    public function __construct() {
        $this->userCode = esc_attr(get_option('user_code', ''));
        $this->apiToken = esc_attr(get_option('api_token', ''));
        add_action( 'gform_after_submission', array($this, 'post_to_third_party'), 10, 2 );
    }

    public function url_get_contents ($Url) {
        if (!function_exists('curl_init')){
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function CallAPI($parameters){
        $APIResult = $this->url_get_contents("https://api.lessannoyingcrm.com?UserCode=$this->userCode&APIToken=$this->apiToken&".
            "Function=$this->functionName&Parameters=".urlencode(json_encode($parameters)));
        $APIResult = json_decode($APIResult, true);

        if(@$APIResult['Success'] === true){
            //echo "Success!";
        }
        else{
            echo "<!-- LACRM API call failed. Error: ". $APIResult["Error"]."-->";
        }
        return $APIResult;
    }

    public function post_to_third_party( $entry, $form ) {

        //if (isset($CSForm[3][1]) and isset($CSForm[1][1]) and isset($CSForm[0][1]) and isset($CSForm[3][1]) and isset($CSForm[2][1]) and isset($CSForm[4][1]) and isset($CSForm[5][1])){
        $CSForm = clacrm_get_values($form['id']);
        $parameters = array(
            "FullName"=> $entry[$CSForm[3][1]],
            "Email"=>array(
                0=>array(
                    "Text"=>$entry[$CSForm[1][1]],
                    "Type"=>"Work"
                )
            ),
            "Phone"=>array(
                0=>array(
                    "Text"=>$entry[$CSForm[0][1]],
                    "Type"=>"Work"
                )
            ),
            "CompanyName"=> $entry[$CSForm[2][1]],
            "Address"=>array(
                0=>array(
	                "Street"=>$entry[$CSForm[4][1].".1"],
	                "City"=>$entry[$CSForm[4][1].".2"],
	                "State"=>$entry[$CSForm[4][1].".3"],
	                "Zip"=>$entry[$CSForm[4][1].".4"],
	                "Country"=>$entry[$CSForm[4][1].".5"],
	                "Type"=>"Shipping"
                )
            ),
            "Title"=> $entry[$CSForm[5][1]],
        );

            $this->CallAPI($parameters);
        //}
    }

}