<?php

class Reply
{
    public $id, $body, $created_at;
    public $user_id, $user_name, $user_email;
    public $question_id;
    public $likes;
    public $course_id, $instructor_id;
    public $is_instructor;

    public function __construct($params = null)
    {
        if(!empty($params['id'])) $this->id = $params['id'];
        if(!empty($params['body'])) $this->body = $params['body'];
        if(!empty($params['created_at'])) $this->created_at = $params['created_at'];
        if(!empty($params['user_id'])) $this->user_id = $params['user_id'];
        if(!empty($params['user_name'])) $this->user_name = $params['user_name'];
        if(!empty($params['user_email'])) $this->user_email = $params['user_email'];
        if(!empty($params['question_id'])) $this->question_id = $params['question_id'];
        if(!empty($params['likes'])) $this->likes = $params['likes'];
        if(!empty($params['course_id'])) $this->course_id = $params['course_id'];
        if(!empty($params['instructor_id'])) $this->instructor_id = $params['instructor_id'];

        $this->created_at = time_elapsed_string($this->created_at);

        if($this->likes == null) $this->likes = 0;
    }

    public function increase_likes()
    {
        $this->likes++;
    }
}

?>