<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\ZipStream\ZipStream\Option;

use Isolated\Inpost_Pay\ZipStream\MyCLabs\Enum\Enum;
/**
 * Methods enum
 *
 * @method static STORE(): Method
 * @method static DEFLATE(): Method
 * @psalm-immutable
 */
class Method extends Enum
{
    const STORE = 0x0;
    const DEFLATE = 0x8;
}
