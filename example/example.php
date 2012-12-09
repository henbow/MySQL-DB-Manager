<?php
ini_set('display_errors', 1);
error_reporting('E_ALL');

include '../libs/DBActiveRecord.php';

$db = new DB_active_record();

$db->select('col1,col2')
	->from('table1')
	->where(array(
		'col1' => '1', 
		'col2' => 'title of content'
	))
   	->where("col3 = 'desc of content'")
	->order_by('col2', 'desc')
	->_build_query();
	
echo $db->get_queries();
