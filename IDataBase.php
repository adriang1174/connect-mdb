<?php
/* 
 * Interface para todas las implementaciones de Base de datos.
 */

abstract class Ftl_IDataBase {

	
        /*PROPERTIES*/
        protected static $_instance   = null;
        protected $_link              = null;
        protected $_resource          = null;
        protected $_stmt              = null;
        protected $_inTransaction     = false;
        protected $_fetchMode         = Ftl_DB::FETCH_ASSOC;
        protected $_affectedRows      = null;

        //public abstract static function getInstance();

        public abstract function  __construct();
        
        public abstract function connect();
	public abstract function close();

        /*TRANSACTIONS*/

        public abstract function beginTransaction();
        public abstract function commit();
        public abstract function rollback();
        public abstract function inTransaction();

        /*QUERY & EXECUTE*/

	public abstract function query( $sql, $data = array() );
	public abstract function execute( $sql, $data = array() );

//
        /*PREPARED STATEMENT*/
        public abstract function executePreparedStatement($statement,$data=array());
        public function executeSP($sp,$data=array(),$fetch=Ftl_DB::FETCH_ASSOC)
        {
            $res = $this->executePreparedStatement("call $sp",$data);
            if ($res)
            {
                switch ($fetch)
                {
                    case Ftl_DB::FETCH_NUM:
                        return $this->fetchAllNumeric();
                    case Ftl_DB::FETCH_OBJECT:
                        return $this->fetchAllObject();
                    case Ftl_DB::FETCH_ASSOC:
                    default:
                        return $this->fetchAllAssoc();
                }
            }
            else
                return null;
        }

//        /*FETCH AND RETURN*/
//        public function setFetchMode    ($mode=Ftl_DB::FETCH_ASSOC);
//
        /*
         * Funcion: fetchAllAssoc
         * Desc:    Recupera todas las filas del resultado de ejecutar la consulta $sql
         *          como un array asociativo. Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchAllAssoc   ( $sql=null, $data=null )
        {
            return $this->fetchAllMode( $sql, $data, Ftl_DB::FETCH_ASSOC );
        }

        /*
         * Funcion: fetchAllNumeric
         * Desc:    Recupera todas las filas del resultado de ejecutar la consulta $sql
         *          como un array numérico. Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchAllNumeric ( $sql=null, $data=null )
        {
            return $this->fetchAllMode( $sql, $data, Ftl_DB::FETCH_NUM );
        }

        /*
         * Funcion: fetchAllObject
         * Desc:    Recupera una todas las filas del resultado de ejecutar la consulta $sql
         *          como un array de objetos. Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchAllObject  ( $sql=null, $data=null )
        {
            return $this->fetchAllMode( $sql, $data, Ftl_DB::FETCH_OBJECT );
        }


        /*
         * Funcion: fetchAssoc
         * Desc:    Recupera una fila del resultado de ejecutar la consulta $sql
         *          como un array asociativo. Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchAssoc ( $sql=null, $data=null )
        {
            return $this->fetchMode( $sql, $data, Ftl_DB::FETCH_ASSOC );
        }

        /*
         * Funcion: fetchNumeric
         * Desc:    Recupera una fila del resultado de ejecutar la consulta $sql
         *          como un array numerico. Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchNumeric ( $sql=null, $data=null )
        {
            return $this->fetchMode( $sql, $data, Ftl_DB::FETCH_NUM );
        }

        /*
         * Funcion: fetchObject
         * Desc:    Recupera una fila del resultado de ejecutar la consulta $sql
         *          como un array de objetos. Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchObject ( $sql=null, $data=null )
        {
            return $this->fetchMode( $sql, $data, Ftl_DB::FETCH_OBJECT );
        }
        
        /*
         * Funcion: fetchVal
         * Desc:    Recupera un valor del resultado de ejecutar la consulta $sql.
         *          Si no se pasa una $sql se toma el resultado del statement en curso.
         */
        public function fetchVal ($sql=null,$data=null)
        {
            return $this->fetchMode($sql, $data, Ftl_DB::FETCH_NUM, 0);
        }

        /*
         * Funcion: fetchAllMode
         * Desc:    Retorna todas las filas del resultado de la consulta $sql segun el modo $mode
         *          $mode: Ftl_DB::FETCH_ASSOC, Ftl_DB::FETCH_NUM, Ftl_DB::FETCH_OBJECT
         */
        protected abstract function fetchAllMode    ( $sql=null, $data=null, $mode = Ftl_DB::FETCH_ASSOC );

        /*
         * Funcion: fetchMode
         * Desc:    Retorna una filas del resultado de la consulta $sql segun el modo $mode
         *          $mode: Ftl_DB::FETCH_ASSOC, Ftl_DB::FETCH_NUM, Ftl_DB::FETCH_OBJECT
         */
        protected abstract function fetchMode       ( $sql=null, $data=null, $mode = Ftl_DB::FETCH_ASSOC, $col=-1 );

        /*
         * Funcion: getLastInsertId
         * Desc:    Obtiene el id generado por una consulta de insert
         */
        public abstract function getLastInsertId();


        /*
         * Funcion: getAffectedRows
         * Desc:    Retorna
         */
        public function getAffectedRows()
        {
            return $this->_affectedRows;
        }
//        public function getGuid();
//
        public function getFoundRows() {
		return $this->fetchVal( 'SELECT FOUND_ROWS()' );
	}
//
//
//
//        /*CLEAN*/

        /*
         * Funcion: escape
         * Desc:    Funcion recursiva que escapea todos los valores recibidos en $data
         */
        public abstract function escape( $data=null );


        
        /* Function: getEscapedQuery
         * Descripcion: Escapea los datos recibidos en $data y los reemplaza en la consulta
         *              $sql.
        */
        function getEscapedQuery($sql,$data=array())
        {
            

            if (!$this->connect() || is_null($sql) || trim($sql) == '' )
            {
                return $sql;
            }

            if ( is_array ( $data ) )
            {
                foreach($data as $k => $v)
                {
                    $sql = str_replace(":".$k, $this->escape($v), $sql);
                }
            }
            
            return $sql;
        }

        
        /*
         * Inserta los datos pasados por parametro ($data) en una tabla ($table)
         * Si la tabla tiene una PK autoincrement retorna el nuevo id generado, sino retorna true.
         * Si se especifica $psmode=false entonces no lo ejecuta como prepared statement
         * Ej:
         *

         * $data =  array
         *          (
         *              'nombre'    => 'Lucas',
         *              'sexo'      => 'M',
         *              'fecha_nac' => '2011-09-01'
         *          );
         * $res = $con->insert ( 'usuarios', $data );
         *
         */
        function insert ( $table, $data )
        {

            if ($table == null || trim( $table ) == '')
            {
                return false;
            }

            $sql = "INSERT INTO $table SET ";

            $fields = array();

            foreach($data as $k=>$v)
            {
                $fields[] = $k . ' = ' . $this->escape($v);
            }

            $sql .= implode (',',$fields);

            


            $this->execute( $sql );
            return $this->getLastInsertId();
        }

        function update ( $table, $data, $where=null )
        {

            if ($table == null || trim( $table ) == '')
            {
                return false;
            }

            $sql = "UPDATE $table SET ";

            $fields = array();

            foreach($data as $k=>$v)
            {
                $fields[] = $k . ' = ' . $this->escape($v);
            }

            $sql .= implode (',',$fields);

            if ( !is_null ($where) && trim($where) != '' )
            {
                $sql .= " WHERE " . str_ireplace("where", "", $where);
            }

            return $this->execute( $sql );


        }

        function delete ($table,$where=null)
        {


            if ($table == null || trim( $table ) == '')
            {
                return false;
            }

            $sql = "DELETE FROM $table ";

            if ( !is_null ($where) && trim($where) != '' )
            {
                $sql .= "WHERE " . str_ireplace("where", "", $where);
            }


            return $this->execute( $sql );
        }

        function truncate ($table)
        {

            if ($table == null || trim( $table ) == '')
            {
                return false;
            }

            $sql = "TRUNCATE TABLE $table ";

            return $this->execute( $sql );

        }


        //Funciones de agregado
        public function max( $table, $where=null, $column="*" )
        {
            $sql = "select max($column) from $table " . ( $where ? " where $where " : "");
            return $this->fetchVal( $sql );
        }
	public function count( $table, $where=null, $column="*" )
        {
            $sql = "select count($column) from $table " . ( $where ? " where $where " : "");
            return $this->fetchVal( $sql );
        }
        public function sum( $table, $where=null, $column="*" )
        {
            $sql = "select sum($column) from $table " . ( $where ? " where $where " : "");
            return $this->fetchVal( $sql );
        }
}
?>
