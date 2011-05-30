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
  include_once($GLOBALS['I_DIALOGS']);

  // include_once($GLOBALS['I_BASE']);

  class EditContent extends Base {
    public $G;
    public $gid;
    public $content_id;
    public $Group;
   
    public $revision;
    public $new_revision;
    public $published_revision;

    public $usefile;
    public $filepath;
    
    private $GroupList;
    private $ContentList;
    private $filename;
    private $name;
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
        $this->name     = base64_decode($_GET['file']);
        $this->filepath = CONTENT_PATH . $this->GetGroupLocation($this->Group['id']) . "/" . $this->name;
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
      if (in_array("pcontent", $this->C_ACCESS)) {    
        // Handle the actual update / revision stuff
        if ($this->HandleFormSubmission() == 1) {
          $query    = "select * from $GLOBALS[DB_PREFIX]content where id='$this->content_id'";
          $record   = $this->G->DB->GetRow($query);
          $name     = $record['name'];
          $type     = $record['type'];
          
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
          $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' and revision='$this->new_revision'";
          $record = $this->G->DB->GetRow($query);
          $content = base64_decode($record['text']);
    
          if (file_put_contents($filename, $content) == false) {
            // print "Unable to publish the file to disk, please verify that $filename is writable.<br/>";
            return(-1);
          } else {
            // print "Content published to the file: $filename<br/>";
            // Save this revision
            $this->published_revision = $this->new_revision;
            $query = "update $GLOBALS[DB_PREFIX]content set published_revision='$this->published_revision' where id='$this->content_id'";
            $this->G->DB->Execute($query);
            return(1);
          }
        } else {
          return(-1);
        }
      }
    } // Publish
    
    public function HandleFormSubmission() {
      if (
        in_array("ucontent", $this->C_ACCESS) ||
        (isset($_GET['file']) && in_array("ucontent", $this->G_ACCESS))        
      ) {
        if (get_magic_quotes_gpc()) {
          $content  = base64_encode(stripslashes($_POST['content']));
        } else {
          $content  = base64_encode($_POST['content']);
        }
        
        if ($this->usefile) {
          // Adding new content from existing file.
          $name     = str_replace(".inc.php", "", $this->name);
          $filename = $this->GetGroupLocation($this->Group['id']) . "/" . $this->name;
          $group_id = $this->gid;
  
          $query = "insert into $GLOBALS[DB_PREFIX]content (owner_id, name, published, approved, filename) values ('{$_SESSION['id']}', '{$name}', '1', '1', '{$filename}')";
          $this->G->DB->Execute($query);
          $content_id = $this->G->DB->Insert_ID();
  
          $query = "insert into $GLOBALS[DB_PREFIX]content_revisions (content_id, user_id, revision, text) values ('$content_id', '{$_SESSION['id']}', '1', '$content')";
          $this->G->DB->Execute($query);
          
          $query = "insert into $GLOBALS[DB_PREFIX]user_content (content_id, user_id, access) values ('$content_id', '{$_SESSION['id']}', 'vcontent,ucontent')";
          $this->G->DB->Execute($query);
  
          $query = "insert into $GLOBALS[DB_PREFIX]content_groups (content_id, group_id, access) values ('$content_id', '{$this->gid}', 'vcontent,ucontent')";
          $this->G->DB->Execute($query);
  
          $this->content_id = $content_id;
        } else {
          // Update existing content.       
          $this->content_revision = $this->GetContentRevision($this->content_id, "");
          $new_revision = $this->content_revision['revision'] + 1;
          
          $this->new_revision = $new_revision;
          $query = "insert into $GLOBALS[DB_PREFIX]content_revisions (content_id, user_id, revision, text) values ('$this->content_id', '{$_SESSION['id']}', '{$new_revision}', '$content')";
          $this->G->DB->Execute($query);
          // $this->published_revision = $this->G->DB->Insert_ID();
        }
        return(1);
      } else {
        return(-1);
      }
    } // HandleFormSubmission

    public function PrintForm() {
      // ALWAYS reset post variables;
      $this->ResetPostVariables();
      if ($this->usefile) {
        $action = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->Group['id'] . "&file=" . base64_encode($this->name);        
      } else {
        $action = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->Group['id'] . "&content_id=" . $this->content_id;
        if (isset($_GET['revision'])) {
          $this->content_revision = $this->GetContentRevision($this->content_id, $_GET['revision']);
        } else {
          $this->content_revision = $this->GetContentRevision($this->content_id, "");
        }
        $user_contact         = $this->GetContactByUserID($this->content_revision['user_id']);
        $user_name            = $user_contact['fname'] . " " . $user_contact['lname'];
        $content              = $this->GetContent($this->content_id, "");
        $this->name           = $content['name'];
        $type                 = $content['type'];
        $file                 = $content['filename'];
        $path                 = $file;
        
        $stylesheet_id        = $content['stylesheet'];        
        $stylesheet_revision  = $this->GetStylesheet($this->Group['id'], $stylesheet_id, "");
        $stylesheet_id        = $stylesheet_revision['content_id'];
        if (trim($stylesheet_id)) {
          $query = "select * from $GLOBALS[DB_PREFIX]content where id='$stylesheet_id'";
          $stylesheet_content = $this->G->DB->GetRow($query);
          $stylesheet_path    = $stylesheet_content['filename'];
        }
        
      }
      
      $imagepath      = CONTENT_URL . $this->GetGroupLocation($this->Group['id']) . "/" . basename($path);

      if (!trim($file)) {
        $file = $this->name;
      }

      $ext =  strtolower(substr($file, strrpos($file, '.') + 1));

      if (
        $ext == "png" ||
        $ext == "jpg" ||
        $ext == "gif" ||
        $ext == "jpeg"
      ) {
        $type = 2;
      }
      
      if (
        $type != 2 &&
        $ext != "php" &&
        $ext != "html" &&
        $ext != "htm" &&
        $ext != "asp" &&
        $ext != "css"
      ) {
        $type = -1;
      }


?>
<div align="center">
  <a href="<?php print $GLOBALS['H_EDITGROUP_location'] . "&id=" . $this->Group['id']; ?>">Back to Group/Content Listing</a>
  <a href="javascript: new_window('preview', '<?php print $GLOBALS['H_CONTENTPREVIEW_location'] . "&content_id=" . $this->content_id . "&revision=" . $this->content_revision['revision']; ?>', 800, 600);">Preview This Content</a>
</div>
  <div id="EditContentTabContainer" dojoType="dijit.layout.TabContainer" style="width: 99%; height: 50em;">
    <div dojoType="dijit.layout.ContentPane" title='<img src="./images/icons/user/user_edit.png" alt="Content" Title="Content" /> Content'>
      <form method="post" action="<?php print $action ?>" id="EditContentForm">
<!-- dojoType="dijit.form.Form" -->
        <table style="width: 100%;" border="1">
          <tbody>
            <tr>
              <td colspan="2" valign="top" align="center" style="border: 1px solid #000;">
                <?php
                  if ($type == 2) {
                ?>
                <img src="<?php print $imagepath . $this->name; ?>" alt="<?php print $this->name; ?>" />
                <?php
                    print "<br />Unable to edit content: " . $this->name;
                  }
                  
                  if ($type == -1) {
                    print "Unable to edit content: " . $this->name;
                  }
                  
                  if ($type != 2 && $type != -1) {
                ?>
                <textarea id="content" name="content" rows="30" cols="99" style="width: 100%;"><?php
                
                    if ($this->usefile) {
                      print htmlspecialchars(
                        file_get_contents($this->filepath),
                        ENT_QUOTES,
                        "UTF-8"
                      );
                    } else {
                      $this->G->PrintDBTextValue('content',
                        htmlspecialchars(
                          base64_decode($this->content_revision['text']),
                          ENT_QUOTES,
                          "UTF-8"
                        ), '');
                    }
                
                ?></textarea>
                <?php
                  $wysiwyg=0;
                  $module = $GLOBALS['Modules']->ModulesByLinkLocation("H_EDITCONTENT_WYSIWYG");
                  if (is_array($module)) {
                    while(list($key,$val) = each ($module)) {
                      $val->PrintForm();
                      $wysiwyg=1;
                    }
                  }
                ?>
                <?php
                    if (
                      ($type == 1 || $this->usefile == 1) &&
                      $wysiwyg == 0
                    ) {
                ?>
                <script>
                <!--
                <?php
                  if (isset($stylesheet_path)) {
                ?>
                    FCKeditor.EditorAreaCSS = '<?php print $stylesheet_path; ?>';
                    FCKeditor.EditorAreaStyles = 'body { color: Grey }';
                <?php
                  }
                ?>
                  var oFCKeditor = new FCKeditor('content');
                  oFCKeditor.BasePath = './fckeditor/';
                  oFCKeditor.Height = '500';
                  oFCKeditor.Config["DefaultLanguage"] = "en" ;
                  <?php
                    if (isset($stylesheet_path)) {
                  ?>
                  oFCKeditor.Config["EditorAreaCSS"] = '<?php print $stylesheet_path; ?>';
                  oFCKeditor.Config["EditorAreaStyles"] = 'body { color: Grey }';
                  <?php
                    }
                  ?>
                  oFCKeditor.ReplaceTextarea() ;
                -->
                </script>
                <?php
                    }
                  }
                ?>
              </td>
            </tr>
          </tbody>
        </table>
<?php
  if ($type != 2) {
?>
  <?php
    if (!$this->usefile) {
      $access = $this->C_ACCESS;
    } else {
      $access = $this->G_ACCESS;
    }
      if (in_array('ucontent', $access)) {
  ?>
<input type="reset" value="Cancel" />
<input type="submit" name="submit" value="Save" />
<!--
        <button dojoType="dijit.form.Button" type="reset">
          <img src="./images/icons/cancel.png" alt="Cancel">Cancel</img>
        </button>
        <button dojoType="dijit.form.Button" type="submit" name="submit" value="submit">
          <img src="./images/icons/database/database_save.png" alt="Save">Save</img>
        </button>
-->
  <?php
    }
    if (!$this->usefile) {
      if (in_array('pcontent', $this->C_ACCESS)) {
  ?>
<input type="submit" name="publish" value="Publish" />
<!--
        <button dojoType="dijit.form.Button" type="submit" name="publish" value="publish">
          <img src="./images/icons/database/database_save.png" alt="Save">Publish</img>
        </button>
-->
  <?php
      }
    }
  }

  if (
    in_array('dcontent', $this->C_ACCESS) ||
    (isset($_GET['file']) && in_array("ucontent", $this->G_ACCESS))
  ) {
?>
<input type="submit" name="delete" value="Delete" />
<!--
        <button dojoType="dijit.form.Button" type="submit" name="delete" value="delete">
          <img src="./images/icons/database/database_delete.png" alt="Delete">Delete</img>
        </button>
-->
<?php
  }

  $module = $GLOBALS['Modules']->ModulesByLinkLocation("H_EDITCONTENT");
  if (is_array($module)) {
    while(list($key,$val) = each ($module)) {
?>
  <button dojoType="dijit.form.Button" id="EditContentCMD<?php print $key; ?>" onclick="javascript:new_window('mod', '<?php print $GLOBALS[$val->module_location . '_location'] . "&content_id=" . $_GET['id']; ?>', 800, 600);">
    <img src="./images/icons/user/user_add.png" alt="<?php print $val->module_name; ?>"><?php print $val->module_name; ?></img>
  </button>
<?php
    }
  }
?>
    </form>
  </div>
  <div dojoType="dijit.layout.ContentPane" title="Details">
    <div
      dojoType="dijit.layout.ContentPane"
      class="box"
      hasShadow="true"
      id="cpEditContentDetails"
      href="<?php print $GLOBALS['H_EDITCONTENTDETAILS_location'] . "&id=" . $this->Group['id'] . "&content_id=" . $this->content_id; ?>"
    ></div>
  </div>
  <div dojoType="dijit.layout.ContentPane" title="Access Control"> 
    <table class="formAnswer" style="width: 100%;">
      <tbody>
        <tr>
          <td><?php $this->ContentList->PrintCACL($this->content_revision['content_id']); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php
    if ($type != 2) {
  ?>
  <div dojoType="dijit.layout.ContentPane" title="Revision List">
    <table class="formAnswer" style="width: 100%;">
      <tbody>    
        <tr>
          <td valign="top" width="10%">Using Revision (#<?php print $this->content_revision['revision']; ?>):</td>
          <td valign="top" width="90%">
            <?php print $this->PrintContentRevisionList(); ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php
    }
  ?>
</div>
<?php
    } // PrintForm
    
  } // EditContent

  $EditContent = new EditContent();
  $Dialogs = new Dialogs();

  if (isset($_GET['file'])) {
    $EditContent->SetUseFile(1);
  } else {
    if (!in_array("vcontent", $EditContent->C_ACCESS)) {
      print "Sorry, you don't have permission to view this content.<br/>";
      exit;
    }
  }
  
  if (isset($_POST['submit'])) {
    if (
        in_array("ucontent", $EditContent->C_ACCESS) ||
        (isset($_GET['file']) && in_array("ucontent", $EditContent->G_ACCESS))
    ) {
      if ($EditContent->HandleFormSubmission() == 1) {
        if ($EditContent->usefile) {
          header("Location: " . $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $EditContent->gid . "&content_id=" . $EditContent->content_id);
        } else {
          // This isn't redundant, two users could edit the same file in the same time and would want to see their respective revisions:
          $url = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $EditContent->gid . "&content_id=" . $EditContent->content_id . "&revision=" . $EditContent->new_revision;
          header("Location: " . $url);
        }
      } else {
        $Dialogs->PrintDialog("status", "Update Status", "Error: Problem updating content.<br/>");
      }
    } else {
      $Dialogs->PrintDialog("status", "Update Status", "Error: You do not have permission to update content.<br/>");
    }
  } else {
    if (isset($_POST['publish'])) {
      if (in_array("pcontent", $EditContent->C_ACCESS)) {
        if ($EditContent->Publish() != 1) {
          $Dialogs->PrintDialog("status", "Update Status", "Error: there was a problem publishing the content.<br/>");
        } else {
          // $Dialogs->PrintDialog("status", "Update Status", "Published content.<br/>");
        }
      } else {
        $Dialogs->PrintDialog("status", "Update Status", "Error: You do not have permission to publish content.<br/>");
        print "Sorry, you do not have permission to publish content.<br/>";
      }
    }
    
    if (isset($_POST['delete']) || isset($_GET['delete'])) {
      if (
        in_array('dcontent', $EditContent->C_ACCESS) ||
        (isset($_GET['file']) && in_array("dcontent", $EditContent->G_ACCESS))
      ) {
        if ($EditContent->DeleteContent($_GET['content_id'], $EditContent->usefile, $EditContent->filepath)) {
          // $Dialogs->PrintDialog("status", "Delete Status", "Content deleted.<br/>");
          $url = $GLOBALS['H_EDITGROUP_location'] . "&id=" . $EditContent->gid;
          header("Location: " . $url);
        } else {
          $Dialogs->PrintDialog("status", "Delete Status", "Error: There was a problem deleting content.<br/>");
        }
      } else {
        $Dialogs->PrintDialog("status", "Update Status", "Error: You do not have permission to delete content.<br/>");
      }
    }
    $EditContent->PrintForm();
  } 
?>
