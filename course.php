<?php
    require_once('../../config.php');
    require_once('lib/eip.php');

    require_login();

    $category        = optional_param('category', '', PARAM_RAW);
    $assign_course   = optional_param('assign_course', 0, PARAM_INT);

    $capabilitycourse = get_user_capability_course('moodle/role:assign');
    $categorycourses = get_courses_wmanagers($category);

    $capabilitycategorycourse = array();
    foreach ($capabilitycourse as $cc)
    {
        //$capabilitycategorycourse[] = $categorycourses[$cc->id];
        if ($cc->id != SITEID)
            if (array_key_exists($cc->id, $categorycourses))
            {
                $std = $categorycourses[$cc->id];
                $capabilitycategorycourse[$cc->id] = $std->fullname;
            }
    }

    if ($assign_course)
    {
        $context = get_context_instance(CONTEXT_COURSE, $assign_course);
        $assignableroles  = get_assignable_roles($context, ROLENAME_BOTH);
        echo renderoptions($assignableroles);
    }
    else
        echo renderoptions($capabilitycategorycourse);
