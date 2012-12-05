<?php
include '../libs/DBActiveRecord.php';

$db = new DB_active_record();

// Use get() method to select data from database
$db->get('cds');
$data1 = $db->result_array();

// Using chainable method
$data2 = $db->get('cds')->result_array();

print '<pre>';
print_r($data1);
print_r($data2);
print '</pre>';