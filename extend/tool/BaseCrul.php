<?php
namespace tool;
use Exception;

class BaseCrul
{

    /**
     * @var BaseRes
     */
    private $log;
    private $url;
    private $setopt;

    /**
     * @param string $url
     * @throws Exception
     */
    public function __construct(string $url)
    {
        $this->log = new BaseRes();
        $this->url = $url;
        if (strlen($this->url) < 5) {
            $this->log->errorPush(__METHOD__, 'strlen($this->url) < 5')->successGet();
        }
        if (strtolower(substr($this->url, 0, 5)) == "https") {
            $this->setopt[CURLOPT_SSL_VERIFYPEER] = false;
            $this->setopt[CURLOPT_SSL_VERIFYHOST] = false;
        }
        $this->setopt = [
            CURLOPT_TIMEOUT => 60,//	设置cURL允许执行的最长秒数。
            CURLOPT_CONNECTTIMEOUT => 10,//在发起连接前等待的时间，如果设置为0，则无限等待。
            //CURLOPT_FAILONERROR=>true,//显示HTTP状态码，默认行为是忽略编号小于等于400的HTTP信息。
            //  CURLOPT_NETRC=>true,//在连接建立以后，访问~/.netrc文件获取用户名和密码信息连接远程站点。
            //   CURLOPT_UNRESTRICTED_AUTH=>true,//在使用CURLOPT_FOLLOWLOCATION产生的header中的多个locations中持续追加用户名和密码信息，即使域名已发生改变
            //CURLOPT_VERBOSE=>true,//启用时会汇报所有的信息，存放在STDERR或指定的CURLOPT_STDERR中。
            CURLOPT_RETURNTRANSFER => true,//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        ];
    }

    /**
     * @param bool $json
     * @return mixed
     * @throws Exception
     */
    function get(bool $json = false)
    {
        $ch = curl_init($this->url);
        curl_setopt_array($ch, $this->setopt);
        $resp = curl_exec($ch);
        curl_close($ch);
        if ($json) {
            $resp = json_decode($resp, false, 512, JSON_BIGINT_AS_STRING);
        }
        return $this->log->successSet($resp)->successGet();
    }
}