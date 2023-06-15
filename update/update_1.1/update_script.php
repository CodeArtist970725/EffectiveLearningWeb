<?php
	$CI = get_instance();
	$CI->load->database();
	$CI->load->dbforge();

	//insert data in settings table
	$addons_data = array( 'version' => '1.1' );
	$CI->db->where('unique_identifier', 'offline_payment');
	$CI->db->update('addons', $addons_data);
?>
