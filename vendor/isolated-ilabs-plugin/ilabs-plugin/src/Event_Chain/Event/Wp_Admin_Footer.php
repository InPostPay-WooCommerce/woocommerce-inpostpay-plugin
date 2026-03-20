<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Event;

use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Abstracts\Abstract_Event;
class Wp_Admin_Footer extends Abstract_Event
{
    public function create()
    {
        add_action('admin_footer', function () {
            $this->callback();
        });
    }
}
