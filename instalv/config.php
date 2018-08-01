<?php
define('IG_USERNAME', 'USERNAME');
define('IG_PASS', 'PASSWORD');

$cfg_callbacks = [
    'like' => function($user) {
        $msg = $user->getUsername() . ' liked !';
        exec('nohup notify-send "' . $msg . '" > /dev/null &');
    },
    'comment' => function($user, $comment) {
        $msg = $user->getUsername() . ' : ' . $comment;
        exec('nohup notify-send "' . $msg . '" > /dev/null &');
    }
];
