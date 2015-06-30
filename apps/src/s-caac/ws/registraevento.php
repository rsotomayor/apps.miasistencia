<?php


function registraEvento($xmldata_p) {

  return -3;

}


ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("registraevento.wsdl");
$server->addFunction("registraEvento");
$server->handle();
?>
