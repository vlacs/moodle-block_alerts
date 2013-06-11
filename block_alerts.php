<?php

require_once(dirname(__FILE__) . '/lib.php');

class block_alerts extends block_base {
    function init() {
        require(dirname(__FILE__) . '/version.php');
        $this->title = get_string('blockname', 'block_alerts');
        $this->version = $plugin->version;
        $this->cron = 0; // Enable this when the time comes.
    }

    function get_content() {
        global $USER, $COURSE, $CFG;
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $alerts = Alerts::fetch_alerts($COURSE->id, $USER->id);
        $count = count($alerts);

        // We have two options. We can either show condensed alerts here or we 
        // can show a link that brings you to a page that shows you all of your 
        // alerts.
        if($count) {
            $alerts_str = Alerts::str('youhavealerts') . " ({$count})";
            $url = new moodle_url($CFG->wwwroot . '/blocks/alerts/course.php');
            $url->param('user_id', $USER->id);
            $url->param('course_id', $COURSE->id);
            $url = $url->out();
            $alerts_str = "<span style=\"background-color: #FFFF66\"><a href=\"{$url}\">$alerts_str</a></span>";

            $this->content->text = $alerts_str;
        }

        return $this->content;
    }
}
