<?php


namespace Daviswwang\LaravelLog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaravelOSS
 * @method static bool upload(string $ability, array | mixed $arguments = [])
 * @package Daviswwang\LaravelOSS\Facades
 */
class LaravelLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravellog';
    }
}
