<?php


function registraEvento($xmldata_p) {
  $fo = fopen("/tmp/registraevento.log","a+");

  $xml=simplexml_load_string($xmldata_p);
  foreach ( $xml as $key => $valor ) {
    $data = "KEY $key  VALOR $valor\n";
    fputs($fo,$data);
  }
  fclose($fo);

  return 0;
}


ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("registraevento.wsdl");
$server->addFunction("registraEvento");
$server->handle();
?>
