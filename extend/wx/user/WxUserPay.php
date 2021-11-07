<?php

namespace wx\user;

use Exception;
use GuzzleHttp\Exception\RequestException;
use think\exception\ValidateException;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use wx\WxPayBase;
use wx\WxPayParam;
use tool\BaseRes;

class WxUserPay extends WxPayBase
{
    /**
     * @var BaseRes
     */
    private $log;

    function __construct(WxPayParam $config)
    {
        parent::__construct($config);
        $this->log = new BaseRes();
    }

    public $v3PayTransactionsJsapiParam = [
        "total" => '金额number',
        "description" => '测试商品名称string',
        "attach" => "自定义数据说明string",
        "time_expire" => "3600",
        "currency" => 'CNY',
    ];

    /**
     * @return false|string|void
     * @throws Exception
     */
    function v3PayTransactionsJsapi(
        $openId,
        $notify_url,
        $total,
        $out_trade_no='订单号',
        $description = '测试商品名称',
        $attach = "自定义数据说明",
        $time_expire = 3600
    )
    {
        $total = (int)$total;
        $out_trade_no=date('YmdHis');
        try {
            validate([
                'total' => '>:0',
            ], [
                'total' => '金额必须大于0',
            ])->check([
                'total' => $total
            ]);
        } catch (ValidateException $e) {
            $this->log->errorPush(
                __METHOD__,
                __LINE__,
                $e->getMessage()
            )->successGet();
        }
        try {
            $resp = $this->api->v3->pay->transactions->jsapi
                ->post(['json' => [
                    "time_expire" => date("Y-m-d\TH:i:s+08:00", time() + $time_expire),
                    "amount" => [
                        "total" => $total,
                        "currency" => 'CNY',
                    ],
                    "mchid" => $this->merchantId,
                    "description" => $description,
                    "notify_url" => $notify_url,
                    "payer" => [
                        "openid" => $openId,
                    ],
                    "out_trade_no" => $out_trade_no,
                    "goods_tag" => "WXG",
                    "appid" => $this->appid,
                    "attach" => $attach,
                    "scene_info" => [
                        "payer_client_ip" => $_SERVER['REMOTE_ADDR'],
                    ]
                ]]);
            if ($resp->getStatusCode() == 200) {
                $r = json_decode($resp->getBody());
                $params = [
                    'appId' => $this->appid,
                    'timeStamp' => (string)Formatter::timestamp(),
                    'nonceStr' => Formatter::nonce(),
                    'package' => 'prepay_id=' . $r->prepay_id,
                ];
                $params += ['paySign' => Rsa::sign(
                    Formatter::joinedByLineFeed(...array_values($params)),
                    $this->merchantPrivateKeyInstance
                ), 'signType' => 'RSA'];
                return json_encode($params);
            } else {
                $this->log->errorPush(
                    __METHOD__,
                    __LINE__
                )->successGet();
            }
        } catch (Exception $e) {
            $this->log->errorPush(
                __METHOD__,
                __LINE__,
                $e->getMessage()
            );
            if ($e instanceof RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                $this->log->errorPush(
                    $r->getStatusCode() . ' ' . $r->getReasonPhrase(),
                    $r->getBody()
                );
            }
            $this->log->errorPush(
                $e->getTraceAsString()
            )->successGet();
        }
    }
}