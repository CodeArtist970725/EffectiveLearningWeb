<?php

class Instructor
{
    public $id, $user_id, $term_body, $phone_one, $phone_two;
    public $nric_number, $nick_name;
    public $kbz_bank_number, $aya_bank_number, $cb_bank_number, $mab_bank_number;

    public function __construct($params = null)
    {
        if(!empty($params['id'])) $this->id = $params['id'];
        if(!empty($params['user_id'])) $this->user_id = $params['user_id'];
        if(!empty($params['term_body'])) $this->term_body = $params['term_body'];
        if(!empty($params['phone_one'])) $this->phone_one = $params['phone_one'];
        if(!empty($params['phone_two'])) $this->phone_two = $params['phone_two'];
        if(!empty($params['kbz_bank_number'])) $this->kbz_bank_number = $params['kbz_bank_number'];
        if(!empty($params['aya_bank_number'])) $this->aya_bank_number = $params['aya_bank_number'];
        if(!empty($params['cb_bank_number'])) $this->cb_bank_number = $params['cb_bank_number'];
        if(!empty($params['mab_bank_number'])) $this->mab_bank_number = $params['mab_bank_number'];
        if(!empty($params['nric_number'])) $this->nric_number = $params['nric_number'];
        if(!empty($params['nick_name'])) $this->nick_name = $params['nick_name'];
    }
}

?>