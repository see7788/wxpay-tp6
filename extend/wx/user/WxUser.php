<?php

namespace wx\user;

use Exception;
use think\response\Json;
use tool\BaseCrul;
use tool\BaseValidate;
use tool\BaseRes;
use tool\Tp6Url;
use wx\WxQiYeParam;

class WxUser
{
    /**
     * @var WxUserCookie
     */
    protected $usercache;
    /**
     * @var BaseRes
     */
    private $log;
    /**
     * @var BaseValidate
     */
    private $validate;
    /**
     * @var Tp6Url
     */
    protected $tp6url;
    private
        $appid,
        $appsecret,
        $weixinstate = '识别wx',
        $getUserInfo_land;////zh_CN 简体，zh_TW 繁体，en 英语


    function __construct(WxQiYeParam $config, $getUserInfo_land = 'zh_CN')
    {
        $this->appid = $config->appid;
        $this->appsecret = $config->appsecret;
        $this->usercache = new WxUserCookie($config);
        $this->log = new BaseRes();
        $this->tp6url = new Tp6Url();
        $this->validate = new BaseValidate();
        $this->getUserInfo_land = $getUserInfo_land;
        if (!$this->validate->is_wxH5()) {
            $this->log->errorPush(
                __METHOD__,
                '不是微信环境不能调用微信功能' . __LINE__
            );
        }
    }

    private function wxerror(string $code): string
    {
        $error = [
            "10003" => "redirect_uri域名与后台配置不一致",
            "10004" => "此公众号被封禁",
            "10005" => "此公众号并没有这些scope的权限",
            "10006" => "必须关注此测试号",
            "10009" => "操作太频繁了，请稍后重试",
            "10010" => "scope不能为空",
            "10011" => "redirect_uri不能为空",
            "10012" => "appid不能为空",
            "10013" => "state不能为空",
            "10015" => "公众号未授权第三方平台，请检查授权状态",
            "10016" => "不支持微信开放平台的Appid，请使用公众号Appid"
        ];
        if (empty($error[$code])) {
            return $code;
        } else {
            return $code . $error[$code];
        }
    }

    /**
     * @param $url
     * @return mixed
     * @throws Exception
     */
    private function jsonFromCurl($url)
    {
        $c = new BaseCrul($url);
        return $c->get('json');
    }

    /**
     * 用户 第一步:取得code //snsapi_base || snsapi_userinfo
     * @param bool $snsapi_userinfo_bool
     */
    private function createOpenIdTo(bool $snsapi_userinfo_bool)
    {
        $url = $this->tp6url->brotherUrl('createOpenIdcallback');
        $redirect_uri = urlencode($url);
        $state = $this->weixinstate;
        $scope = $snsapi_userinfo_bool ? 'snsapi_userinfo' : 'snsapi_base';
        header("Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=$redirect_uri&response_type=code&scope=$scope&state=$state#wechat_redirect");
    }

    /**
     * 用户第二步:取得code换取openid
     * @param $code
     * @param $state
     * @throws Exception
     */
    function createOpenIdcallback($code, $state)
    {
        if ($state !== $this->weixinstate) {
            $this->log->errorPush(__METHOD__, `input(state) !==$this->weixinstate`)->successGet();
        }
        $getIdurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" .
            $this->appid .
            "&secret=" .
            $this->appsecret .
            "&code=$code&grant_type=authorization_code";
        $db = $this->jsonFromCurl($getIdurl);
        if (!empty($db->errcode)) {

            $this->log->errorPush(
                __METHOD__,
                __LINE__,
                $db->errmsg,
                $this->wxerror($db->errcode)
            )->successGet();
        }
        $this->usercache->openid($db->openid);
        $this->usercache->access_token($db->access_token);
        $this->usercache->refresh_token($db->refresh_token);
        $this->usercache->scope_snsapi_userinfo($db->scope);
        $u = $this->usercache->url(false);
        header("Location:$u");
    }

    /**
     * @param string $callbackGoto
     * @param bool $snsapi_userinfo_bool
     * @return mixed|void
     */
    protected function getUserOpenId(string $callbackGoto, bool $snsapi_userinfo_bool)
    {
        $id = $this->usercache->openid(false);
        if ($id && $snsapi_userinfo_bool) {
            $id2 = $this->usercache->scope_snsapi_userinfo(false);
            if ($id2) {
                return $id;
            }
        } else if ($id) {
            return $id;
        }
        $this->usercache->url($callbackGoto);
        $this->createOpenIdTo($snsapi_userinfo_bool);
    }

    /**
     * @throws Exception
     */
    protected function refresh_access_token(): Json
    {
        $refresh_token = $this->usercache->refresh_token(false);
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$this->appid&grant_type=refresh_token&refresh_token=$refresh_token";
        $db = $this->jsonFromCurl($url);
        if (!empty($db->errcode)) {
            return $this->log->errorPush(
                __METHOD__,
                __LINE__,
                $db->errmsg,
                $this->wxerror($db->errcode)
            )->successGet();
        }
        $this->usercache->openid($db->openid);
        $this->usercache->access_token($db->access_token);
        $this->usercache->refresh_token($db->refresh_token);
        $this->usercache->scope_snsapi_userinfo($db->scope);
        return $db->access_token;
    }

    /**
     * 第四步：拉取用户信息(需scope为 snsapi_userinfo)
     * @param string $callbackGoto
     * @return mixed
     * @throws Exception
     */
    protected function getUserInfo(string $callbackGoto)
    {
        $openId = $this->getUserOpenId($callbackGoto, true);
        $access_token = $this->usercache->access_token(false);
        if (!$access_token) {
            $access_token = $this->refresh_access_token();
        }
        $accessUrl = "https://api.weixin.qq.com/sns/auth?access_token=$access_token&openid=$openId";
        $errcode = $this->jsonFromCurl($accessUrl)->errcode;
        if ($errcode) {
            $access_token = $this->refresh_access_token();
        }
        $userinfourl = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&lang=$this->getUserInfo_land&openid=$this->appid";
        $db = $this->jsonFromCurl($userinfourl);
        if (!empty($db->errcode)) {
            $this->log->errorPush(
                __METHOD__,
                __LINE__,
                $db->errmsg,
                $this->wxerror($db->errcode)
            )->successGet();
        }
        return $db;
    }


}


