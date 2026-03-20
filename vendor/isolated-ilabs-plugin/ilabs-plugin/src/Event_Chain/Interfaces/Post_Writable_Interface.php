<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces;

interface Post_Writable_Interface
{
    public function get_post_id();
    public function get_key() : string;
}
