<?php

class Course
{
    public $id, $title, $short_description, $description, $is_free_course, $category_id;
    public $instructor_id, $user_id;

    public function __construct($params = null)
    {
        if(!empty($params['id'])) $this->id = $params['id'];
        if(!empty($params['title'])) $this->title = $params['title'];
        if(!empty($params['short_description'])) $this->short_description = $params['short_description'];
        if(!empty($params['description'])) $this->description = $params['description'];
        if(!empty($params['is_free_course'])) $this->is_free_course = $params['is_free_course'];
        if(!empty($params['category_id'])) $this->category_id = $params['category_id'];
        if(!empty($params['user_id'])) {
            $this->instructor_id = $params['user_id'];
            $this->user_id = $params['user_id'];
        }
    }
}

?>