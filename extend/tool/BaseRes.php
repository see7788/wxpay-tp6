<?php

namespace tool;

use Exception;

class BaseRes
{
    //env('APP_DEBUG')
    private $successDb, $errorDb = [];

    /**
     * array|string $validate 验证器类名或者验证规则数组
     * array  $message 错误提示信息
     * bool $batch 是否批量验证
     * bool  $failException是否抛出异常

    function validate($validate,$message)
    {
        validate()->
    } */

    /**
     * @param ...$db
     * @return  BaseRes
     */
    function errorPush(...$db): BaseRes
    {
        array_push($this->errorDb, ...$db);
        return $this;
    }

    function successSet($db): self
    {
        $this->successDb = $db;
        return $this;
    }

    /**
     * @throws Exception
     */
    function successGet()
    {
        if ($this->errorDb) {
            if (env('APP_DEBUG')) {
                return json($this->errorDb);
            }
            throw new Exception(json_encode($this->errorDb));
        } else {
            return $this->successDb;
        }
    }
}