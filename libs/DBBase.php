<?php
/**
 * DBManBase class
 * @author Hendro Wibowo (hendro@tiwule.net)
 * @link http://www.github.com/
 * @since 05 November 2012
 * 
 */

include 'DBConfig.php';

class DB {
    var $_username = "";
    var $_password = "";
    var $_hostname = "";
    var $_database = "";
    var $_port     = "";
    var $_conn_id  = FALSE;
    var $_sql      = "";
    var $_pconnect = FALSE;
    var $_char_set    = 'utf8';
    var $_dbcollat    = 'utf8_general_ci';
    var $_escape_char = '`';
    var $_like_escape_str = '';
    var $_like_escape_chr = '';
    var $_count_string    = 'SELECT COUNT(*) AS ';
    var $_random_keyword  = ' RAND()';
    var $_save_queries    = FALSE;

    var $delete_hack = TRUE;
    var $queries;
    var $query_count;
    var $result_id;
    var $result_array;
    var $result_object;
    var $row_array;
    var $num_rows;
    
    function __construct(){        
        $this->_username = DBConfig::$db_user;
        $this->_password = DBConfig::$db_pass;
        $this->_hostname = DBConfig::$db_host;
        $this->_database = DBConfig::$db_name;
        $this->_pconnect = DBConfig::$db_pconnect;
                
        $this->_initialize();
    }
    
    function _initialize(){
        if (is_resource($this->_conn_id) OR is_object($this->_conn_id))
        {
            return TRUE;
        }
    
        $this->_conn_id = ($this->_pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();

        if ( ! $this->_conn_id)
        {
            die('[ERROR]: Unable to connect to the database');
            
            return FALSE;
        }

        if ($this->_database != '')
        {
            if ( ! $this->db_select())
            {
                die('[ERROR]: Unable to select database: '.$this->_database);
            
                return FALSE;           
            }
            else
            {
                if ( ! $this->db_set_charset($this->_char_set, $this->_dbcollat))
                {
                    return FALSE;
                }
        
                return TRUE;
            }
        }

        return TRUE;
    }

    function db_connect()
    {
        if ($this->_port != '')
        {
            $this->_hostname .= ':'.$this->_port;
        }
        
        return @mysql_connect($this->_hostname, $this->_username, $this->_password, TRUE);
    }
    
    function db_pconnect()
    {
        if ($this->_port != '')
        {
            $this->_hostname .= ':'.$this->_port;
        }

        return @mysql_pconnect($this->_hostname, $this->_username, $this->_password);
    }
    
    function reconnect()
    {
        if (mysql_ping($this->_conn_id) === FALSE)
        {
            $this->_conn_id = FALSE;
        }
    }

    function db_select()
    {
        return @mysql_select_db($this->_database, $this->_conn_id);
    }

    function db_set_charset($charset, $collation)
    {
        return @mysql_query("SET NAMES '".$this->escape_str($charset)."' COLLATE '".$this->escape_str($collation)."'", $this->_conn_id);
    }
    
    function _version()
    {
        return "SELECT version() AS ver";
    }

    function _execute($sql)
    {
        $sql = $this->_prep_query($sql);
        return @mysql_query($sql, $this->_conn_id);
    }
    
    function _prep_query($sql)
    {
        if ($this->delete_hack === TRUE)
        {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
            {
                $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
            }
        }
        
        return $sql;
    }
    
    function simple_query($sql)
    {
        if ( ! $this->_conn_id)
        {
            $this->_initialize();
        }

        return $this->_execute($sql);
    }
    
    function query($sql, $return_object = FALSE)
    {
        if ($sql == '')
        {
            return FALSE;
        }

        if ($this->_save_queries == TRUE)
        {
            $this->queries[]   = $sql;
            $this->query_count = count($this->queries);
        }
                
        if (FALSE === ($this->result_id = $this->simple_query($sql)))
        {
            return FALSE;
        }
        
        if ($this->is_write_type($sql) === TRUE)
        {
            return TRUE;
        }
        
        if($return_object === TRUE) 
        {
            $this->result_object = $this->result_object();
        } 
        else 
        {
            $this->result_array  = $this->result_array();
            $this->row_array     = isset($this->result_array[0]) ? $this->result_array[0] : NULL;
        }
        
        $this->num_rows = $this->num_rows();
        
        $this->_conn_id   = NULL;
        $this->result_id  = NULL;
        
        return $this;
    }
    
    function num_rows()
    {
        return @mysql_num_rows($this->result_id);
    }
    
    function row_array()
    {
        return $this->row_array;
    }
    
    function result_object()
    {
        if (count($this->result_object) > 0)
        {
            return $this->result_object;
        }
        
        if ($this->result_id === FALSE OR $this->num_rows() == 0)
        {
            return array();
        }

        while ($row = $this->_fetch_object())
        {
            $this->result_object[] = $row;
        }
        
        return $this->result_object;
    }
    
    function result_array()
    {
        if (count($this->result_array) > 0)
        {
            return $this->result_array;
        }

        if ($this->result_id === FALSE OR $this->num_rows() == 0)
        {
            return array();
        }

        while ($row = $this->_fetch_assoc())
        {
            $this->result_array[] = $row;
        }
        
        return $this->result_array;
    }
    
    function reset_all(){
        $this->queries = NULL;
        $this->query_count = NULL;
        $this->result_id = NULL;
        $this->result_array = NULL;
        $this->result_object = NULL;
        $this->row_array = NULL;
        $this->num_rows = NULL;
        
        return $this;
    }
    
    function display_errors() {
        return "Error occured: " . $this->_error_number() . " => " . $this->_error_message();
    }
    
    function _fetch_assoc()
    {
        return mysql_fetch_assoc($this->result_id);
    }
    
    function _fetch_object()
    {
        return mysql_fetch_object($this->result_id);
    }

    function is_write_type($sql)
    {
        if ( ! preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql))
        {
            return FALSE;
        }
        return TRUE;
    }
    
    function escape_str($str, $like = FALSE)    
    {   
        if (is_array($str))
        {
            foreach($str as $key => $val)
            {
                $str[$key] = $this->escape_str($val, $like);
            }
        
            return $str;
        }

        if (function_exists('mysql_real_escape_string') AND is_resource($this->_conn_id))
        {
            $str = mysql_real_escape_string($str, $this->_conn_id);
        }
        elseif (function_exists('mysql_escape_string'))
        {
            $str = mysql_escape_string($str);
        }
        else
        {
            $str = addslashes($str);
        }
        
        // escape LIKE condition wildcards
        if ($like === TRUE)
        {
            $str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
        }
        
        return $str;
    }
        
    function affected_rows()
    {
        return @mysql_affected_rows($this->_conn_id);
    }
    
    function insert_id()
    {
        return @mysql_insert_id($this->_conn_id);
    }

    function count_all($table = '')
    {
        if ($table == '')
        {
            return 0;
        }
    
        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

        if ($this->num_rows == 0)
        {
            return 0;
        }

        $row = $query->row();
        return (int) $this->numrows;
    }

    function _list_tables($prefix_limit = FALSE)
    {
        $sql = "SHOW TABLES FROM ".$this->_escape_char.$this->database.$this->_escape_char; 

        if ($prefix_limit !== FALSE AND $this->dbprefix != '')
        {
            $sql .= " LIKE '".$this->escape_like_str($this->dbprefix)."%'";
        }

        return $sql;
    }
    
    function _list_columns($table = '')
    {
        return "SHOW COLUMNS FROM ".$table;
    }

    function _field_data($table)
    {
        return "SELECT * FROM ".$table." LIMIT 1";
    }

    function _error_message()
    {
        return mysql_error($this->_conn_id);
    }
    
    function _error_number()
    {
        return mysql_errno($this->_conn_id);
    }

    function _escape_identifiers($item)
    {
        if ($this->_escape_char == '')
        {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id)
        {
            if (strpos($item, '.'.$id) !== FALSE)
            {
                $str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);  
                
                // remove duplicates if the user already included the escape
                return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
            }       
        }
        
        if (strpos($item, '.') !== FALSE)
        {
            $str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;            
        }
        else
        {
            $str = $this->_escape_char.$item.$this->_escape_char;
        }
    
        // remove duplicates if the user already included the escape
        return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
    }
            
    function _from_tables($tables)
    {
        if ( ! is_array($tables))
        {
            $tables = array($tables);
        }
        
        return '('.implode(', ', $tables).')';
    }

    function _insert($table, $keys, $values)
    {   
        return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
    }
    
    function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
    {
        foreach($values as $key => $val)
        {
            $valstr[] = $key." = ".$val;
        }
        
        $limit = ( ! $limit) ? '' : ' LIMIT '.$limit;
        
        $orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';
    
        $sql = "UPDATE ".$table." SET ".implode(', ', $valstr);

        $sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';

        $sql .= $orderby.$limit;
        
        return $sql;
    }

    function _truncate($table)
    {
        return "TRUNCATE ".$table;
    }
    
    function _delete($table, $where = array(), $like = array(), $limit = FALSE)
    {
        $conditions = '';

        if (count($where) > 0 OR count($like) > 0)
        {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->ar_where);

            if (count($where) > 0 && count($like) > 0)
            {
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $like);
        }

        $limit = ( ! $limit) ? '' : ' LIMIT '.$limit;
    
        return "DELETE FROM ".$table.$conditions.$limit;
    }

    function _close($conn_id)
    {
        @mysql_close($conn_id);
    }   
}

/**
 * End of file DBBase.php
 * 
 */