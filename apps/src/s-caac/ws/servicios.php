<?php
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Software       : APPS                                                    //
//   Layer          : SAVTEC                                                  //
//   Package        : WS                                                      //
//   Component      : Principal                                               //
//                                                                            //
//   File           : servicios.php                                           //
//   Author         : RSO ( Rafael Sotomayor BrulÃ©)                          //
//   EMail          : rsotomayor@savtec.cl                                    //
//   Type           : PHP Code                                                //
//   Usage          :                                                         //
//   Purpose        :                                                         //
//                                                                            //
//                                                                            //
//   who when        what                                                     //
//   ~~~ ~~~~~~~~ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
//   RSO             Creation                                                 //
//                                                                            //
////////////////////////////////////////////////////////////////////////////////

class Servicios {
  var $link_a ;
  var $id_a ;
  var $idusuario_a ;

  function __construct($id_p=NULL) {
    global $idcliente_g ;
    $this->id_a = $id_p ;

    $this->tposicion_a      = $idcliente_g.'_db.stk_registroeventos';
    $this->tregistros_a     = $idcliente_g.'_db.sac_registros';
    $this->tusuarios_a      = $idcliente_g.'_db.sac_usuarios';
    $this->tusuarioacceso_a = $idcliente_g.'_db.sac_usuarioacceso';
    $this->taccesos_a       = $idcliente_g.'_db.sac_accesos';
  }
    
  function __destruct() {

  } 

  function setLink($link_p) {
    $this->link_a = $link_p;  
  } 
  
  function help($record_p) {
    header( 'Location: https://docs.google.com/document/d/1SVORqbUFslPPsFBvlox62-rj2rLr7N0dczJ-ZyxgNT4/pub' ) ;
  }

  function testServer($record_p) {
    require_once ("registraevento.php");

    $fp = fopen('/tmp/ma.log', 'a');
    fwrite($fp, print_r($record_p, TRUE));
    fclose($fp);
    
    
    $idmodulo = isset($record_p['idmodulo']) ? $record_p['idmodulo'] : NULL;
    $ts = isset($record_p['ts']) ? $record_p['ts'] : NULL;
    
    $record = $record_p;
    if ( $ts != NULL ) {
      $record['fechahora'] = strftime('%Y-%m-%d %H:%M:%S',$ts);
    }
    
    $dummy = getRegistroByModulo($idmodulo);
    if ( $dummy == NULL ) {
      $description = "TEST SERVER MODULO ERROR"; 
      $response = "KO.IDMODULO";
    } else {
      
      $idcliente = $dummy['idcliente'];

      if ( $idcliente == NULL ) {
        $description = "TEST SERVER MODULO NULL ERROR"; 
        $response = "KO.IDMODULO.NULL";
      } else {
        $record['tablename'] = $idcliente.'_db.stk_registroeventos';
        $record['tablename_estadomodulo'] = $idcliente.'_db.stk_estadomodulo';

        $retcode = registraAcceso($record) ;

        if ( !( $retcode == 0 || $retcode == 1 ) ) {
          $description = "TEST SERVER [".$idcliente."] ERROR"; 
          $response = "KO";
        } else {
          $description = "TEST SERVER [".$idcliente."] OK"; 
          $response = "OK";      
        }
      }
      
    }

    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>'; 
    return $xml;
  }

  function uploadFile($record_p) {


    $idmodulo    = isset($record_p['idmodulo']) ? $record_p['idmodulo'] : NULL ;


    $uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/upload_svt/' ;

    $mensaje = 'FALLA PROCESA' ;

    if ( isset($_FILES['sendfile']) ) {
	    if (is_uploaded_file($_FILES['sendfile']['tmp_name'])) {
		    $tmp_name  = $_FILES['sendfile']['tmp_name'] ;
		    $name      = $idmodulo.'_'.$_FILES['sendfile']['name'] ;
		    //~ echo 'Archivo subido OK '.$tmp_name.'<br />' ;
        $response     = 'OK' ;
		    move_uploaded_file($tmp_name, "$uploads_dir/$name");
        if ( $response == 'OK' ) {
          $description = 'PROCESA OK' ;
        } else {
          $description = $mensaje ;
        } 


	    } else {
		    //~ echo "Archivo no subido '". $_FILES['sendfile']['tmp_name'] . "'<br />";
        $response     = 'KO' ;
        $description  = 'UPLOAD.ERROR' ;
	    }
    } else {
      $response     = 'KO' ;
      $description  = 'UPLOAD.NODATA' ;
    }

    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;

  }


  function uploadDataBase($record_p) {


    $idmodulo    = isset($record_p['idmodulo']) ? $record_p['idmodulo'] : NULL ;
    $uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/upload_solem/' ;

    $mensaje = 'FALLA PROCESA' ;

    if ( isset($_FILES['sendfile']) ) {
	    if (is_uploaded_file($_FILES['sendfile']['tmp_name'])) {
        $strFecha  = strftime('%Y%m%d%H%M%S',time());
		    $tmp_name  = $_FILES['sendfile']['tmp_name'] ;
		    $name      = $strFecha.'_'.$idmodulo.'_'.$_FILES['sendfile']['name'] ;
		    //~ echo 'Archivo subido OK '.$tmp_name.'<br />' ;
        $response     = 'OK' ;
		    move_uploaded_file($tmp_name, "$uploads_dir/$name");
        if ( $response == 'OK' ) {
          $description = 'PROCESA OK' ;
        } else {
          $description = $mensaje ;
        } 
	    } else {
		    //~ echo "Archivo no subido '". $_FILES['sendfile']['tmp_name'] . "'<br />";
        $response     = 'KO' ;
        $description  = 'UPLOAD.ERROR' ;
	    }
    } else {
      $response     = 'KO' ;
      $description  = 'UPLOAD.NODATA' ;
    }

    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;

  }

 function uploadLogFile($record_p) {
    $description = NULL ;
    $response = 'KO.UPLOADLOGFILE' ;

    $idmodulo    = isset($record_p['idmodulo']) ? $record_p['idmodulo'] : NULL ;
    $md5sum      = isset($record_p['md5sum']) ? $record_p['md5sum'] : NULL ;
    $tipoarchivo = isset($record_p['tipoarchivo']) ? $record_p['tipoarchivo'] : NULL ;


    $uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/upload_log/' ;

    $mensaje = 'FALLA PROCESA' ;

    if ( isset($_FILES['sendfile']) ) {
	    if (is_uploaded_file($_FILES['sendfile']['tmp_name'])) {
		    $tmp_name  = $_FILES['sendfile']['tmp_name'] ;
        $md5file   = md5_file($tmp_name);
        if ( $md5file == $md5sum || $tipoarchivo == 'part' ) {
  		    $name         = $idmodulo.'_'.$_FILES['sendfile']['name'] ;
          $response     = 'OK' ;
  		    move_uploaded_file($tmp_name, "$uploads_dir/$name");
          if ( $response == 'OK' ) {
            $description = 'PROCESA OK' ;
          } else {
            $description = $mensaje ;
          } 

        } else {
          $response = "KO.MD5SUMERROR";
        }
	    } else {
		    //~ echo "Archivo no subido '". $_FILES['sendfile']['tmp_name'] . "'<br />";
        $response     = 'KO' ;
        $description  = 'UPLOAD.ERROR' ;
	    }
    } else {
      $response     = 'KO' ;
      $description  = 'UPLOAD.NODATA' ;
    }

    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;

  }

  function actualizaEstadoDescarga($record_p) {
    $response = 'OK' ;
    $description = NULL ;
    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;  
  }


  function requestEmailPassword($record_p) {
    require_once ("registraevento.php");


    $fp = fopen('/tmp/matest.log', 'a');
    fwrite($fp,"BEGIN ==> requestEmailPassword\n");    
    fwrite($fp, print_r($record_p, TRUE));
    fwrite($fp,"END ==> requestEmailPassword\n");    
    fclose($fp);   

    $rutusuario = isset($record_p['rutusuario']) ? trim($record_p['rutusuario']) : NULL ;
    $rutempresa = isset($record_p['rutempresa']) ? trim($record_p['rutempresa']) : NULL ;
    $email      = isset($record_p['email']) ? trim($record_p['email']) : NULL ;
    $password   = isset($record_p['password']) ? trim($record_p['password']) : NULL ;

    $idmodulo   = isset($record_p['idmodulo']) ? trim($record_p['idmodulo']) : NULL ;
    $idmovil    = isset($record_p['imei']) ? trim($record_p['imei']) : NULL ;


    $rutusuario = str_replace('.','',$rutusuario);
    $rutusuario = str_replace('-','',$rutusuario);
    
    $rutempresa = str_replace('.','',$rutempresa);
    $rutempresa = str_replace('-','',$rutempresa);
    

    $rutusuario  = strtoupper($rutusuario);
    $rutempresa  = strtoupper($rutempresa);
    
    

    if ( $idmodulo == NULL ) {
      $response = "KO.IDMODULO";   
      $description = 'Modulo No Valido' ;
    } else if ( $idmovil == NULL ) {
      $response = "KO.IDMOVIL";   
      $description = 'IMEI No Valido' ;
    } else if ( validaRut($rutusuario) == NULL ) {
      $response = "KO.RUTUSUARIO";      
      $description = 'Rut Usuario no valido' ;
    } else if ( validaRut($rutempresa) == NULL ) {
      $response = "KO.RUTEMPRESA";      
      $description = 'Rut Empresa no valido' ;
    } else if ( validaMail($email) == false ) {
      $response = "KO.EMAIL";      
      $description = 'Correo Electrónico incorrecto' ;
    } else if ( ($organizacion_r = getRegistroOrganizacion($rutempresa)) == NULL ) {
      $response = "KO.EMPNOTFOUND";      
      $description = 'Empresa no encontrada' ;
    } else if ( ($usuario_r = getRegistroUsuarioByRut($organizacion_r['idcliente'],$rutusuario)) == NULL ) {
      $response = "KO.USRNOTFOUND";      
      $description = 'Usuario no encontrado' ;
    } else if ( $usuario_r['idorganizacion'] != $organizacion_r['rut'] ) {
      $response = "KO.USRNOTEMPRESA";      
      $description = 'Usuario no encontrado en empresa' ;
    } else if ( !($usuario_r['email'] == $email || $usuario_r['email2'] == $email) ) {
      $response = "KO.EMAILNOREGISTRADO";      
      $description = 'Email No Registrado' ;      
    } else {
      $response = "KO.CAMBIAPASSWORD";  
      $description  = "Favor revise su correo ".$usuario_r['email'].'|';
      $description .= 'y proceda a cambiar su contrasena';
      
      $nombre       = $usuario_r['apellidos'].','.$usuario_r['nombres'];
      $email        = $usuario_r['email'];
      $idcliente    = $organizacion_r['idcliente'];

      $usuarios_r[] = array(
                      "idusuario" => $usuario_r['rut'] ,
                      "to"        => "to" ,
                      "name"      => $nombre,
                      "email"     => $email
                         );

      $idusuario = strtolower($usuario_r['idusuario']);

      $token    = sha1('cambiapasswordmiasistencia'.$idusuario.'savtec');

      $mensaje  = "Estimado Usuario<br />";
      $mensaje .= "Favor dirigase a http://$idcliente.miasistencia.cl/apps/index.php?mod=s-caac&class=pwd&token=$token&id=$idusuario";
      $mensaje .= "<br /><br /><br />";
      $mensaje .= "Atentos saludos<br />";
      
                               
      $subject  = "[MIASISTENCIA] Solicitud de cambio de Contrasena";
      $mailBody = $mensaje;

      enviaMail($usuarios_r,$subject,$mailBody);
  
      
    }



    //~ $description = 'En desarrollo ('.strftime('%Y-%m-%d %H:%M:%S',time()).')' ;
    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;  


  }


  function registraUsuario($record_p) {
    require_once ("registraevento.php");


    $fp = fopen('/tmp/matest.log', 'a');
    fwrite($fp,"BEGIN ==> registrando Usuario\n");    
    fwrite($fp, print_r($record_p, TRUE));
    fwrite($fp,"END ==> registrando Usuario\n");    
    fclose($fp);   

    $rutusuario = isset($record_p['rutusuario']) ? trim($record_p['rutusuario']) : NULL ;
    $rutempresa = isset($record_p['rutempresa']) ? trim($record_p['rutempresa']) : NULL ;
    $nombres    = isset($record_p['nombres']) ? trim($record_p['nombres']) : NULL ;
    $apellidos  = isset($record_p['apellidos']) ? trim($record_p['apellidos']) : NULL ;

    $email      = isset($record_p['email']) ? trim($record_p['email']) : NULL ;
    $password   = isset($record_p['password']) ? trim($record_p['password']) : NULL ;

    $idmodulo   = isset($record_p['idmodulo']) ? trim($record_p['idmodulo']) : NULL ;
    $idmovil    = isset($record_p['imei']) ? trim($record_p['imei']) : NULL ;



    $rutusuario = str_replace('.','',$rutusuario);
    $rutusuario = str_replace('-','',$rutusuario);
    
    $rutempresa = str_replace('.','',$rutempresa);
    $rutempresa = str_replace('-','',$rutempresa);
    

    $rutusuario = str_replace(' ','',$rutusuario);
    $rutusuario = str_replace(' ','',$rutusuario);
    
    $rutempresa = str_replace(' ','',$rutempresa);
    $rutempresa = str_replace(' ','',$rutempresa);


    $rutusuario  = strtoupper($rutusuario);
    $rutempresa  = strtoupper($rutempresa);
    

    
    $passwordrut = "sha1:".sha1($rutusuario);
    
    $ultimoregistro_r = getUltimoRegistroByModulo($idmodulo);
    
    $tsUltimoRegistro = strtotime($ultimoregistro_r['fechahora']);
    $TMAX_REGISTRO    = 0 ;
    
    $organizacion_r = getRegistroOrganizacion($rutempresa);
    $usuario_r      = getRegistroUsuarioByRut($organizacion_r['idcliente'],$rutusuario);    
    
    $password_r      = getRegistroUsuarioByPassword($organizacion_r['idcliente'],$usuario_r['idusuario']) ;
    $passwordusuario = $password_r['apassword'];

    
    if ( strlen($rutusuario) < 6 ) {
      $response = "KO.RUTUSUARIO";      
    } else if ( strlen($rutempresa) < 6 ) {
      $response = "KO.RUTEMPRESA";      
    } else if ( validaMail($email) == false ) {
      $response = "KO.EMAIL";      
      $description = 'Correo Electrónico incorrecto' ;
    } else {
      $response     = "OK";  

      $description  = 'Usuario Registrado|';

      if ( $usuario_r != NULL ) {
        $description            .= 'Nombre: '.$usuario_r['apellidos'].','.$usuario_r['nombres'].'|';
        $description            .= 'Rut Empresa Usuario: '.$usuario_r['idorganizacion'].'|';
      } else {
        $usuario_r['apellidos']      = $apellidos ;
        $usuario_r['nombres']        = $nombres ;
        $usuario_r['email']          = $email ;        
        $usuario_r['idorganizacion'] = $rutempresa ;
        $usuario_r['rut']            = $rutusuario ;
                
        $description            .= 'Nombre: '.$usuario_r['apellidos'].','.$usuario_r['nombres'].'|';
        $description            .= 'Rut Empresa Usuario: '.$usuario_r['idorganizacion'].'|';        


        $description .= 'RUT: '.$rutusuario.'|';
      }


      if ( $organizacion_r != NULL ) {
        $description .= '================== EMPRESA ==================|';
        $description .= 'Rut Empresa: '.$organizacion_r['rut']."|";
        $description .= 'Razon Social: '.$organizacion_r['razonsocial']."|";
        $description .= 'Nombre Fantasia: '.$organizacion_r['nombrefantasia']."|";
        $description .= 'Dirección: '.$organizacion_r['direccion']."|";
        $description .= 'Telefono: '.$organizacion_r['telefono']."|";
        $description .= 'Email Empresa: '.$organizacion_r['email'];
      } else {
        $organizacion_r['rut']       = $rutempresa ;
        $organizacion_r['idcliente'] = 'ma' ;       
        $description .= '================== EMPRESA ==================|';
        $description .= 'Rut Empresa: '.$rutempresa."|";
      }


      if ( $usuario_r != NULL && $organizacion_r != NULL ) {
        $myparam['idmodulo']         = $idmodulo ;
        $myparam['idcliente']        = $organizacion_r['idcliente'] ;
        $myparam['fechahora']        = strftime('%Y-%m-%d %H:%M:%S',time());
        $myparam['rutusuario']       = $usuario_r['rut'];
        $myparam['rutorganizacion']  = $organizacion_r['rut'];
        $myparam['email']            = $usuario_r['email'] ;

      
        if ( actualizaModulo($myparam) != 0 ) {
          $response     = "KO.ACTMODULO";
          $description  = $myparam['msg']; 
        }
        
        
      }
      
    }

/*
    if ( $idmodulo == NULL ) {
      $response = "KO.IDMODULO";   
      $description = 'Modulo No Valido' ;
    } else if ( $idmovil == NULL ) {
      $response = "KO.IDMOVIL";   
      $description = 'IMEI No Valido' ;
    } else if ( validaRut($rutusuario) == NULL ) {
      $response = "KO.RUTUSUARIO";      
      $description = 'Rut Usuario no valido' ;
    } else if ( validaRut($rutempresa) == NULL ) {
      $response = "KO.RUTEMPRESA";      
      $description = 'Rut Empresa no valido' ;
    } else if ( validaMail($email) == false ) {
      $response = "KO.EMAIL";      
      $description = 'Correo Electrónico incorrecto' ;
    } else if ( $organizacion_r == NULL ) {
      $response = "KO.EMPNOTFOUND";      
      $description = 'Empresa no encontrada' ;
    } else if ( $usuario_r == NULL ) {
      $response = "KO.USRNOTFOUND ";      
      $description = 'Usuario ['.$rutusuario.'] no encontrado en cliente '.$organizacion_r['idcliente'] ;
    } else if ( $usuario_r['idorganizacion'] != $organizacion_r['rut'] ) {
      $response = "KO.USRNOTEMPRESA";      
      $description = 'Usuario ['.$usuario_r['rut'].'] de ['.$usuario_r['idorganizacion'].'] no encontrado en empresa ['.$organizacion_r['rut'].']' ;
    } else if ( !($usuario_r['email'] == $email || $usuario_r['email2'] == $email) ) {
      $response = "KO.EMAILNOREGISTRADO";      
      $description = 'Email No Registrado' ;      
    } else if ( ($password !== $passwordrut) && ($password !== $password_r['apassword']) ) {
      $response    = "KO.PWDWRONG [".$password."] [".$passwordrut."] ";
      $description = 'Contraseña Incorrecta' ;
    } else if ( (time() - $tsUltimoRegistro) < $TMAX_REGISTRO*60)  {
      $deltaT       = (time() - $tsUltimoRegistro)/60;
      $deltaT       = round($deltaT,0);      
      $response     = "KO.USRALRREGISTERED";
      $description  = 'Usuario ya registrado hace menos de '.$TMAX_REGISTRO.' minutos|';
      $description .= "para dispositivo [$idmodulo]|";
      $description .= 'Espere '.($TMAX_REGISTRO-$deltaT).' minutos';
    } else {
      $response = "OK";  
      $description  = 'Usuario Registrado|';
      $description .= 'Nombre: '.$usuario_r['apellidos'].','.$usuario_r['nombres'].'|';
      $description .= 'Rut Empresa Usuario: '.$usuario_r['idorganizacion'].'|';
      $description .= '================== EMPRESA ==================|';
      $description .= 'Rut Empresa: '.$organizacion_r['rut']."|";
      $description .= 'Razon Social: '.$organizacion_r['razonsocial']."|";
      $description .= 'Nombre Fantasia: '.$organizacion_r['nombrefantasia']."|";
      $description .= 'Dirección: '.$organizacion_r['direccion']."|";
      $description .= 'Telefono: '.$organizacion_r['telefono']."|";
      $description .= 'Email Empresa: '.$organizacion_r['email'];
  
      $myparam['idmodulo']         = $idmodulo ;
      $myparam['idcliente']        = $organizacion_r['idcliente'] ;
      $myparam['fechahora']        = strftime('%Y-%m-%d %H:%M:%S',time());
      $myparam['rutusuario']       = $usuario_r['rut'];
      $myparam['rutorganizacion']  = $organizacion_r['rut'];
      $myparam['email']            = $usuario_r['email'] ;
      
      
      if ( actualizaModulo($myparam) != 0 ) {
        $response     = "KO.ACTMODULO";
        $description  = $myparam['msg']; 
      }

    }
*/


    //~ $description = 'En desarrollo ('.strftime('%Y-%m-%d %H:%M:%S',time()).')' ;
    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;  


  }


  function registraMarca($record_p) {
    require_once ("registraevento.php");

    $fp = fopen('/tmp/matest.log', 'a');
    fwrite($fp, print_r($record_p, TRUE));
    fwrite($fp,"registrando Marca\n");    
    fclose($fp);

    $retval = registraEventoMarca($record_p);

    if ( $retval == 0 ) {
      $response = 'OK' ;
    } else {
      switch ( $retval ) {
        case 1:
          $response = 'KO.NULLACCESO';break;
        case 2:
          $response = 'KO.ACCESODENEGADO';break;
        case 5:
          $response = 'KO.PWDINCORRECTA';break;
        default:
          $response = 'KO.REGISTRO'  ;
          break;
      }
    }


    $fechahora = strftime('%Y-%m-%d %H:%M:%S',time());
    if ( $response == 'OK' ) {
      $description = $record_p['ticket'] ;
    } else {
      $description = "Error al registrar asistencia|"; 
      $description = $record_p['ticket']; 
       
    }
    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;  
    
  }


} 



?>
