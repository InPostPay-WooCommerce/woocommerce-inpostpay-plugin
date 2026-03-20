<?php

namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Logger;

interface Logger_Interface
{
    public function log($log);
    /**
     * @depecated
     * @param string $message
     * @param array|null $args
     * @param string|null $context
     *
     * @return mixed
     */
    public function error(string $message, array $args = null, string $context = null);
}
