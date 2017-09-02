<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Ftl_DB_DataBaseException extends Exception
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code
    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown
    
    public function __construct($message = null, $code = 0)
    {
        if (!$message) $message = 'Unknown '. get_class($this);
        if (!is_numeric($code)) $code = -1;

        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }
}

class Ftl_DB_Error
{
    private $_nro;
    private $_msg;
    private $_query;

    public function __construct($nro,$msg,$query=null)
    {
        $this->_nro = $nro;
        $this->_msg = $msg;
        $this->_query = $query;
    }
    public function getNro() {
        return $this->_nro;
    }

    public function setNro($_nro) {
        $this->_nro = $_nro;
    }

    public function getMsg() {
        return $this->_msg;
    }

    public function setMsg($_msg) {
        $this->_msg = $_msg;
    }

    public function getQuery() {
        return $this->_query;
    }

    public function setQuery($_query) {
        $this->_query = $_query;
    }


}

class Ftl_DB
{



    public static $dns;
    public static $username;
    public static $password;
    public static $host;
    public static $base;
    private static $instance;


    const FN_IDENTIFIER   = "dbfn_";
    
    const MySql     = 'MySql';
    const PDO       = 'PDO';
    const MySqli    = 'MySqli';

    const FETCH_OBJECT  = 0;
    const FETCH_NUM     = 1;
    const FETCH_ASSOC   = 2;

    const CHARSET_UTF8  = 'UTF8';


    private function __construct() {    }

    /**
     * Crea una instancia de la clase PDO
     *
     * @access public static
     * @return object de la clase PDO
     */
    public static function getInstance($charset = self::CHARSET_UTF8) {

        if (!isset(self::$instance)) {

            switch (DB_TYPE)
            {

                case Ftl_DB::MySql:
                    self::$instance = Ftl_MySql::getInstance(DB_HOST,DB_USER,DB_PASS,DB_BASE);
                    break;
                case Ftl_DB::PDO:
                    self::$instance = Ftl_PDO::getInstance();
                    break;

                default:
                    self::$instance = null;
                    break;
            }

        }

        return self::$instance;
    }

    /**
     * Impide que la clase sea clonada
     *
     * @access public
     * @return string trigger_error
     */
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

}


?>
