<?php
include 'DBBase.php';

/**
 * DBActiveRecord.php
 * Class to manipulate SQL and wrap it to methods for easier usages
 * @author Hendro Wibowo (hendro@tiwule.net)
 * @since 05 December 2012
 * @version 1.0.2
 * 
 */

class DB_active_record extends DB {
    var $_where = "";
    var $_fields = "*";
    var $_order_by = "";
    var $_limit = "";
    var $_like = "";
    var $_table = "";
    var $_join = "";
    
	/**
	 * Compile SELECT query
	 * @access private 
	 * @return object $this. Return the class object to maintain chainability.
	 * 
	 */
    function _build_query() {
        $this->_sql  = "SELECT " . $this->_fields . " FROM " . $this->_table;
        
        if(is_array($this->_where)) {
            $wh = array();
            
            foreach($this->_where as $fields => $values) {
                $wh[] = $fields . " = '" . $values . "'";
            }
            
            $this->_where = implode(' AND ', $wh);
        }
        
        if($this->_where != '' OR $this->_like != '') 
        	$where = " WHERE ";
		else
			$where = "";
        
        $this->_sql .= $this->_join;
		$this->_sql .= $where;
        $this->_sql .= $this->_where;
        $this->_sql .= $this->_like;
        $this->_sql .= $this->_order_by;
        $this->_sql .= $this->_limit;
        
        if(preg_match('/\sOR\s|\sAND\s/', $this->_sql)) $this->_sql = preg_replace('/\sOR\s|\sAND\s/', '', $this->_sql, 1);
        
        $this->_where = "";
        $this->_fields = "*";
        $this->_order_by = "";
        $this->_limit = "";
        $this->_like = "";
        $this->_table = "";
        $this->_join = "";
        
        return $this;
    }
    
	/**
	 * Get the result from compiled query that produced by _build_query() method
	 * @access public
	 * @param string $table <optional>
	 * @param mixed $where <optional>. Can be array or string.
	 * @return object $this. Return the class object to maintain chainability.
	 * 
	 */
    function get($table = '', $where = '') {
        if($table != '') $this->from($table);
        if($where != '') $this->where($where);

        $this->_build_query();
        
        return $this->reset_all()->query(trim($this->_sql));
    }
    
	/**
	 * Compile fields from array or string for SELECT <fields> clause
	 * @access public
	 * @param mixed $fields <optional>. Can be array or string.
	 * @return object $this. Return the class object to maintain chainability.
	 * 
	 */
    function select($fields) {
        $the_fields = '';
        
        if($fields != '') {
            if(is_array($fields)) {
                if(count($fields[0]) == 2) {
                    foreach ($fields as $field_name => $field_as) {
                        $the_fields .= $field_name . " AS " . $field_as . ",";
                    }
                } else {
                    foreach ($fields as $field) {
                        $the_fields .= $field . ",";
                    }
                }
                
                $this->_fields = trim($the_fields, ',');
            } else {
                $this->_fields = $fields;
            }
        }
        
        return $this;        
    }
    
	/**
	 * Generate table name for SELECT <fields> FROM <table> query
	 * @access public
	 * @param string $table. The table name.
	 * @param mixed $alias <optional>. Alias of table. Useful for join operation.
	 * @return object $this. Return the class object to maintain chainability.
	 * 
	 */
    function from($table, $alias = '') {
        if($alias == '') {
            $this->_table = $table;
        } else {
            $this->_table = $table . " " . $alias;
        }
        
        return $this;
    }

	/**
	 * Generate WHERE clause
	 * @access public 
	 * @param array $where. Can be array or string.
	 * @param string $condition_keyword <Optional>. This parameter has value 'AND' or 'OR'. Default is 'AND'
	 * @return object $this. Return the class object to maintain chainability.
	 * 
	 */
    function where($where, $condition_keyword = 'AND') {
        $condition = "";
        
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values) {
                $wh[] = $fields ." = '" . $values . "'";
            }
            
            $condition = implode(' ' . $condition_keyword . ' ', $wh);
        } else {
            $condition = $where;
        }
        
        $this->_where = $condition;
            
        return $this;
    }
	
	/**
	 * Generate WHERE <fields> NOT IN (<values>) clause
	 * @access public 
	 * @param string $field_name. The field name.
	 * @param array $values. The values of field.
	 * @param string $condition_keyword <Optional>. This parameter has value 'AND' or 'OR'. Default is 'AND'
	 * @return object $this. Return the class object to maintain chainability.
	 * 
	 */
	function where_not_in($field_name, $values, $condition_keyword = 'AND') {
        $the_values = array();
            
        foreach($values as $value) {
            $the_values[] = "'" . $values . "'";
        }
		
        $this->_where = $field_name . " NOT IN (" . implode(',', $the_values) . ")";
            
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
                
                $conditions[] = $field . " LIKE '" . $value . "'";
            }
            
            $condition = implode(' ' . $condition_keyword . ' ', $conditions);
        } else {
            $condition = $like;
        }
        
        $this->_like = " " . $condition_keyword . " " . $condition;
        
        return $this;
    }
    
    function join($table, $on, $type = '') {
        $join_type = "";
        
        switch ($type) {
            case 'inner':
                $join_type = " INNER JOIN ";
                break;
                
            case 'left':
                $join_type = " LEFT JOIN ";
                break;
                
            case 'right':
                $join_type = " RIGHT JOIN ";
                break;
            
            default:
                $join_type = " JOIN ";
                break;
        }
        
        $this->_join .= $join_type . " " . $table . " ON " . $on . " ";
        
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
                $field_array[] = $field;
            }
            
            $the_fields = implode(', ', $field_array);
        } else {
            $the_fields = $fields;
        }
        
        $this->_order_by = " ORDER BY " . $the_fields . " " . strtoupper($order) . " ";
        
        return $this;
    }
    
    function insert_data($table, $data) {
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
    
    function update_data($table, $data, $where) {
        if($table == '') return FALSE;
        if(!is_array($data)) return FALSE;
        
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values) {
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
    
    function delete_data($table, $where) {
        if(is_array($where)) {
            $wh = array();
            
            foreach($where as $fields => $values){
                $wh[] = "`" . $fields . "` = '" . $values . "'";
            }
            
            $where = "WHERE " . implode(' AND ', $wh);
        }
        
        $this->_sql  = "DELETE FROM `".$table."` " . $where;
        $this->_sql .= $this->_limit;
        
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