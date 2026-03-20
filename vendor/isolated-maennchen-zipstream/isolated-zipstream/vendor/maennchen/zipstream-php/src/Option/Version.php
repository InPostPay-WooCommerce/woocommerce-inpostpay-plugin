<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\ZipStream\ZipStream\Option;

use Isolated\Inpost_Pay\ZipStream\MyCLabs\Enum\Enum;
/**
 * Class Version
 * @package ZipStream\Option
 *
 * @method static STORE(): Version
 * @method static DEFLATE(): Version
 * @method static ZIP64(): Version
 * @psalm-immutable
 */
class Version extends Enum
{
    const STORE = 0xa;
    // 1.00
    const DEFLATE = 0x14;
    // 2.00
    const ZIP64 = 0x2d;
    // 4.50
}
