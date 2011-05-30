<?php

  if (!isset($_GET['parent_id'])) {
    print "Sorry, you must specify a parent id before adding a group.<br/>";
    exit;
  }

  if (!in_array("cgroup", $this->G->ACL->GetGroupACL($_GET['parent_id'], $_SESSION['id']))) {
    print "Sorry, you do not have permission to create child groups for this group.<br/>";
    exit;
  }
  
  
  $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$_GET['parent_id']}'";
  $rs    = $GLOBALS['Globals']->DB->Execute($query);
  if ($rs->RecordCount() == 0) {
    print "Sorry, there is no such parent with id: {$_GET['parent_id']} to add a child group to.<br/>";
    exit;
  }


  include_once($GLOBALS['I_BASE']);

  class AddGroup extends Base {
    public $G;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    
    
    private function FixName($name) {
      $name    = preg_replace('/[^a-zA-Z0-9*]/', '', $name);
      $name    = str_replace('%', '', $name);
      $name    = strtolower($name);
      return($name);
    } // FixName
    
    public function HandleFormSubmission() {
      // First, fix up the name;
      $name = $this->FixName($_POST['name']);
      // Is the group valid?
      if (!$this->IsValidGroupID($_GET['parent_id'])) {
        print "Sorry, the parent group is not valid.<br/>";
        return(-1);
      }
      
      // Next, get the path to this group;
      $path     = "." . $this->GetGroupLocation($_GET['parent_id']) . "/";
      $fullpath = $path . $name;
      if (file_exists($path)) {
        if (!file_exists($fullpath)) {
          if (!mkdir($fullpath, 0755)) {
            print "There was a problem creating the directory, $fullpath.<br/>";
            print "Group has not been created.<br/>";
            return(-1);
          }
        }
        $groupid = $this->CreateNewGroup($name, $_GET['parent_id'], $_POST['description']);
        if ($groupid != -1) {
          header("Location: " . $GLOBALS['H_EDITGROUP_location'] . "&id=" . $groupid);
        } else {
          print "There was a problem creating this group.<br/>";
        }
      } else {
        print "There is a file system error.  The parent group does not exist.  Please contact the system administrator and make sure the following path exists:<br/>";
        print "Parent group path (which does not exist!): $path<br/>";
        print "Full path to desired group: $fullpath<br/>";
      }
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div 
  id="AddGroupDialog" 
>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> Add New Group:</h3>
    <form method="post" action="<?php print $GLOBALS['H_ADDGROUP_location'] . "&parent_id=" . $_GET['parent_id']; ?>" id="AddGroupForm">
      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td width="30%" valign="top">Base Location:</td>
            <td><?php print $this->GetGroupLocation($_GET['parent_id']); ?></td>
          </tr><tr>
            <td width="30%">Name:</td>
            <td>
              <input type="text" id="name" name="name" value="<?php $this->G->PrintTextValue('name', ''); ?>" />
            </td>
          </tr><tr>
            <td valign="top">Description:</td>
            <td>
              <textarea id="description" name="description" rows="20" cols="40"><?php $this->G->PrintTextValue('description', ''); ?></textarea>
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
    
  } // AddGroup
  
  $AddGroup = new AddGroup();
  
  if (isset($_POST['submit'])) {
    $AddGroup->HandleFormSubmission();
    $AddGroup->PrintForm();
  } else {
    $AddGroup->PrintForm();
  } 
?>