<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

/*
	CREATE TABLE IF NOT EXISTS `granola_menubuilder` (
		`id` int(10) unsigned NOT NULL auto_increment,
		`menu_id` int(10) unsigned NOT NULL,
		`menu_name` varchar(50) NOT NULL,
		`parent_id` int(10) unsigned NOT NULL,
		`content_id` int(10) unsigned NOT NULL,
		`ordering` int(3) NOT NULL,
		`openin` int(1) NOT NULL,
		`name` varchar(50) NOT NULL,
		`alias` varchar(50) NOT NULL,
		`external` varchar(255) default NULL,
		PRIMARY KEY  (`id`),
		KEY `menu_id` (`menu_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
*/

  include_once("./modules/base.inc.php");

  class menubuilder extends BaseModules {
    private $gid;
    private $Group;
    
    public function __construct() {
      parent::__construct();
      $this->module_name        = "Menu Builder";
      $this->module_description = "A menu creation module, which allows for group-level menu creation.";
      $this->module_location    = "H_EDITMENUBUILDER";
			$this->module_linked_from = "H_EDITGROUP";
      $this->module_path        = "./modules/menubuilder/menubuilder.inc.php";
			
			// Need to register the H_EDITGROUPCALENDAR
			$GLOBALS['Globals']->AddLocation("H_EDITMENUBUILDER", $this->module_path, array('admin', 'normal'));
		  $GLOBALS['CONST_NO_MENU'][] = "H_EDITMENUBUILDER";
      
      if (trim($_GET['id'])) {
        $this->gid = $_GET['id'];
        // $this->GetGroupInformation();
      }
      
      // Finally, is this module active or not?
      $key    = base64_encode($this->module_path);
      $query  = "select `status` from " . DB_PREFIX . "modules where `key`='$key'";
      $rs     = $GLOBALS['Globals']->DB->Execute($query);
      if ($rs->RecordCount() == 1) {
        $record = $rs->FetchRow();
        if ($record['status'] == 1) {
          $this->active = 1;
        } else {
          $this->active = 0;
        }
      } else {
        $this->active = 0;
      }

    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct

    public function Install() {
      $query = "
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "menubuilder` (
					`id` int(10) unsigned NOT NULL auto_increment,
					`menu_id` int(10) unsigned NOT NULL,
					`menu_name` varchar(50) NOT NULL,
					`parent_id` int(10) unsigned NOT NULL,
					`content_id` int(10) unsigned NOT NULL,
					`ordering` int(3) NOT NULL,
					`openin` int(1) NOT NULL,
					`name` varchar(50) NOT NULL,
					`alias` varchar(50) NOT NULL,
					`external` varchar(255) default NULL,
					PRIMARY KEY  (`id`),
					KEY `menu_id` (`menu_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

			";
      print "Installing DB Table: " . $query . "<br/>";
      $GLOBALS['Globals']->DB->Query($query);
    }
    
    public function GetMenu($id) {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $GLOBALS['Globals']->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function HandleFormSubmission() {
    } // HandleFormSubmission
    
    public function PrintHeaderSection() {
    }
    public function PrintBodySection() {
?>
<body>
<?php
			if (trim($_GET['location'])) {
				if ($_GET['location'] == "H_EDITMENUBUILDER") {
					$aModule = $GLOBALS['Modules']->ModulesByLocation("H_EDITMENUBUILDER");
					// Get the first registered module that matches our 'config' name;
					while(list($key,$val) = each($aModule)) {
						if ($val->module_name == "Menu Builder") {
							$val->PrintForm();
						}
					}
				}
			}
    }
    
    public function PrintFooterSection() {
?>
</body>
<?php
    }

    public function PrintForm() {
?>
<table>
  <tbody>
    <tr><td>Editing Menu: <?php print $this->record['menu_name']; ?></td></tr>
  </tbody>
</table>
<?php
    } // PrintForm 
  } // Calendar
?>