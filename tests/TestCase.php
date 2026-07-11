<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function prepareUrlForRequest($uri)
    {
        if (str_starts_with($uri, '/')) {
            $uri = substr($uri, 1);
        }

        if (! str_starts_with($uri, 'http')) {
            $mainDomainRoutes = ['', 'all-packages', 'warning', 'billing', 'portal', 'php-artisan-optimize'];
            $isMainDomain = in_array(explode('/', $uri)[0], $mainDomainRoutes);
            $domain = $isMainDomain ? 'http://localhost' : 'http://billing.localhost';
            $uri = $domain.'/'.$uri;
        }

        return trim($uri, '/');
    }
}
