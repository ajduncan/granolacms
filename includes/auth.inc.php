<?php

  // Authentication module.
  
  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['E_ADODB']);
  
  class Authentication extends Base {
    public $G;
    public $Authenticated;
    
    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->Authenticated = 0;
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct
    
    public function Authenticate($username, $password) {
      $count = $this->G->DB->GetOne("select count(*) from $GLOBALS[DB_PREFIX]users where email='$username' and password='$password'");
      if ($count == 1) {
        $record = $this->G->DB->GetRow("select * from $GLOBALS[DB_PREFIX]users where email='$username' and password='$password'");
        // success.  Set up session variables;
        $_SESSION['authenticated']  = true;
        
        // User table;
        $_SESSION['username']       = $record['email'];
        $_SESSION['password']       = $record['password'];
        $_SESSION['email']          = $record['email'];
        $_SESSION['type']           = $record['type'];
        $_SESSION['parent_id']      = $record['parent_id'];
        $_SESSION['contact_id']     = $record['contact_id'];
        $_SESSION['id']             = $record['id'];
        
        $this->Authenticated = 1;

      } // if ($count == 1) {
    } // Authenticate 
  } // Authentication

?>
