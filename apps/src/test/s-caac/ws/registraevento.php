<?php


if ( isset($_POST['wsname']) ) {
  $wsname = $_POST['wsname'] ;
} else if ( $_GET['wsname'] ) {
  $wsname = $_GET['wsname'] ;  
} else {
  $wsname = NULL ;  
}

if ( $wsname == NULL ) {
  require_once ("configuration.php");
  set_include_path(get_include_path() . PATH_SEPARATOR . $cscwebPath_g);
  require_once ("dbconn.php");
  $dbconn_g    = new DBConn($dbConfig_g) ;
  $dbconn_g->connect();
  $link_g  = $dbconn_g->getConnection();
}


function validaEntero($str_p ) {
  if ( ( strlen($str_p) == 0) || 
      ( preg_match("/^[0-9\-]*$/s",$str_p) == false )  ) {      
   return false ;
  }
  return true ; 
}

function validaMail($email_p){
  return preg_match("/^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$/i", $email_p );
}


function validaTwitter($email_p){
  return preg_match("/^@[A-Z0-9.-]+.[A-Z]{2,4}$/i", $email_p );
}

function digitoverificador($r) {
  $s=1;
  for($m=0;$r!=0;$r/=10) 
    $s=($s+$r%10*(9-$m++%6))%11 ;
  return chr($s?$s+47:75);
}

function validaRut($str_p) {
  $retval = -1 ;

  $pos = strpos($str_p, '-');

  if ( $pos == false ) {
    $str_p = substr($str_p,0,-1).'-'.substr($str_p,-1);
  }
  
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


    
function actualizaModulo($idmodulo_p,$idcliente_p) {
  global $link_g;
  
  $tname = 'apps_db.sac_modulos' ;
  
  $fechahora = strftime('%Y-%m-%d %H:%M:%S',time());

  try {
    $ret = $link_g->Replace($tname, 
        array(  'id'        => $idmodulo_p,
                'idcliente' => $idcliente_p,
                'fechahora' => $fechahora),
        array('id','idcliente'),
        $autoquote=true
        );
  } catch (exception $e) { 
    //~ echo "Error: , ".$e->msg."<br>";
    return -1;
  }
  
  
  return 0;

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

function getRegistroOrganizacion($idorganizacion_p) {
  global $link_g;


  $idorganizacion = strtoupper(str_replace("-","",$idorganizacion_p));
  
  $sqlString  = "SELECT
      * FROM
      ma_db.sac_organizacion
      WHERE 
      UPPER(REPLACE(rut,'-','')) = '$idorganizacion' " ;

  $link_g->SetFetchMode(ADODB_FETCH_ASSOC); 
  $rs = $link_g->Execute($sqlString);

  return $rs->fields ;

}

function getRegistroUsuarioByRut($idcliente_p,$idusuario_p) {
  global $link_g;
  
  $dbname = $idcliente_p.'_db' ;
  $idusuario_p = strtoupper(str_replace("-","",$idusuario_p));  
  
  $sqlString  = "SELECT
      * FROM
      $dbname.sac_usuarios
      WHERE 
      UPPER(REPLACE(rut,'-','')) = '$idusuario_p' " ;
  $link_g->SetFetchMode(ADODB_FETCH_ASSOC); 

  try {
    $rs = $link_g->Execute($sqlString);
  } catch (exception $e) { 
    //~ echo "Error: , ".$e->msg."<br>";
    return NULL;
  } 


  return $rs->fields ;

}

function getRegistroUsuarioByPassword($idcliente_p,$idusuario_p) {
  global $link_g;


  $sqlString  = "SELECT * FROM savtec_common.s_login 
                 WHERE
                 idusuario  = '$idusuario_p' AND idorganizacion = '$idcliente_p' " ;
  
  $link_g->SetFetchMode(ADODB_FETCH_ASSOC); 


  try {
    $rs = $link_g->Execute($sqlString);
  } catch (exception $e) { 
    //~ echo "Error: , ".$e->msg."<br>";
    return NULL;
  } 


  return $rs->fields ;

}



function registraAcceso(&$record_p) {
  global $link_g;
  $retval = 0 ;

  date_default_timezone_set('UTC');

  $ipaddress       = isset($_SERVER['REMOTE_ADDR'])    ? $_SERVER['REMOTE_ADDR'] : NULL ;
  $idusuario       = isset($record_p['idusuario'])     ? $record_p['idusuario'] : NULL;
  $idevento        = isset($record_p['idevento'])      ? $record_p['idevento'] : 'REPORT.ESTADO' ;
  $idtipoevento    = isset($record_p['idtipoevento'])  ? $record_p['idtipoevento'] : NULL ;
  $idincidente     = isset($record_p['idincidente'])   ? $record_p['idincidente'] : NULL ;  ;
  $fechahoraserver = strftime('%Y-%m-%d %H:%M:%S',time());

  $fechahora       = isset($record_p['fechahora'])     ? $record_p['fechahora'] : NULL ;  ;
  $ts              = ($fechahora != NULL ) ? strtotime($fechahora) : time();

//  $tsgps           = isset($record_p['tsgps'])      ? $record_p['tsgps'] : $ts ;
  $tsgps           = $ts ;
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


  $latitud          = str_replace (",",".", $latitud);
  $longitud         = str_replace (",",".", $longitud);
  $altura           = str_replace (",",".", $altura);  
  $rumbo            = str_replace (",",".", $rumbo); 
  $velocidad        = str_replace (",",".", $velocidad);  

  $szTemperature   = isset($record_p['temp'])     ? $record_p['temp'] : NULL ;
  if ( $szTemperature != NULL ) {
    sscanf($szTemperature, "%f%s", $temperature,$degree);
  } else {
    $temperature = NULL ;
    $degree = "F" ;
  }

  $id              = sha1($idmodulo.'-'.$idevento.'-'.$idusuario.'-'.$idestado.'-'.$fechahora);
  $hash_sum        = isset($record_p['hash_sum'])        ? $record_p['hash_sum'] : NULL ;   
  $tablename       = $record_p['tablename'];
  
  $record_p['fechahora'] = $fechahora;
  
  if ( $record_p['idio'] == 'E' ) {
    $record_p['idtransaccion'] = 'ENTRADA';
  } else if ( $record_p['idio'] == 'S' ) {
    $record_p['idtransaccion'] = 'SALIDA';
  } else {
    $record_p['idtransaccion'] = 'NOTKNOWN';
  }

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
        nota,
        hash_sum) 
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
        '$nota',
        '$hash_sum'
        ) ";


  //~ $fo = fopen("/tmp/registraAcceso.log","a+");
  //~ $data = "SQL $sqlString\n";
  //~ fputs($fo,$data);
  //~ fclose($fo);

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

  $tablename_estadomodulo  = isset($record_p['tablename_estadomodulo']) ? $record_p['tablename_estadomodulo'] : NULL ;


  if ( $idevento == 'REPORT.ESTADO' && $idtipoevento == 'TEST.SERVER' && $tablename_estadomodulo != NULL ) {

    $modelo              = isset($record_p['modelo'])     ? $record_p['modelo'] : NULL ;
    $ntemplates          = isset($record_p['ntemplates']) ? $record_p['ntemplates'] : NULL ;
    $ntransacciones      = isset($record_p['ntransacciones']) ? $record_p['ntransacciones'] : NULL ;
    $nusuarios           = isset($record_p['nusuarios'])  ? $record_p['nusuarios'] : NULL ;
    $spacefree           = isset($record_p['spacefree'])  ? $record_p['spacefree'] : NULL ;
    $spacesize           = isset($record_p['spacesize'])  ? $record_p['spacesize'] : NULL ;
    $spaceused           = isset($record_p['spaceused'])  ? $record_p['spaceused'] : NULL ;
    $spaceusedporcentaje = isset($record_p['spaceusedporcentaje'])  ? $record_p['spaceusedporcentaje'] : NULL ;
    $temp                = isset($record_p['temp'])       ? $record_p['temp'] : NULL ;
    $tlastsync           = isset($record_p['tlastsync'])  ? $record_p['tlastsync'] : NULL ;
    $tup                 = isset($record_p['tup'])        ? $record_p['tup'] : NULL ;
    $memoryfree          = isset($record_p['memoryfree']) ? $record_p['memoryfree'] : NULL ;
    $memorytotal         = isset($record_p['memorytotal']) ? $record_p['memorytotal'] : NULL ;
    $memoryused          = isset($record_p['memoryused']) ? $record_p['memoryused'] : NULL ;


    $sqlString = "INSERT INTO $tablename_estadomodulo (
          id,
          modelo,
          ntemplates,
          ntransacciones,
          nusuarios,
          spacefree,
          spacesize,
          spaceused,
          spaceusedporcentaje,
          memoryfree,
          memorytotal,
          memoryused,
          temp,
          tlastsync,
          tup)
          VALUES (
          '$id',
          '$modelo',          
          '$ntemplates',
          '$ntransacciones',
          '$nusuarios',
          '$spacefree',
          '$spacesize',
          '$spaceused',
          '$spaceusedporcentaje',
          '$memoryfree',
          '$memorytotal',
          '$memoryused',          
          '$temp',
          '$tlastsync',
          '$tup');
          ";
    
      $fo = fopen("/tmp/registraEstado.log","a+");
      $data = "SQL $sqlString\n";
      fputs($fo,$data);
      fclose($fo);

      try {
        $rs = $link_g->Execute($sqlString);
      } catch (exception $e) { 
        $pos = strpos($e->msg, 'Duplicate entry');
        if ($pos === false) {
          $retval = -1 ;
        } else {
          $retval = 1 ;
        }
      }

    
  }



  return $retval;



}



function registraEvento($xmldata_p) {
  $retval = 0;


  $idacceso = NULL;

  $xml = simplexml_load_string($xmldata_p);

  $fo = fopen("/tmp/registraevento.log","a+");
  fputs($fo,"==================================================================");
  foreach ( $xml as $key => $valor ) {
    $data = "KEY $key VALOR $valor\n";
    fputs($fo,$data);
    $record[$key] = $valor ;
  }
  fclose($fo);


  switch ( $record['idevento'] ) {
    case 'A':
      $record['idevento']       = 'REPORT.ACCESO' ;
      $record['idestado']       = ($record['idio'] == 'E' ) ? 'ENTRADA' : 'SALIDA'; 
      break;
    case 'I':
      $record['idevento']        = 'INICIO.VIAJE' ;
      break;
    case 'T':
      $record['idevento']        = 'TERMINO.VIAJE' ;
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


function registraEventoMarca(&$record_p) {
  $retval = 0;

  $record_p ['ticket'] = 'NO TICKET DISPONIBLE';
  $idacceso = NULL;
  
  $xmldata = $record_p['xmldata'];

  $xml = simplexml_load_string($xmldata);

  $fo = fopen("/tmp/registramarca.log","a+");
  fputs($fo,"==================================================================\n");
  foreach ( $xml as $key => $valor ) {
    $data = "KEY $key VALOR $valor\n";
    fputs($fo,$data);
    $record[$key] = $valor ;
  }
  fclose($fo);

  if ( !isset($record['idevento']) ) {
    return -2;
  }


  switch ( $record['idevento'] ) {
    case 'A':
      $record['idevento']       = 'REPORT.ACCESO' ;
      $record['idestado']       = ($record['idio'] == 'E' ) ? 'ENTRADA' : 'SALIDA'; 
      break;
    case 'I':
      $record['idevento']        = 'INICIO.VIAJE' ;
      break;
    case 'T':
      $record['idevento']        = 'TERMINO.VIAJE' ;
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

  //~ $fo = fopen("/tmp/registramarca.log","a+");
  //~ $data = "IDACCESO $idacceso\n";
  //~ fputs($fo,$data);
  //~ fclose($fo);

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

  //~ $record_p ['ticket'] = 'NO TICKET DISPONIBLE';
  
    $rutusuario = isset($record['idusuario']) ? trim($record['idusuario']) : NULL ;
    $password   = isset($record['password']) ? trim($record['password']) : NULL ;
    
    $usuario_r  = getRegistroUsuarioByRut($idcliente,$rutusuario);
    $empresa_r  = getRegistroOrganizacion($usuario_r['idorganizacion']);
    
    if ( $password != NULL ) {
      $password_r           = getRegistroUsuarioByPassword($idcliente,$usuario_r['idusuario']);
      if ( $password_r['apassword'] !== $password ) {
        $record_p ['ticket']  = "Contraseña incorrecta ($idcliente,$rutusuario) " ;
        return 5;
      }
    }
    
    global $tzoffset_g;
    
    if ( $tzoffset_g == NULL ) {
      $tzoffset_g = -4;  
    }
    
    $record_p ['ticket']     = '---------- CONTROL ASISTENCIA ----------|' ;    
    $record_p ['ticket']    .= '*** TRABAJADOR ***|' ;    
    $record_p ['ticket']    .= $usuario_r['nombres'].' '.$usuario_r['apellidos'].'|' ;    
    $record_p ['ticket']    .= 'RUT: '.$usuario_r['rut'].'|' ;    
    $record_p ['ticket']    .= strftime('%d-%m-%Y %H:%M:%S',strtotime($record['fechahora'])+$tzoffset_g*3600).'|' ;    
    $record_p ['ticket']    .= '*** EMPLEADOR ***|' ;    
    $record_p ['ticket']    .= $empresa_r['razonsocial'].'|' ;    
    $record_p ['ticket']    .= 'RUT: '.$empresa_r['rut'].'|' ;    
    $record_p ['ticket']    .= 'DIRECCION: '.$empresa_r['direccion'].'|' ;    
    $record_p ['ticket']    .= '*** DATOS ADICIONALES ***|' ;    
    $record_p ['ticket']    .= 'TRANSACCION: '.$record['idtransaccion'].'|';
    $record_p ['ticket']    .= 'EVENTO: '.$record['idtipoevento'].'|';
    $record_p ['ticket']    .= 'MODULO: '.$record['idmodulo'].'|';
    $record_p ['ticket']    .= 'POSICION: ('.$record['latitud'].','.$record['longitud'].')';    
    
    //~ $rutempresa = isset($record['rutempresa']) ? trim($record['rutempresa']) : NULL ;
    //~ $email      = isset($record['email']) ? trim($record['email']) : NULL ;
    //~ $password   = isset($record['password']) ? trim($record['password']) : NULL ;

    //~ $idmodulo   = isset($record['idacceso']) ? trim($record['idacceso']) : NULL ;
    //~ $idmovil    = isset($record['imei']) ? trim($record['imei']) : NULL ;


    //~ if ( $idmodulo == NULL ) {
      //~ $response = "KO.IDMODULO";   
      //~ $description = 'Modulo No Valido' ;
    //~ } else if ( $idmovil == NULL ) {
      //~ $response = "KO.IDMOVIL";   
      //~ $description = 'IMEI No Valido' ;
    //~ } else if ( validaRut($rutusuario) == NULL ) {
      //~ $response = "KO.RUTUSUARIO";      
      //~ $description = 'Rut Usuario no valido' ;
    //~ } else if ( validaRut($rutempresa) == NULL ) {
      //~ $response = "KO.RUTEMPRESA";      
      //~ $description = 'Rut Empresa no valido' ;
    //~ } else if ( validaMail($email) == false ) {
      //~ $response = "KO.EMAIL";      
      //~ $description = 'Correo Electrónico incorrecto' ;
    //~ } else if ( ($organizacion_r = getRegistroOrganizacion($rutempresa)) == NULL ) {
      //~ $response = "KO.EMPNOTFOUND";      
      //~ $description = 'Empresa no encontrada' ;
    //~ } else if ( ($usuario_r = getRegistroUsuarioByRut($organizacion_r['idcliente'],$rutusuario)) == NULL ) {
      //~ $response = "KO.USRNOTFOUND";      
      //~ $description = 'Usuario no encontrado' ;
    //~ } else if ( !($usuario_r['email'] == $email || $usuario_r['email2'] == $email) ) {
      //~ $response = "KO.EMAILNOREGISTRADO";      
      //~ $description = 'Email No Registrado' ;      
    //~ } else if ( ($password_r = getRegistroUsuarioByPassword($organizacion_r['idcliente'],$usuario_r['idusuario'])) == NULL ) {
      //~ $response = "KO.PWDNOTFOUND";      
      //~ $description = 'Usuario o contraseña no registrada' ;
    //~ } else if ( $password_r['apassword'] !== $password ) {
      //~ $response    = "KO.PWDWRONG";
      //~ $description = 'Contraseña Incorrecta' ;
    //~ } else {
      //~ $response = "OK";  
      //~ $description  = 'Usuario Registrado|';
      //~ $description .= 'Nombre: '.$usuario_r['apellidos'].','.$usuario_r['nombres'].'|';
      //~ $description .= '================== EMPRESA ==================|';
      //~ $description .= 'Rut Empresa: '.$organizacion_r['rut']."|";
      //~ $description .= 'Razon Social: '.$organizacion_r['razonsocial']."|";
      //~ $description .= 'Nombre Fantasia: '.$organizacion_r['nombrefantasia']."|";
      //~ $description .= 'Dirección: '.$organizacion_r['direccion']."|";
      //~ $description .= 'Telefono: '.$organizacion_r['telefono']."|";
      //~ $description .= 'Email Empresa: '.$organizacion_r['email'];

    //~ }
  


  return $retval;;
}


if ( $wsname == NULL ) {
  ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
  $server = new SoapServer("registraevento.wsdl");
  $server->addFunction("registraEvento");
  $server->handle();
}

?>
