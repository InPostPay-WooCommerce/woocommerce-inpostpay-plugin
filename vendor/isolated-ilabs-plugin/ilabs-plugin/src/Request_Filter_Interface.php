<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin;

interface Request_Filter_Interface
{
    public function filter($key, $value);
}
