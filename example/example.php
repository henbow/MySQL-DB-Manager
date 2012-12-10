<?php
ini_set('display_errors', 1);
error_reporting('E_ALL');

include '../libs/DBActiveRecord.php';

$db = new DB_active_record();

$db->select('col1,col2')
	->from('table1')
	->join('table2', 'table1.col1=table2.col2','inner')
	->join('table3', 'table.col1=table2.col2','left')
	->where(array(
		'col1' => '1', 
		'col2' => 'title of content'
	))
   	->where("col3 = 'desc of content'")
	->like(array('col4'=>'test'))
	->like('col3="sd"')
	->order_by('col2', 'desc')
	->_build_query();
	
echo $db->get_queries();
