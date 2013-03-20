<?php

class block_course_copy_alerts extends alerts {

    public function __construct() {
        parent::__construct();
        $this->title = course_copy::str('coursecopynotification');
        $this->description = ''; // course_copy requirement checking has this.
        $this->severity = ''; // 
    }
    public function get_full_html() {
    }
}

