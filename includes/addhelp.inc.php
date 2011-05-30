<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  if (!isset($_GET['id'])) {
    if (!isset($_GET['category_id'])) {
      print "Sorry, you must have a category id set.<br/>";
      exit;
    } else {
      // Need to set the group id;
      $query  = "select * from $GLOBALS[DB_PREFIX]help_category where id='{$_GET['category_id']}'";
      $record = $GLOBALS['Globals']->DB->GetRow($query);
      $_GET['id'] = $record['id'];
    }
  }

  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class AddHelp extends Base {
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

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation
        
    private function GetContentRevision() {
      if (isset($_GET['revision'])) {
        $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' and revision='{$_GET['revision']}'";
      } else {
        $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' order by revision DESC";
      }
      $this->content_revision = $this->G->DB->GetRow($query);
    } // GetContentRevision
    
    public function GetSiteLocation($gid) {
      $location = $this->GetGroupLocation($gid);
      $location = str_replace("/content/", "", $location);
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
        $this->GetContentRevision();
        $new_revision = $this->content_revision['revision'] + 1;
        $this->new_revision = $new_revision;
        $query = "insert into $GLOBALS[DB_PREFIX]content_revisions (content_id, user_id, revision, text) values ('$this->content_id', '{$_SESSION['id']}', '{$new_revision}', '$content')";
        $this->G->DB->Execute($query);
      }
    } // HandleFormSubmission
    
    private function PrintContentRevisionList() {
?>
<select name="contentrevisions" onchange="location.href=this.options[this.selectedIndex].value">
<?php
    $query = "select revision from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id'";
    $rs  = $this->G->DB->Execute($query);
    $rows = $rs->GetRows();
    foreach ($rows as $row) {
      $url = $GLOBALS['H_AddCONTENT_location'] . "&id=" . $this->Group['id'] . "&content_id=" . $this->content_id . "&revision=" . $row['revision'];
      print "<option value=\"$url\">" . $row['revision'] . "</option>";
    }
?>
</select>
<?php
    } // PrintContentRevisionList
    
    public function PrintForm() {
      // ALWAYS reset post variables;
      $this->ResetPostVariables();
      $action = $GLOBALS['H_ADDHELP_location'] . "&id=" . $this->Group['id'] . "&content_id=" . $this->content_id;
      // $this->GetContentRevision();
      // $user_contact = $this->GetContactByUserID($this->content_revision['user_id']);
      // $user_name    = $user_contact['fname'] . " " . $user_contact['lname'];
      // $this->name   = $content['name'];
?>
<div 
  id="AddContentDialog" 
>
  <br/>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_add.png" alt="" /> Add Help:</h3>
    <form method="post" action="<?php print $action ?>" id="AddContentForm">
      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td valign="top">
              <b>Content Information</b><br/>
              <table class="formAnswer" style="width: 100%;">
                <tbody>
                  <tr>              
                    <td width="30%" valign="top">Location:</td>
                    <td><?php print $this->GetGroupLocation($this->Group['id']); ?></td>
                  </tr><tr>
                    <td width="30%">Name:</td>
                    <td><?php print $this->name; ?></td>
                  </tr><tr>
                    <td width="30%">Link:</td>
                    <td><a href="<?php print WEB_SITE . "?location=" . $this->GetSiteLocation($this->Group['id']) . "/" . $this->name; ?>">Link</a></td>
                  </tr><tr>
                    <td width="30%">Raw Link:</td>
                    <td><a href="<?php print str_replace("/index_acms.php", "", WEB_SITE) . $this->GetGroupLocation($this->Group['id']) . "/" . $this->name . ".inc.php"; ?>">Raw Link</a></td>
                  </tr><tr>
                  <?php
                    if (!$this->usefile) {
                  ?>
                    <td width="30%">Revision:</td>
                    <td>
                      #<?php print $this->content_revision['revision']; ?>, 
                      Use other revisions:
                      <?php print $this->PrintContentRevisionList(); ?>
                    </td>
                  </tr><tr>
                    <td width="30%">Timestamp:</td>
                    <td><?php print $this->content_revision['timestamp']; ?></td>
                  </tr><tr>
                    <td width="30%">Last Added:</td>
                    <td><?php print $user_name . " (" . $this->content_revision['user_id'] . ")"; ?></td>
                  </tr><tr>          
                  <?php
                    }
                  ?>
                </tbody>
              </table>
            </td><td valign="top">
              <b>Access Control</b><br/>
              <table class="formAnswer" style="width: 100%;">
                <tbody>
                  <tr>
                    <td><?php $this->ContentList->PrintCACL($this->content_revision['content_id']); ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr><tr>
            <td valign="top" colspan="2" align="center">Content:</td>
          </tr><tr>
            <td colspan="2" valign="top" align="center">
              <textarea id="content" name="content" rows="50" cols="100" style="width: 100%; height: 400px"><?php
              
                if ($this->usefile) {
                  print file_get_contents($this->filepath);
                } else {
                  $this->G->PrintDBTextValue('content', base64_decode($this->content_revision['text']), '');
                }
              
              ?></textarea>
              <script>
              <!--
                var oFCKAddor = new FCKAddor('content');
                oFCKAddor.BasePath = './fckAddor/';
                oFCKAddor.Height = "400" ; 
                oFCKAddor.ReplaceTextarea() ;
              -->
              </script>

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
    
  } // AddHelp
  
  $AddContent = new AddHelp();
 
  if (isset($_POST['submit'])) {
    $AddContent->HandleFormSubmission();
    if ($AddContent->usefile) {
      header("Location: " . $GLOBALS['H_AddCONTENT_location'] . "&id=" . $AddContent->gid . "&content_id=" . $AddContent->content_id);
    } else {
      // This isn't redundant, two users could Add the same file in the same time and would want to see their respective revisions:
      $url = $GLOBALS['H_AddCONTENT_location'] . "&id=" . $AddContent->gid . "&content_id=" . $AddContent->content_id . "&revision=" . $AddContent->new_revision;
      header("Location: " . $url);
    }
  } else {
    $AddContent->PrintForm();
  } 
?>

<br/>

<a href="<?php print $GLOBALS['H_HELPLIST_location'] . "&category_id=" . $_GET['category_id']; ?>">Back to Help Listing</a>