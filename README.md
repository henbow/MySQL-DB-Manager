MySQL-DB-Manager
================

DB Manager for MySQL with Active Record (but still limited). I use this DB Manager in several of my PHP projects.

<strong>How to use it:</strong>

<pre>
&lt;?php
include '../libs/DBActiveRecord.php';

$db = new DB_active_record();

// Use get() method to select data from database
$db-&gt;get('cds');
$data1 = $db-&gt;result_array();

// Using chainable method
$data2 = $db-&gt;get('cds')-&gt;result_array();

print '&lt;pre&gt;';
print_r($data1);
print_r($data2);
print '&lt;/pre&gt;';
</pre>