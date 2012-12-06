<?php
include 'DBBase.php';

/**
 * DBActiveRecord.php
 * @author Hendro Wibowo (hendro@tiwule.net)
 * 
 */

class DB_active_record extends DB {
    var $_where = "";
    var $_fields = "*";
    var $_order_by = "";
    var $_limit = "";
    var $_like = "";
    
    function get($table, $where = '') {
        $conditions = "";
        
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values) {
                $wh[] = "`" . $fields . "` = '" . $values . "'";
            }
            
            $conditions = "WHERE " . implode(' AND ', $wh);
        } else {
            $conditions = "WHERE " . $where;
        }
        
        $this->_sql = "SELECT " . $this->_fields . " FROM `" . $table . "` ";
        
        if($where != '') {
            $this->_sql .= $conditions;
        } else {
            $this->_sql .= $this->_where;
        }
        
        if(strpos($this->_sql, 'WHERE')) {
            $this->_sql .= " AND " . $this->_like;
        } else {
            $this->_sql .= $this->_like == '' ? '' : " WHERE " . $this->_like;
        }
        
        $this->_sql .= $this->_order_by;
        $this->_sql .= $this->_limit;
        
        return $this->reset_all()->query(trim($this->_sql));
    }

    function select($fields = '') {
        $the_fields = '';
        
        if($fields != '') {
            if(is_array($fields)) {
                
            } else {
                
            }
        }
        
        return $this;        
    }
    
    function like($like, $condition_keyword = 'AND', $prefix = 'BOTH') {
        $condition = "";
        
        if(is_array($like)) {
            $conditions = array();
            
            foreach ($like as $field => $value) {
                switch ($prefix) {                    
                    case 'BEFORE':
                        $value = "%" . $value;
                        break;
                        
                    case 'AFTER':
                        $value = $value . "%";
                        break;
                        
                    case 'BOTH':
                    default:
                        $value = "%" . $value . "%";
                        break;
                }
                
                $conditions[] = "`" . $field . "` LIKE '" . $value . "'";
            }
            
            $condition = implode(' ' . $condition_keyword . ' ', $conditions);
        } else {
            $condition = $like;
        }
        
        $this->_like = $condition;
        
        return $this;
    }
    
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
    
    function delete($table, $where = '') {
        if($where == '') return FALSE;
        
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values){
                $wh[] = "`" . $fields . "` = '" . $values . "'";
            }
            
            $where = "WHERE " . implode(' AND ', $wh);
        }
        
        $this->_sql  = "DELETE FROM `".$table."` " . $where;
        
        if($this->_limit != '') $this->_sql .= $this->_limit;
        
        return $this->reset_all()->query($this->_sql);
    }
    
    function get_queries($all = FALSE) {
        return $all === TRUE ? $this->queries : $this->_sql;
    }
}
 
/**
 * End of file DBActiveRecord.php
 * 
 */