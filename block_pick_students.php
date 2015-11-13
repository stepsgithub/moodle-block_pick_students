<?php
class block_pick_students extends block_list {
    public function init() {
        $this->title = get_string('pick_students', 'block_pick_students');
    }

    function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $this->course = $this->page->course;

        $url = new moodle_url($CFG->wwwroot . '/blocks/pick_students/pick.php', array('courseid' => $this->course->id));

        // checks whether user can do role assignment
        if (has_capability('moodle/course:enrolreview', $this->context)) {
            $this->content->items[] = html_writer::tag('a', get_string('enrolledusers', 'enrol'), array('href' => $url));
            $this->content->icons[] = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/users'), 'class' => 'iconsmall', 'alt' => get_string('enrolledusers', 'enrol')));
        }

        return $this->content;
    }

    function applicable_formats() {
        return array(
          'course-view' => true,
          'site-index' => true,
          'admin-index' => true
        );
    }
}
