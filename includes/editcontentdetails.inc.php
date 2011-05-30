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

  class EditContentDetails extends Base {
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
   
    public function HandleFormSubmission() {
      // Update existing content.
      $show_last_edited = 0;
      if (trim($_POST['show_last_edited'])) {
        $show_last_edited = $_POST['show_last_edited'];
      }
      $show_last_author = 0;
      if (trim($_POST['show_last_author'])) {
        $show_last_author = $_POST['show_last_author'];
      }
      
      if (trim($_POST['global_template'])) {
        $flags = 'global';
      } else {
        $flags = '';
      }
      
      $query = "update $GLOBALS[DB_PREFIX]content set flags='$flags', show_last_edited='$show_last_edited', show_last_author='$show_last_author', header='{$_POST['header_template']}', footer='{$_POST['footer_template']}', stylesheet='{$_POST['stylesheet']}' where id='{$this->content_id}'";
      $this->G->DB->Execute($query);
    } // HandleFormSubmission  
    
    public function PrintForm() {
      // ALWAYS reset post variables;
      $this->ResetPostVariables();
      if ($this->usefile) {
        $action = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->Group['id'] . "&file=yes&name=" . base64_encode($this->name);
        
      } else {
        $action = $GLOBALS['H_EDITCONTENTDETAILS_location'] . "&id=" . $this->Group['id'] . "&content_id=" . $this->content_id;
        $this->content_revision = $this->GetContentRevision($this->content_id, $_GET['revision']);
        $user_contact = $this->GetContactByUserID($this->content_revision['user_id']);
        $user_name    = $user_contact['fname'] . " " . $user_contact['lname'];
        $content      = $this->GetContent($this->content_id);
        $this->name   = $content['name'];
        $site_location = $this->GetSiteLocation($this->Group['id']);
        
        if (substr($site_location, 0, 1) == "/") {
          $site_location = ltrim($site_location, "/");
        }
        
        if (trim($site_location)) {
          $link         = WEB_SITE . "?location=" . $site_location . "/" . $this->name;
          $raw_link     = str_replace("/index.php", "", WEB_SITE) . $this->GetGroupLocation($this->Group['id']) . "/" . $this->name . ".inc.php"; 
        } else {
          $link         = WEB_SITE . "?location=" . $this->name;
          $raw_link     = str_replace("/index.php", "", WEB_SITE) . $this->GetGroupLocation($this->Group['id']) . "/" . $this->name . ".inc.php"; 
        }
      }
?>
<form method="post" action="<?php print $action ?>" id="EditContentDetailsForm" dojoType="dijit.form.Form">
  <table class="formAnswer" style="width: 100%;">
    <tbody>
      <tr>
        <td valign="top">
          <b>Content Information</b> [<a class="help" href="<?php print $GLOBALS['H_HELP_location'] . "&help_id=1"; ?>">♥</a>]<br/>
          <table class="formAnswer" style="width: 100%;" cellpadding="5" cellspacing="5">
            <tbody>
              <tr>              
                <td width="10%" valign="top">Location:</td>
                <td><?php print $this->GetGroupLocation($this->Group['id']); ?></td>
              </tr><tr>
                <td>Name:</td>
                <td><?php print $this->name; ?></td>
              </tr><tr>
                <td>Link:</td>
                <td><?php print $link; ?> (<a href="<?php print $link; ?>">Link</a>)</td>
              </tr><tr>
                <td>Raw Link:</td>
                <td><?php print $raw_link; ?> (<a href="<?php print $raw_link; ?>">Raw Link</a>)</td>
              </tr>
              <?php
                // Todo: abstract this.
                if ($content['type'] != 3) {
              ?>
              <tr>
                <td>Header Template:</td>
                <td>
                  <select name="header_template">
                    <option value="0" <?php $this->G->PrintDBSelectedValue("header_template", "0", $content['header'], "0"); ?>>Group Inherit</option>
                    <?php $this->ContentList->PrintTemplateOptions($this->Group['id'], "header_template", $content['header']);  ?>
                    <option value="-1">----------</option>
                    <?php $this->ContentList->PrintGlobalTemplates("header_template", $content['header']); ?>
                  </select>
                </td>
              </tr><tr>
                <td>Footer Template:</td>
                <td>
                  <select name="footer_template">
                    <option value="0" <?php $this->G->PrintDBSelectedValue("footer_template", "0", $content['footer'], "0"); ?>>Group Inherit</option>
                    <?php $this->ContentList->PrintTemplateOptions($this->Group['id'], "footer_template", $content['footer']);  ?>
                    <option value="-1">----------</option>
                    <?php $this->ContentList->PrintGlobalTemplates("header_template", $content['header']); ?>
                  </select>
                </td>
              </tr>
              <?php
                }
              ?>
              <?php
                if ($content['type'] != 4) {
              ?>
              <tr>
                <td>Stylesheet:</td>
                <td>
                  <select name="stylesheet">
                    <option value="0" <?php $this->G->PrintDBSelectedValue("stylesheet", "0", $content['stylesheet'], "0"); ?>>Group Inherit</option>
                    <?php $this->ContentList->PrintStylesheetOptions($this->Group['id'], "stylesheet", $content['stylesheet']);  ?>
                  </select>
                </td>
              </tr>
              <?php
                }
              ?>
              <?php
                if (!$this->usefile) {
              ?>
              <tr>
                <td>Global:</td>
                <td>
                  <input
                    type="checkbox"
                    name="global_template"
                    value="1"
                    <?php
                      if (isset($_POST['global_template'])) {
                        if ($_POST['global_template'] == "1") {
                          print "checked";
                        }
                      } else {
                        if (in_array('global', explode(',', $content['flags']))) {
                          print "checked";
                        }
                      }
                    ?>
                  />
                </td>
              <tr>
                <td>Timestamp:</td>
                <td><?php print $this->content_revision['timestamp']; ?></td>
              </tr><tr>
                <td>Last Edited:</td>
                <td><?php print $user_name . " (" . $this->content_revision['user_id'] . ")"; ?></td>
              </tr><tr>
                <td>Show Last Edited Time:</td>
                <td>
                  <input
                    type="checkbox"
                    name="show_last_edited"
                    value="1"
                    <?php
                      if (isset($_POST['show_last_edited'])) {
                        if ($_POST['show_last_edited'] == "1") {
                          print "checked";
                        }
                      } else {
                        if ($content['show_last_edited'] == 1) {
                          print "checked";
                        }
                      }
                    ?>
                  />
                </td>
              </tr><tr>
                <td>Show Last Edited Author:</td>
                <td>
                  <input
                    type="checkbox"
                    name="show_last_author"
                    value="1"
                    <?php
                      if (isset($_POST['show_last_author'])) {
                        if ($_POST['show_last_author'] == "1") {
                          print "checked";
                        }
                      } else {
                        if ($content['show_last_author'] == 1) {
                          print "checked";
                        }
                      }
                    ?>
                  />
                </td>
              </tr>
              <?php
                }
              ?>
            </tbody>
          </table>
        </td>
      </tr><tr>
        <td align="left">
<?php
  if (
      in_array("ucontent", $this->C_ACCESS) ||
      (isset($_GET['file']) && in_array("ucontent", $this->G_ACCESS))
  ) {
?>
          <button type="submit" name="submit" value="submit">
            <img src="./images/icons/database/database_save.png" alt="Save">Save</img>
          </button>
<?php
  }
?>
        </td>
      </tr>
    </tbody>
  </table>
</form>
<?php
    } // PrintForm
    
  } // EditContentDetails

  $EditContentDetails = new EditContentDetails();

  if (isset($_GET['file'])) {
    $EditContentDetails->SetUseFile(1);
  }
  
  if (isset($_POST['submit'])) {
    if (
        in_array("ucontent", $EditContentDetails->C_ACCESS) ||
        (isset($_GET['file']) && in_array("ucontent", $EditContentDetails->G_ACCESS))
    ) {
      $EditContentDetails->HandleFormSubmission();
    }
    header("Location: " . $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $EditContentDetails->gid . "&content_id=" . $EditContentDetails->content_id);
  } 
  $EditContentDetails->PrintForm();
?>