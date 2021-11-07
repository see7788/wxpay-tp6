<?php

namespace wx\user;


use wx\WxQiYeAppIdParam;

class WxUserCookie
{
    public $appid;

    function __construct(WxQiYeAppIdParam $c)
    {
        $this->appid = $c->appid;
    }

    /**
     * @param $data //删除===null,
     * @return mixed|void
     */
    function refresh_token($data)
    {
        $c = md5($this->appid . __METHOD__);
        if ($data === null) {
            cookie($c, null);
        } else if ($data) {
            return cookie($c, $data, 51840000);//30*24*7200
        } else {
            return cookie($c);
        }
    }

    /**
     *@param $data //删除===null,
     * @return mixed|void
     */
    function access_token($data)
    {
        $c = md5($this->appid . '_' . __METHOD__);
        if ($data === null) {
             cookie($c, null);
        } else if ($data) {
            return cookie($c, $data, 7000);
        } else {
            return cookie($c);
        }
    }

    /**
     * @param $data //删除===null,
     * @return mixed|void
     */
    function openid($data)
    {
        $c = md5($this->appid . '_' . __METHOD__);
        if ($data === null) {
             cookie($c, null);
        } else if ($data) {
            cookie($c, $data);
            return $data;
        } else {
            return cookie($c);
        }
    }

    /**
     * @param $data //删除===null,
     * @return mixed|void
     */
    function url($data)
    {
        $c = md5($this->appid . '_' . __METHOD__);
        if ($data ===null) {
            cookie($c, null);
        } else if ($data) {
            cookie($c, $data);
            return $data;
        } else {
            return cookie($c);
        }
    }

    /**
     * @param $data //删除===null,c存入只能是snsapi_userinfo
     * @return mixed|void
     */
    function scope_snsapi_userinfo($data)
    {
        $c = md5($this->appid . '_' . __METHOD__);
        if ($data ===null) {
            cookie($c, null);
        } else if ($data) {
            if($data=='snsapi_userinfo'){
                cookie($c, $data);
                return $data;
            }else{
                return false;
            }
        } else {
            return cookie($c);
        }
    }


}