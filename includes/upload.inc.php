<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Invalid upload";
    exit(0);
  }

  if (!isset($_GET['id'])) {
    header("HTTP/1.1 500 Internal Server Error");
    print "No group id set.";
    exit(0);
  }

  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class Upload extends Base {
    public $fh;
    public $G;
    public $gid;
    public $Group;
   
    private $filename;
    private $name;
    private $filepath;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
      fclose($this->fh);
    } // destruct    

    public function HandleFormSubmission() {

      if (trim($_FILES["Filedata"]["name"])) {
        $error = $_FILES["Filedata"]["error"];
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name    = $_FILES["Filedata"]["tmp_name"];
            $file_name   = $_FILES["Filedata"]["name"];
        } else {
          header("HTTP/1.1 500 Internal Server Error");
          print "Error uploading file: $error<br/>";
          return(-1);
        }
      }
      
      if (trim($file_name)) {
        $content_name = $this->FormatContentName($file_name);
      } else {
        header("HTTP/1.1 500 Internal Server Error");
        print "Error, content has no name.";
        return(-1);
      }
      
      $ext = substr($file_name, strrpos($file_name, '.') + 1);
      $filename     = $this->GetGroupLocation($this->gid) . "/" . $content_name;
      // . "." . $ext;
      
      $count = "count($GLOBALS[DB_PREFIX]content.name)";
      $query = "
                select
                  $count
                from
                  $GLOBALS[DB_PREFIX]content,
                  $GLOBALS[DB_PREFIX]content_groups
                where
                  name='$content_name' and
                  $GLOBALS[DB_PREFIX]content.id = $GLOBALS[DB_PREFIX]content_groups.content_id and
                  $GLOBALS[DB_PREFIX]content_groups.group_id={$this->gid}
              ";
      $rs = $this->G->DB->GetRow($query);
     
      if ($rs[$count] > 0) {
        header("HTTP/1.1 500 Internal Server Error");
        print "Sorry, a content item in this group already exists with the name: $content_name<br/>";
        return(-1);
      }
      
      // Add things:
      $add = CONTENT_PATH . "$filename";
      if (trim($_FILES["Filedata"]["name"])) {
        if (!move_uploaded_file($tmp_name, $add)) {
          header("HTTP/1.1 500 Internal Server Error");
          print "Error, could not save uploaded file.<br/>";
        }
      } else {
        header("HTTP/1.1 500 Internal Server Error");
        print "Error, could not save uploaded file.<br/>";
      }
      header("HTTP/1.1 200 OK");
      if (DEBUG == 1) {
        fwrite($GLOBALS['DEBUG_FH'], "Successfully added file: $add<br/>");
      }
      return(1);
    } // HandleFormSubmission
  }

  $Upload = new Upload();
  $Upload->HandleFormSubmission();
?>