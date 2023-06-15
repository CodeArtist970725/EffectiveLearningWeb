<?php

class Question
{
    public $id, $title, $body, $created_at;
    public $user_id, $user_name, $user_email;
    public $course_id, $course_title, $course_description;
    public $reply_count, $replies;
    public $likes;

    public function __construct($params_or_key = null)
    {
        if(!empty($params_or_key['id'])) $this->id = $params_or_key['id'];
        if(!empty($params_or_key['title'])) $this->title = $params_or_key['title'];
        if(!empty($params_or_key['body'])) $this->body = $params_or_key['body'];
        if(!empty($params_or_key['created_at'])) $this->created_at = $params_or_key['created_at'];

        if(!empty($params_or_key['user_id'])) $this->user_id = $params_or_key['user_id'];
        if(!empty($params_or_key['user_name'])) $this->user_name = $params_or_key['user_name'];
        if(!empty($params_or_key['user_email'])) $this->user_email = $params_or_key['user_email'];

        if(!empty($params_or_key['course_id'])) $this->course_id = $params_or_key['course_id'];
        if(!empty($params_or_key['course_title'])) $this->course_title = $params_or_key['course_title'];
        if(!empty($params_or_key['course_description'])) $this->course_description = $params_or_key['course_description'];

        if(!empty($params_or_key['reply_count'])) $this->reply_count = $params_or_key['reply_count'];
        if(!empty($params_or_key['likes'])) $this->likes = $params_or_key['likes'];

        if($this->created_at) $this->created_at = time_elapsed_string($this->created_at);

        if($this->reply_count == null) $this->reply_count = 0;
        if($this->likes == null) $this->likes = 0;
        $this->replies = [];
    }

    public function push_reply($reply)
    {
        array_push($this->replies, $reply);
        $this->reply_count = count($this->replies);
    }

    public function increase_reply_count()
    {
        $this->reply_count++;
    }

    public function increase_likes()
    {
        $this->likes++;
    }
}

?>