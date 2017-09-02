<?php
    if (!isset($skip_session)){
        session_start();
    }
    
    header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
     
    //error_reporting(E_ERROR | E_WARNING | E_PARSE);
    
    

    define('DS', DIRECTORY_SEPARATOR);
    define('PATH_FRM', dirname(__FILE__) );
    define('PATH_JS', PATH_FRM . DS . 'js' . DS );
    define('PATH_TEMP', PATH_FRM . DS . 'temp' . DS );
    define('PATH_SITE', dirname(dirname(__FILE__)) . DS );
    define('PATH_ADMIN',PATH_FRM . DS . '..' . DS . 'admin' . DS );

    require_once PATH_FRM . DS . 'Log.php';
    require_once PATH_FRM . DS . 'Loader.php';
    Ftl_Loader::getInstance();
    require_once PATH_FRM . DS . 'Environment.php';
    require_once PATH_FRM . DS . 'DB.php';
    
    define('ENVIRONMENT',strtolower(Ftl_Environment::detect()));

    require_once PATH_FRM . DS . 'Environment' . DS . 'Config-' . ENVIRONMENT . '.php';
	//require_once PATH_FRM . DS . 'Environment' . DS . 'Config-development.php';
    

    $_GET       = Ftl_ArrayUtil::map( $_GET , 'stripslashes' );
    $_POST      = Ftl_ArrayUtil::map( $_POST , 'stripslashes' );
    $_COOKIE    = Ftl_ArrayUtil::map( $_COOKIE , 'stripslashes' );
    $_REQUEST   = Ftl_ArrayUtil::map( $_REQUEST , 'stripslashes' );

    if ( !defined( 'SSL_ENABLED' ) )        define( 'SSL_ENABLED', false );

    if ( !defined( 'PATH_UPLOADS' ) )       define( 'PATH_UPLOADS' , PATH_SITE . 'uploads' . DS);


    if ( !defined( 'DB_TYPE' ) )            define( 'DB_TYPE' , Ftl_DB::PDO );
    if ( !defined( 'DB_PREFIX' ) )          define( 'DB_PREFIX' , "" );
    
    if ( !defined( 'DB_CHARSET' ) )         define( 'DB_CHARSET' , 'utf8' );
    if ( !defined( 'DB_USE_CACHE' ) )       define( 'DB_USE_CACHE' , false );
    if ( !defined( 'DB_PATH_CACHE' ) )      define( 'DB_PATH_CACHE',PATH_TEMP . 'db' . DS );
    if ( !defined( 'DB_TIMEOUT_CACHE' ) )   define( 'DB_TIMEOUT_CACHE',24 );//horas

    if ( !defined( 'LANG' ) )               define( 'LANG', Ftl_Language::ES );//Defino el idioma en español por default

    define ( 'FB_SCOPE', 'public_profile,email,publish_actions,user_friends' );

    define ( 'SCRIPT_NAME', Ftl_Path::getScriptName() ); //Pagina actual

    define( 'URL_UPLOADS',  URL_ROOT . "uploads/");
    
    
    

    define ( 'FOTO_W', 600 );
    define ( 'FOTO_H', 400 ); 

    if ( !defined( 'SESSION_KEY' ) )   define( 'SESSION_KEY',md5('admin') );//horas
    
    if ( !defined( 'ID_ACCION' ) )   define( 'ID_ACCION',2 );//horas
    

    $ioHelper   = new Ftl_IOHelper();
    $ioHelper->addFromArray($_REQUEST);
    

?>
