<?php
  if (!isset($_GET['id'])) {
    print "Sorry, you must specify a group id before editing Group Access Control List(s).<br/>";
    exit;
  }

  if (!isset($_GET['user_group_id'])) {
    print "Sorry, you must specify a user id before editing Group Access Control List(s).<br/>";
    exit;
  }

  include_once($GLOBALS['I_BASE']);

  class EditGACL extends Base {
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
      $this->user_group_id = $_GET['user_group_id'];
      $this->Group = $this->GetGroupRecord($this->gid);
      $this->UserGroupRecord = $this->GetUserGroupRecord($this->user_group_id);
    } // Init

    public function GetUserGroupRecord($user_group_id) {      
      $query   = "select * from $GLOBALS[DB_PREFIX]user_groups where id='{$user_group_id}'";
      return($this->G->DB->GetRow($query));
    } // GetUserGroupRecord


    public function HandleFormSubmission() {
      $access         = implode(",", $_POST['access']);
      $group          = $_GET['id'];
      $user_group_id  = $_GET['user_group_id'];
      
      $query  = "select user_id from $GLOBALS[DB_PREFIX]user_groups where id='$user_group_id'";
      $record = $this->G->DB->GetRow($query);
      $user   = $record['user_id'];

      $query = "update $GLOBALS[DB_PREFIX]user_groups set access='$access' where id='$user_group_id'";
      $this->G->DB->Execute($query);
      
      print "Access list updated.<br/>";
    } // HandleFormSubmission
    
    public function PrintForm() {
      $this->Init();
      $user_record = $this->GetContactByUserID($this->UserGroupRecord['user_id']);
      $user = $user_record['fname'] . " " . $user_record['lname'];
      $arrayUGA = explode(",", $this->UserGroupRecord['access']);

      if (in_array("cgroup", $arrayUGA)) {
        $dbcgroup = "cgroup";
      } else {
        $dbcgroup = "";
      }
      if (in_array("auser", $arrayUGA)) {
        $dbcuser = "auser";
      } else {
        $dbcuser = "";
      }
      if (in_array("acontent", $arrayUGA)) {
        $dbacontent = "acontent";
      } else {
        $dbacontent = "";
      }
      
      if (in_array("vcontent", $arrayUGA)) {
        $dbview = "vcontent";
      } else {
        $dbview = "";
      }
      if (in_array("ucontent", $arrayUGA)) {
        $dbupdate = "ucontent";
      } else {
        $dbupdate = "";
      }
      if (in_array("pcontent", $arrayUGA)) {
        $dbpublish = "pcontent";
      } else {
        $dbpublish = "";
      }
      if (in_array("dcontent", $arrayUGA)) {
        $dbdelete = "dcontent";
      } else {
        $dbdelete = "";
      }
      

?>
<div 
  id="EditGACLDialog" 
>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> Editing Access Control For <?php print $user; ?>:</h3>
    <form method="post" action="<?php print $GLOBALS['H_EDITGACL_location'] . "&id=" . $_GET['id'] . "&user_group_id=" . $_GET['user_group_id']; ?>" id="EditGACLForm">
      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td width="30%" valign="top">Group:</td>
            <td><?php print $this->Group['name']; ?></td>
          </tr><tr>
            <td width="30%">User:</td>
            <td><?php print $user; ?></td>
          </tr><tr>
            <td valign="top">Access:</td>
            <td>
              <input type="checkbox" name="access[]" value="cgroup" <?php $this->G->PrintDBCheckedValue("access", "cgroup", $dbcgroup, ''); ?> /> Create Group<br/>
              <input type="checkbox" name="access[]" value="auser" <?php $this->G->PrintDBCheckedValue("access", "auser", $dbcuser, ''); ?> /> Create Users<br/>
              <input type="checkbox" name="access[]" value="vcontent" <?php $this->G->PrintDBCheckedValue("access", "vcontent", $dbview, ''); ?> /> View<br/>
              <input type="checkbox" name="access[]" value="ucontent" <?php $this->G->PrintDBCheckedValue("access", "ucontent", $dbupdate, ''); ?> /> Update<br/>
              <input type="checkbox" name="access[]" value="pcontent" <?php $this->G->PrintDBCheckedValue("access", "pcontent", $dbpublish, ''); ?> /> Publish<br/>
              <input type="checkbox" name="access[]" value="dcontent" <?php $this->G->PrintDBCheckedValue("access", "dcontent", $dbdelete, ''); ?> /> Delete
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
  <a href="<?php print $GLOBALS['H_EDITGROUP_location'] . "&id=" . $_GET['id']; ?>">Back to Edit Group</a>
</div>
<?php
    } // PrintForm
    
  } // EditGACL
  
  $EditGACL = new EditGACL();
  
  if (isset($_POST['submit'])) {
    $EditGACL->HandleFormSubmission();
    $EditGACL->PrintForm();
  } else {
    $EditGACL->PrintForm();
  } 
?>