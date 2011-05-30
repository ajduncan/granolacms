<?php  

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
    
  
  // include_once($GLOBALS['I_BASE']);
  
  class AjaxDaemon extends Base {
    public $G;
    public $gid;
    public $Group;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function Publish($cid) {
      $query    = "select * from $GLOBALS[DB_PREFIX]content where id='$cid'";
      $record   = $this->G->DB->GetRow($query);
      $name     = $record['name'];
      $type     = $record['type'];
      
      $query    = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='$cid'";
      $grecord  = $this->G->DB->GetRow($query);
      $this->gid = $grecord['group_id'];
      $this->GetGroupInformation();
      
      if ($type == "1" || $type == "3") {
        $filename = CONTENT_PATH . $this->GetGroupLocation($this->Group['id']) . "/" . $record['name'] . ".inc.php";
      }
      
      if ($type == "4") {
        $filename = CONTENT_PATH . $this->GetGroupLocation($this->Group['id']) . "/" . $record['name'] . ".css";
      }
      
      if ($type != "1" && $type != "3" && $type != "4") {
        print "Unable to publish this file type.<br/>";
        return(-1);
      }

      // Get the 'new revision' contents and plaster it into the file:
      $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$cid' order by revision DESC";
      $record = $this->G->DB->GetRow($query);
      $content = base64_decode($record['text']);

      if (file_put_contents($filename, $content) == false) {
        // print "Unable to publish the file to disk, please verify that $filename is writable.<br/>";
        return(-1);
      } else {
        // print "Content published to the file: $filename<br/>";
        // Save this revision
        $query = "update $GLOBALS[DB_PREFIX]content set published_revision='{$record['id']}' where id='$cid'";
        $this->G->DB->Execute($query);
        return(1);
      }
    } // Publish
    
    public function HandleFormSubmission() {

      if (isset($_POST['cid'])) {
        $this->C_ACCESS = $this->ACL->GetContentACL($_POST['cid'], $_SESSION['id']);
      }

      if (isset($_POST['publish_content'])) {
        if (in_array('pcontent', $this->C_ACCESS)) {
          if ($this->Publish($_POST['cid'])) {
            print "Published OK.<br/>";
          } else {
            print "Failed to publish.<br/>";
          }
        }
      }
    } // HandleFormSubmission
    
  } // EditGroup
  
  $AjaxDaemon = new AjaxDaemon();
  
  if (isset($_POST['submit'])) {
    $AjaxDaemon->HandleFormSubmission();
  } 
?>