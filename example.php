<?php

require 'vendor/autoload.php';

$db = Ftl_DB::getInstance();
$consulta = 'SELECT * FROM F_ART';
$filas  = $db->fetchObject($consulta);
$db->close();

print_r($filas);

?>