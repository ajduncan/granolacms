<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  if (!isset($_GET['id'])) {
    if (!isset($_GET['content_id'])) {
      print "Sorry, you must have either a group or content id set.<br/>";
      exit;
    } else {
      // Need to set the group id;
      $query  = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='{$_GET['content_id']}'";
      $record = $GLOBALS['Globals']->DB->GetRow($query);
      $_GET['id'] = $record['group_id'];
    }
  }

  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class AddContent extends Base {
    public $G;
    public $gid;
    public $content_id;
    public $Group;
   
    public $revision;
    public $new_revision;
    
    private $GroupList;
    private $ContentList;
    public $usefile;
    private $filename;
    private $name;
    private $filepath;
    private $content_revision;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      if (isset($_GET['content_id'])) {
        $this->content_id = $_GET['content_id'];
      }
      if (isset($_GET['revision'])) {
        $this->revision = $_GET['revision'];
      }
      
      $this->Group = array();
      $this->GetGroupInformation();
      $this->usefile = 0;
      $this->ContentList = new ContentList();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function SetUseFile($usefile) {
      if ($usefile) {
        $this->usefile  = 1;
        $this->name     = base64_decode($_GET['name']);
        $this->filepath = $GLOBALS['WEBROOT'] . $this->GetGroupLocation($this->Group['id']) . "/" . $this->name;
      } else {
        $this->usefile  = 0;
      }
    }

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function GetSiteLocation($gid) {
      $location = $this->GetGroupLocation($gid);
      $location = str_replace("/content", "", $location);
      return($location);
    } // GetSiteLocation
    
    public function Publish() {
      // Handle the actual update / revision stuff
      $this->HandleFormSubmission();
      
      $query    = "select * from $GLOBALS[DB_PREFIX]content where id='$this->content_id'";
      $record   = $this->G->DB->GetRow($query);
      $name     = $record['name'];
      $filename = $GLOBALS['WEBROOT'] . $this->GetGroupLocation($this->Group['id']) . "/" . $record['name'] . ".inc.php";

      // Get the 'new revision' contents and plaster it into the file:
      $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' and revision='$this->new_revision'";
      $record = $this->G->DB->GetRow($query);
      $content = base64_decode($record['text']);

      if (file_put_contents($filename, $content) == false) {
        print "Unable to publish the file to disk, please verify that $filename is writable.<br/>";
      } else {
        print "Content published to the file: $filename<br/>";
      }
      
    } // Publish
    
    public function HandleFormSubmission() {
      if (trim($_FILES["browse"]["name"])) {
        $error = $_FILES["browse"]["error"];
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name    = $_FILES["browse"]["tmp_name"];
            $file_name   = $_FILES["browse"]["name"];
        } else {
          print "Error uploading file: $error<br/>";
          return(-1);
        }
      }
      
      if (trim($_POST['name'])) {
        $content_name = $this->FormatContentName($_POST['name']);
        // fix up
      } else {
        if (trim($file_name)) {
          $content_name = $this->FormatContentName($file_name);
        } else {
          print "Error, content has no name.";
          return(-1);
        }
      }
      
      $type = $_POST['type'];
      
      // print "Got: $type, $content_name, $file_name<br/>";

      // Adding new content from existing file.
      $content_name = str_replace(".inc.php", "", $content_name);
      if (
          ($type == "1") ||
          ($type == "3")
      ) {
        $ext = "inc.php";
      }
      
      if ($type == "4") {
        $ext = "css";
      }
      
      if ($type != "1" && $type != "3" && $type != "4") {
        // Use the extension from the file:
        $ext = substr($file_name, strrpos($file_name, '.') + 1);
        // $ext = "";
      }
      $filename     = $this->GetGroupLocation($this->Group['id']) . "/" . $content_name . "." . $ext;
      
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
                  $GLOBALS[DB_PREFIX]content_groups.group_id={$this->Group['id']}
              ";
      $rs = $this->G->DB->GetRow($query);
     
      if ($rs[$count] > 0) {
        print "Sorry, a content item in this group already exists with the name: $content_name<br/>";
        return(-1);
      }
      
      // Add things:
      if (trim($_FILES["browse"]["name"])) {
        if (!move_uploaded_file($tmp_name, CONTENT_PATH . "$filename")) {
          print "Error, could not save uploaded file.<br/>";
        }
      } else {
        // Make the file
        $fh = fopen(CONTENT_PATH . $filename, "w") or die("Error, could not write file, " . CONTENT_PATH . "$filename, to disk.<br/>");
        fclose($fh);
      }
      $group_id     = $this->gid;

      $query = "insert into $GLOBALS[DB_PREFIX]content (owner_id, name, published, approved, filename, type) values ('{$_SESSION['id']}', '{$content_name}', '1', '1', '{$filename}', '{$type}')";
      $this->G->DB->Execute($query);
      $content_id = $this->G->DB->Insert_ID();

      $query = "insert into $GLOBALS[DB_PREFIX]content_revisions (content_id, user_id, revision, text) values ('$content_id', '{$_SESSION['id']}', '1', '$content')";
      $this->G->DB->Execute($query);
      $this->new_revision = $this->G->DB->Insert_ID();
      
      $query = "insert into $GLOBALS[DB_PREFIX]user_content (content_id, user_id, access) values ('$content_id', '{$_SESSION['id']}', 'vcontent,ucontent')";
      $this->G->DB->Execute($query);

      $query = "insert into $GLOBALS[DB_PREFIX]content_groups (content_id, group_id, access) values ('$content_id', '{$this->gid}', 'vcontent,ucontent')";
      $this->G->DB->Execute($query);
      
      $this->content_id = $content_id;
      return(1);
    } // HandleFormSubmission
    
    public function PrintForm() {
      // ALWAYS reset post variables;
      $this->ResetPostVariables();
      $action = $GLOBALS['H_ADDCONTENT_location'] . "&id=" . $this->Group['id'];
?>
<div 
  id="AddContentDialog" 
>
  <br/>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_add.png" alt="" /> Add/Create Content: [<a class="help" href="<?php print $GLOBALS['H_HELP_location'] . "&help_id=1"; ?>">♥</a>]</h3>
    <form method="post" action="<?php print $action ?>" id="AddContentForm" enctype="multipart/form-data">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />

      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td valign="top">
              <b></b><br/>
              <table class="formAnswer" style="width: 100%;">
                <tbody>
                  <tr>              
                    <td width="30%" valign="top">Location:</td>
                    <td><?php print $this->GetGroupLocation($this->Group['id']); ?></td>
                  </tr><tr>
                    <td width="30%">Name:</td>
                    <td>
                      <input
                        type="text"
                        name="name"
                        value="<?php print $this->name; ?>"
                      />  
                    </td>
                  </tr><tr>
                    <td width="30%">Type:</td>
                    <td>
                      <select name="type">
                        <option value="1">Document</option>
                        <option value="2">Media</option>
                        <option value="3">Template</option>
                        <option value="4">Stylesheet</option>
                      </select>
                    </td>
                  </tr><tr>
                    <td width="30%">-</td>
                    <td></td>
                  </tr><tr>
                    <td width="30%">Upload existing content:</td>
                    <td>
                      <input type="file" name="browse" value="Browse" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr><tr>
            <td colspan="2" align="center">
              <button type="reset">
                <img src="./images/icons/cancel.png" alt="Cancel">Cancel</img>
              </button>  				  
              <button type="submit" name="submit" value="submit">
                <img src="./images/icons/database/database_save.png" alt="Save">Save</img>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>
<?php
    } // PrintForm
    
  } // AddContent
?>
<!-- <div align="center"><a href="<?php print $GLOBALS['H_EDITGROUP_location'] . "&id=" . $_GET['id']; ?>">Back to Group/Content Listing</a></div> -->
<?php
  $AddContent = new AddContent();
 
  if (isset($_POST['submit'])) {
    if ($AddContent->HandleFormSubmission() == 1) {
      // This isn't redundant, two users could Add the same file in the same time and would want to see their respective revisions:
      $url = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $AddContent->gid . "&content_id=" . $AddContent->content_id . "&revision=" . $AddContent->new_revision;
      header("Location: " . $url);
      // print "Success.";
    } else {
      print "Failed to create content.";
      $AddContent->PrintForm();
    }
  } else {
    $AddContent->PrintForm();
  } 
?>
