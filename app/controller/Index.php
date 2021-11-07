<?php

namespace app\controller;

use Exception;
use think\response\Json;
use think\response\View;
use wx\user\WxUserPay;
use wx\user\WxUser;
use wx\WxQiYeAppIdParam;

class Index extends WxUser
{
    private $wxPay, $openId, $payCallBack;

    function __construct()
    {
        $c = new WxQiYeAppIdParam();
        parent::__construct($c);
        $this->wxPay = new WxUserPay($c);
        $this->payCallBack = $this->tp6url->brotherUrl('success');
        $to = $this->tp6url->brotherUrl('v3_pay_transactions_jsapi_laozhou');
        $this->openId = $this->getUserOpenId($to, false);
    }

    /**
     * @return Json
     * @throws Exception
     */
    public function index(): Json
    {
        $c2 = $this->getUserOpenId('success',false);
        return json($c2);
    }
    /**
     * @return Json
     * @throws Exception
     */
    public function index2(): Json
    {
        $url = $this->tp6url->brotherUrl('success');
        $c2 = $this->getUserInfo($url);
        return json($c2);
    }

    function success(): string
    {
        var_dump(input());
        return 'success';
    }

    /**
     * @return View
     * @throws Exception
     */
    public function v3_pay_transactions_jsapi_tpl(): View
    {
        $db = $this->wxPay->v3PayTransactionsJsapi(
            $this->openId,
            $this->payCallBack,
            1
        );
        return view(__FUNCTION__, [
            'msg' => '测试jsapi支付',
            'str' => $db//模板用{$str|raw}
        ]);
    }

    /**
     * @return View
     * @throws Exception
     */
    public function v3_pay_transactions_jsapi_laozhou(): View
    {
        $db = $this->wxPay->v3PayTransactionsJsapi(
            $this->openId,
            $this->payCallBack,
            1
        );
        return view('', [
            "apiPath" => $this->tp6url->brotherUrl('v3_pay_transactions_jsapi_tpl'),
            "apiParam" => $this->wxPay->v3PayTransactionsJsapiParam,
            "payState" => $db
        ]);
    }
}


