<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  class EditRemoteDetails extends Base {
    public $G;
    public $gid;
    public $Group;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      // $this->GetGroupInformation();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation
    
    public function HandleFormSubmission() {
      $useremote = $_POST['useremote'];
      
      $gid        = $this->gid;
      
      $username   = $_POST['username_0'];
      $password   = $_POST['password_0'];
      $hostname   = $_POST['host_0'];
      $directory  = $_POST['directory_0'];
      
      $query = "delete from {$GLOBALS['DB_PREFIX']}group_publishing where gid=$this->gid";
      $this->G->DB->Query($query);
      
      $query = "insert into {$GLOBALS['DB_PREFIX']}group_publishing (username, password, hostname, directory, gid) values ('$username', '$password', '$hostname', '$directory', '$gid')";
      $this->G->DB->Query($query);
  
    } // HandleFormSubmission
    
    public function PrintTab($name, $record) {
      if ($record == null) {
        $username   = "username_0";
        $password   = "password_0";
        $host       = "host_0";
        $directory  = "directory_0";
      } else {
        $username   = "username_" . $record['id'];
        $password   = "password_" . $record['id'];
        $host       = "host_" . $record['id'];
        $directory  = "directory_" . $record['id'];
        
        $_POST[$username]  = $record['username'];
        $_POST[$password]  = $record['password'];
        $_POST[$host]      = $record['hostname'];
        $_POST[$directory] = $record['directory'];
        
      }
?>
  <table>
    <tbody>
      <tr>
        <td>Remote Username</td>
        <td>
          <input
            dojoType="dijit.form.TextBox"
            type="text"
            name="<?php print $username; ?>"
            class="input"
            value="<?php print $this->G->GetPostValue($username); ?>"
          />
        </td>
      </tr><tr>
        <td>Remote Password:</td>
        <td>
          <input
            dojoType="dijit.form.TextBox"
            type="password"
            name="<?php print $password; ?>"
            class="input"
            value="<?php print $this->G->GetPostValue($password); ?>"
          />
        </td>
      </tr><tr>
        <td>Hostname:</td>
        <td>
          <input
            dojoType="dijit.form.TextBox"
            type="text"
            name="<?php print $host; ?>"
            class="input"
            value="<?php print $this->G->GetPostValue($host); ?>"
          />
        </td>
      </tr><tr>
        <td>Remote Directory:</td>
        <td>
          <input
            dojoType="dijit.form.TextBox"
            type="text"
            name="<?php print $directory; ?>"
            class="input"
            value="<?php print $this->G->GetPostValue($directory); ?>"
          />
        </td>
      </tr><tr>
        <td colspan="2">
          <button
            dojoType="dijit.form.Button"
            type="submit"
            name="Test"
            value="submit"
          >Test Connection</button>
          <button
            dojoType="dijit.form.Button"
            type="submit"
            name="Publish"
            value="submit"
          >Publish</button>
          <button
            dojoType="dijit.form.Button"
            type="submit"
            name="submit"
            value="submit"
          >Save Settings &raquo;</button>
          
        </td>
      </tr>
    </tbody>
  </table>
<?php
    } // PrintTab
    
    // Abstract this into PublishSCP, etc.
    
    public function Publish() {
      $path         = CONTENT_PATH . $this->GetGroupLocation($this->gid) . "/";
      $query = "select * from {$GLOBALS['DB_PREFIX']}group_publishing where gid=$this->gid";
      $rs = $this->G->DB->Query($query);
      if ($rs->RecordCount() <= 0) {
        // Nothing to publish.
      } else {
        include_once($GLOBALS['I_REMOTEPUBLISH']);
        $rp = new RemotePublish();
        // for each;
        while (!$rs->EOF) {
          $record = $rs->FetchRow();
          
          $a_hostname = split(':', $record['hostname']);
          $host = $a_hostname[0];
          $port = $a_hostname[1];
          
          if (!trim($port)) {
            // $port = 22;
            $port = 420;
          }
          
          $rp->Init($host, $port, $record['username'], $record['password']);
          if ($rp->Connect()) {
            $rp->Login();
            // Get directory content listing in the form of:
            // array('/full/path/to/file', '/full/path/to/remote/file');
            
            $arrayListing = $this->BuildRemoteDirectoryList($record['directory']);
  
            foreach ($arrayListing as $file) {
              if (!is_dir($path . "/" . $file)) {
                // does this file exist in content?
                $name = split("\.", $file);
                $content_id = $this->GetContentIDFromFile($name[0]);
                $type       = $this->GetContentTypeFromID($content_id);
                if ($content_id != -1) {
                  // if document type, assemble the header, content, footer: upload that stream.
                  if (
                        ($type == 2) ||
                        ($type == 4)
                  ) {
                    $rp->Send(array($path . $file => $record['directory'] . $file));
                  }
                  
                  // for now, upload 1:
                  if ($type == 1) {
                    // Call Prismatic to construct a temporary file:
                    $content = $this->GetContent($content_id);
                    $name    = $content['name'];
                    $site_location = $this->GetSiteLocation($this->gid);
                    if (trim($site_location)) {
                      $link         = WEB_SITE . "?location=" . $site_location . "/" . $name;
                    } else {
                      $link         = WEB_SITE . "?location=" . $name;
                    }

                    // $fs = file_get_contents($link);
                    
                    
                    
                    // Clean the temporary file up (transformations?)
                    
                    // Send it:
                    $rp->Send(array($path . $file => $record['directory'] . $file));
                  }                
                }
              }
            } // foreach
          } else {
            print "Remote host would not connect or authentication method is unsupported.<br/>";
          }    
        }
      }      
    }

    public function GetAlphaListing($directory) {
      $files          = array();
      $directories    = array();
      if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
          if (
                $file != "." &&
                $file != ".." &&
                stristr($file, '.inc.php')
          ) {
            if (is_dir($directory . $file)) {
              $directories[] = $file;
            } else {
              $files[] = $file;
            }
          }
        }
        closedir($handle);
      }
      asort($files);
      asort($directories);
      return(array_merge($files, $directories));
    }
    
    public function BuildRemoteDirectoryList($remote) {
      $path         = CONTENT_PATH . $this->GetGroupLocation($this->gid) . "/";
      $arrayListing = $this->GetAlphaListing($path);
      return($arrayListing);
    }
    
    public function PrintForm() {
?>
<div class="formAnswer">
  <form dojoType="dijit.form.Form" action="<?php print $GLOBALS['H_EDITREMOTEDETAILS_location'] . "&id=" . $this->gid; ?>" method="post">
    <table border="0" width="100%">
      <tr>
        <td colspan="2">
          <input
            dojoType="dijit.form.CheckBox"
            type="checkbox"
            name="useremote"
            class="input"
            value="<?php print $this->G->GetPostValue('username'); ?>"
          />              
          Use Remote Publishing
        </td>
      </tr><tr>
        <td colspan="2">
<?php
      $query = "select * from {$GLOBALS['DB_PREFIX']}group_publishing where gid=$this->gid";
      $rs = $this->G->DB->Query($query);
      if ($rs->RecordCount() <= 0) {
        $this->PrintTab("New Connection", null);
      } else {
        // for each;
        while (!$rs->EOF) {
          $record = $rs->FetchRow();
          $this->PrintTab($record['hostname'], $record);
        }
        // $this->PrintTab("New Connection");
      }
?>
        </td>
      </tr>
    </table>
  </form>
</div>
<?php
    } // PrintForm  
  } // EditRemoteDetails
  
  $EditRemoteDetails = new EditRemoteDetails();
  
  while(list($key,$val) = each($_POST)) {
    // print "$key = $val<br/>";
  }
  
  if (isset($_POST['submit'])) {
    $editRemoteDetails = new EditRemoteDetails();
    $editRemoteDetails->HandleFormSubmission();
    $location = $GLOBALS['H_EDITGROUP_location'] . "&id=" . $_GET['id'];
    header("Location: " . $location);
  }
  
  if (isset($_POST['Publish'])) {
    $editRemoteDetails = new EditRemoteDetails();
    $editRemoteDetails->Publish();
    $location = $GLOBALS['H_EDITGROUP_location'] . "&id=" . $_GET['id'];
    // header("Location: " . $location);   
  }
  
  if (
    !isset($_POST['Publish']) &&
    !isset($_POST['submit'])
  ) {
    $EditRemoteDetails->PrintForm();
  }
  
?>