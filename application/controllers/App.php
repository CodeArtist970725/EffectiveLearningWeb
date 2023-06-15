<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . '/libraries/Notification.php';
require APPPATH . 'helpers/common_helper.php';
// require APPPATH . '/models/addons/Certificate_model.php';

class App extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->library('session');
        $this->load->model('addons/offline_payment_model');

        $this->load->model('addons/Certificate_model', 'certificate_model');
    }


    public function index()
    {
        $this->load->view('backend/index');
    }

    public function test_post()
    {
        $categories = array();
        $categories["ok"] = true;
        $this->set_response($categories, REST_Controller::HTTP_OK);
    }

    public function login_post()
    {
        $res = array();

        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $credential = array('email' => $email, 'password' => sha1($password), 'status' => 1);

        // Checking login credential for admin
        $query = $this->db->get_where('users', $credential);

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $this->session->set_userdata('user_id', $row->id);
            $this->session->set_userdata('role_id', $row->role_id);
            $this->session->set_userdata('role', get_user_role('user_role', $row->id));
            $this->session->set_userdata('name', $row->first_name . ' ' . $row->last_name);
            // $this->session->set_flashdata('flash_message', get_phrase('welcome').' '.$row->first_name.' '.$row->last_name);

            if ($row->role_id == 1) {
                $this->session->set_userdata('admin_login', '1');
            } else if ($row->role_id == 2) {
                $this->session->set_userdata('user_login', '1');
            }
            $res['data'] = $row;
            $res['result'] = true;
        } else {
            // $this->session->set_flashdata('error_message',get_phrase('invalid_login_credentials'));
            $res['result'] = false;
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function getAvgRating($course_id)
    {
        $query = "SELECT *
        from rating
        WHERE ratable_id = " . $course_id;
        $ratings = $this->db->query($query)->result_array();

        $rating_val = 0.0;
        $cnt = count($ratings);
        for ($j = 0; $j < $cnt; $j++) {
            $rating_val += $ratings[$j]['rating'];
        }
        if ($cnt) {
            $rating_val /= $cnt;
        }
        return $rating_val;
    }

    public function discover_post()
    {
        $res = array();

        // Latest Courses
        $latest_courses = $this->db->query("SELECT course.*, users.first_name, users.last_name
        FROM course, users
        WHERE users.id = course.user_id
        ORDER BY date_added DESC
        LIMIT 10")->result_array();

        for ($i = 0; $i < count($latest_courses); $i++) {

            $latest_courses[$i]['rating'] = $this->getAvgRating($latest_courses[$i]['id']);
        }

        $res['latest_courses'] = $latest_courses;

        // Top Courses
        $top_courses = $this->db->query("SELECT course.*, users.first_name, users.last_name
        FROM course, users
        WHERE users.id = course.user_id
        AND is_top_course = 1")->result_array();

        for ($i = 0; $i < count($top_courses); $i++) {
            $top_courses[$i]['rating'] = $this->getAvgRating($top_courses[$i]['id']);
        }

        $res['top_courses'] = $top_courses;

        $courses = $this->db->query("SELECT course.*, users.first_name, users.last_name
        FROM course, users
        WHERE users.id = course.user_id")->result_array();

        for ($i = 0; $i < count($courses); $i++) {
            $courses[$i]['rating'] = $this->getAvgRating($courses[$i]['id']);
        }

        $res['courses'] = $courses;

        // Categories
        $categories = $this->db->query("select * from category WHERE parent = 0")->result_array();
        for ($i = 0; $i < count($categories); $i++) {
            $cate = $categories[$i];
            $query = "SELECT category_id, COUNT(*) AS cnt
            FROM course
            WHERE category_id = " . $cate['id'] . "
            GROUP BY category_id";
            $course_cnt = $this->db->query($query)->result_array();
            if (count($course_cnt) == 0) {
                $categories[$i]['course_cnt'] = 0;
            } else {
                $categories[$i]['course_cnt'] = $course_cnt[0]['cnt'];
            }
            $title = $categories[$i]['name'];
            $categories[$i]['name'] = str_replace("&amp;", "&", $title);
        }
        $res['categories'] = $categories;


        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function mycourses_post()
    {
        $res = array();
        $user_id = $this->session->userdata('user_id');

        $query = "SELECT *
        FROM users
        WHERE id = $user_id";
        $res['user_info'] = $this->db->query($query)->result_array()[0];

        $query = "SELECT *
        FROM enrol
        WHERE user_id = $user_id";
        $pays = $this->db->query($query)->result_array();

        $mycourses = array();
        foreach ($pays as $pay) {
            $query = "SELECT course.*, users.first_name, users.last_name
            FROM course, users
            WHERE users.id = course.user_id
            AND course.id = " . $pay['course_id'];
            $course = $this->db->query($query)->result_array();
            if (count($course) > 0) {

                $course[0]['rating'] = $this->getAvgRating($course[0]['id']);

                $course[0]['progress'] = course_progress($pay['course_id'], $user_id);

                array_push($mycourses, $course[0]);
            }
        }
        $res['mycourses'] = $mycourses;
        // $res['courses'] = array();
        // $res['courses'] = json_decode($res['user_info']['watch_history'], true);
        // for($i = 0; $i < count($res['courses']); $i++) {
        //     $query = "SELECT *
        //     FROM lesson
        //     WHERE id = ".$res['courses'][$i]['lesson_id'];
        //     $res['courses'][$i]['info'] = $this->db->query($query)->result_array()[0];
        // }

        $res['wishlist'] = json_decode($res['user_info']['wishlist'], true);
        $wishes = array();
        for ($i = 0; $i < count($res['wishlist']); $i++) {

            $flag = 0;
            for ($j = 0; $j < count($mycourses); $j++) {
                if ($mycourses[$j]['id'] == $res['wishlist'][$i]) {
                    $flag = 1;
                    break;
                }
            }
            if($flag) {
                continue;
            }
            $query = "SELECT course.*, users.first_name, users.last_name
            FROM course, users
            WHERE users.id = course.user_id
            AND course.id = " . $res['wishlist'][$i];
            $course = $this->db->query($query)->result_array();
            if (count($course) > 0) {
                $course[0]['rating'] = $this->getAvgRating($course[0]['id']);
                array_push($wishes, $course[0]);
            }
        }
        $res['wishes'] = $wishes;
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function done_lesson_post()
    {
        $res = array();
        $lesson_id = $_POST['lesson_id'];
        $res['lesson_id'] = $lesson_id;



        $user_id = $this->session->userdata('user_id');


        $query = "SELECT *
        FROM users
        WHERE id = $user_id";
        $user_info = $this->db->query($query)->result_array()[0];

        $watch_history = json_decode($user_info['watch_history'], true);
        $flag = 0;
        for ($i = 0; $i < count($watch_history); $i++) {
            if ($watch_history[$i]['lesson_id'] == $lesson_id) {
                $flag = 1;
                $watch_history[$i]['progress'] = "1";
            }
        }

        if ($flag == 0) {
            $pu_arr = array();
            $pu_arr['lesson_id'] = $lesson_id;
            $pu_arr['progress'] = "1";
            array_push($watch_history, $pu_arr);
        }


        $data['watch_history'] = json_encode($watch_history);
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);

        $course_progress = course_progress(37, $user_id);
        $res['progress'] = $course_progress;
        $this->certificate_model->check_certificate_eligibility("lesson", $lesson_id, $user_id);

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function enrol_post()
    {
        $res = array();
        $user_id = $this->session->userdata('user_id');
        $course_id = $_POST['course_id'];

        $res['user_id'] = $user_id;
        $res['course_id'] = $course_id;

        $query = "SELECT *
        FROm enrol
        WHERE user_id = $user_id AND course_id = $course_id";
        $enr = $this->db->query($query)->result_array();
        if (count($enr) == 0) {
            $data['user_id'] = $user_id;
            $data['course_id'] = $course_id;
            $data['date_added'] = time();
            $this->db->insert('enrol', $data);
        }


        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function ask_question_post()
    {
        $res = array();

        $course_id = $_POST['course_id'];
        $body = $_POST['body'];
        $title = $_POST['title'];

        $data['course_id'] = $course_id;
        $data['body'] = $body;
        $data['title'] = $title;
        // $data['created_at'] = time();
        $data['user_id'] = $this->session->userdata('user_id');
        $this->db->insert('questions', $data);

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function reply_question_post()
    {
        $res = array();

        $question_id = $_POST['question_id'];
        $body = $_POST['body'];

        $data['question_id'] = $question_id;
        $data['body'] = $body;
        // $data['created_at'] = time();
        $data['user_id'] = $this->session->userdata('user_id');
        $this->db->insert('replies', $data);

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function get_cert_url_post()
    {
        $res = array();


        $course_id = $_POST['course_id'];
        $user_id = $this->session->userdata('user_id');

        $query = "SELECT *
        FROM certificates
        WHERE student_id = $user_id
        AND course_id = $course_id";

        $cert = $this->db->query($query)->result_array();
        $res['filename'] = $cert[0]['shareable_url'];

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function set_wish_post()
    {
        $res = array();

        $user_id = $this->session->userdata('user_id');
        $wish = $_POST['wish'];
        $course_id = $_POST['course_id'];

        $query = "SELECT *
        FROM users
        WHERE id = $user_id";
        $user_info = $this->db->query($query)->result_array()[0];
        $wishlist = json_decode($user_info['wishlist'], true);
        if ($wish == 'true') {
            // Set wish
            array_push($wishlist, $course_id);
        } else {
            // Remove Wish
            for ($i = 0; $i < count($wishlist); $i++) {
                if ($wishlist[$i] == $course_id) {
                    array_splice($wishlist, $i, 1);
                    break;
                }
            }
        }
        $data['wishlist'] = json_encode($wishlist);
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function course_detail_post()
    {
        $res = array();
        $course_id = $_POST['course_id'];


        $user_id = $this->session->userdata('user_id');

        $query = "SELECT *
        FROM users
        WHERE id = $user_id";
        $user_info = $this->db->query($query)->result_array()[0];

        $res['wish'] = false;
        $wishlist = json_decode($user_info['wishlist'], true);
        for ($i = 0; $i < count($wishlist); $i++) {
            if ($wishlist[$i] == $course_id)
                $res['wish'] = true;
        }

        $query = "SELECT course.*, users.first_name, users.last_name
        FROM course, users
        WHERE users.id = course.user_id
        AND course.id = $course_id
        ORDER BY date_added DESC
        LIMIT 5";
        // Latest Courses
        $course_info = $this->db->query($query)->result_array();


        $res['course_info'] = $course_info[0];
        $query = "SELECT *
        from rating
        WHERE ratable_id = " . $course_id;
        $ratings = $this->db->query($query)->result_array();

        $rating_sum = 0.0;
        $rating_avg = 0.0;
        $rating_cnt = count($ratings);
        $rating_marks = [0, 0, 0, 0, 0, 0];
        for ($j = 0; $j < $rating_cnt; $j++) {
            $rating_sum += $ratings[$j]['rating'];
            $rating_marks[$ratings[$j]['rating']] += 1;
        }
        if ($rating_cnt) {
            $rating_avg = $rating_sum / $rating_cnt;
        }
        $res['rating_sum'] = $rating_sum;
        $res['rating_avg'] = $rating_avg;
        $res['rating_cnt'] = $rating_cnt;
        $res['ratings'] = $ratings;
        $res['rating_marks'] = $rating_marks;

        $res['progress'] = course_progress($course_id, $user_id);
        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++=
        // SECTIONS
        $query = "SELECT *
        FROM section
        WHERE course_id = $course_id";
        $sections = $this->db->query($query)->result_array();

        $sec_arr = array();
        foreach ($sections as $sec) {
            $sec_arr[$sec['id']] = $sec;
        }
        for ($i = 0; $i < count($sections); $i++) {
            $query = "SELECT *
            FROM lesson
            WHERE section_id = " . $sections[$i]['id'];
            $less = $this->db->query($query)->result_array();
            $sec_done = 0;
            for ($k = 0; $k < count($less); $k++) {
                $title = $less[$k]['title'];
                $less[$k]['title'] = str_replace("&#039;", "'", $title);
                $title = $less[$k]['title'];
                $less[$k]['title'] = str_replace("&quot;", "\"", $title);
                $done = lesson_progress($less[$k]['id'], $user_id);
                $less[$k]['done'] = $done;
                $sec_done += $done;
            }
            $sections[$i]['progress'] = $sec_done;
            $sections[$i]['lessons'] = $less;
        }
        $res['sections'] = $sections;

        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // LIVES
        $query = "SELECT *
        FROM live_class
        WHERE course_id = $course_id";
        $lives = $this->db->query($query)->result_array();
        $res['lives'] = $lives;

        $query = "SELECT *
        FROM lesson
        WHERE course_id = $course_id";
        $lessons = $this->db->query($query)->result_array();

        $total_time = 0;
        $quiz_cnt = 0;
        for ($i = 0; $i < count($lessons); $i++) {
            $spt = explode(':', $lessons[$i]['duration']);
            $dur_time = (int) $spt[0] * 3600 + (int) $spt[1] * 60 + (int) $spt[2];
            $lessons[$i]['dur'] = $dur_time;
            $total_time += $dur_time;

            if ($lessons[$i]['lesson_type'] == 'quiz') {
                $quiz_cnt++;
            }
        }

        $res['lessons'] = $lessons;

        $res['course_info']['duration'] = $total_time;
        $res['course_info']['quiz_cnt'] = $quiz_cnt;

        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // Q & A
        $query = "SELECT *
        FROM questions
        WHERE course_id = $course_id
        ORDER BY created_at DESC";
        $questions = $this->db->query($query)->result_array();
        for ($i = 0; $i < count($questions); $i++) {
            $uid = $questions[$i]['user_id'];
            $query = "SELECT *
            FROM users
            WHERE id = $uid";
            $user = $this->db->query($query)->result_array();
            if (count($user) > 0) {

                $questions[$i]['first_name'] = $user[0]['first_name'];
                $questions[$i]['last_name'] = $user[0]['last_name'];
            } else {

                $questions[$i]['first_name'] = "John";
                $questions[$i]['last_name'] = "Paulo";
            }
            $query = "SELECT *
            FROM replies
            WHERE question_id = " . $questions[$i]['id'];
            $replies = $this->db->query($query)->result_array();
            for ($j = 0; $j < count($replies); $j++) {
                $query = "SELECT *
                FROM users
                WHERE id = " . $replies[$j]['user_id'];
                $user = $this->db->query($query)->result_array();
                if (count($user) > 0) {
                    $replies[$j]['first_name'] = $user[0]['first_name'];
                    $replies[$j]['last_name'] = $user[0]['last_name'];
                } else {
                    $replies[$j]['first_name'] = "John";
                    $replies[$j]['last_name'] = "Paulo";
                }
            }
            $questions[$i]['replies'] = $replies;
        }
        $res['questions'] = $questions;

        // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // Instructor Info
        // Reviews, Students, Courses
        $inst_id = $course_info[0]['user_id'];
        $res['inst_id'] = $inst_id;

        $query = "SELECT id
        FROM course
        WHERE user_id = $inst_id";
        $inst_courses = $this->db->query($query)->result_array();
        $course_ids = array();
        $inst_review_cnt = 0;
        $inst_student_cnt = 0;
        foreach ($inst_courses as $i_course) {
            array_push($course_ids, $i_course['id']);
            $query = "SELECT *
            FROM enrol
            WHERE course_id = " . $i_course['id'];
            $ins = $this->db->query($query)->result_array();
            $inst_student_cnt += count($ins);
        }
        $query = "SELECT *
        FROM rating
        WHERE user_id = " . $i_course['id'];
        $rs = $this->db->query($query);
        $inst_review_cnt = count($rs);
        $res['inst_course_cnt'] = count($course_ids);
        $res['inst_review_cnt'] = $inst_review_cnt;
        $res['inst_student_cnt'] = $inst_student_cnt;

        //----------- check if the course is purchased or pending or free or not bought --------
        $res['state'] = 0;  // not purchased yet
        if (count($course_info) > 0) {
            if ($course_info[0]['price'] == '0') {
                $res['state'] = 1;
            }
        }

        $pending_arr = $this->offline_payment_model->pending_offline_payment($user_id)->result_array();
        foreach ($pending_arr as $row) {
            $course_id_arr = json_decode($row['course_id']);
            foreach ($course_id_arr as $course_id_in) {
                if ($course_id_in == $course_id) {
                    $res['state'] = 2;   // pending course.
                    break;
                }
            }
        }

        $query = "SELECT *
        FROM enrol
        WHERE user_id = $user_id
        AND course_id = $course_id";
        // Latest Courses        
        $enrols_course = $this->db->query($query)->result_array();
        if (count($enrols_course) > 0) {
            $res['state'] = 3;           // purchased already.
        }



        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function cate_detail_post()
    {
        $res = array();

        $cate_id = $_POST['cate_id'];
        $query = "SELECT course.*, users.first_name, users.last_name
        FROM course, users
        WHERE course.user_id = users.id
        AND category_id = " . $cate_id;
        $courses = $this->db->query($query)->result_array();

        for ($i = 0; $i < count($courses); $i++) {
            $courses[$i]['rating'] = $this->getAvgRating($courses[$i]['id']);
        }

        $res['courses'] = $courses;

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function register_post()
    {

        $res = array();
        $data['first_name'] = $_POST['name'];
        $data['last_name'] = "";
        $data['email'] = $_POST['email'];
        $data['password'] = sha1($_POST['password']);

        $verification_code =  md5(rand(100000000, 200000000));
        $data['verification_code'] = $verification_code;

        if (get_settings('student_email_verification') == 'enable') {
            $data['status'] = 0;
        } else {
            $data['status'] = 1;
        }

        $data['wishlist'] = json_encode(array());
        $data['watch_history'] = json_encode(array());
        $data['date_added'] = strtotime(date("Y-m-d H:i:s"));
        $social_links = array(
            'facebook' => "",
            'twitter'  => "",
            'linkedin' => ""
        );
        $data['social_links'] = json_encode($social_links);
        $data['role_id']  = 2;

        // Add paypal keys
        $paypal_info = array();
        $paypal['production_client_id'] = "";
        array_push($paypal_info, $paypal);
        $data['paypal_keys'] = json_encode($paypal_info);
        // Add Stripe keys
        $stripe_info = array();
        $stripe_keys = array(
            'public_live_key' => "",
            'secret_live_key' => ""
        );
        array_push($stripe_info, $stripe_keys);
        $data['stripe_keys'] = json_encode($stripe_info);

        $validity = $this->user_model->check_duplication('on_create', $data['email']);

        $res['validity'] = $validity;
        if ($validity) {
            $user_id = $this->user_model->register_user($data);
            $data['user_id'] = $user_id;
            $res['data'] = $data;


            $this->session->set_userdata('user_id', $user_id);
            $this->session->set_userdata('role_id', 2);
            $this->session->set_userdata('role', get_user_role('user_role', $user_id));
            $this->session->set_userdata('name', $data['first_name']);
            $this->session->set_userdata('user_login', '1');

            if (get_settings('student_email_verification') == 'enable') {
                $this->email_model->send_email_verification_mail($data['email'], $verification_code);
                # $this->session->set_flashdata('flash_message', get_phrase('your_registration_has_been_successfully_done').'. '.get_phrase('please_check_your_mail_inbox_to_verify_your_email_address').'.');
            } else {
                # $this->session->set_flashdata('flash_message', get_phrase('your_registration_has_been_successfully_done'));
            }
        } else {
            # $this->session->set_flashdata('error_message', get_phrase('email_duplication'));
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    public function get_profile_post()
    {
        $user_id = $this->session->userdata('user_id');
        if (isset($user_id)) {
            //$user_id = 29;
            $query = "SELECT *
            FROM users
            WHERE id = $user_id
            ";
            $user_details = $this->db->query($query)->result_array();
            if (count($user_details) > 0) {
                $res['user_details'] = $user_details[0];
            } else {
                $res['user_details'] = array();
            }
        } else {
            $res['user_details'] = array();
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    public function messaging_post()
    {
        $res = array();
        $current_user = $this->session->userdata('user_id');
        if (!isset($current_user)) {
            return $this->set_response($res, REST_Controller::HTTP_OK);
        }
        $action = $_POST['action']; // 'view', 'send', 'reply'
        if ($action == 'view') {


            //$current_user = 1;
            $this->db->where('sender', $current_user);
            $this->db->or_where('receiver', $current_user);
            $message_threads = $this->db->get('message_thread')->result_array();
            foreach ($message_threads as $row) {
                $message_thread_code = $row['message_thread_code'];
                $this->db->order_by('message_id', 'desc');
                $this->db->limit(1);
                $this->db->where(array('message_thread_code' => $message_thread_code));
                $msgStr = $this->db->get('message')->result_array();
                $this->db->get('users');
                $this->db->where(array('id' => $row['sender']));
                $sender_info = $this->db->get('users')->row_array();
                $this->db->get('users');
                $this->db->where(array('id' => $row['receiver']));
                $receiver_info = $this->db->get('users')->row_array();

                $onemessage = array(
                    'sender' => $row['sender'],
                    'sender_info' => $sender_info,
                    'is_SenderImage' => true,
                    'receiver' => $row['receiver'],
                    'receiver_info' => $receiver_info,
                    'is_ReceiverImage' => true,
                    'code' => $row['message_thread_code'],
                    'message' => $msgStr[0]['message'],
                    'timestamp' => $msgStr[0]['timestamp']
                );
                array_push($res, $onemessage);
            }
            //$res = $message_threads;
            $this->set_response($res, REST_Controller::HTTP_OK);
        } elseif ($action == 'send') {
            // $message_thread_code = $this->crud_model->send_new_private_message();
            // $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            // redirect(site_url('home/my_messages/read_message/' . $message_thread_code), 'refresh');
        } elseif ($action == 'reply') {
            // $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            // $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            // redirect(site_url('home/my_messages/read_message/' . $param2), 'refresh');
        }
    }

    public function get_notifications_post()
    {
        $res = array();
        $user_id = $this->session->userdata('user_id');
        //$user_id = 27;  // for test, when real env, disable this line
        $result_array = $this->db->order_by('created_at', 'desc')->get_where('notifications', ['user_id' => $user_id])->result_array();

        foreach ($result_array as $row) {
            $sender_id = $row['from_id'];
            $query = "SELECT * 
            FROM users
            WHERE users.id = $sender_id
            ";
            $users = $this->db->query($query)->result_array();
            if (count($users) > 0) {
                $user_info = $users[0];
                $oneinfo = array(
                    'noti_id' => $row['id'],
                    'sender_id' => $sender_id,
                    'sender_name' => $user_info['first_name'] . " " . $user_info['last_name'],
                    'is_SenderImage' => true,
                    'content' => $row['body'],
                    'timestamp' => $row['created_at']
                );
                array_push($res, $oneinfo);
            }
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function purchase_history_post()
    {
        $res = array();
        $user_id = $this->session->userdata('user_id');
        // $user_id = 29;
        $hist_arr = [];
        if ($user_id > 0) {
            $hist_arr = $this->db->get_where('payment', array('user_id' => $user_id))->result_array();
        } else {
            $hist_arr = $this->db->get('payment')->result_array();
        }
        foreach ($hist_arr as $row) {
            $course_id = $row['course_id'];
            $query = "SELECT course.*, users.first_name, users.last_name
            FROM course, users
            WHERE users.id = course.user_id
            AND course.id = $course_id
            ";
            // Latest Courses
            $course = $this->db->query($query)->result_array();
            if (count($course) > 0) {

                $course_infor = $course[0];
                $onePurchase = array(
                    'id' => $row['id'],
                    'course_id' => $row['course_id'],
                    'title' => $course_infor['title'],
                    'amount' => $row['amount'],
                    'instructor_name' => $course_infor['first_name'] . $course_infor['last_name'],
                    'method' => $row['payment_type'],
                    'date_added' => $row['date_added']
                );
                array_push($res, $onePurchase);
            }
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function pending_course_post()
    {
        $res = array();
        $user_id = $this->session->userdata('user_id');
        $pending_arr = $this->offline_payment_model->pending_offline_payment($user_id)->result_array();
        foreach ($pending_arr as $row) {
            $course_id_arr = json_decode($row['course_id']);
            foreach ($course_id_arr as $course_id) {
                $query = "SELECT course.*, users.first_name, users.last_name
                FROM course, users
                WHERE users.id = course.user_id
                AND course.id = $course_id
                ";
                // Latest Courses
                $course = $this->db->query($query)->result_array();
                if (count($course) > 0) {
                    $course_infor = $course[0];
                    $onePurchase = array(
                        'id' => $row['id'],
                        'course_id' => $course_id,
                        'title' => $course_infor['title'],
                        'amount' => $row['amount'],
                        'instructor_name' => $course_infor['first_name'] . $course_infor['last_name'],
                        'date_added' => $row['timestamp']
                    );
                    array_push($res, $onePurchase);
                }
            }
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function change_password_post()
    {
        $res = array();
        $result = $this->user_model->change_password($this->session->userdata('user_id'));
        $res['result'] = $result;
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function logout_post()
    {
        $arr_items = array('user_id');
        $this->session->unset_userdata($arr_items);
        $res = array('result' => 'ok');
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function attach_payment_document_post()
    {
        $res['result'] = "Image Upload failed!";
        $file_extension = pathinfo($_FILES['payment_document']['name'], PATHINFO_EXTENSION);
        if ($file_extension == 'jpg' || $file_extension == 'pdf' || $file_extension == 'txt' || $file_extension == 'png' || $file_extension == 'docx') {
            $total_amount = $_POST['amount'];
            $course_id = $_POST['cart_items'];
            $data['user_id'] = $_POST['user_id'];
            $data['amount'] = $total_amount;
            $data['course_id'] = json_encode(array($course_id));
            $data['document_image'] = rand(6000, 10000000) . '.' . $file_extension;
            $data['timestamp'] = strtotime(date('H:i'));
            $data['status'] = 0;
            $this->db->insert('offline_payment', $data);
            move_uploaded_file($_FILES['payment_document']['tmp_name'], 'uploads/payment_document/' . $data['document_image']);
        } else {
            $this->set_response($res, REST_Controller::HTTP_OK);
            return;
        }
        $res['result'] = "File Uploaded successfully.";
        // $res['result'] = $data;
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
}
