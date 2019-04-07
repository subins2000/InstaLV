# [InstaLV](//subinsb.com/InstaLV) ❤

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

[Read the blog post](//subinsb.com/InstaLV)

## FAQ

#### OBS gives a "Failed to connect" error

This is mostly due to an invalid stream key: The stream key changes **every** time you start a new stream so it must be replaced in OBS every time.

#### I've stopped streaming but Instagram still shows me as live

This is due to you not running the "stop" command inside the script. You cannot just close the command window to make Instagram stop streaming, you must run the stop command in the script. If you *do* close the command window however, start it again and just run the stop command, this should stop Instagram from listing to live content.

#### I get an error inside of Instagram when archiving my story

This is usually due to archiving a stream that had no content (video). Just delete the archive and be go on with your day.

# Donate

If you have found this software useful, [Please Donate](https://subinsb.com/donate)
