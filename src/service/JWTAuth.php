<?php
namespace App\Service\JWTAuth;

use Firebase\JWT\JWT;
use UnexpectedValueException;

class JWTAuth{

    public static function validateToken($JWT, $passphrase, $arr=array('HS256')){
        try {
            $decoded = JWT::decode($JWT,$passphrase, $arr=null);
            $payload = json_decode(json_encode($decoded),true);
            }catch (UnexpectedValueException $e) {
            $res=array("status"=>false,"Error"=>$e->getMessage());
            return $res;
            } 
            return array("status"=>true,$payload);
    }


    public static function getHeaderToken(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
            $token = explode(" ", $headers)[1];
            return $token;
        }else{
            return null;
        }
        
    }
}