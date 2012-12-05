<?php
include 'DBBase.php';

class DB_active_record extends DB {
    var $_where = "";
    var $_fields = "*";
    var $_order_by = "";
    var $_limit = "";
    
    function limit($limit, $offset = '') {
        if($offset == '') {
            $this->_limit = " LIMIT " . $limit . " ";
        } else {
            $this->_limit = " LIMIT " . $offset . ", " . $limit . " ";
        }
        
        return $this;
    }
    
    function order_by($fields, $order = 'ASC') {
        $the_fields = '';
        
        if(is_array($fields)) {
            $field_array = array();
            
            foreach ($fields as $field) {
                $field_array[] = "`" . $field . "`";
            }
            
            $the_fields = implode(', ', $field_array);
        } else {
            $the_fields = "`" . $fields . "`";
        }
        
        $this->_order_by = " ORDER BY " . $the_fields . " " . $order . " ";
        
        return $this;
    }
    
    function get($table, $where = '') {
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values){
                $wh[] = "`" . $fields . "` = '" . $values . "'";
            }
            
            $where = "WHERE " . implode(' AND ', $wh);
        } 
        
        $this->_sql  = "SELECT " . $this->_fields . " FROM `" . $table . "` ";
        $this->_sql .= $where != '' ? $where : $this->_where;
        $this->_sql .= $this->_order_by;
        $this->_sql .= $this->_limit;
        
        return $this->_sql;
        
        return $this->reset_all()->query($this->_sql);
    }
    
    function insert($table, $data) {
        if($table == '') return FALSE;
        if(!is_array($data)) return FALSE;
        
        $fields = "";
        $values = "";
        
        foreach ($data as $key => $value) {
            $fields .= "`" . $key . "`,";
            $values .= "'" . $value . "',";
        }
        
        $this->_sql = "INSERT INTO `".$table."` (" . trim($fields, ',') . ") VALUES (" . trim($values, ',') . ")";
        
        return $this->reset_all()->query($this->_sql);
    }
    
    function update($table, $data, $where) {
        if($table == '') return FALSE;
        if(!is_array($data)) return FALSE;
        
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values){
                $wh[] = "`" . $fields . "` = '" . $values . "'";
            }
            
            $where = "WHERE " . implode(' AND ', $wh);
        }
        
        $fields = array();
        
        foreach ($data as $key => $value) {
            $fields[] = "`" . trim($key) . "` = '" . trim($value) . "'";
        }        
        
        $this->_sql = "UPDATE `".$table."` SET " . implode(', ', $fields) . " " . $where;
                
        return $this->reset_all()->query($this->_sql);
    }
    
    function delete($table, $where = '', $like = '', $limit = FALSE) {
        if($where == '') return FALSE;
        
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values){
                $wh[] = "`" . $fields . "` = '" . $values . "'";
            }
            
            $where = "WHERE " . implode(' AND ', $wh);
        }
        
        $this->_sql = "DELETE FROM `".$table."` " . $where;
        
        return $this->reset_all()->query($this->_sql);
    }
}
 
/**
 * End of file DBActiveRecord.php
 * 
 */