<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/Instructor.php';
require APPPATH . '/libraries/Revenue.php';

class FileSettings {
    public $url, $directory, $extension;
    public function __construct($p_url = null, $p_directory = null, $p_extension = null)
    {
        $this->url = $p_url;
        $this->directory = $p_directory;
        $this->extension = $p_extension;
    }
}

class Ajax_model extends CI_Model
{
    public $video_directory, $video_url;
    public $revenue_directory, $revenue_url;
    public $file_handle_settings;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');

        $this->file_handle_settings = [
            'video' => new FileSettings(base_url('uploads/videos/'), APPPATH.'../uploads/videos', 'mp4'),
            'pdf' => new FileSettings(base_url('uploads/revenue_files/'), APPPATH.'../uploads/revenue_files', 'pdf'),
            'png' => new FileSettings(base_url('uploads/revenue_files/'), APPPATH.'../uploads/revenue_files', 'png'),
            'jpg' => new FileSettings(base_url('uploads/revenue_files/'), APPPATH.'../uploads/revenue_files', 'jpg'),
            'jpeg' => new FileSettings(base_url('uploads/revenue_files/'), APPPATH.'../uploads/revenue_files', 'jpeg')
        ];

        foreach($this->file_handle_settings as $key => $value) {
            if(!is_dir($value->directory)) mkdir($value->directory);
        }
    }

    public function get_file_url($type, $filename)
    {
        if($filename == null) return null;
        if(empty($this->file_handle_settings[$type])) return null;
        $data = $this->file_handle_settings[$type];
        return $data->url.$filename;
    }

    public function get_video_files_from_folder()
    {
        $ans = [];
        $video_directory = $this->file_handle_settings['video']->directory;
        foreach(scandir($video_directory) as $row) {
            if(substr(strtolower($row), -4) !== '.mp4') continue;
            array_push($ans, [
                'url' => $this->video_url.$row,
                'filename' => $row
            ]);
        }
        return $ans;
    }

    public function ajax_upload_file($type, $upload_file = 'upload_file')
    {
        if(empty($this->file_handle_settings[$type])) {
            echo json_encode([
                'success' => false,
                'content' => 'Invalid Type'
            ]);
            return;
        }
        $filestg = $this->file_handle_settings[$type];
        $this->upload->initialize(array(
            "upload_path" => $filestg->directory,
            "allowed_types" => $filestg->extension,
            "remove_spaces" => TRUE
        ));
        if(!$this->upload->do_upload($upload_file)) {
            return [
                'success' => false,
                'content' => $this->upload->display_errors()
            ];
        } else {
            return [
                'success' => true,
                'content' => 'File uploaded successfully',
                'filename' => $this->upload->file_name
            ];
        }
    }

    public function get_video_url_by_lesson_id($lesson_id)
    {
        if(!$lesson_id) return null;
        $query = $this->db->get_where('lesson', ['id' => $lesson_id])->result();
        if(count($query) == 0) return null;
        return $this->video_url.$query[0]->video_file;
    }

    public function get_instructor_by_user_id($user_id)
    {
        $query = $this->db->get_where('instructor', ['user_id' => $user_id])->result_array();
        if(count($query) == 0) $ans = new Instructor();
        $ans = new Instructor($query[0]);
        $query = $this->db->get_where('frontend_settings', ['key' => 'instructor_term'])->result();
        if(count($query) > 0) $ans->term_body = $query[0]->value;
        return $ans;
    }

    public function insert_instructor($user_id, $data) {
        $data['user_id'] = $user_id;
        $this->db->insert('instructor', $data);
        return $this->db->insert_id();
    }

    public function update_instructor_by_user_id($user_id, $data) {
        $query = $this->db->get_where('instructor', ['user_id' => $user_id])->result_array();
        if(count($query) == 0) {
            return $this->insert_instructor($user_id, $data);
        }
        return $this->db->update('instructor', $data, ['user_id' => $user_id]);
    }

    public function get_all_course()
    {
        $query = $this->db->query("SELECT id, id as course_id, user_id as instructor_id, user_id, title from course")->result();
        return $query;
    }

    public function get_students_by_course_id($course_id)
    {
        $query = $this->db->query('select course_id, user_id, users.email as user_email, users.first_name as first_name, users.last_name as last_name from enrol JOIN users on enrol.user_id = users.id where course_id = '.$course_id);
        return $query->result();
    }

    public function insert_payment($data)
    {
        $this->db->insert('payment', $data);
        return $this->db->insert_id();
    }

    public function instructor_receive_payment($user_id, $payment_id)
    {
        $query = $this->db->query("SELECT payment.id as payment_id, course.id as course_id from payment JOIN course on payment.course_id = course.id where course.user_id = ".$user_id." and payment.id = ".$payment_id)->result();
        if(count($query) == 0) return false;
        return $this->db->update('payment', ['instructor_payment_status' => 1], ['id' => $payment_id]);
    }

    public function update_payment($id, $data)
    {
        return $this->db->update('payment', $data, ['id' => $id]);
    }
}