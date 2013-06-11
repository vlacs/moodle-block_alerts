<?php
global $CFG;
require_once("{$CFG->dirroot}/blocks/course_copy/lib.php");

class block_course_copy_alerts extends alerts {

    public static function fetch_alerts($course_id, $user_id) {
        $context = get_context_instance(CONTEXT_COURSE, $course_id);
        $is_student = true;
        if(has_capability('mod/quiz:grade', $context, $user_id)) {
            $is_student = false;
        }
        $alerts = array();
        $course_copy = course_copy::create();
        $pushes = $course_copy->fetch_pending_pushes_by_child_course($course_id);
        if(!$pushes) {
            return false;
        }

        foreach($pushes as $push) {
            $push_instances = $course_copy->fetch_push_instances($push->id, true);
            if(!$push_instances) {
                // Skip over this odd occurance.
                // TODO Get rid of this error.
                error('Why did we no push instances when we checked by child?');
                continue;
            }
            foreach($push_instances as $pi) {
                $dest_course_id = $pi->dest_course_id;
                // We don't let pushes go if there are multiple CMs with the 
                // same name. If that's true we can't be certain which one to 
                // deprecate or copy grades to.
                if(course_copy::course_module_matches_many($push, $pi)) {
                    // TODO: Don't show this to a student. If the user cannot 
                    // grade in this course, then don't return any alerts.
                    if(!$is_student) {
                        $desc = course_copy::str('coursemoduleswithsamename');
                        $alert = new block_course_copy_alerts();
                        $description = Alerts::heading_wrapper(self::get_cm_name($push->course_module_id), 2); 
                        $description .= "<p>$desc</p>";
                        $alert->set_description($description);
                        $alert->set_severity(BLOCK_ALERTS_SEVERITY_CRITICAL);
                        $alerts[] = $alert;
                    }
                    continue;
                }

                $matching_cm_id = course_copy::match_course_module($push->course_module_id, $pi->dest_course_id);
                // If we have match, we could be copying grades and changing 
                // names. So we need to run a requirement check for each course 
                // module to make sure all attmpts are wrapped up.
                if($matching_cm_id) {
                    // TODO: Check to see if we're copying grades or not. If 
                    // we're not, this check isn't necessary.
                    $check = course_copy_requirement_check::check_course_module($matching_cm_id);
                    if(!$check->passed()) {
                        $alert = new block_course_copy_alerts();
                        $alert->set_severity(BLOCK_ALERTS_SEVERITY_CRITICAL);

                        if($is_student) {
                            // Check and describe by user id.
                            if(!$check->passed_for_user($user_id)) {
                                $description .= $check->describe($user_id, false);
                                $alert->set_summary($check->describe($user_id, true));
                                $alerts[] = $alert;
                            }
                        } else {
                            $description = $check->describe(false, false);
                            $alert->set_description($description);
                            $alert->set_summary($check->describe(false, true));
                            $alerts[] = $alert;
                        }
                        continue;
                    }
                }

            }
        }
        return $alerts;
    }

    public function __construct() {
        parent::__construct();
        $this->title = course_copy::str('coursecopynotification');
        $this->issuer = course_copy::str('blockname');
    }
}

