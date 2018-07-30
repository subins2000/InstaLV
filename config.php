<?php
define('IG_USERNAME', 'USERNAME');
define('IG_PASS', 'PASSWORD');

$cfg_callbacks = [
    'like' => function($user) {
        var_dump($user->getUsername());
    },
    'comment' => function($user, $comment) {
        var_dump($user->getUsername());
        var_dump($comment);
    }
];
