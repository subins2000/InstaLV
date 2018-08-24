# InstaLV ❤

A PHP script + webapp that allows you to go live on Instagram with any streaming program that supports RTMP!

Built with [mgp25's amazing Instagram Private API Wrapper for PHP](https://github.com/mgp25/Instagram-API/).

**InstaLV** is a fork of [JRoy/InstagramLive-PHP](https://github.com/JRoy/InstagramLive-PHP).

![Screenshot](//i.imgur.com/4LmfMXi.png)

## Features

* A webapp to easily control livestream
* See livestream viewers
* See likes & comments during livestream
* Pin/Unpin comments
* Call custom callbacks when a user like or comment

## Setup

* [Install PHP](http://php.net/downloads.php)
* [Install Composer](https://getcomposer.org/download/)
* Download the [latest release](https://github.com/subins2000/InstaLV/releases) or clone this repo
* Run ```composer install``` in the downloaded/cloned folder
* Edit the username and password inside `config.php` with your Instagram username and password
* Run the `live.php` script :
    ```bash
    php live.php
    ```
* On another terminal, run the server :
    ```bash
    php -S localhost:8000 server.php
    ```
  You can also simply execute the `run_server.sh` or `run_server.bat` (Windows) which will run the above command.
* Open the URL [`http://localhost:8000`](http://localhost:8000) in a browser.
* In the InstaLV webpage, click on the button `Stream Key/URL`. Copy the Stream-URL and Stream-Key and paste them into your streaming software. [See OBS-Setup](#obs-setup)

## OBS-Setup

* Go to the "Stream" section of your OBS Settings
* Set "Stream Type" to "Custom Streaming Server"
* Set the "URL" field to the stream url you got from the script
* Set the "Stream key" field to the stream key you got from the script
* Make Sure "Use Authentication" is **unchecked** and press "OK"
* Start Streaming in OBS
* To stop streaming, run the "stop" command in your terminal and then press "Stop Streaming" in OBS
* Note: To emulate the exact content being sent to Instagram, set your OBS canvas size to 720x1280. This can be done by going to Settings->Video and editing Base Canvas Resolution to "720x1280".

## FAQ

#### OBS gives a "Failed to connect" error

This is mostly due to an invalid stream key: The stream key changes **every** time you start a new stream so it must be replaced in OBS every time.

#### I've stopped streaming but Instagram still shows me as live

This is due to you not running the "stop" command inside the script. You cannot just close the command window to make Instagram stop streaming, you must run the stop command in the script. If you *do* close the command window however, start it again and just run the stop command, this should stop Instagram from listing to live content.

#### I get an error inside of Instagram when archiving my story

This is usually due to archiving a stream that had no content (video). Just delete the archive and be go on with your day.

# Donate

If you have found this software useful, [Please Donate](https://subinsb.com/donate)
