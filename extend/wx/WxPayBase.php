<?php

namespace wx;

use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use wx\WxPayParam;

class WxPayBase
{
    public $api, $appid, $merchantId, $merchantPrivateKeyInstance;
    function __construct(WxPayParam $config)
    {
        $this->appid = $config->appid;
        $this->merchantId = $config->merchantId;
        $merchantPrivateKeyFilePath = 'apiclient_key.pem';
        $this->merchantPrivateKeyInstance = $privateKey = Rsa::from($config->certDir . $merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
        $platformPublicKeyInstance = Rsa::from($config->certDir . $config->platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
        $this->api = Builder::factory([
            'mchid' => $config->merchantId,
            'serial' => $config->merchantCertificateSerial,
            'privateKey' => $privateKey,
            'certs' => [
                $config->platformCertificateSerial => $platformPublicKeyInstance,
            ],
        ]);
    }
}
