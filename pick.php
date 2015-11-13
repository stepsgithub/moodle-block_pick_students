<?php

require_once('../../config.php');
require_once('lib/eip.php');

require_once('lib/filters.php');

$courseid = optional_param('courseid', SITEID, PARAM_INTEGER);

if ($courseid == SITEID) {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
    $title = '';
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
    $title = print_context_name($context) . ' - ';
}

require_capability('moodle/course:enrolreview', $context);

// eip
$sid            = optional_param('_sid', '', PARAM_RAW);
$ban            = optional_param('_ban', '', PARAM_RAW);

$urlparams = array('courseid' => $courseid);

$PAGE->set_url('/blocks/pick_students/pick.php', $urlparams);
$PAGE->set_pagelayout('base');

$strtitle = get_string('enrolledusers', 'enrol');

$PAGE->requires->data_for_js('strchoose', get_string('choose') . '...', true);
$PAGE->requires->js('/blocks/pick_students/javascript/jquery-1.7.2.min.js', true);
$PAGE->requires->js('/blocks/pick_students/javascript/jquery.relatedselects.min.js', true);
$PAGE->requires->js('/blocks/pick_students/javascript/relatedselects.js', true);

$PAGE->set_title($strtitle);
$PAGE->set_heading($title . $strtitle);

$PAGE->navbar->add(get_string('pick_students', 'block_pick_students'));
$PAGE->navbar->add($strtitle);

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle, 2);

echo html_writer::start_tag('form', array('action'=>'assign.php', 'method'=>'post', 'id' => 'students'));

    echo html_writer::start_tag('div');

    echo get_string('school', 'block_pick_students');
    choose_from_menu(schlist(), '_sid', '', 'choose',
                    '',
                    '0', false, false, 0, '_sid');
    echo get_string('class', 'block_pick_students');
    echo html_writer::start_tag('select', array('name' => '_ban'));
    echo html_writer::end_tag('select');

    echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'courseid', 'value'=>$courseid));
    echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sid', 'value'=>$sid));
    echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'ban', 'value'=>$ban));

    echo html_writer::start_tag('/div');

    if (!empty($sid) && !empty($ban)) {

    askban($sid, $ban);

    $sort         = optional_param('sort', 'name', PARAM_ALPHA);
    $dir          = optional_param('dir', 'ASC', PARAM_ALPHA);
    $page         = optional_param('page', 0, PARAM_INT);
    $perpage      = optional_param('perpage', 30, PARAM_INT);        // how many per page

    // create the user filter form
    $ufiltering = new pick_user_filtering();

    // Carry on with the user listing
    $context = context_system::instance();
    $extracolumns = get_extra_user_fields($context);
    $columns = array_merge(array('firstname', 'lastname'), $extracolumns,
            array('city', 'country', 'lastaccess'));

    foreach ($columns as $column) {
        $string[$column] = get_string("$column");
        if ($sort != $column) {
            $columnicon = "";
            if ($column == "lastaccess") {
                $columndir = "DESC";
            } else {
                $columndir = "ASC";
            }
        } else {
            $columndir = $dir == "ASC" ? "DESC":"ASC";
            if ($column == "lastaccess") {
                $columnicon = $dir == "ASC" ? "up":"down";
            } else {
                $columnicon = $dir == "ASC" ? "down":"up";
            }
            $columnicon = " <img src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";

        }
        $$column = "<a href=\"pick.php?sort=$column&amp;dir=$columndir&amp;courseid=$courseid&amp;_sid=$sid&amp;_ban=$ban\">".$string[$column]."</a>$columnicon";
    }

    if ($sort == "name") {
        $sort = "firstname";
    }

    list($extrasql, $params) = $ufiltering->pick_get_sql_filter();
    $users = get_users_listing($sort, $dir, $page*$perpage, $perpage, '', '', '',
            $extrasql, $params, $context);
    $usercount = get_users(false);
    $usersearchcount = get_users(false, '', false, null, "", '', '', '', '', '*', $extrasql, $params);

    if ($extrasql !== '') {
        echo $OUTPUT->heading("$usersearchcount / $usercount ".get_string('users'));
        $usercount = $usersearchcount;
    } else {
        echo $OUTPUT->heading("$usercount ".get_string('users'));
    }

    $strall = get_string('all');

    $baseurl = new moodle_url('/blocks/pick_students/pick.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'courseid' => $courseid, '_sid' => $sid, '_ban' => $ban));
    echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);

    flush();

    echo html_writer::start_tag('div', array('style' => 'text-align: center;'));
    print_heading(get_string('eip_students', 'block_pick_students'));
    echo '<textarea cols="100" rows="5">';
    foreach ($SESSION->user_filtering['username'] as $item)
        echo $item['stname'] . "\t" . $item['value'] . "\n";
    echo '</textarea>';
    print_heading(get_string('moodle_students', 'block_pick_students'));
    echo html_writer::end_tag('div');

    if (!$users) {
        $match = array();
        echo $OUTPUT->heading(get_string('nousersfound'));

        $table = NULL;

    } else {

        $countries = get_string_manager()->get_list_of_countries(false);
        if (empty($mnethosts)) {
            $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
        }

        foreach ($users as $key => $user) {
            if (isset($countries[$user->country])) {
                $users[$key]->country = $countries[$user->country];
            }
        }
        if ($sort == "country") {  // Need to resort by full country name, not code
            foreach ($users as $user) {
                $susers[$user->id] = $user->country;
            }
            asort($susers);
            foreach ($susers as $key => $value) {
                $nusers[] = $users[$key];
            }
            $users = $nusers;
        }

        $override = new stdClass();
        $override->firstname = 'firstname';
        $override->lastname = 'lastname';
        $fullnamelanguage = get_string('fullnamedisplay', '', $override);
        if (($CFG->fullnamedisplay == 'firstname lastname') or
            ($CFG->fullnamedisplay == 'firstname') or
            ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'firstname lastname' )) {
            $fullnamedisplay = "$firstname / $lastname";
        } else { // ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'lastname firstname')
            $fullnamedisplay = "$lastname / $firstname";
        }

        $table = new html_table();
        $table->head = array ();
        $table->align = array();
        $table->head[] = $fullnamedisplay;
        $table->align[] = 'left';
        foreach ($extracolumns as $field) {
            $table->head[] = ${$field};
            $table->align[] = 'left';
        }
        $table->head[] = $city;
        $table->align[] = 'left';
        $table->head[] = $country;
        $table->align[] = 'left';
        $table->head[] = $lastaccess;
        $table->align[] = 'left';

        $table->width = "100%";

        foreach ($users as $user) {
            if ($user->username == 'guest') {
                continue; // do not dispaly dummy new user and guest here
            }

            // for remote users, shuffle columns around and display MNET stuff

            if ($user->lastaccess) {
                $strlastaccess = format_time(time() - $user->lastaccess);
            } else {
                $strlastaccess = get_string('never');
            }
            $fullname = fullname($user, true);

            $row = array ();
            $row[] = $fullname;
            foreach ($extracolumns as $field) {
                $row[] = $user->{$field};
            }
            $row[] = $user->city;
            $row[] = $user->country;
            $row[] = $strlastaccess;
            $table->data[] = $row;
        }
    }

    // add filters
    //$ufiltering->display_add();
    //$ufiltering->display_active();

    if (!empty($table)) {
        echo html_writer::table($table);
        echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
    }

    print_spacer(30);

    if ($users = get_users_listing($sort, $dir, 0, 0, '', '', '',
            $extrasql, $params, $context))
        foreach($users as $user)
            echo html_writer::empty_tag('input', array('type'=> 'hidden', 'name' => 'users['.$user->id.']', 'value' => $user->username));

            $SESSION->user_filtering = array();
    } else {
        echo $OUTPUT->heading(get_string('no_pick_students', 'block_pick_students'));
    }

echo html_writer::start_tag('div');
    if ($courseid != SITEID)
    {
    $assignableroles  = get_assignable_roles(get_context_instance(CONTEXT_COURSE, $courseid));
    echo html_writer::start_tag('p');
    echo get_string('role');
    choose_from_menu($assignableroles,  'assign_roleid_course', '', 'choose',
                    '',
                    '0', false, false, 0, 'assign_roleid_course');
    echo html_writer::end_tag('p');
    }
    else
    {
    $displaylist = array();
    $notused = array();
    make_categories_list($displaylist, $notused); // make_categories_list($displaylist, $notused, 'moodle/role:assign');
    echo html_writer::start_tag('p');
    echo get_string('category');
    choose_from_menu($displaylist, "category", '', 'choose',
                    '',
                    '0', false, false, 0, 'category');
    echo html_writer::end_tag('p');
    echo html_writer::start_tag('p');
    echo get_string('course') . html_writer::start_tag('select', array('name' => 'assign_course'));
    echo html_writer::end_tag('select');
    echo html_writer::end_tag('p');
    echo html_writer::start_tag('p');
    echo get_string('role');
    choose_from_menu(array(),  'assign_roleid', '', 'choose',
                    '',
                    '0', false, false, 0, 'assign_roleid');
    echo html_writer::end_tag('select');
    echo html_writer::end_tag('p');
    }

    echo html_writer::start_tag('div', array('style' => 'text-align: center;'));
    echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()));
    echo html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'approve', 'id'=>'approve', 'value'=>get_string('approve'), 'disabled'=>'disabled'));
    echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

echo html_writer::end_tag('form');

echo $OUTPUT->footer();
