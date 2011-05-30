<?php

  include_once($GLOBALS['I_BASE']);

  class Menu extends Base {
  
    private $arrayPairs;
  
    public function __construct() {
      parent::__construct();
      $this->arrayPairs = array();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct
    
    public function AddItem($name, $link) {
      $this->arrayPairs[$name] = $link;
    } // AddItem
    
    public function Size() {
      return(array_size($this->arrayPairs));
    } // Size
    
    public function PrintItem($index) {
      $link = $this->arrayPairs[$index];
      $site_location = $GLOBALS['REF_SITE'] . "?location=";
      $location = str_replace($site_location, "", $link);
      if (trim($location)) {
        if ($GLOBALS['Globals']->CanAccess($location)) {
          print "<a href=\"$link\">$index</a>";
        }
      }
    } // PrintItem
    
    public function PrintMenu() {
      // print "<ul>";
      while (list($key, $val) = each($this->arrayPairs)) {
        $this->PrintItem($key);
      } // while
      // print "</ul>";
    }
  } // Menu
?>
