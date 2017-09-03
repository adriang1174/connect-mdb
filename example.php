<?php

require 'vendor/autoload.php';

$db = Ftl_DB::getInstance();

$consulta = "SELECT * FROM F_ART where CODART in('0000002', '0000003')";
//$consulta = "SELECT count(*) FROM F_ART";

$filas  = $db->fetchAllObject($consulta);

$db->close();

print_r($filas);

?>