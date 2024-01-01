<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

global $wbdb;

class lknDb
{

    private $result = ''; // sorgu sonucu

    private $result_data;
    /**
     * @var wpdb
     */
    private $db_class; // MySQL kaynağı
    private $effected_rows = 0; // kayıt pointeri. namı diğer cursor :D
    private $prefix_mask;
    private $sql;
    private $limit_start;
    private $limit_end;
    private $errorNum;
    private $errorMsg = '';
    protected static $_instance;
    private $_log;
    private $_count;

    /**
     * class için gerekli değişkenler
     *
     */
    function __construct()
    {

        $simpleEcommercePlugin = simpleToolsEcommerce::getInstance();

        $this->db_class = $simpleEcommercePlugin->getDb();
        // print_r($this->db_class);
        $this->prefix_mask = $simpleEcommercePlugin->get("_db_prefix_mask");
        $this->_count = 0;
        $this->_log = '';
    }

    /**
     * kullanılan kaynağı serbest bırak
     *
     */
    function __destruct()
    {

        /* 		if(is_resource($this->result))
          @mysql_free_result($this); */
    }

    // --------------------------------------------------------------------

    /**
     * sorgu class içerisine al
     */
    function query($sql)
    {
        if (strpos($sql, '#__users AS u')) {
        }
        $sql = $this->tableName($sql);
        $count = $this->_count;
        $count++;
        $this->_log .= "$count . $sql<br />";
        $this->_count = $count;
        $this->sql = $sql;
    }

    // Prevent users to clone the instance
    public final function __clone()
    {

        exit('Clone is not allowed.');
    }

    /**
     * sorguyu çalıştır
     *
     */
    function setQuery()
    {

        if (strtoupper(substr($this->sql, 0, 6)) == "SELECT" || strtoupper(substr($this->sql, 0, 4)) == 'SHOW' || strtoupper(substr($this->sql, 0, 7)) == 'EXPLAIN' || strtoupper(substr($this->sql, 0, 8)) == 'DESCRIBE') {
            $this->result = $this->db_class->get_results($this->sql);


            if (is_array($this->result)) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->effected_rows = $this->db_class->query($this->sql);
        }
    }

    /**
     * en son işlemde kaç kayıt geri döndü. (yalnızca select)
     *
     * @return integer
     */
    function num_rows()
    {
        return $this->db_class->num_rows;
    }

    /**
     * @return int önceki işlemde dönen kayıt sayısı (insert,update,delete)
     */
    function getAffectedRows()
    {

        return $this->effected_rows;

        //mysql_affected_rows($this->db);
    }

    /**
     * tablo adını dönderir. #__ ---->jos değişimimi yapar
     *
     * @param string $table
     * @return string
     */
    function tableName($table)
    {
        global $table_prefix;
        return str_replace($this->prefix_mask, $table_prefix, $table);
    }

    /**
     * Summary of loadObjectList
     * @param mixed $key
     * @return array|object|string
     */
    function loadObjectList()
    {

        return $this->result;
    }

    function loadObject()
    {

        $array = $this->loadObjectList();
        return count($array) > 0 ? $array[0] : array();
    }

    function CreateInsertSql($data, $table, $query = 'INSERT')
    {

        $tablename = $this->tableName($table);
        $cols = array();
        $values = array();
        foreach ($data as $field => $value) {
            $cols[] = "$field";
            //$value=$this->_escape($value);
            $values[] = "'$value'";
        }

        $columns = implode(',', $cols);
        $datafields = implode(',', $values);

        return "$query INTO $tablename ($columns) VALUES ($datafields)";
    }

    /**
     * update işlemi için sorgu oluşturur
     *
     * @param array $data
     * @param string $table
     * @param string $where : fieldAdi='deger' şeklinde olmalı
     * @return string
     */
    function CreateUpdateSql($data, $table, $where)
    {

        $tabname = $this->tableName($table);
        $sql = "UPDATE $tabname SET ";
        $items = array();
        foreach ($data as $key => $value) {
            $items[] = $key . "='" . $value . "'";
        }

        $sql .= implode(', ', $items);
        if (isset($where)) {
            $sql .= " WHERE " . $where;
        }

        return $sql;
    }

    /**
     * en son eklenen verinin id'sini dönderirir
     */
    function get_insert_id()
    {

        return $this->db_class->insert_id;
        //
    }

    /**
     * Sorgu sonucunu dizi olarak al
     *
     * @param  string
     * @return array
     */
    function fetch_array()
    {

        return $this->db_class->loadAssocList();
        //@mysql_fetch_assoc($this->result);
    }

    /**
     * Get a result row as an enumerated array
     *
     * @param  string
     * @return array
     */
    function fetch_row()
    {
        return $this->db_class->loadRowList();
        //return @mysql_fetch_row($this->result);
    }

    # Escape the given string

    function _escape($string)
    {

        return $this->db_class->_escape($string);
    }

    /**
     * nulldate dönderir date/time
     *
     * @param  string $dateTime 'datetime', 'date', 'time'
     * @return string  Quoted null/zero date string
     */
    function getNullDate($dateTime = 'datetime')
    {

        if ($dateTime == 'datetime') {
            return '0000-00-00 00:00:00';
        } elseif ($dateTime == 'date') {
            return '0000-00-00';
        } else {
            return '00:00:00';
        }
    }

    /**
     *
     * @return lknDb
     */
    public static function getInstance()
    {

        static $_instance;
        if (!isset($_instance)) {
            $_instance = new lknDb();
        }

        return $_instance;
    }

    /**
     * start parametresini alıp. sql sorguların sonunda Limit 0, 5 gibi ek yapar
     *
     * @return string
     */
    function getLimit()
    {

        $config = lknConfig::getInstance();
        $recordPerPage = $config->get('recordPerPage');

        $start = lknInputFilter::filterInput($_REQUEST, 'start');
        $start = (int)$start;

        $start = (int)$start;

        if ($start == '' || $start <= 0) {
            $start = 1;
        }

        $limitStart = ($start - 1) * $recordPerPage;
        $limitEnd = $recordPerPage;

        $this->limit_start = $limitStart + 1;
        $this->limit_end = $limitStart + $limitEnd;

        $sql = "\n LIMIT $limitStart, $limitEnd";

        return $sql;
    }

    /**
     * class içerisindeki herhangi bir değeri dönderir
     *
     * @param  string $var class değişken adı
     * @return mixed
     */
    function get($var)
    {

        if (isset($this->$var)) {
            return $this->$var;
        } else {
            return null;
        }
    }

    /**
     * veri array'ında ilk kayıt
     *
     */
    function getFistRecord()
    {

        $data = $this->get_object_list();
        if (!isset($data)) {
            $data = $this->fetch_array();
        }
        if (count($data) > 0) {
            return $data[0];
        } else
            return null;
    }

    /**
     * nesne listesi
     *
     * @return array
     */
    function get_object_list()
    {
        return $this->result;
    }

    /**
     * yollanan sql sorgusuna ait ilk veriyi dönderir
     *
     * @param string $sql
     * @return array
     */
    function loadTable($sql)
    {

        $_db = lknDb::createInstance();
        $_db->query($sql);
        $_db->setQuery();
        $count = $_db->num_rows();
        if ($count > 0) {
            return $_db->loadObject();
        } else {
            return;
        }
    }

    function getErrorMessage()
    {
        return $this->db_class->last_error;
    }

    function getErrorNumber()
    {
        return $this->db_class->getErrorNum();

        /* if(isset($this->errorNum)){
          return $this->errorNum;
          }else{
          return '';
          } */
    }

    function tableExist($tbl)
    {

        $sql = "DESCRIBE $tbl";

        $this->query($sql);
        $this->setQuery();
        if (!$this->result) {
            return '0';
        } else {
            return '1';
        }
    }

    /**
     * array A list of all the tables in the database
     *
     * @access    public
     * @return object
     */
    function getTableList($numinarray = 0)
    {

        $this->query('SHOW TABLES');
        $this->setQuery();
        $count = $this->num_rows();
        if ($count == 0) {
            return NULL;
        } else {
            $rows = $this->fetch_row();
            $array = array();

            foreach ($rows as $row) {
                $array[] = $row[$numinarray];
            }


            return $array;
        }
    }

    /**
     * Retrieves information about the given tables
     *
     * @access    public
     * @param    string            A table name
     * @param    boolean            Only return field types, default true
     * @return    array            An array of fields by table
     */
    function getTableFields($table, $typeonly = true)
    {

        settype($tables, 'array'); //force to array
        $result = array();

        $this->query('SHOW FIELDS FROM ' . $table);
        $this->setQuery();
        $fields = $this->loadObjectList();

        if ($typeonly) {
            foreach ($fields as $field) {
                $result[$field->Field] = preg_replace("/[(0-9)]/", '', $field->Type);
            }
        } else {
            foreach ($fields as $field) {
                $result[$field->Field] = $field;
            }
        }

        return $result;
    }
}

function lknGetCount($sql)
{

    $_db = lknDb::createInstance();

    $_db->query($sql);
    $_db->setQuery();

    $count2 = $_db->num_rows();


    $count2 = $count2 > 0 ? $count2 : 0;
    return $count2;
}
