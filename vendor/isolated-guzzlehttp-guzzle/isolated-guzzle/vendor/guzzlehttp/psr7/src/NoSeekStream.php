<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Isolated_Guzzlehttp\GuzzleHttp\Psr7;

use Isolated\Inpost_Pay\Isolated_Guzzlehttp\Psr\Http\Message\StreamInterface;
/**
 * Stream decorator that prevents a stream from being seeked.
 */
final class NoSeekStream implements StreamInterface
{
    use StreamDecoratorTrait;
    /** @var StreamInterface */
    private $stream;
    public function seek($offset, $whence = \SEEK_SET) : void
    {
        throw new \RuntimeException('Cannot seek a NoSeekStream');
    }
    public function isSeekable() : bool
    {
        return \false;
    }
}
