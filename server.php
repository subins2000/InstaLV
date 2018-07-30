<?php
$live_response =  json_decode(@file_get_contents(__DIR__ . '/live_response'), true);

if (empty($live_response))
    $live_response = [
        'comments' => [],
        'likes'    => [],
    ];
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark">
            <a class="navbar-brand" href="#">
                InstaLiveStream!
            </a>
        </nav>
        <div class="container">
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
    </body>
</html>
