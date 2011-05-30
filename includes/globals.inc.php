<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  // globals.inc.php, global variables and settings not appropriate for config.inc.php.  
  
  include_once("./adodb/adodb.inc.php");
  include_once("./adodb/adodb-exceptions.inc.php");
  include_once("./includes/acl.inc.php");
    
  // Let's define internal classes:
  define("HTML_HEADER", "./includes/header.inc.php");
  define("HTML_BODY", "./includes/body.inc.php");
  define("HTML_FOOTER", "./includes/footer.inc.php");
  define("HTML_LOGIN", "./includes/login.inc.php");
  
  // The importan JSON Handler class
  define("JSON_HANDLER", "./includes/jsonhandler.inc.php");
  
  class Globals {
    public $DB;
    public $validLocations;
    public $ACL;
    public $Menu;
    
    public function __construct() {
      $this->DB = NewADOConnection('mysql');
      $this->DB->Connect(SQL_HOSTNAME, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE);
      $this->DB->SetFetchMode(ADODB_FETCH_ASSOC);
      $this->validLocations = array();
      $this->ACL = new ACL();
    } // construct
    
    public function __destruct() {
    } // destruct
    
    // Set global variables, add type/safe checking later.
    public function SetGlobal($gVariableName, $gVariableValue) {
      $GLOBALS[$gVariableName] = $gVariableValue;
    }
    
    public function SetSession($sessionName, $sessionValue) {
      $_SESSION[$sessionName] = $sessionValue;
    }
    
    public function GetPostValue($postvalue) {
      if (isset($_POST[$postvalue])) {
        return($_POST[$postvalue]);
      } else {
        return('');
      }
    } // GetPostValue
    
    // group/db access control per files.
    public function IsValidLocation($location) {
      if (in_array($location, $this->validLocations)) {
        return true;
      } else {
        return false;
      }
    } // IsValidLocation
    
    public function CanAccess($location) {
      if (in_array($_SESSION['type'], $GLOBALS['CONST_HTML_ACCESS'][$location])) {
        return (true);
      } else {
        return (false);
      }
    } // CanAccess
    
    public function AddLocation($name, $location, $group) {
      $GLOBALS[$name]                       = $location;
      $GLOBALS['CONST_HTML_ACCESS'][$name]  = $group;
      // Further, check first constant character "H" for this;
      $GLOBALS[$name . "_location"]         = $GLOBALS['REF_SITE'] . "?location=$name";
      array_push($this->validLocations, $name);
    } // AddLocation
    
    public function PrintTextValue($name, $default) {
      if (isset($_POST[$name])) {
        print $_POST[$name];
      } else {
        print $default;
      }
    } // PrintTextValue

    public function PrintDBTextValue($name, $dbvalue, $default) {
      if (isset($_POST[$name])) {
        print $_POST[$name];
      } else {
        if (isset($dbvalue)) {
          print $dbvalue;
        } else {
          print $default;
        }
      }
    } // PrintDBTextValue
    
    public function PrintSelectedValue($name, $value, $default) {
      if (isset($_POST[$name])) {
        if ($value == $_POST[$name]) {
          print "selected";
        }
      } else {
        print $default;
      } // isset $name..
    } // PrintSelectedValue
  
    public function PrintDBSelectedValue($name, $value, $dbvalue, $default) {
      if (isset($_POST[$name])) {
        if ($value == $_POST[$name]) {
          print "selected";
        }
      } else {
        if (isset($dbvalue)) {
          if ($dbvalue == $value) {
            print "selected";
          }
        } else {
          print $default;
        }
      }
    } // PrintDBSelectedValue

    public function PrintCheckedValue($name, $value, $default) {
      if (isset($_POST[$name])) {
        if ($value == $_POST[$name]) {
          print "checked";
        }
      } else {
        print $default;
      } // isset $name..
    } // PrintCheckedValue

    public function PrintDBCheckedValue($name, $value, $dbvalue, $default) {
      if (isset($_POST[$name])) {
        if (is_array($_POST[$name])) {
          if (in_array($value, $_POST[$name]) == $value) {
            print "checked";
          }
        }
      } else {
        if (isset($dbvalue)) {
          if ($dbvalue == $value) {
            print "checked";
          }
        } else {
          print $default;
        }
      }
    } // PrintDBCheckedValue

  }  // Globals

  $Globals = new Globals();
  $GLOBALS['Globals'] = $Globals;
  
  // Files that don't require group permissions to access:
  
  // I - include file.
  // H - HTML file, referenced by name.
  // E - External file, like an include file but not part of this package
  // R - Reference, just a name.
  
  $Globals->AddLocation("I_BASE", "./includes/base.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_ACL", "./includes/acl.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_AUTH", "./includes/auth.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_MENU", "./includes/menu.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_LOCATION", "./includes/location.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_DIALOGS", "./includes/dialogs.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_RS2JSON", "./includes/rs2json.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_REMOTEPUBLISH", "./includes/remotepublish.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("I_MODULES", "./includes/modules.inc.php", array('admin'));  
  $Globals->AddLocation("I_BASEMODULES", MODULES_PATH . "/base.inc.php", array('admin'));
  
  $Globals->AddLocation("E_ADODB", "./adodb/adodb.inc.php", array('admin', 'normal'));
  
  $Globals->AddLocation("R_LOGOUT", "", array('admin', 'normal'));

  $Globals->AddLocation("H_LOGIN", "./includes/login.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_BODY", "./includes/body.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_WELCOME", "./includes/welcome.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_AJAXD", "./includes/ajaxd.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_LAZYTREE", "./includes/lazytree.inc.php", array('admin', 'normal'));
  
  // This starts the section where we care about groups a user belongs to...:
  $Globals->AddLocation("H_USERLIST", "./includes/userlist.inc.php", array('admin'));
  $Globals->AddLocation("H_EDITUSER", "./includes/edituser.inc.php", array('admin'));
  $Globals->AddLocation("H_ADDUSER", "./includes/adduser.inc.php", array('admin'));
  $Globals->AddLocation("H_HELP", "./includes/help.inc.php", array('admin', 'normal'));
  
  $Globals->AddLocation("H_GROUPLIST", "./includes/grouplist.inc.php", array('admin'));
    $Globals->AddLocation("H_JSONGROUPLIST", "./includes/jsongrouplist.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_EDITGROUP", "./includes/editgroup.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_EDITREMOTEDETAILS", "./includes/editremotedetails.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_EDITGROUPDETAILS", "./includes/editgroupdetails.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_EDITGACL", "./includes/editgacl.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_ADDGACL", "./includes/addgacl.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_SEARCHREPLACE", "./includes/searchreplace.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_ADDGROUP", "./includes/addgroup.inc.php", array('admin', 'normal'));

  $Globals->AddLocation("H_MYGROUPS", "./includes/mygroups.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_MYCONTENT", "./includes/mycontent.inc.php", array('admin', 'normal'));

  $Globals->AddLocation("H_CONTENTLIST", "./includes/contentlist.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_JSONCONTENTLIST", "./includes/jsoncontentlist.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_MEDIALIST", "./includes/medialist.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_EDITCONTENT", "./includes/editcontent.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_EDITCONTENTDETAILS", "./includes/editcontentdetails.inc.php", array('admin', 'normal'));
  $Globals->AddLocation("H_ADDCONTENT", "./includes/addcontent.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_UPLOADCONTENT", "./includes/uploadcontent.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_UPLOAD", "./includes/upload.inc.php", array('admin', 'normal'));

    $Globals->AddLocation("H_EDITCACL", "./includes/editcacl.inc.php", array('admin', 'normal'));
    $Globals->AddLocation("H_ADDCACL", "./includes/addcacl.inc.php", array('admin', 'normal'));
  
    $Globals->AddLocation("H_CONTENTPREVIEW", "./includes/contentpreview.inc.php", array('admin', 'normal'));

  $Globals->AddLocation("H_MODULELIST", "./includes/modulelist.inc.php", array('admin'));

  $Globals->AddLocation("H_HELPLIST", "./includes/helplist.inc.php", array('admin'));
  $Globals->AddLocation("H_EDITHELP", "./includes/edithelp.inc.php", array('admin'));
  $Globals->AddLocation("H_ADDHELP", "./includes/addhelp.inc.php", array('admin'));
  
  $Globals->AddLocation("H_JSONHANDLER", "./includes/jsonhandler.inc.php", array('admin', 'normal'));

  $GLOBALS['CONST_NO_MENU'] = array(
    'H_ADDGROUP',
    'H_ADDCONTENT',
    'H_UPLOADCONTENT',
    'H_CONTENTPREVIEW',
    'H_EDITGROUPDETAILS',
    'H_EDITREMOTEDETAILS',
    'H_EDITCONTENTDETAILS',
    'H_CONTENTLIST',
    'H_MEDIALIST',
    'H_SEARCHREPLACE',
    'H_UPLOAD',
    'H_JSONGROUPLIST',
    'H_JSONCONTENTLIST',
    'H_JSONHANDLER',
    'H_AJAXD'
  );

?>
