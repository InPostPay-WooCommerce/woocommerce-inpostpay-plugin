<?php

namespace Ilabs\Inpost_Pay\rest;

use Ilabs\Inpost_Pay\InpostPay;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;


abstract class Admin
{
    protected array $post = [];
    protected array $get = [];
    protected array $delete = [];

    protected bool $restricted = false;

    abstract protected function describe();

    public function register(): void {
        if ($this->restricted && !$this->canAccess()) {
            return;
        }
        $this->describe();
        add_action('rest_api_init', function ($server) {
            foreach ($this->post as $path => $function) {
                $server->register_route('inpost', $path, [
                    'methods' => 'POST',
                    'callback' => function ($request) use ($function) {
                        $this->allowOriginHeader();
						LSCacheHelper::no_cache();
                        return $function($request);
                    },
                    'permission_callback' => '__return_true',
                ]);
            }

            foreach ($this->get as $path => $function) {
                $server->register_route('inpost', $path, [
                    'methods' => 'GET',
                    'callback' => function ($request) use ($function) {
                        $this->allowOriginHeader();
	                    LSCacheHelper::no_cache();
                        return $function($request);
                    },
                    'permission_callback' => '__return_true',
                ]);
            }

            foreach ($this->delete as $path => $function) {
                $server->register_route('inpost', $path, [
                    'methods' => 'DELETE',
                    'callback' => function ($request) use ($function) {
                        $this->allowOriginHeader();
	                    LSCacheHelper::no_cache();
                        return $function($request);
                    },
                    'permission_callback' => '__return_true',
                ]);
            }
        });
    }

    /**
     * Check if the current request is allowed access.
     *
     * @return bool
     */
    private function canAccess(): bool
    {
	    return current_user_can('manage_options');
    }

	private function allowOriginHeader(): void {

	}


}
