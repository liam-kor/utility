<?php
namespace LunaDev\Utility;

use Ixudra\Curl\CurlService;
use LunaDev\Utility\LunaBuilder;

class LunaCurl extends CurlService {

    /**
     * @return string
     */
    public function to($url)
    {
        $builder = new LunaBuilder();
        
        return $builder->to($url)->returnResponseObject();
    }

}