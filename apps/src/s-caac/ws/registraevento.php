<?php


require_once ("configuration.php");
set_include_path(get_include_path() . PATH_SEPARATOR . $cscwebPath_g);
require_once ("dbconn.php");
$dbconn_g    = new DBConn($dbConfig_g) ;
$dbconn_g->connect();
$link_g  = $dbconn_g->getConnection();

function validaEntero($str_p ) {
  if ( ( strlen($str_p) == 0) || 
      ( preg_match("/^[0-9\-]*$/s",$str_p) == false )  ) {      
   return false ;
  }
  return true ; 
}

function digitoverificador($r) {
  $s=1;
  for($m=0;$r!=0;$r/=10) 
    $s=($s+$r%10*(9-$m++%6))%11 ;
  return chr($s?$s+47:75);
}

function validaRut($str_p) {
  $retval = -1 ;
  $rut = str_replace(".","",$str_p);
  $rut = str_replace(" ","",$rut);
  
  $dummy = explode("-",$rut);

  if ( count($dummy) == 2 ) {
    if ( $dummy[1] == 'G' || $dummy[1] == 'g' ) {
      return $rut;  
    }

    if (  validaEntero($dummy[0]) == false ) {
      return NULL ;
    } else {
      $rut = $dummy[0] ;
      $dv =  digitoverificador($rut);
      
      if ( $dv == $dummy[1] ) {
        $retval = $rut."-".$dv; 
      } else {
        return NULL ;
      }
    }
         
  } else {
    return NULL ;
  }

  return $retval;    
}


function getRegistroByModulo($idmodulo_p) {
  global $link_g;
  
  $sqlString  = "SELECT
      * FROM
      apps_db.sac_modulos
      WHERE 
      id = '$idmodulo_p'" ;

  $link_g->SetFetchMode(ADODB_FETCH_ASSOC); 
  $rs = $link_g->Execute($sqlString);

  return $rs->fields ;

}

function registraAcceso($record_p) {
  global $link_g;
  $retval = 0 ;
  date_default_timezone_set('UTC');

  $ipaddress       = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL ;
  $idusuario       = isset($record_p['idusuario']) ? $record_p['idusuario'] : NULL;
  $idevento        = isset($record_p['idevento'])      ? $record_p['idevento'] : 'REPORT.ESTADO' ;
  $idtipoevento    = isset($record_p['idtipoevento'])      ? $record_p['idtipoevento'] : NULL ;
  $idincidente     = isset($record_p['idincidente'])   ? $record_p['idincidente'] : NULL ;  ;
  $fechahoraserver = strftime('%Y-%m-%d %H:%M:%S',time());

  $fechahora       = isset($record_p['fechahora'])   ? $record_p['fechahora'] : NULL ;  ;

  $tsgps           = isset($record_p['tsgps'])      ? $record_p['tsgps'] : $ts ;
  $fechahoragps    = strftime('%Y-%m-%d %H:%M:%S',$tsgps);

  $idacceso        = isset($record_p['idacceso'])        ? $record_p['idacceso'] : NULL ;
  $idmodulo        = isset($record_p['idmodulo'])        ? $record_p['idmodulo'] : NULL ;
  $idmovil         = isset($record_p['idmovil'])         ? $record_p['idmovil'] : NULL ;
  $latitud         = isset($record_p['latitud'])         ? $record_p['latitud'] : NULL ;
  $longitud        = isset($record_p['longitud'])        ? $record_p['longitud'] : NULL ;
  $velocidad       = isset($record_p['velocidad'])       ? $record_p['velocidad'] : NULL ;
  $altura          = isset($record_p['altura'])          ? $record_p['altura'] : NULL ;
  $rumbo           = isset($record_p['rumbo'])           ? $record_p['rumbo'] : NULL ;

  $idresultado     = isset($record_p['idresultado'])      ? $record_p['idresultado'] : NULL ;
  $scorehuella     = isset($record_p['score'])           ? $record_p['score'] : NULL ;
  $idfinger        = isset($record_p['idfinger'])        ? $record_p['idfinger'] : NULL ;
  $idestado        = isset($record_p['idestado'])        ? $record_p['idestado'] : NULL ;
  $nota            = isset($record_p['nota'])            ? $record_p['nota'] : NULL ;


  $szTemperature   = isset($record_p['temperatura'])     ? $record_p['temperatura'] : NULL ;
  if ( $szTemperature != NULL ) {
    sscanf($szTemperature, "%f%s", $temperature,$degree);
  } else {
    $temperature = NULL ;
    $degree = "F" ;
  }

  $id              = sha1($idmodulo.'-'.$idevento.'-'.$idusuario.'-'.$idestado.'-'.$fechahora);

  $tablename       = $record_p['tablename'];

  $sqlString = "INSERT INTO $tablename(
        id,
        idevento,
        idtipoevento,
        fechahora,
        fechahora_servidor,
        fechahora_gps,
        idacceso,
        idmodulo,
        idmovil,
        idusuario,
        latitud,
        longitud,
        velocidad,
        rumbo,
        altura,
        temperature,
        idincidente,
        idestado,
        idfinger,
        idresultado,
        scorehuella,
        ipaddress,
        nota) 
        VALUES (
        '$id',
        '$idevento',
        '$idtipoevento',
        '$fechahora',
        '$fechahoraserver',
        '$fechahoragps',
        '$idacceso',
        '$idmodulo',
        '$idmovil',
        '$idusuario',
        '$latitud',
        '$longitud',
        '$velocidad',
        '$rumbo',
        '$altura',
        '$temperature',
        '$idincidente',
        '$idestado',
        '$idfinger',
        '$idresultado',
        '$scorehuella',
        '$ipaddress',
        '$nota'
        ) ";


#  $fo = fopen("/tmp/registraevento.log","a+");
#  $data = "SQL $sqlString\n";
#  fputs($fo,$data);
#  fclose($fo);

  try {
    $rs = $link_g->Execute($sqlString);
  } catch (exception $e) { 
    $flagmail = false ;
    $pos = strpos($e->msg, 'Duplicate entry');
    if ($pos === false) {
      $retval = -1 ;
    } else {
      $retval = 1 ;
    }
  }

  return $retval;



}



function registraEvento($xmldata_p) {
  $retval = 0;


  $idacceso = NULL;

  $xml = simplexml_load_string($xmldata_p);

  $fo = fopen("/tmp/registraevento.log","a+");
  foreach ( $xml as $key => $valor ) {
    $data = "KEY $key VALOR $valor\n";
    fputs($fo,$data);
    $record[$key] = $valor ;
  }
  fclose($fo);


  switch ( $record['idevento'] ) {
    case 'A':
      $record['idevento']       = 'IDENTIFICA.RUT' ;
      $record['idestado']       = ($record['idio'] == 'E' ) ? 'ENTRADA' : 'SALIDA'; 
      break;
    case 'I':
      $record['idevento']       = 'INICIO.VIAJE' ;
      break;
    case 'T':
      $record['idevento']       = 'TERMINO.VIAJE' ;
      break;
    case 'S':
      $record['idevento']       = 'REPORT.ESTADO' ;
      break;
   case 'P':
      $record['idevento']       = 'REPORT.POSICION' ;
      break;
    default:
      break;
  }

  $idacceso = isset($record['idacceso']) ? $record['idacceso'] : NULL ;

  $record['idmodulo'] = isset($record['idmodulo']) ? $record['idmodulo'] : $idacceso ;
  $record['idmovil']  = isset($record['idmovil']) ? $record['idmovil'] : $idacceso ;

  $fo = fopen("/tmp/registraevento.log","a+");
  $data = "IDACCESO $idacceso\n";
  fputs($fo,$data);
  fclose($fo);

  if ( $idacceso == NULL ) {
    return 1;
  }

  $dummy = getRegistroByModulo($idacceso);
  if ( $dummy == NULL ) {
    return 2;
  }

  $idcliente = $dummy['idcliente'];

  if ( $idcliente == NULL ) {
    return 3;

  }

  $record['tablename'] = $idcliente.'_db.stk_registroeventos';

  $retcode = registraAcceso($record) ;
  if ( !( $retcode == 0 || $retcode == 1 ) ) {
    return 4;
  }


#  $data = "SQL: $sqlString\n";
#  fputs($fo,$data);

#KEY idusuario  VALOR 109774022
#KEY fechahora  VALOR 2015-06-30 17:21:23
#KEY idfinger  VALOR -1
#KEY idgroup  VALOR 0
#KEY idevento  VALOR A
#KEY idusuariosesion  VALOR NA
#KEY idacceso  VALOR 00D069495C8E
#KEY idio  VALOR S
#KEY idtipoevento  VALOR RUT.MANUAL
#KEY idresultado  VALOR OK
#KEY score  VALOR 0
#KEY fix  VALOR 65538
#KEY tsgps  VALOR 0.000000
#KEY latitud  VALOR -33.028978
#KEY longitud  VALOR -71.580785
#KEY altura  VALOR 100.000000
#KEY rumbo  VALOR 0.000000
#KEY velocidad  VALOR 0.000000
#KEY flagenviado  VALOR 0



  return $retval;;
}


ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("registraevento.wsdl");
$server->addFunction("registraEvento");
$server->handle();
?>
