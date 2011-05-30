<?php
  if (!isset($_GET['content_id'])) {
    print "Sorry, you must specify a content id before adding Group Access Control List(s).<br/>";
    exit;
  }

  if (!in_array("pcontent", $this->G->ACL->GetContentACL($_GET['content_id'], $_SESSION['id']))) {
    print "Sorry, you don't have access to modify the content access control list for this content.<br/>";
    exit;
  }

  include_once($GLOBALS['I_BASE']);

  class AddCACL extends Base {
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
      $users      = $_POST['user'];
      $accesses   = $_POST['access'];
      $access     = implode(",", $accesses);
      $content_id = $_GET['content_id'];
      foreach ($users as $user) {
        $query = "delete from $GLOBALS[DB_PREFIX]user_content where user_id='$user' and content_id='$content_id'";
        $this->G->DB->Execute($query);
        $query = "insert into $GLOBALS[DB_PREFIX]user_content (user_id, content_id, access) values ('$user', '$content_id', '$access')";
        $this->G->DB->Execute($query);
      }
      
      header("Location: " . $GLOBALS['H_EDITCONTENT_location'] . "&content_id=" . $content_id);
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div 
  id="AddCACLDialog" 
>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> Add New Content Access Control List:</h3>
    <form method="post" action="<?php print $GLOBALS['H_ADDCACL_location'] . "&content_id=" . $_GET['content_id']; ?>" id="AddCACLForm">
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
              <input type="checkbox" name="access[]" value="vcontent" /> View Content<br/>
              <input type="checkbox" name="access[]" value="ucontent" /> Update Content<br/>
              <input type="checkbox" name="access[]" value="pcontent" /> Publish Content<br/>
              <input type="checkbox" name="access[]" value="dcontent" /> Delete Content<br/>
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
    
  } // AddCACL
  
  $AddCACL = new AddCACL();
  
  if (isset($_POST['submit'])) {
    $AddCACL->HandleFormSubmission();
    $AddCACL->PrintForm();
  } else {
    $AddCACL->PrintForm();
  } 
?>