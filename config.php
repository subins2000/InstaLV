<?php
define('IG_USERNAME', 'USERNAME');
define('IG_PASS', 'PASSWORD');

$cfg_callbacks = [
    'like' => function($user) {
        $msg = $user->getUsername() . ' liked !';
        var_dump($msg);
    },
    'comment' => function($user, $comment) {
        $msg = $user->getUsername() . ' : ' . $comment;
        var_dump($msg);
    }
];
