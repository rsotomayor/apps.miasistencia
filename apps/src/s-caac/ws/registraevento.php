<?php


function registraEvento($xmldata_p) {
  $fo = fopen("/tmp/registraevento.log","a+");
  fputs ($fo,$xmldata_p);
  fclose($fo);
  return 0;
}


ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("registraevento.wsdl");
$server->addFunction("registraEvento");
$server->handle();
?>
