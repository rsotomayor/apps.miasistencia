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


    $fp = fopen('/tmp/ma.log', 'a');
    fwrite($fp, print_r($record_p, TRUE));
    fclose($fp);


    $response  = "TEST SERVER OK";
    $xml  = '<?xml version="1.0"?>';
    $xml .= '<result>';
    $xml .= '<response>OK</response>';  
    $xml .= '<description>'.$response.'</description>';  
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


} 



?>
