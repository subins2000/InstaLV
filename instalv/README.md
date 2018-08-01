# InstaLV

InstaLV is a small webapp to control the live stream. It has the ability to :

* Get stream info (url,key), viewers info
* See likes & comments during live
* Stop stream
* Callbacks during like and comment

## Usage

* Change the config inside `config.php`
* Open a terminal and run the `live.php` :
    ```bash
    php live.php
    ```
* Open another terminal and start a local server
    ```bash
    php -S localhost:8000 server.php
    ```
    For easy use, there's a `run_server.sh` script which will do this.
* Open the url `http://localhost:8000/` in a browser to see the webapp
