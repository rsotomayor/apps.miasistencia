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

  function registraUsuario($record_p) {
    require_once ("registraevento.php");

    $fp = fopen('/tmp/matest.log', 'a');
    fwrite($fp,"BEGIN ==> registrando Usuario\n");    
    fwrite($fp, print_r($record_p, TRUE));
    fwrite($fp,"END ==> registrando Usuario\n");    
    fclose($fp);

    $response    = 'OK' ;
    $description = 'En desarrollo ('.strftime('%Y-%m-%d %H:%M:%S',time()).')' ;
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
        default:
          $response = 'KO.REGISTRO'  ;
          break;
      }
    }


    
    $description = NULL ;
    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>'.$response.'</response>';  
    $xml .= '<description>'.$description.'</description>';  
    $xml .= '</result>';    
    return $xml;  
    
  }


} 



?>
