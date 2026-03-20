<?php

namespace Isolated\Inpost_Pay\Isolated_Guzzlehttp\GuzzleHttp;

use Isolated\Inpost_Pay\Isolated_Guzzlehttp\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message) : ?string;
}
