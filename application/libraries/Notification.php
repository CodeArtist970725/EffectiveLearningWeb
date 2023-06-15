<?php

class Notification
{
    public $id, $body, $user_id, $created_at, $title, $link, $from_id;
    public $user_email, $user_name;

    public function __construct($params)
    {
        if(!empty($params['id'])) $this->id = $params['id'];
        if(!empty($params['body'])) $this->body = $params['body'];
        if(!empty($params['user_id'])) $this->user_id = $params['user_id'];
        if(!empty($params['created_at'])) $this->created_at = $params['created_at'];
        if(!empty($params['title'])) $this->title = $params['title'];
        if(!empty($params['link'])) $this->link = $params['link'];
        if(!empty($params['from_id'])) $this->from_id = $params['from_id'];

        $this->created_at = time_elapsed_string($this->created_at);
    }
}

?>