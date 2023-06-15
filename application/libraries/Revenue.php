<?php

class Revenue
{
    public $id;
    public $student_id, $student_name, $student_email;
    public $payment_type, $amount, $date_added, $last_modified, $admin_revenue, $instructor_revenue, $instructor_payment_status;
    public $attachment_name, $attachment_url;
    public $course_id, $course_title, $instructor_id, $instructor_name, $instructor_email;

    public function __construct($params = null)
    {
        if(!empty($params['id'])) $this->id = $params['id'];
        if(!empty($params['payment_type'])) $this->payment_type = $params['payment_type'];
        if(!empty($params['student_name'])) $this->student_name = $params['student_name'];
        if(!empty($params['student_email'])) $this->student_email = $params['student_email'];
        if(!empty($params['payment_type'])) $this->payment_type = $params['payment_type'];
        if(!empty($params['amount'])) $this->amount = $params['amount'];
        if(!empty($params['date_added'])) $this->date_added = $params['date_added'];
        if(!empty($params['last_modified'])) $this->last_modified = $params['last_modified'];
        if(!empty($params['admin_revenue'])) $this->admin_revenue = $params['admin_revenue'];
        if(!empty($params['instructor_revenue'])) $this->instructor_revenue = $params['instructor_revenue'];
        if(!empty($params['instructor_payment_status'])) $this->instructor_payment_status = $params['instructor_payment_status'];

        if(!empty($params['attachment_name'])) $this->attachment_name = $params['attachment_name'];
        if(!empty($params['attachment_url'])) $this->attachment_url = $params['attachment_url'];

        if(!empty($params['course_id'])) $this->course_id = $params['course_id'];
        if(!empty($params['course_title'])) $this->course_title = $params['course_title'];
        if(!empty($params['instructor_id'])) $this->instructor_id = $params['instructor_id'];
        if(!empty($params['instructor_name'])) $this->instructor_name = $params['instructor_name'];
        if(!empty($params['instructor_email'])) $this->instructor_email = $params['instructor_email'];
    }
}

?>