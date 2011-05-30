<?php
  if (!isset($_GET['id'])) {
    print "Sorry, you must specify a group id before editing User Access Control List(s).<br/>";
    exit;
  }

  if (!isset($_GET['user_content_id'])) {
    print "Sorry, you must specify a user id before editing User Access Control List(s).<br/>";
    exit;
  }

  include_once($GLOBALS['I_BASE']);

  class EditCACL extends Base {
    public $G;
    public $Group;

    public function __construct() {
      parent::__construct();
      $this->Init();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function Init() {
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->user_content_id = $_GET['user_content_id'];
      $this->Group = $this->GetGroupRecord($this->gid);
      $this->UserContentRecord = $this->GetUserContentRecord($this->user_content_id);
      $this->content_id = $this->UserContentRecord['content_id'];
      if (!in_array("pcontent", $this->G->ACL->GetContentACL($this->content_id, $_SESSION['id']))) {
        print "Sorry, you don't have access to modify the content access control list for this content.<br/>";
        exit;
      }

      $this->Content = $this->GetContent($this->content_id);
    } // Init

    public function GetUserContentRecord($user_content_id) {      
      $query   = "select * from $GLOBALS[DB_PREFIX]user_content where id='{$user_content_id}'";
      return($this->G->DB->GetRow($query));
    } // GetUserGroupRecord

    public function HandleFormSubmission() {
      $access         = implode(",", $_POST['access']);
      $group          = $_GET['id'];
      $user_content_id  = $_GET['user_content_id'];
      
      $query  = "select user_id from $GLOBALS[DB_PREFIX]user_content where id='$user_content_id'";
      $record = $this->G->DB->GetRow($query);
      $user   = $record['user_id'];

      $query = "update $GLOBALS[DB_PREFIX]user_content set access='$access' where id='$user_content_id'";
      $this->G->DB->Execute($query);
      
      print "Access list updated.<br/>";
    } // HandleFormSubmission
    
    public function PrintForm() {
      $this->Init();
      $user_record = $this->GetContactByUserID($this->UserContentRecord['user_id']);
      $user = $user_record['fname'] . " " . $user_record['lname'];
      $arrayUCA = explode(",", $this->UserContentRecord['access']);

      if (in_array("vcontent", $arrayUCA)) {
        $dbview = "vcontent";
      } else {
        $dbview = "";
      }
      if (in_array("ucontent", $arrayUCA)) {
        $dbupdate = "ucontent";
      } else {
        $dbupdate = "";
      }
      if (in_array("pcontent", $arrayUCA)) {
        $dbpublish = "pcontent";
      } else {
        $dbpublish = "";
      }
?>
<div 
  id="EditCACLDialog" 
>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> Editing Access Control For <?php print $user; ?>:</h3>
    <form method="post" action="<?php print $GLOBALS['H_EDITCACL_location'] . "&id=" . $_GET['id'] . "&user_content_id=" . $_GET['user_content_id']; ?>" id="EditCACLForm">
      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td width="30%" valign="top">Group:</td>
            <td><?php print $this->Group['name']; ?></td>
          </tr><tr>
            <td width="30%" valign="top">Content:</td>
            <td><?php print $this->Content['name']; ?></td>
          </tr><tr>
            <td width="30%">User:</td>
            <td><?php print $user; ?></td>
          </tr><tr>
            <td valign="top">Access:</td>
            <td>
              <input type="checkbox" name="access[]" value="vcontent" <?php $this->G->PrintDBCheckedValue("access", "vcontent", $dbview, ''); ?> /> View<br/>
              <input type="checkbox" name="access[]" value="ucontent" <?php $this->G->PrintDBCheckedValue("access", "ucontent", $dbupdate, ''); ?> /> Update<br/>
              <input type="checkbox" name="access[]" value="pcontent" <?php $this->G->PrintDBCheckedValue("access", "pcontent", $dbpublish, ''); ?> /> Publish
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
  <br/>
  <a href="<?php print $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $_GET['id'] . "&content_id=" . $this->content_id; ?>">Back to Edit Content</a>
</div>
<?php
    } // PrintForm
    
  } // EditCACL
  
  $EditCACL = new EditCACL();
  
  if (isset($_POST['submit'])) {
    $EditCACL->HandleFormSubmission();
    $EditCACL->PrintForm();
  } else {
    $EditCACL->PrintForm();
  } 
?>