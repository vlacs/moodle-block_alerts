<?php

define('BLOCK_ALERTS_SEVERITY_CRITICAL', 'alert_critical');
define('BLOCK_ALERTS_SEVERITY_WARNING', 'alert_warning');
define('BLOCK_ALERTS_SEVERITY_INFO', 'alert_info');

abstract class Alerts {

    // We may want to eventually move this to a CSS file, but since we need to 
    // inject this into a Moodle page without altering Moodle code, we should 
    // stick to keeping style info inside the HTML itself.
    public static $SEVERITY_COLORS = array (
        // Critical
        BLOCK_ALERTS_SEVERITY_CRITICAL => array (
            'background' => '#FFA8A8',
            'border' => '#FF1F1F'
        ),
        // Warning
        BLOCK_ALERTS_SEVERITY_WARNING => array (
            'background' => '#FFF0B3',
            'border' => '#FFCC00'
        ),
        // Info
        BLOCK_ALERTS_SEVERITY_INFO => array (
            'background' => '#B3FFB3',
            'border' => '#00B300'
        )
    );

    /**
     * Some basic information that should exist for any alert.
     */
    protected $title;
    protected $description;
    protected $severity;
    protected $issuer;

    public function __construct() {
        $this->title = '';
        $this->description = '';
        $this->severity = 0;
        $this->issuer = '';
    }

    // This finds every plugin and attempts to fetch alterts based on the 
    // course id and user id.
    public static function fetch_alerts($course_id, $user_id) {
    }

    public static function fetch_alerts_as_html($course_id, $user_id) {
    }

    public static function fetch_alerts_summary_as_html($course_id, $user_id) {
    }

    /**
     * This method makes a box (div) and puts stuff ($text) into it.
     * The box gets assigned some style and classes to make it catch the user's 
     * eye.
     *
     * TODO:This should be refactored for Moodle 2.4.
     *
     * @param string    $text is what gets put inside the box.
     * @param string    $severity alters the attributes of the box.
     * @return string
     */
    public static function box_wrapper($text, $severity=BLOCK_ALERTS_SEVERITY_INFO) {
        // I was thinking about using print_box_start and such for this, but 
        // I changed my mind. This can be refactored to be much nicer looking 
        // when it gets ported to Moodle 2.4 using the newer output libs.
        // --jdoane (3/20/2013)
        if(!isset(self::$SEVERITY[$severity])) { error('invalid severity level for box wrapper'); }
        $bg = self::$SEVERITY[$severity]['background'];
        $bc = self::$SEVERITY[$severity]['border'];
        $style = "border: 1px solid {$bc}; background-color: {$bg};";
        return "<div class=\"alert_block\" style=\"{$style}\">{$text}</div>";
    }

    /**
     * Abstract methods implemented by the plugin.
     */

    /**
     * Gets a short description of the alert.
     * TODO: Debate - Should this be abstract?
     */
    public abstract function get_summary();

    public function get_full_html() {
    }

    public function get_summary_html() {
    }

    /**
     * Normal class methods; getter functions.
     */

    /**
     * Gets the title of the alert.
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Gets the full description of the alert.
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Determines how important this alert is and how it should be presented.
     */
    public function get_severity() {
        return $this->severity;
    }

    /**
     * Who or what created the alert?
     */
    public function get_issuer() {
    }
}
