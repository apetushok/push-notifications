<?php

namespace Push;

use Push\AndroidService;
use Push\IosService;

class Push
{
    public function getService($name,$config)
    {
        if($name == "android"){
            return new AndroidService($config);
        }elseif($name == "ios"){
            return new IosService($config);
        }
    }
}