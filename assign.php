<?php
    require_once('../../config.php');

    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad');
    }

    // eip
    $sid           = optional_param('sid', '', PARAM_RAW);

    $context = null;
    $assign_course = optional_param('assign_course', 0, PARAM_INT);
    if ($assign_course)
    {
        $context = get_context_instance(CONTEXT_COURSE, $assign_course);
    }
    else
    {
        $courseid = required_param('courseid', PARAM_INT); // context id
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $PAGE->set_course($course);
        $context = $PAGE->context;
    }
    if (!$context) {
        error("Context ID was incorrect (can't find it)");
    }

    $assign_roleid = optional_param('assign_roleid', 0, PARAM_INT);
    $assign_roleid_course = optional_param('assign_roleid_course', 0, PARAM_INT);
    if (!$assign_roleid && !$assign_roleid_course) {
        error("Role ID can't find it");
    } else {
        $assign_roleid = ($assign_roleid ? $assign_roleid : $assign_roleid_course);
    }

    $role = $DB->get_record("role", array("id" => $assign_roleid));

    $inmeta = 0;
    if ($context->contextlevel == CONTEXT_COURSE) {
        $courseid = $context->instanceid;
        if ($course = $DB->get_record('course', array('id' => $courseid))) {
            $inmeta = $course->metacourse;
        } else {
            error('Invalid course id');
        }

    } 
    /*else if (!empty($courseid)){ // we need this for user tabs in user context
        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            error('Invalid course id');
        }

    } else {
        $courseid = SITEID;
        $course = clone($SITE);
    }*/

    require_login($course);

    require_capability('moodle/role:assign', $context);

    $users = optional_param('userid', array(), PARAM_INT);        // array of user id
    $roleid = $role->id;

    if (count($users) > 0 and ($form = data_submitted()) and confirm_sesskey()) {

        if (count($users) > 0)
        {
            $assignableroles  = get_assignable_roles(get_context_instance(CONTEXT_COURSE, $courseid));
	    if (array_key_exists($role->id, $assignableroles))
            {
            // For each "userd" in the form...
            foreach ($form->userid as $userid => $value) {

            $user_has_role = $DB->record_exists('role_assignments', array('userid' => $userid, 'contextid' => $context->id, 'roleid' => $roleid));

            // Assign or Unassign role
            /*if ( $assign_unassign == 1 && $user_has_role ) {
                role_unassign( $role->id, $userid, $context->id );
            } else*/ if ( /*$assign_unassign == 0 &&*/ !$user_has_role ) {
                role_assign( $role->id, $userid, $context->id );                  
            }

            if (!is_enrolled($context, $userid))
            {
                enrol_try_internal_enrol($courseid, $userid);
            }

            }
            }
        }

        global $SESSION;
        $SESSION->user_filtering = array();

        // Redirect to calling page
        redirect('pick.php?courseid='.$courseid, get_string('changessaved'));
    }

$PAGE->set_url('/blocks/pick_students/assign.php');
$PAGE->set_pagelayout('base');

$title = print_context_name($context) . ' - ';
$strtitle = get_string('enrolledusers', 'enrol');

$PAGE->set_title($strtitle);
$PAGE->set_heading($title . $strtitle);

$PAGE->navbar->add(get_string('pick_students', 'block_pick_students'));
$PAGE->navbar->add($strtitle);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignrolesin', 'role', print_context_name($context)), 2);

    echo '<form method="post" action="assign.php?courseid='.$courseid.'" name="form">';
    echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';

    echo '<input type="hidden" name="assign_course" value="'.$assign_course.'" />';
    echo '<input type="hidden" name="assign_roleid" value="'.$assign_roleid.'" />';

    if (isset($_POST['users']) && count($_POST['users']) > 0)
    {

    $table->head  = array (get_string('fullname'),
                get_string('email'),
                get_string('institution'),
                get_string('department'),
                get_string('city'),
                "");
    $table->align = array ('left', 'left', 'left', 'left', 'left', 'center');
    $table->width = "100%";
    foreach ($_POST['users'] as $userid => $username) {
            // Simply skip users not existing
            if ( !($user = $DB->get_record("user", array('id' => $userid))) ) {
                continue;
            }

            $message = 'ok';
            $isok = true;

                // Check if user has/has not the role in the context
                $user_has_role = $DB->record_exists('role_assignments', array('userid' => $userid, 'contextid' => $context->id, 'roleid' => $roleid));

                if ( $user_has_role /*&& !$assign_unassign*/ ) {
                    //$message = '<span class="error">'.'Already assigned'.'</span>';
                    $isok = false;
                }/* else if ( !$user_has_role && $assign_unassign ) {
                    $message = '<span class="error">'.'Not assigned'.'</span>';
                    $isok = false;
                }*/

            $table->data[] = array(
            fullname($user, true),
            $user->email,
            $user->institution,
            $user->department,
            $user->city,
            $message. '<input type="hidden" name="userid['.$userid.']" value="1" />' );
    }
    print_table($table);
    
    }
    else
    {
        print_heading(get_string('nousersfound')); 
    }

           echo "<br/>";
           echo "<div class='continuebutton'>";
           notice_yesno(get_string('confirm_execution', 'block_pick_students'), '', $CFG->wwwroot.'/blocks/pick_students/pick.php?courseid=' . $courseid);
           echo "</div>";

    echo "</form>\n";


echo $OUTPUT->footer();
