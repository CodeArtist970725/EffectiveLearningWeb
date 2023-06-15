<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/Question.php';
require APPPATH . '/libraries/Reply.php';
require APPPATH . '/libraries/Course.php';
require APPPATH . '/libraries/Notification.php';

if (file_exists("application/aws-module/aws-autoloader.php")) {
    include APPPATH.'aws-module/aws-autoloader.php';
}

class Forum_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    public function get_course_by_user($user_id)
    {
        $where = $this->is_admin_user($user_id) ? '' : 'where enrol.user_id='.$user_id.' or course.user_id='.$user_id;
        $query = $this->db->query("select DISTINCT(course.id) as id, title, short_description, description, category_id, is_free_course from course join enrol on course_id = course.id ".$where." GROUP by course.id")->result_array();
        $ans = [];
        foreach($query as $row) {
            array_push($ans, new Course($row));
        }
        return $ans;
    }

    public function get_questions_by_user($user_id, $count = 10, $skip = 0, $search = '', $course_id = null)
    {
        $where = $this->is_admin_user($user_id) ? '' : 'where enrol.user_id='.$user_id.' or course.user_id='.$user_id;
        $query = $this->db->query("select DISTINCT(course.id) as id, title, short_description, description, category_id, is_free_course from course join enrol on course_id = course.id ".$where." GROUP by course.id")->result_array();
        $course_ids = [];
        if($course_id) {
            array_push($course_ids, $course_id);
        }
        else {
            foreach($query as $row) array_push($course_ids, $row['id']);
        }
        if(count($course_ids) == 0) return [];

        $query = $this->db->query("select questions.id from questions where ((body LIKE '%".$search."%') or (title LIKE '%".$search."%')) and (course_id in (".implode(",", $course_ids).")) ORDER BY questions.created_at DESC  LIMIT ".$count." OFFSET ".$skip)->result_array();
        $questions = [];
        foreach($query as $row) {
            array_push($questions, $this->get_question_by_id($row['id']));
        }

        return $questions;
    }

    public function check_permision_user_question($user_id, $question_id)
    {
        if($this->is_admin_user($user_id)) return true;
        if(count($this->db->query("select * from enrol join questions on enrol.course_id = questions.course_id where enrol.user_id = ".$user_id." and questions.id = ".$question_id)->result()) > 0) return true;
        $res = $this->db->query("select course.user_id as instructor_id from questions join course on questions.course_id = course.id where questions.id = ".$question_id)->result();
        if(count($res) == 0) return false;
        return $res[0]->instructor_id == $user_id;
    }

    public function is_admin_user($user_id)
    {
        $res = $this->db->query("select role_id from users where users.id = ".$user_id)->result();
        if(count($res) == 0) return false;
        return ($res[0]->role_id == 1);
    }

    public function can_edit_question($user_id, $question_id)
    {
        if($this->is_admin_user($user_id)) return true;
        $res = $this->db->query("select users.id as user_id, questions.id as question_id from users join questions on users.id = questions.user_id where questions.id = ".$question_id)->result();
        return (count($res) == 0 || $res[0]->user_id != $user_id) ? false : true;
    }

    public function can_edit_reply($user_id, $reply_id)
    {
        if($this->is_admin_user($user_id)) return true;
        $res = $this->db->query("select replies.user_id as reply_user_id, questions.user_id as question_user_id, course.user_id as instructor_id from replies inner join questions on questions.id = replies.question_id inner join course on questions.course_id = course.id where replies.id = ".$reply_id)->result();
        return (count($res) == 0 || ($res[0]->reply_user_id != $user_id && $res[0]->question_user_id != $user_id && $res[0]->instructor_id != $user_id)) ? false : true;
    }

    public function get_course_by_id($course_id)
    {
        $course = $this->db->get_where('course', ['id' => $course_id])->result_array()[0];
        return new Course($course);
    }

    public function get_question_by_id($question_id, $include_reply = false)
    {
        $query = $this->db->get_where('questions', ['id' => $question_id])->result_array()[0];
        $question = new Question($query);

        $question->likes = count($this->db->get_where('likes', ['qr_id' => $question_id, 'qr_type' => 0])->result());
        $question->reply_count = count($this->db->get_where('replies', ['question_id' => $question_id])->result());

        $user = $this->db->get_where('users', ['id' => $question->user_id])->result()[0];
        $question->user_name = ($user->first_name." ".$user->last_name);
        $question->user_email = $user->email;

        $course = $this->get_course_by_id($question->course_id);
        $question->course_title = $course->title;
        $question->course_description = $course->description;

        if($include_reply) {
            $query = $this->db->get_where('replies', ['question_id' => $question->id])->result_array();
            foreach($query as $row) {
                $reply = $this->get_reply_by_id($row['id']);
                $question->push_reply($reply);
            }
        }
        
        return $question;
    }

    public function get_reply_by_id($reply_id)
    {
        $query = $this->db->get_where('replies', ['id' => $reply_id])->result_array()[0];
        $reply = new Reply($query);

        $user = $this->db->get_where('users', ['id' => $reply->user_id])->result()[0];
        $reply->user_name = ($user->first_name." ".$user->last_name);
        $reply->user_email = $user->email;

        $query = $this->db->query("select course.user_id as instructor_id, course.id as course_id from course JOIN questions on course.id = questions.course_id where questions.id = ".$reply->question_id)->result();
        if(count($query) > 0) {
            $reply->course_id = $query[0]->course_id;
            $reply->instructor_id = $query[0]->instructor_id;
            $reply->is_instructor = ($reply->user_id == $reply->instructor_id);
        }

        $reply->likes = count($this->db->get_where('likes', ['qr_id' => $reply->id, 'qr_type' => 1])->result());

        return $reply;
    }

    public function insert_reply($user_id, $question_id, $body) {
        $data = [
            'user_id' => $user_id,
            'question_id' => $question_id,
            'body' => $body,
            'created_at' => (new DateTime)->format('Y-m-d H:i:s')
        ];
        $this->db->insert('replies', $data);
        return $this->db->insert_id();
    }

    public function insert_question($user_id, $course_id, $title, $body) {
        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'title' => $title,
            'body' => $body,
            'created_at' => (new DateTime)->format('Y-m-d H:i:s')
        ];
        $this->db->insert('questions', $data);
        return $this->db->insert_id();
    }

    public function insert_like($user_id, $qr_id, $qr_type) {
        $data = [
            'user_id' => $user_id,
            'qr_id' => $qr_id,
            'qr_type' => $qr_type
        ];
        if(count($this->db->get_where('likes', $data)->result()) > 0) return null;

        $this->db->insert('likes', $data);
        return [
            'chanel' => 'likes',
            'event' => $qr_type == 0 ? 'question' : 'reply',
            'value' => $qr_id
        ];
    }

    public function insert_notification($user_id, $from_id, $link, $title, $body)
    {
        $data = [
            'user_id' => $user_id,
            'from_id' => $from_id,
            'link' => $link,
            'title' => $title,
            'body' => $body,
            'created_at' => (new DateTime)->format('Y-m-d H:i:s')
        ];
        $this->db->insert('notifications', $data);
        $data['id'] = $this->db->insert_id();
        return $data;
    }

    public function remove_notification_by_id($id)
    {
        return $this->db->delete('notifications', ['id' => $id]);
    }

    public function get_notifications_by_user($user_id)
    {
        $query = $this->db->order_by('created_at', 'desc')->get_where('notifications', ['user_id' => $user_id])->result_array();
        $ans = [];
        foreach($query as $row) {
            array_push($ans, new Notification($row));
        }
        return $ans;
    }

    public function edit_question($question_id, $title, $body)
    {
        return $this->db->set(['title' => $title, 'body' => $body])->where('id', $question_id)->update('questions');
    }

    public function delete_question($question_id)
    {
        $this->db->delete('replies', ['question_id' => $question_id]);
        return $this->db->delete('questions', ['id' => $question_id]);
    }

    public function edit_reply($reply_id, $body)
    {
        return $this->db->set(['body' => $body])->where('id', $reply_id)->update('replies');
    }

    public function delete_reply($reply_id)
    {
        return $this->db->delete('replies', ['id' => $reply_id]);
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}