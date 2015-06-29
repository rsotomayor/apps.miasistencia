<?php

#ini_set('display_errors', 0);


require_once ("servicios.php");

$myws = new Servicios();


$idwsname  = isset($_GET['wsname']) ? $_GET['wsname'] : NULL ;

if ( isset($_GET['wsname']) ) {
  $idwsname = $_GET['wsname'] ;
  $record = $_GET;
} else if ( isset($_POST['wsname']) ) {
  $idwsname = $_POST['wsname'] ;
  $record = $_POST;
} else {
  $xml  = '<?xml version="1.0"?>';
  $xml .= '<result>';
  $xml .= '<response>KO</response>';  
  $xml .= '<description>No existe servicio</description>';  
  $xml .= '</result>';
}

if ( $idwsname != NULL ) {
  $record = isset($_GET['wsname']) ? $_GET : $_POST ;
  if ( method_exists($myws,$idwsname) ) {
    $xml  = $myws->$idwsname($record);
  } else {
    $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<result>';
    $xml .= '<response>KO</response>';  
    $xml .= '<description>Funcion o método['.$idwsname.'] no existe</description>';  
    $xml .= '</result>';
  }
  
} 

if ( !isset($_GET['debug']) && !isset($_POST['debug']) && $idwsname != 'help') {
  header ('content-type: text/xml');
} 
#print_r($_FILES);echo "\n";
#print_r($_POST);echo "\n";

echo $xml;

 
 
?>

