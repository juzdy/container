<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
return [
    'container' => [
        'cache' => [
            'driver' => env('CONTAINER_CACHE_DRIVER', Juzdy\Cache\NullCache::class),
        ],
    ],
];