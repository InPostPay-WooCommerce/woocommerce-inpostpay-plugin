<?php

namespace {
    // Don't redefine the functions if included multiple times.
    if (!\function_exists('Isolated\\Inpost_Pay\\Isolated_Guzzlehttp\\GuzzleHttp\\describe_type')) {
        require __DIR__ . '/functions.php';
    }
}
