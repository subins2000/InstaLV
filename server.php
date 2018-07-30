<?php
$live_response =  json_decode(@file_get_contents(__DIR__ . '/live_response'), true);

if (empty($live_response))
    $live_response = [
        'comments' => [],
        'likes'    => [],
    ];

$live_response['comments'] = array_reverse($live_response['comments']);
$live_response['likes'] = array_reverse($live_response['likes']);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark">
            <a class="navbar-brand" href="#">
                InstaLiveStream!
            </a>
        </nav>
        <div class="container">
            <h2>Controls</h2>
            <div class="form-group">
                <label>Refresh between</label>
                <input type="number" id="refresh_secs" />
                <label>seconds.<label>
                <span>Pause <input type="checkbox" id="pause_refresh" />
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
                                echo <<<HTML
<li class="list-group-item">
    {$comment['username']} : {$comment['comment']}
</li>
HTML;
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
