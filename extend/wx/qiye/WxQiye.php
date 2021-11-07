<?php

namespace wx\qiye;

use Exception;
use tool\BaseCrul;
use tool\BaseRes;
use wx\WxQiYeParam;

class WxQiye
{
    /**
     * @var BaseRes
     */
    private $log;
    private $appid, $appsecret;
//zccangchu.com
    function __construct(WxQiYeParam $qiyeconfig)
    {
        $this->log = new BaseRes();
        $this->appid = $qiyeconfig->appid;
        $this->appsecret =$qiyeconfig->appsecret;
    }

    /**
     * @return mixed|BaseRes
     * @throws Exception
     */
    function getOpenId()
    {
        $cname = md5(__METHOD__ . '_' . $this->appid );
        $token = cache($cname);
        if (!$token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" .
                $this->appid .
                "&secret=" .
                $this->appsecret;
            $successDb = (new BaseCrul($url))->get('json');
            if (!empty($successDb->errcode)) {
                return $this->log->errorPush(
                    __METHOD__,
                    $successDb->errcode
                );
            } else {
                $token = $successDb->access_token;
                cache($cname, $token, $successDb->expires_in - 200);
            }
        }
        return $this->log->successSet($token)->successGet();
    }
}