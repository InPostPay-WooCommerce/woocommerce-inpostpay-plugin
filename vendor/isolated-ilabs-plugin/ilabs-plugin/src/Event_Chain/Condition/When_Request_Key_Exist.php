<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Condition;

use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Abstracts\Abstract_Condition;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces\Condition_Interface;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Request;
class When_Request_Key_Exist extends Abstract_Condition implements Condition_Interface
{
    /**
     * @var string
     */
    private $key;
    public function __construct(string $key)
    {
        $this->key = $key;
    }
    public function assert() : bool
    {
        return null !== (new Request())->get_by_key($this->key);
    }
    /**
     * @return string
     */
    public function get_key() : string
    {
        return $this->key;
    }
}
