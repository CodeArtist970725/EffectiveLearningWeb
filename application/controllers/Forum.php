<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Forum extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        if (!$this->session->userdata('cart_items')) {
            $this->session->set_userdata('cart_items', array());
		}
		if(!$this->session->user_id) redirect(site_url('home/login'));

		$this->load->model('forum_model');
	}

	public function index()
	{
		$page_data['page_name'] = (APPPATH . '/views/forum/all_questions');
		$page_data['questions'] = $this->forum_model->get_questions_by_user($this->session->user_id, 10);
		$page_data['courses'] = $this->forum_model->get_course_by_user($this->session->user_id);
		$page_data['page_title'] = get_phrase('forum');

		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
	}

	public function get_questions()
	{
		if(!$this->session->user_id || !$this->input->post('course_id')) return;
		$course_id = $this->input->post('course_id');
		$q_cnt = $this->input->post('question_count') ?? 5;
		$search = $this->input->post('search') ?? '';
		$user_id = $this->session->user_id;
		$ans = $this->forum_model->get_questions_by_user($user_id, $q_cnt, 0, $search, $course_id);

		echo json_encode($ans);
	}

	public function question($question_id)
	{
		if(!$this->forum_model->check_permision_user_question($this->session->user_id, $question_id)) {
			redirect(site_url('forum'));
			return;
		}
		$page_data['page_name'] = (APPPATH . '/views/forum/question');
		$page_data['question'] = $this->forum_model->get_question_by_id($question_id, true);
		$page_data['page_title'] = get_phrase('question');

		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
	}

	public function get_question_replies($question_id)
	{
		if(!$this->forum_model->check_permision_user_question($this->session->user_id, $question_id) && !$this->session->admin_login) return;
		$ans = $this->forum_model->get_question_by_id($question_id, true);
		echo json_encode($ans);
	}

	public function make_reply($question_id)
	{
		if(!$this->session->user_id || $question_id == null) {
			redirect(site_url('home/login'));
			return;
		}
		$reply_id = $this->forum_model->insert_reply($this->session->user_id, $question_id, $this->input->post('reply_body'));
		$reply = $this->forum_model->get_reply_by_id($reply_id);

		$this->trigger_pusher('make_reply', $question_id, $reply);

		$question = $this->forum_model->get_question_by_id($question_id, true);
		
		$link = site_url('home/lesson/').$question->course_id.'/'.$question->course_id.'?question_id='.$question_id;
		if($question->user_id != $this->session->user_id) {
			$this->forum_model->insert_notification($question->user_id, $this->session->user_id, $link, $question->title, $reply->body);
			$this->trigger_pusher('notifications', $question->user_id, ['reload' => true]);
		}
		$course = $this->forum_model->get_course_by_id($question->course_id);
		if($course->user_id != $this->session->user_id) {
			$this->forum_model->insert_notification($course->user_id, $this->session->user_id, $link, $question->title, $reply->body);
			$this->trigger_pusher('notifications', $course->user_id, ['reload' => true]);
		}
		foreach($question->replies as $reply) {
			if($reply->user_id != $this->session->user_id) {
				$this->forum_model->insert_notification($reply->user_id, $this->session->user_id, $link, $question->title, $reply->body);
				$this->trigger_pusher('notifications', $reply->user_id, ['reload' => true]);
			}
		}

		redirect(site_url('forum/question/'.$question_id));
	}

	public function ajax_make_reply($question_id)
	{
		if(!$this->session->user_id || $question_id == null) return;

		$reply_id = $this->forum_model->insert_reply($this->session->user_id, $question_id, $this->input->post('reply_body'));
		$reply = $this->forum_model->get_reply_by_id($reply_id);

		$this->trigger_pusher('make_reply', 'for_question', $reply);

		$question = $this->forum_model->get_question_by_id($question_id, true);

		$link = site_url('home/lesson/').$question->course_id.'/'.$question->course_id.'?question_id='.$question_id;
		if($question->user_id != $this->session->user_id) {
			$this->forum_model->insert_notification($question->user_id, $this->session->user_id, $link, $question->title, $reply->body);
			$this->trigger_pusher('notifications', $question->user_id, ['reload' => true]);
		}
		$course = $this->forum_model->get_course_by_id($question->course_id);
		if($course->user_id != $this->session->user_id) {
			$this->forum_model->insert_notification($course->user_id, $this->session->user_id, $link, $question->title, $reply->body);
			$this->trigger_pusher('notifications', $course->user_id, ['reload' => true]);
		}
		foreach($question->replies as $reply) {
			if($reply->user_id != $this->session->user_id) {
				$this->forum_model->insert_notification($reply->user_id, $this->session->user_id, $link, $question->title, $reply->body);
				$this->trigger_pusher('notifications', $reply->user_id, ['reload' => true]);
			}
		}

		echo "1";
	}

	public function trigger_pusher($chanel, $event, $data)
	{
		require (APPPATH . '/../vendor/autoload.php');
		$options = array(
			'cluster' => 'us2',
			'useTLS' => true
		);
		$pusher = new Pusher\Pusher(
			'e5402ab131348277484a',
			'5dd5720d3838dac82bf8',
			'986045',
			$options
		);
		$pusher->trigger($chanel, $event, $data);
	}

	public function make_question($ajax = null)
	{
		if(!$this->session->user_id || !$this->input->post('course_id') || !$this->input->post('title') || !$this->input->post('body')) {
			if(!$ajax) redirect(site_url('home/login'));
			return;
		}
		$course_id = $this->input->post('course_id');
		$title = $this->input->post('title');
		$question_id = $this->forum_model->insert_question($this->session->user_id, $course_id, $title, $this->input->post('body'));
		$question = $this->forum_model->get_question_by_id($question_id);

		$this->trigger_pusher('make_question', 'new-'.$course_id, $question);

		$instructor_id = $this->forum_model->get_course_by_id($course_id)->instructor_id;
		$link = site_url('home/lesson/').$course_id.'/'.$course_id.'?question_id='.$question_id;
		$this->forum_model->insert_notification($instructor_id, $this->session->user_id, $link, $title, $question->body);

		$noti = $this->forum_model->get_notifications_by_user($instructor_id);

		$this->trigger_pusher('notifications', $instructor_id, $noti);

		if(!$ajax) redirect(site_url('forum'));
		else echo "1";
	}

	public function search()
	{
		if(!$this->session->user_id) return;
		$count = $this->input->post('count');
		$search = $this->input->post('search_word');
		$course = $this->input->post('course_id') ?? null;
		if($course == -1) $course = null;
		$questions = $this->forum_model->get_questions_by_user($this->session->user_id, $count, 0, $search, $course);

		echo json_encode($questions);
	}

	public function make_like($type)
	{
		$question_id = $this->input->post('question_id');
		if(!$question_id || !$this->session->user_id) {
			echo "0";
			return;
		}
		$data = $this->forum_model->insert_like($this->session->user_id, $question_id, $type);
		if($data) {
			$this->trigger_pusher($data['chanel'], $data['event'], $data['value']);
			echo "1";
		}
		else {
			echo "2";
		}
	}

	public function remove_notification($id)
	{
		echo $this->forum_model->remove_notification_by_id($id);
	}

	public function get_all_notifications()
	{
		$noti = $this->forum_model->get_notifications_by_user($this->session->user_id);
		echo json_encode($noti);
	}

	public function edit_question($question_id) {
		$user_id = $this->session->user_id;
		if(!$user_id || !$this->forum_model->can_edit_question($user_id, $question_id)) {
			echo json_encode(["response" => "Permision denined"]);
			return;
		}
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		echo json_encode(['response' => $this->forum_model->edit_question($question_id, $title, $body)]);

		$this->trigger_pusher('edit-delete', 'edit-question', $question_id);
	}

	public function delete_question($question_id)
	{
		$user_id = $this->session->user_id;
		if(!$user_id || !$this->forum_model->can_edit_question($user_id, $question_id)) {
			echo json_encode(["response" => "Permision denined"]);
			return;
		}
		echo json_encode(['response' => $this->forum_model->delete_question($question_id)]);

		$this->trigger_pusher('edit-delete', 'delete-question', $question_id);
	}

	public function edit_reply($reply_id) {
		$user_id = $this->session->user_id;
		if(!$user_id || !$this->forum_model->can_edit_reply($user_id, $reply_id)) {
			echo json_encode(['response' => 'Permision denined']);
			return;
		}
		$body = $this->input->post('body');
		echo json_encode(['response' => $this->forum_model->edit_reply($reply_id, $body)]);

		$this->trigger_pusher('edit-delete', 'edit-reply', $reply_id);
	}

	public function delete_reply($reply_id)
	{
		$user_id = $this->session->user_id;
		if(!$user_id || !$this->forum_model->can_edit_reply($user_id, $reply_id)) {
			echo json_encode(['response' => 'Permision denined']);
			return;
		}
		echo json_encode(['response' => $this->forum_model->delete_reply($reply_id)]);

		$this->trigger_pusher('edit-delete', 'delete-reply', $reply_id);
	}
}
