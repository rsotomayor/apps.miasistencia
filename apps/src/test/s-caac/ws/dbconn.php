<?php
include("adodb/adodb-exceptions.inc.php"); 
include("adodb/adodb.inc.php"); 

class DBConn {

  var $link_a;
  var $dbdriver_a  ;
  var $dbserver_a  ;
  var $dbselect_a  ;
  var $dbuser_a    ;
  var $dbpwd_a     ;


  function __construct($dbconfig_p) {

    $this->dbdriver_a  = $dbconfig_p["driver"]  ;
    $this->dbserver_a  = $dbconfig_p["host"]  ;
    $this->dbselect_a  = $dbconfig_p["name"]  ;         
    $this->dbuser_a    = $dbconfig_p["user"]    ;
    $this->dbpwd_a     = $dbconfig_p["password"]     ;

  }

  function __destruct() {
    // mysql_query($this->link_a->_connectionID);
  }

  function connect() {
    $retcode = 0 ;
    $this->link_a = NULL ;
    $db = ADONewConnection($this->dbdriver_a);
    try { 
//         $db->debug = true; 
      $db->NConnect($this->dbserver_a, $this->dbuser_a, $this->dbpwd_a, $this->dbselect_a);
      $this->link_a = $db ;
      if ( $this->dbdriver_a == "mysql" ) {
        $sqlString = "SET NAMES 'utf8'" ;
        $this->link_a->Execute($sqlString);
      } 
    } catch (exception $e) { 
      $retcode = -1 ;
    }

    return $retcode ;
  }

  function getConnection() {
    return $this->link_a;
  
  }
  
  function startTrans($link_p) {
    global $dbConfig_g ;
    if ( $dbConfig_g["driver"] == "mysql" ) {
      $sqlString = "SET AUTOCOMMIT=0; " ;
       $link_p->Execute($sqlString);
    } 
  }

  function completeTrans($link_p) {
    global $dbConfig_g ;    
    if ( $dbConfig_g["driver"] == "mysql" ) {
      $sqlString = "COMMIT; " ;
      $link_p->Execute($sqlString);
    } 
  }
 
  


} 


?>
