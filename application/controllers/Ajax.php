<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('ajax_model');
        $this->load->model('amazon_model');
    }

    public function load_video_files()
    {
        echo json_encode($this->ajax_model->get_video_files_from_folder());
    }

    public function ajax_upload_file($type)
    {
        echo json_encode($this->ajax_model->ajax_upload_file($type));
    }

    public function update_instructor_term()
    {
        if(!$this->session->user_id) {
            echo json_encode([
                'success' => false,
                'content' => 'Invalid User'
            ]);
            return;
        }
        $data = $this->input->post();
        if($this->ajax_model->update_instructor_by_user_id($this->session->user_id, $data)) {
            echo json_encode([
                'success' => true,
                'content' => 'Updated Successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'content' => 'Operation Failed'
            ]);
        }
    }

    public function get_students($course_id)
    {
        if(!$this->session->userdata('admin_login')) {
            echo json_encode([
                'success' => false,
                'content' => 'Permission denined'
            ]);
            return;
        }
        echo json_encode($this->ajax_model->get_students_by_course_id($course_id));
    }

    public function make_payment_revenue()
    {
        $data = $this->input->post();
        if(strlen($data['attachment_name']) == 0) $data['attachment_name'] = null;
        $data['date_added'] = $data['last_modified'] = time();

        $response = [];
        if($this->ajax_model->insert_payment($data)) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false];
        }

        echo json_encode($response);
    }

    public function instructor_receive_payment($payment_id)
    {
        if($this->ajax_model->instructor_receive_payment($this->session->user_id, $payment_id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function update_payment($payment_id)
    {
        if(!$this->session->userdata('admin_login')) {
            echo json_encode([
                'success' => false,
                'content' => 'Permission denined'
            ]);
            return;
        }
        $data = $this->input->post();
        if($this->ajax_model->update_payment($payment_id, $data)) {
            echo json_encode([
                'success' => true,
                'content' => 'Updated Successfully'
            ]);
        }
    }

    public function aws_upload($para)
    {
        if($para == 'course') {
            echo json_encode($this->amazon_model->getJsCourseConfig());
        } else if($para == 'lesson') {
            echo json_encode($this->amazon_model->getJsLessonConfig());
        } else {
            echo json_encode(['error' => 'Invalid Request']);
        }
    }
}
