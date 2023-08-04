<?php
namespace App\Service\JWTAuth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;

class JWTAuth{

    public static function validateToken($JWT){
        try {
            $decoded = JWT::decode($JWT,new Key('506069hhh', 'HS256'));
            $payload = json_decode(json_encode($decoded),true);
            }catch (UnexpectedValueException $e) {
            $res=array("status"=>false,"Error"=>$e->getMessage());
            return $res;
            } 
            return array("status"=>true,$payload);
    }


    public static function getHeaderToken(){
       $headers = getallheaders();
    
       if(isset($headers['Body'])){
        //$token = explode(' ', $headers['Authorization'])[1];
        return $headers['Body'];
       }else{
        return null;
       }
        
    }
}