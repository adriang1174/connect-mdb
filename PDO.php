<?php

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDOnew
 *
 * @author Luki
 */
class Ftl_PDO extends Ftl_IDataBase{

    public function  __construct(){}

    /*
     * Funcion: getInstance
     */
    public static function getInstance(){
        if( self::$_instance == null )
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /*
     * Funcion: connect
     * Desc:    Establece la conexión con la base de datos.
     */
    public function connect()
    {
        try
        {
            if( !is_null( $this->_link ) )
            {
                    return true;
            }

            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_BASE;

            $options = array (

                //PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                1000 => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                1002 => "SET NAMES " . DB_CHARSET
                

                
            );

            //$this->_link = new PDO( $dsn, DB_USER, DB_PASS,  $options);
            ///Aqui modificar para MDB
            //if(DB == 'msaccess')
            $dbname = DB_NAME;
            $this->_link = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbname; Uid=; Pwd=;");
            
            

            if( is_null( $this->_link ) )
            {
                throw new Ftl_DB_DataBaseException( "No se pudo realizar la conección con la BBDD", -2 );
            }

            return true;
        }
        catch (PDOException $e)
        {
            throw new Ftl_DB_DataBaseException( $e->getMessage(), $e->getCode() );
        }

    }

    /*
     * Funcion: close
     * Desc:    Cierra la conexión con la base de datos.
     */
    public function close()
    {
        $this->_inTransaction = false;
        $this->_link = null;
    }

    /*TRANSACTIONS*/

    /*
     * Funcion: beginTransaction
     * Desc:    Inicializa una transaccion
     */
    public function beginTransaction()
    {
        try{

            if( !$this->connect() )
            {
                return false;
            }

            if ( $this->_inTransaction )
            {
                return true;
            }

            $this->_inTransaction = $this->_link->beginTransaction();

            return $this->_inTransaction;
            
        }catch(Exception $e){

            $this->_stmt = null;
            throw new Ftl_DB_DataBaseException($e->getMessage(), $e->getCode());

        }

    }

    /*
     * Funcion: commit
     * Desc:    Commitea la transaccion en curso
     */
    public function commit()
    {
        try{

            if ( !$this->_inTransaction )
            {
                return false;
            }

            $this->_inTransaction = !$this->_link->commit();

            return !$this->_inTransaction;


        }catch(Exception $e){
            throw new Ftl_DB_DataBaseException($e->getMessage(), $e->getCode());
        }

    }

    /*
     * Funcion: rollback
     * Desc:    vuelve atras la ejecucion de una transaccion
     */
    public function rollback()
    {
        try{

            if ( !$this->_inTransaction )
            {
                return false;
            }

            $this->_inTransaction = !$this->_link->rollBack();

            return !$this->_inTransaction;

            //return $this->_inTransaction;

        }catch(Exception $e){
            throw new Ftl_DB_DataBaseException($e->getMessage(), $e->getCode());
        }
    }

    public function inTransaction()
    {
        return $this->_inTransaction;

    }


    /*QUERY & EXECUTE*/

    /*
     * Funcion: query
     * Desc:    Ejecuta una consulta $sql
     */
    public function query( $sql, $data = null )
    {
        try{

            //Si me llega $sql vacio o no puedo realizar la conexion, retorno false
            if ( $sql == "" || !$this->connect() )
            {
                return false;
            }

            if ( isset ($this->_stmt ) )
            {
                $this->_stmt->closeCursor();
                $this->_stmt = null;
            }

            //Si me llegan datos en $data, los escapeo y los reemplazo en el $sql
            $sql = $this->getEscapedQuery( $sql, $data );

            $this->_stmt = $this->_link->query( $sql );
            $this->_affectedRows = $this->_stmt->rowCount();

            return $this->_stmt;
        
        }catch(Exception $e){

            $this->_stmt = null;
            throw new Ftl_DB_DataBaseException($e->getMessage()." ".$sql, $e->getCode());

        }

    }

    /*
     * Funcion: query
     * Desc:    Ejecuta una consulta $sql
     */
    public function execute( $sql, $data = null )
    {
        try{

            //Si me llega $sql vacio o no puedo realizar la conexion, retorno false
            if ( $sql == "" || !$this->connect() )
            {
                return false;
            }

            //Si me llegan datos en $data, los escapeo y los reemplazo en el $sql
            $this->_affectedRows = $this->_link->exec( $this->getEscapedQuery( $sql, $data ) );

            return $this->_affectedRows;

        }catch(Exception $e){

            $this->_affectedRows = null;
            throw new Ftl_DB_DataBaseException($e->getMessage(), $e->getCode());

        }

    }

    /*PREPARED STATEMENT*/
    public function executePreparedStatement( $statement,$data=array() )
    {
        try{
            $return_val = false;

            //Si me llega $sql vacio o no puedo realizar la conexion, retorno false
            if ( $statement == "" || !$this->connect() )
            {
                return false;
            }


            //Si es un SP, cargo los parametros y asigno los valores.
            //Sino solo asigno valores.
            $auxData = array();
            if ( preg_match( "/^(call)\s+/i",$statement ) )
            {
                $fields = array();
                foreach( $data as $k => $v )
                {
                    if ( strpos($k, '@') !== false )
                    {
                        $fields[] = $k;
                    }
                    else
                    {
                        $fields[] = ':'.$k;
                        $auxData[':'.$k] = $v;
                    }

                }
                $statement .= "(" . implode (',',$fields) . ")";
                
            }
            else
            {
                foreach( $data as $k => $v ){
                    //$stmt->bindValue(':'.$k, $v);
                    $auxData[':'.$k] = $v;
                }
            }

            //Limpio el stmt y lo cierro
            if ( isset ($this->_stmt ) )
            {
                $this->_stmt->closeCursor();
                $this->_stmt = null;
            }


            $this->_stmt = $this->_link->prepare( $statement );
            
            $this->_stmt->execute( $auxData );

            $this->_affectedRows = $this->_stmt->rowCount();

            return $this->_stmt;
            
        }catch(Exception $e){

            $this->_stmt = null;
            throw new Ftl_DB_DataBaseException($e->getMessage(), $e->getCode());

        }
    }


    /*FETCH*/

    /*
     * Funcion: fetchAllMode
     * Desc:    Retorna todas las filas del resultado de la consulta $sql segun el modo $mode
     *          $mode: Ftl_DB::FETCH_ASSOC, Ftl_DB::FETCH_NUM, Ftl_DB::FETCH_OBJECT
     */
    protected function fetchAllMode   ( $sql=null, $data=null, $mode = Ftl_DB::FETCH_ASSOC )
    {

        if (  isset( $sql )  )
        {
                $this->query( $sql, $data );
        }

        if ( is_null ($this->_stmt ) || $this->_stmt->columnCount() < 1 ){
                return array();
        }

        $data = array();

        switch ( $mode )
        {
            case Ftl_DB::FETCH_ASSOC:

                $data = $this->_stmt->fetchAll( PDO::FETCH_ASSOC );

                break;
            case Ftl_DB::FETCH_NUM:

                $data = $this->_stmt->fetchAll( PDO::FETCH_NUM );

                break;
            case Ftl_DB::FETCH_OBJECT:

                $data = $this->_stmt->fetchAll( PDO::FETCH_OBJ );

                break;
        }


        return $data;


    }

    /*
     * Funcion: fetchMode
     * Desc:    Retorna una filas del resultado de la consulta $sql segun el modo $mode
     *          $mode: Ftl_DB::FETCH_ASSOC, Ftl_DB::FETCH_NUM, Ftl_DB::FETCH_OBJECT
     */
    protected function fetchMode   ( $sql=null, $data=null, $mode = Ftl_DB::FETCH_ASSOC, $col=-1 )
    {

        if (  isset( $sql )  )
        {
                $this->query( $sql, $data );
        }

        if ( is_null ($this->_stmt ) || $this->_stmt->columnCount() < 1 ){
                return array();
        }

        $data = array();

        switch ( $mode )
        {
            case Ftl_DB::FETCH_ASSOC:

                $data = $this->_stmt->fetch( PDO::FETCH_ASSOC );

                break;

            case Ftl_DB::FETCH_NUM:

                if ($col > -1)
                    $data = $this->_stmt->fetchColumn( $col );
                else
                    $data = $this->_stmt->fetch( PDO::FETCH_NUM );

                break;

            case Ftl_DB::FETCH_OBJECT:

                $data = $this->_stmt->fetch( PDO::FETCH_OBJ );

                break;
            
        }

        return $data;

    }

    /*
     * Funcion: getLastInsertId
     * Desc:    Obtiene el id generado por una consulta de insert
     */
    public function getLastInsertId()
    {
        try
        {
            return $this->_link->lastInsertId();
        }
        catch(PDOException $e)
        {
            throw new Ftl_DB_DataBaseException($e->getMessage(), $e->getCode());
        }
    }

    /*CLEAN AND ESCAPE*/
    public function escape( $data = null )
    {

            if ( is_null( $data ) )
            {
                return 'NULL';
            }

            if( !is_array( $data ) )
            {

                    if( !$this->connect()  ) {
                            return $data;
                    }

                    if ( is_bool($data) )
                    {
                        return $this->_link->quote( $data, PDO::PARAM_BOOL );
                    }
                    else if ( is_int( $data ) )
                    {
                        return $this->_link->quote( $data, PDO::PARAM_INT );
                    }
                    else if (is_string( $data ))
                    {
                        return $this->_link->quote( $data, PDO::PARAM_STR );
                    }
                    else
                    {
                        return $this->_link->quote( $data );
                    }

            }

            $ret = array();
            foreach( $data as $k => $v ) {
                    $ret[ $k ] = $this->escape( $v );
            }

            return $ret;

    }

    
}
?>
