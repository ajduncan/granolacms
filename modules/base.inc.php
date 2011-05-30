<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  class BaseModules {
    
    public $module_description;
    public $module_name;
    public $module_path;
    public $module_location;
    public $module_linked_from;
    public $active;
    
    public function __construct() {
      $this->module_name        = "This is the default module name.  You should update this message.";
      $this->module_description = "This is the default description for your module.  You should update this message.";
      $this->module_path        = "";
      $this->module_location    = "";
      $this->module_linked_from = "";
      $this->active             = 0;
    } // construct
    
    public function __destruct() {
    } // destruct
    
    public function SetActive($val) {
      if ($val == 0 || $val == 1) {
        $this->active = $val;
      }
    }
    
    public function PrintHeaderSection() {
      
    }
    
    public function PrintBodySection() {
      
    }
    
    public function PrintFooterSection() {
      
    }
    
    public function PrintModuleDescription() {
      print $this->module_description;
    }
    
    public function PrintModuleName() {
      print $this->module_name;
    }

  } // BaseModules

?>
