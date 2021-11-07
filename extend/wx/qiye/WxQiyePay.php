<?php

namespace wx\qiye;

use Exception;
use tool\BaseCrul;
use tool\BaseRes;
use wx\WxPayBase;
use wx\WxPayParam;

class WxQiyePay
{
    /**
     * @var WxPayBase
     */
    public $base;
//zccangchu.com
    function __construct(WxPayParam $config)
    {
        $this->base=new WxPayBase($config);
    }
}