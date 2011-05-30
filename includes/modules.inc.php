<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  include_once("./modules/base.inc.php");

  class Modules extends BaseModules {
    public $gid;
    public $content_id;
    public $Group;
    private $registered;
   
    public function __construct() {
      parent::__construct();
      if (isset($_GET['id'])) {
        $this->gid = $_GET['id'];
      } else {
        $this->gid = null;
      }

      $this->Group = array();
      $this->registered = array();
    } // construct
    
    public function GlobalMethod($method) {
      while(list($key,$val) = each($GLOBALS['Modules']->registered)) {
        if (method_exists($val, $method)) {
          $val->$method();
        }
      }
      reset($GLOBALS['Modules']->registered);
    }
    
    public function GetModuleNames($depth = 0, $path = MODULES_PATH) {
      $files          = array();
      if ($handle = opendir($path)) {
        while (false !== ($file = readdir($handle))) {
          if (
            $file != "." &&
            $file != ".." &&
            $file != ".svn" &&
            $file != "base.inc.php"
          ) {
            if (!is_dir($path . "/$file")) {
              // Need to filter out non php files.
              // Need to perform some form of security check, but we'll assume the file system is secure and make that a requirement.
              if ($file == "index.php") {
                $files[] = $path . "/$file";
              }
            } else {
              if ($depth < 1) {
                $files = array_merge($files, $this->GetModuleNames($depth+1, $path . "/$file"));
              }
            }
          }
        }
        closedir($handle);
      }
      asort($files);
      return($files);
    } // GetModuleNames
    
    public function LoadModules() {
      $files = $this->GetModuleNames();
      while (list($key,$val) = each ($files)) {
        $this->Register($val);
      }
    }
    
    public function Register($name) {
      if (file_exists($name)) {
        if (!in_array($name, $this->registered)) {
          include_once($name);
          $basename = basename($name);
          // strip to the first . in the name
          $pos = strpos($basename, ".");
          $basename = substr($basename, 0, $pos);
          $basename = strtolower($basename);
          $basename = $GLOBALS['granola_module'];
          $this->registered[$name] = new $basename();
        } else {
          // work on this...
          print "Error, a module has already been registered with the name: $name<br/>";
        }
      }
    } // Register

    public function ModulesByKey($key1) {
      $result = array();
      while(list($key,$val) = each($this->registered)) {
        if ($key == $key1) {
          array_push($result, $this->registered[$key]);
        }
      }
      reset($this->registered);
      return($result);
    }
    
    public function ModulesByLocation($location) {
      $result = array();
      while(list($key,$val) = each($this->registered)) {
        if ($this->registered[$key]->module_location == $location) {
          array_push($result, $this->registered[$key]);
        }
      }
      reset($this->registered);
      return($result);
    }

    public function ModulesByLinkLocation($location) {
      $result = array();
      while(list($key,$val) = each($this->registered)) {
        if ($this->registered[$key]->module_linked_from == $location) {
          array_push($result, $this->registered[$key]);
        }
      }
      reset($this->registered);
      return($result);
    }
    
    public function PrintRegistry() {
      while(list($key,$val) = each($this->registered)) {
?>
<tr>
  <td width="15%" valign="top"><?php $this->registered[$key]->PrintModuleName(); ?></td>
  <td width="75%" valign="top"><?php $this->registered[$key]->PrintModuleDescription(); ?><td>
  <td width="10%" valign="top">
    <?php
        if (!$this->registered[$key]->active) {
    ?>
    <button dojoType="dijit.form.Button" id="ModuleListCMD<?php print $key; ?>" onclick="javascript:location.href='<?php print $GLOBALS['H_MODULELIST_location'] . "&activate=" . base64_encode($val->module_path); ?>'">
      <img src="./images/icons/user/user_add.png" alt="Activate <?php print $val->module_name; ?>">Activate <?php print $val->module_name; ?></img>
    </button>
    <?php
        } else {
    ?>
    <button dojoType="dijit.form.Button" id="ModuleListCMD<?php print $key; ?>" onclick="javascript:location.href='<?php print $GLOBALS['H_MODULELIST_location'] . "&deactivate=" . base64_encode($val->module_path); ?>'">
      <img src="./images/icons/user/user_add.png" alt="Deactivate <?php print $val->module_name; ?>">Deactivate <?php print $val->module_name; ?></img>
    </button>
    <?php
        }
    ?>
  </td>
</tr>
<?php
      }
      reset($this->registered);
    }
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function PrintModuleListing() {
      $action = null;
?>
<div id="ModuleListTabContainer" dojoType="dijit.layout.TabContainer" style="width: 99%; height: 50em;">
  <div dojoType="dijit.layout.ContentPane" title='<img src="./images/icons/user/user_edit.png" alt="Modules" Title="Modules" /> Registered Modules'>
    <form method="post" action="<?php print $action ?>" id="ModuleListForm" dojoType="dijit.form.Form">
      <table style="width: 100%;" border="1">
        <tbody>
          <?php $this->PrintRegistry(); ?>
        </tbody>
      </table>
    </form>
  </div>
</div>
<?php
    } // PrintForm
    
  } // Modules
?>