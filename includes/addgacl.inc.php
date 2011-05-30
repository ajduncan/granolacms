<?php
  if (!isset($_GET['id'])) {
    print "Sorry, you must specify a group id before adding Group Access Control List(s).<br/>";
    exit;
  }
  
  if (!in_array("auser", $this->G->ACL->GetGroupACL($_GET['id'], $_SESSION['id']))) {
    print "Sorry, you do not have permission to modify group access control lists for this group.<br/>";
    exit;
  }


  include_once($GLOBALS['I_BASE']);

  class AddGACL extends Base {
    public $G;
    public $Group;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = $this->GetGroupRecord($this->gid);
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    
    
    public function HandleFormSubmission() {
      $users     = $_POST['user'];
      $accesses  = $_POST['access'];
      $access    = implode(",", $accesses);
      $group     = $_GET['id'];
      foreach ($users as $user) {
        $query = "delete from $GLOBALS[DB_PREFIX]user_groups where user_id='$user' and group_id='$group'";
        $this->G->DB->Execute($query);
        $query = "insert into $GLOBALS[DB_PREFIX]user_groups (user_id, group_id, access) values ('$user', '$group', '$access')";
        $this->G->DB->Execute($query);
      }
      header("Location: " . $GLOBALS['H_EDITGROUP_location'] . "&id=" . $group);
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div 
  id="AddGACLDialog" 
>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> Add New Group Access Control List:</h3>
    <form method="post" action="<?php print $GLOBALS['H_ADDGACL_location'] . "&id=" . $_GET['id']; ?>" id="AddGACLForm">
      <table class="formAnswer">
        <tbody>
          <tr>
            <td width="30%" valign="top">Group:</td>
            <td><?php print $this->Group['name']; ?></td>
          </tr><tr>
            <td width="30%" valign="top">User:</td>
            <td>
              <b>CTRL + Click to select multiple</b><br/>
              <select name="user[]" multiple>
                <?php
                  $query = "select * from $GLOBALS[DB_PREFIX]users";
                  $rs = $this->G->DB->Execute($query);
                  foreach ($rs as $record) {
                    $contact = $this->GetContactByUserID($record['id']);
                    $name    = $contact['fname'] . " " . $contact['lname'];
                    print "<option value=\"{$record['id']}\">$name</option>";
                  }
                ?>
              </select>
            </td>
          </tr><tr>
            <td valign="top">Access:</td>
            <td>
              <input type="checkbox" name="access[]" value="cgroup" /> Create Group<br/>
              <input type="checkbox" name="access[]" value="auser" /> Create Users<br/>
              <input type="checkbox" name="access[]" value="vcontent" /> View Content<br/>
              <input type="checkbox" name="access[]" value="ucontent" /> Update Content<br/>
              <input type="checkbox" name="access[]" value="pcontent" /> Publish Content
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
    
  } // AddGACL
  
  $AddGACL = new AddGACL();
  
  if (isset($_POST['submit'])) {
    $AddGACL->HandleFormSubmission();
    $AddGACL->PrintForm();
  } else {
    $AddGACL->PrintForm();
  } 
?>