<?php
  ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
  $client = new SoapClient("http://apps.miasistencia.cl/apps/src/s-caac/ws/registraevento.wsdl");


  $evento = "<?xml version='1.0'?> 
<document>
 <title>¿Cuarenta qué?</title>
 <from>Joe</from>
 <to>Jane</to>
 <body>
  Sé que esa es la respuesta pero, ¿cuál es la pregunta?
 </body>
</document>" ;


  $return = $client->registraEvento($evento);

  echo $return.'<br />';
    



?>

