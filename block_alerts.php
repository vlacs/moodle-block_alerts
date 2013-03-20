<?php

class block_alerts extends block_base {
    function init() {
        require(dirname(__FILE__) . '/version.php');
        $this->title = get_string('blockname', 'block_alerts');
        $this->version = $plugin->version;
        $this->cron = 0; // Enable this when the time comes.
    }

    function get_content() {
    }
}
