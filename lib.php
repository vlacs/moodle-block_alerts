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

    public static function str($string) {
        return get_string($string, 'block_alerts');
    }

    /**
     * Some basic information that should exist for any alert.
     */
    protected $title;
    protected $description;
    protected $summary;
    protected $severity;
    protected $issuer;

    public function __construct() {
        $this->title = '';
        $this->description = '';
        $this->severity = BLOCK_ALERTS_SEVERITY_INFO;
        $this->issuer = '';
    }

    public static function fetch_plugin_classes() {
        $classes = array();
        $path = dirname(__FILE__);
        $path .= '/plugins';
        $files = scandir($path);
        foreach($files as $f) {
            if($f == '.' or $f == '..') {
                continue;
            }
            if(preg_match('/.php$/', $f)) {
                require_once("{$path}/$f");
                $class = preg_replace('/.php$/', '_alerts', $f);
                if(!class_exists($class)) {
                    error('Plugin file exists but class does not.');
                }
                $classes[] = $class;
            }
        }
        return $classes;
    }

    // This finds every plugin and attempts to fetch alterts based on the 
    // course id and user id.
    public static function fetch_alerts($course_id, $user_id) {
        $all_alerts = array();
        $plugins = self::fetch_plugin_classes();
        // If no plugins are installed, we're done.
        if(!$plugins) {
            return false;
        }

        foreach($plugins as $p) {
            $alerts = $p::fetch_alerts($course_id, $user_id);
            if($alerts) {
                $all_alerts = array_merge($all_alerts, $alerts);
            }
        }
        return $all_alerts;
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
    public static function box_wrapper($text, $severity=BLOCK_ALERTS_SEVERITY_INFO, $shrink=false) {
        // I was thinking about using print_box_start and such for this, but 
        // I changed my mind. This can be refactored to be much nicer looking 
        // when it gets ported to Moodle 2.4 using the newer output libs.
        // --jdoane (3/20/2013)
        if(!isset(self::$SEVERITY_COLORS[$severity])) { error('invalid severity level for box wrapper'); }
        $bg = self::$SEVERITY_COLORS[$severity]['background'];
        $bc = self::$SEVERITY_COLORS[$severity]['border'];
        $style = "border: 1px solid {$bc}; background-color: {$bg}; width: auto; min-width: 35em; display: inline-block; margin: 0.75em; padding: 0.4em;";
        if ($shrink) {
            $style .= 'font-size: smaller;';
        }
        $center = '<div style="display: table; margin-left: auto; margin-right: auto;">';
        return "$center<div class=\"alert_block\" style=\"{$style}\">{$text}</div></div><br />";
    }

    /**
     * Basic wrapper that always returns a string. We can get rid of this in 
     * Moodle 2.x since output libraries are a bit more intellegent... or we can 
     * keep using this and just port it.
     */
    public static function heading_wrapper($text, $size=1, $align='center', $class='main') {
        return print_heading($text, $align, $size, $class, true);
    }

    /**
     * Abstract methods implemented by the plugin.
     */

    public function get_html($summary=false) {
        $head_size = 2;

        $text = print_heading($this->title, 'center', $head_size, 'main', true);

        if ($summary) {
            $text .= $this->get_summary();
        } else {
            $text .= $this->get_description();
        }
        $text = self::box_wrapper($text, $this->severity, $summary);
        return $text;
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

    public function set_description($desc) {
        $this->description = $desc;
    }

    public function get_summary() {
        return $this->summary;
    }

    public function set_summary($summary) {
        $this->summary = $summary;
    }

    /**
     * Determines how important this alert is and how it should be presented.
     */
    public function get_severity() {
        return $this->severity;
    }

    public function set_severity($severity) {
        if(!array_key_exists($severity, self::$SEVERITY_COLORS)) {
            error("Unable to set unknown severity level: ($severity)");
        }
        $this->severity = $severity;
    }

    /**
     * Who or what created the alert?
     */
    public function get_issuer() {
        return $this->issuer;
    }
}
