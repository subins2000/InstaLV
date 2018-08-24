<?php
/**
Copyright 2018 Subin Siby

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

if ($_SERVER['REQUEST_URI'] === '/request' && isset($_POST['cmd'])) {
    file_put_contents(__DIR__ . '/request', json_encode([
        'cmd'    => $_POST['cmd'],
        'values' => isset($_POST['values']) ? $_POST['values'] : [],
    ]));
    exit;
}

if ($_SERVER['REQUEST_URI'] === '/response') {
    echo @file_get_contents(__DIR__ . '/response');
    exit;
}

$static_files = [
    '/static/jquery-3.3.1.min.js',
    '/static/bootstrap.min.css'
];

if (in_array($_SERVER['REQUEST_URI'], $static_files)) {
    header('Content-type: text/css', true);
    echo @file_get_contents(__DIR__ . $_SERVER['REQUEST_URI']);
    exit;
}

$live_response =  json_decode(@file_get_contents(__DIR__ . '/live_response'), true);

$live_response = array_merge([
    'comments' => [],
    'likes'    => [],
], empty($live_response) ? [] : $live_response);

$live_response['comments'] = array_reverse($live_response['comments']);
$live_response['likes'] = array_reverse($live_response['likes']);

/**
 * Make pinned comment on top
 */
foreach ($live_response['comments'] as $index => $comment) {
    if ($comment['pinned']) {
        unset($live_response['comments'][$index]);
        array_unshift($live_response['comments'] , $comment);
        break;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="static/bootstrap.min.css" />
        <script type="text/javascript" src="static/jquery-3.3.1.min.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark">
            <a class="navbar-brand" href="#">
                InstaLV!
            </a>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <h2>Controls</h2>
                    <div class="form-group">
                        <label>Refresh between</label>
                        <input type="number" id="refresh_secs" />
                        <label>seconds.<label>
                    </div>
                    <div>
                        Pause : <input type="checkbox" id="pause_refresh" />
                    </div>
                </div>
                <div class="col-8">
                    <h2>Commands</h2>
                    <button type="button"class="btn btn-danger" id="end_stream">End Stream</button>
                    <button type="button"class="btn btn-danger" id="get_stream_info" data-cmd="stream_info">Stream Key/URL</button>
                    <button type="button"class="btn btn-info" id="get_info" data-cmd="info">Info</button>
                    <button type="button"class="btn btn-primary" id="get_viewers" data-cmd="viewers">Viewers</button>
                    <button type="button"class="btn btn-warning" id="clear" data-cmd="clear">Clear Likes & Comments</button>
                    <div style="margin-top: 20px;">
                        <blockquote class="blockquote border" style="min-height: 100px;word-wrap: break-word;">
                            <div id="response"></div>
                            <footer class="blockquote-footer">Response</footer>
                        </blockquote>
                    </div>
                    <script>
                        $(function() {
                            $('#end_stream').on('click', function() {
                                var keep = prompt('Would you like to keep the stream archived for 24 hours ? Type "yes" to keep or anything else to not archive');

                                $.post('/request', {cmd: 'end', values: [keep]});
                            });

                            $('#get_stream_info, #get_info, #get_viewers, #clear').on('click', function() {
                                $.post('/request', {cmd: $(this).data('cmd')});
                            });

                            $(document).on('click', '.pin-comment', function() {
                                var id = $(this).data('id');

                                $.post('/request', {
                                    cmd: 'pin',
                                    values: [id]
                                });
                            });

                            $(document).on('click', '#unpin-comment', function() {
                                $.post('/request', {cmd: 'unpin'});
                            });

                            setInterval(function() {
                                $.get('/response', function(response) {
                                    if ($('#response:hover').length != 0)
                                        return;

                                    response = $.parseJSON(response);

                                    $('#response').html('<u>' + response.cmd + '</u><br/>' + response.values[0]);
                                });
                            }, 1000);
                        });
                    </script>
                </div>
            </div>
            <script>
                var refresh_interval,
                    refresh_secs = parseInt(localStorage['refresh_secs']),
                    rs_input = document.getElementById('refresh_secs');

                if (!refresh_secs)
                    refresh_secs = 5;

                rs_input.value = refresh_secs;

                function startInterval() {
                    refresh_interval = setInterval(function() {
                        if (!document.getElementById('pause_refresh').checked)
                            $('#variable_content').load('/ #variable_content');
                    }, refresh_secs + '000');
                }

                rs_input.onchange = function() {
                    localStorage['refresh_secs'] = rs_input.value;
                    refresh_secs = localStorage['refresh_secs'];

                    clearInterval(refresh_interval);
                    startInterval();
                };

                startInterval();
            </script>
            <div id="variable_content">
                <div class="row">
                    <div class="col-6">
                        <h2>Likes</h2>
                        <ul class="list-group">
                            <?php
                            foreach ($live_response['likes'] as $username) {
                                echo <<<HTML
<li class="list-group-item">
    {$username} liked
</li>
HTML;
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="col-6">
                        <h2>Comments</h2>
                        <ul class="list-group">
                            <?php
                            foreach ($live_response['comments'] as $comment) {
                                if ($comment['pinned']) {
                                    echo <<<HTML
<li class="list-group-item bg-success text-light">
    <div class="row" style="align-items:center;">
        <span class="col-10">{$comment['username']} : {$comment['comment']}</span>
        <a class="col-2 btn" id="unpin-comment">Unpin</a>
    </div>
</li>
HTML;
                                } else {
                                    echo <<<HTML
<li class="list-group-item align-middle">
    <div class="row" style="align-items:center;">
        <span class="col-10">{$comment['username']} : {$comment['comment']}</span>
        <a class="col-2 btn btn-warning pin-comment" data-id="{$comment['id']}">Pin</a>
    </div>
</li>
HTML;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
