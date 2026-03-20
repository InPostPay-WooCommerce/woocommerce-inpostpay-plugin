<?php

namespace Isolated\Inpost_Pay\Isolated_Guzzlehttp\GuzzleHttp;

use Isolated\Inpost_Pay\Isolated_Guzzlehttp\Psr\Http\Message\RequestInterface;
use Isolated\Inpost_Pay\Isolated_Guzzlehttp\Psr\Http\Message\ResponseInterface;
interface MessageFormatterInterface
{
    /**
     * Returns a formatted message string.
     *
     * @param RequestInterface       $request  Request that was sent
     * @param ResponseInterface|null $response Response that was received
     * @param \Throwable|null        $error    Exception that was received
     */
    public function format(RequestInterface $request, ?ResponseInterface $response = null, ?\Throwable $error = null) : string;
}
