<?php
namespace tool;
class BaseValidate
{
    function is_mail($Tb)
    {
        $va = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
        $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
        $Pe = "$va+(\\.$va+)*@($Hb?\\.)+$Hb";
        return is_string($Tb) && preg_match("(^$Pe(,\\s*$Pe)*\$)i", $Tb);
    }

    function is_telphone($str)
    {
        // $tel = '/^0?(13|14|15|17|18)[0-9]{9}$/';
        $token = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        $tel = preg_match($token, $str);
        return (bool)$tel;
//        return true;
    }

    function is_url($eg)
    {
        $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
        return preg_match("~^(https?)://($Hb?\\.)+$Hb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $eg);
    }

    function is_wxH5()
    {
        return $this->agent_strpos('微信H5');
    }

    function is_wxXcx()
    {
        return $this->agent_strpos('微信小程序');
    }

    private function agent_strpos($name = false)
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if ($_SERVER["SERVER_PORT"] != 80 && $_SERVER["SERVER_PORT"] != 443){
            return false;
        }
        if (strpos($agent, 'micromessenger')) {
            return $name ? ($name === '微信H5') : '微信H5';
        } else if (strpos($agent, 'miniprogram')) {
            return $name ? ($name === '微信小程序') : '微信小程序';
        } else {
            return false;
        }
    }
}