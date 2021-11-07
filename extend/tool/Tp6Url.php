<?php

namespace tool;

class Tp6Url
{
    /**
     * @param string|null $funName //null===action
     * @return string
     */
    function brotherUrl(?string $funName): string
    {
        $c = Request();
        $fun = $funName?:$c->action();
        return "{$c->domain()}/index.php/{$c->controller()}/$fun";
    }
}