<?php
if (php_sapi_name() !== "cli") {
    die("You may only run this inside of the PHP Command Line! If you did run this in the command line, please report: \"".php_sapi_name()."\" to the InstagramLive-PHP Repo!");
}

logM("Loading InstagramLive-PHP v0.4...");
set_time_limit(0);
date_default_timezone_set('America/New_York');

//Load Depends from Composer...
require __DIR__.'/vendor/autoload.php';
use InstagramAPI\Instagram;
use InstagramAPI\Request\Live;

require_once 'config.php';
/////// (Sorta) Config (Still Don't Touch It) ///////
$debug = false;
$truncatedDebug = false;
/////////////////////////////////////////////////////

if (IG_USERNAME == "USERNAME" || IG_PASS == "PASSWORD") {
    logM("Default Username and Passwords have not been changed! Exiting...");
    exit();
}

//Login to Instagram
logM("Logging into Instagram...");
$ig = new Instagram($debug, $truncatedDebug);
try {
    $loginResponse = $ig->login(IG_USERNAME, IG_PASS);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        logM("Two-Factor Required! Please check your phone for an SMS Code!");
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();
        print "\nType your 2FA Code from SMS> ";
        $handle = fopen ("php://stdin","r");
        $verificationCode = trim(fgets($handle));
        logM("Logging in with 2FA Code...");
        $ig->finishTwoFactorLogin(IG_USERNAME, IG_PASS, $twoFactorIdentifier, $verificationCode);
    }
} catch (\Exception $e) {
    if (strpos($e->getMessage(), "Challenge") !== false) {
        logM("Account Flagged: Please sign out of all phones and try logging into instagram.com from this computer before trying to run this script again!");
        exit();
    }
    echo 'Error While Logging in to Instagram: '.$e->getMessage()."\n";
    exit(0);
}

//Block Responsible for Creating the Livestream.
try {
    if (!$ig->isMaybeLoggedIn) {
        logM("Couldn't Login! Exiting!");
        exit();
    }
    logM("Logged In! Creating Livestream...");
    $stream = $ig->live->create();
    $broadcastId = $stream->getBroadcastId();
    $ig->live->start($broadcastId);
    // Switch from RTMPS to RTMP upload URL, since RTMPS doesn't work well.
    $streamUploadUrl = preg_replace(
        '#^rtmps://([^/]+?):443/#ui',
        'rtmp://\1:80/',
        $stream->getUploadUrl()
    );

    //Grab the stream url as well as the stream key.
    $split = preg_split("[".$broadcastId."]", $streamUploadUrl);

    $streamUrl = $split[0];
    $streamKey = $broadcastId.$split[1];

    logM("================================ Stream URL ================================\n".$streamUrl."\n================================ Stream URL ================================");

    logM("======================== Current Stream Key ========================\n".$streamKey."\n======================== Current Stream Key ========================");

    logM("^^ Please Start Streaming in OBS/Streaming Program with the URL and Key Above ^^");

    logM("Live Stream is Ready for Commands:");
    newCommand($ig->live, $broadcastId, $streamUrl, $streamKey);
    logM("Something Went Super Wrong! Attempting to At-Least Clean Up!");
    $ig->live->getFinalViewerList($broadcastId);
    $ig->live->end($broadcastId);
} catch (\Exception $e) {
    echo 'Error While Creating Livestream: '.$e->getMessage()."\n";
}

function writeOutput($cmd, $msg) {
    $response = [
        'cmd'    => $cmd,
        'values' => is_array($msg) ? $msg : [$msg],
    ];
    file_put_contents(__DIR__ . '/response', json_encode($output));
}

// The following loop performs important requests to obtain information
// about the broadcast while it is ongoing.
// NOTE: This is REQUIRED if you want the comments and likes to appear
// in your saved post-live feed.
// NOTE: These requests are sent *while* the video is being broadcasted.
$lastCommentTs = 0;
$lastLikeTs = 0;

// The controlling variable for the infinite while loop
$exit = false;

do {
    $cmd = '';

    $request = json_decode(file_get_contents('request'), true);

    if (!empty($request)) {
        $cmd = $request['cmd'];
        $values = $request['values'];
    }

    if($cmd == 'ecomments') {
        $live->enableComments($broadcastId);
        writeOutput('info', "Enabled Comments!");
    } elseif ($cmd == 'dcomments') {
        $live->disableComments($broadcastId);
        writeOutput('info', "Disabled Comments!");
    } elseif ($cmd == 'stop' || $cmd == 'end') {
        //Needs this to retain, I guess?
        $live->getFinalViewerList($broadcastId);
        $live->end($broadcastId);
        writeOutput('prompt', ["Stream Ended!\nWould you like to keep the stream archived for 24 hours ?", 'exit']);
    } elseif ($cmd == 'exit'){
        $added = '';
        $archived = $values[0];

        if ($archived == 'yes') {
            $live->addToPostLive($broadcastId);
            $added = 'Livestream added to archive!\n';
        }

        writeOutput('info', $added . "Wrapping up and exiting...");
    } elseif ($cmd == 'url') {
        writeOutput('info', $streamUrl);
    } elseif ($cmd == 'key') {
        writeOutput('info', $streamKey);
    } elseif ($cmd == 'info') {
        $info = $live->getInfo($broadcastId);
        $status = $info->getStatus();
        $muted = var_export($info->is_Messages(), true);
        $count = $info->getViewerCount();
        writeOutput('info', "Info:\nStatus: $status\nMuted: $muted\nViewer Count: $count");
    } elseif ($cmd == 'viewers') {
        $output = 'Viewers:\n';
        $live->getInfo($broadcastId);
        foreach ($live->getViewerList($broadcastId)->getUsers() as &$cuser) {
            $output .= "@".$cuser->getUsername()." (".$cuser->getFullName().")";
        }
        writeOutput('info', $output);
    }

    // Get broadcast comments.
    // - The latest comment timestamp will be required for the next
    //   getComments() request.
    // - There are two types of comments: System comments and user comments.
    //   We compare both and keep the newest (most recent) timestamp.
    $commentsResponse = $live->getComments($broadcastId, $lastCommentTs);
    $systemComments = $commentsResponse->getSystemComments();
    $comments = $commentsResponse->getComments();
    if (!empty($systemComments)) {
        $lastCommentTs = end($systemComments)->getCreatedAt();
    }
    if (!empty($comments) && end($comments)->getCreatedAt() > $lastCommentTs) {
        $lastCommentTs = end($comments)->getCreatedAt();
    }

    // Get broadcast heartbeat and viewer count.
    $live->getHeartbeatAndViewerCount($broadcastId);

    // Get broadcast like count.
    // - The latest like timestamp will be required for the next
    //   getLikeCount() request.
    $likeCountResponse = $live->getLikeCount($broadcastId, $lastLikeTs);
    $lastLikeTs = $likeCountResponse->getLikeTs();

    foreach($likeCountResponse->getLikers() as $user) {
        $likerID = $user->getUserId();
    }

    foreach ($comments as $comment) {
        $commentText = $comment->getText();
    }

    sleep(2);
} while(!$exit);

/**
 * The handler for interpreting the commands passed via the command line.
 */
function newCommand(Live $live, $broadcastId, $streamUrl, $streamKey) {
    print "\n> ";
    $handle = fopen ("php://stdin","r");
    $line = trim(fgets($handle));

    fclose($handle);
    newCommand($live, $broadcastId, $streamUrl, $streamKey);
}

/**
 * Logs a message in console but it actually uses new lines.
 */
function logM($message) {
    print $message."\n";
}
