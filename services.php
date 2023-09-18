<?php

use Empathy\ELib\Blog\Util;


return [
    'BlogUtil' => function (\DI\Container $c) {
        return new Util();
    }
];
