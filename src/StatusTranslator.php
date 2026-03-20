<?php

namespace Ilabs\Inpost_Pay;

class StatusTranslator
{
    public static function paymentStatuses()
    {
        return [
            'AUTHORIZED',
            'DECLINED',
            'CANCELLED',
            'ERROR',
        ];
    }

    public static function paymentStatusToText($status) {
        switch ($status) {
            case 'AUTHORIZED': return 'Opłacono';
            case 'DECLINED': return 'Płatność odrzucona';
            case 'CANCELLED': return 'Płatnosć znulowana';
            case 'ERROR': return 'Błąd płatności';
            default: return 'Oczekuje na płatność';
        }
    }

    public static function ayastmAvailableStatusses() {
        return wc_get_order_statuses();
    }

    public static function orderStatuses()
    {
        return [
            'wc-on-hold' => 'ORDER_PROCESSING',
            'wc-processing' => 'ORDER_COMPLETED',
            'wc-cancelled' => 'ORDER_TIMEOUT',
            'wc-failed' => 'ORDER_REJECTED',
        ];
    }
}