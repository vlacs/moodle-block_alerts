<?php
require_once(dirname(__FILE__) . '/init.php');

$course_id = required_param('course_id', PARAM_INT);
$user_id = required_param('user_id', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$user = get_record('user', 'id', $user_id);
$alerts = Alerts::fetch_alerts($course_id, $user_id);
$context = get_context_instance(CONTEXT_COURSE, $course_id);

$course_url = new moodle_url("$CFG->wwwroot/course/view.php");
$course_url->param('id', $course_id);

$nav = array(
    array(
        'name' => $course->fullname,
        'link' => $course_url->out()
    ),
    array(
        'name' => Alerts::str('blockname')
    ),
    array(
        'name' => Alerts::str('viewcoursealerts')
    )
);

$heading = Alerts::str('viewcoursealerts');
print_header($heading, $heading, build_navigation($nav));

// If the user logged in isn't the user we're looking up, then grading 
// capabilities for this course are required.
if($USER->id != $user_id) {
    if(!has_capability('mod/quiz:grade', $context, $USER->id)) {
        error("You can't view another user's alerts.");
    }
}

print_heading(Alerts::str('viewcoursealerts') . ' - ' . $course->fullname, 'center', 1);

if($alerts) {
    foreach($alerts as $alert) {
        print $alert->get_html();
    }
} else {
    print_heading(Alerts::str('noalertsforthisuserinthiscourse'), 'center', 2);
}

print_footer();
