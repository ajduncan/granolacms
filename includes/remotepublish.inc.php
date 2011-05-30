<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
     
  class RemotePublish extends Base {
    public $G;
    public $gid;
    public $Group;
    private $connection_id;
    private $host;
    private $port;
    private $user;
    private $pass;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
      $this->GetGroupInformation();
      $this->connection_id = -1;
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function Init($host, $port, $user, $pass) {
      $this->host = $host;
      $this->port = $port;
      $this->user = $user;
      $this->pass = $pass;      
    }

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function Connect() {
      $this->connection_id = ssh2_connect($this->host, $this->port);
      $auth_methods = ssh2_auth_none($this->connection_id, 'andy');
      if (in_array('password', $auth_methods)) {
        return(1);
      } else {
        return(0);
      }
    }
    
    public function Login() {
      ssh2_auth_password($this->connection_id, $this->user, $this->pass);
    }
    
    public function Close() {
      // ???
    }

    public function Send($arraySourceDest) {
      print_r($arraySourceDest);
      if ($this->connection_id != FALSE) {
        if (is_array($arraySourceDest)) {
          while(list($key,$val) = each($arraySourceDest)) {
            ssh2_scp_send($this->connection_id, $key, $val, 0644);
          }
        }
      }
    }
    
  } // RemotePublish

?>
