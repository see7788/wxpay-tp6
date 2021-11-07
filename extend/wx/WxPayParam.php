<?php

namespace wx;

class WxPayParam extends  WxQiYeParam
{
    public
        $merchantId = '微信支付商户号',
        $certDir = 'file:///证书目录/',
        $merchantCertificateSerial = '商户证书序列号',
        $platformCertificateFilePath = '平台证书公钥',
        $platformCertificateSerial = '平台证书序列号，「平台证书」当前五年一换，缓存后就是个常量';
}