<?php
namespace App\Service\Time;


class TimeFormatter{

    public function __construct(){
        
    }

    public static function formatTime()
    {
        return date("Y-m-d\TH:i:s", strtotime(date('Y-m-d h:i:s')));
    }


}