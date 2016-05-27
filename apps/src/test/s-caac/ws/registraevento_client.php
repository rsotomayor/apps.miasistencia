<?php
  ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
  $client = new SoapClient("http://apps.miasistencia.cl/apps/src/s-caac/ws/registraevento.wsdl");


  $evento = "<?xml version='1.0'?> 
<marca>
<idusuario>90500627</idusuario>
<fechahora>2015-06-30 17:16:12</fechahora>
<idfinger>-1</idfinger>
<idgroup>0</idgroup>
<idevento>A</idevento>
<idusuariosesion>NA</idusuariosesion>
<idacceso>00D069495C8E</idacceso>
<idio>S</idio>
<idtipoevento>RUT.MANUAL</idtipoevento>
<idresultado>KO.BLKLST</idresultado>
<score>0</score>
<fix>65538</fix>
<tsgps>0.000000</tsgps>
<latitud>-33.028978</latitud>
<longitud>-71.580785</longitud>
<altura>100.000000</altura>
<rumbo>0.000000</rumbo>
<velocidad>0.000000</velocidad>
<flagenviado>0</flagenviado>
</marca>
" ;

  $return = $client->registraEvento($evento);

  echo $return.'<br />';


?>

