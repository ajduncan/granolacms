<?php
  // Location:  Controls user access to documents.
  
  include_once("./includes/base.inc.php");
  
  class Location extends Base {
    
    public $G;
    
    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
    } // construct
  
    public function __destruct() {
      parent::__destruct();
    } // destruct
  
    public function LoadLocation() {
      $location = $_GET['location'];
      
      if (trim($location)) {
        if ($this->G->IsValidLocation($location)) {
          if ($this->G->CanAccess($location)) {
            include_once($GLOBALS[$location]);
          } else {
            $this->PrintError("Sorry, you do not have permission to access the $location location.");
          }
        } else {
          $this->PrintError("Sorry, $location is not a valid location.");
        }
      }
      
    } // LoadLocation
  
  } // Base
?>
