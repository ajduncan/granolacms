<?php  

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
    
  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  include_once($GLOBALS['H_USERLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class EditGroup extends Base {
    public $G;
    public $gid;
    public $Group;
    private $GroupList;
    private $ContentList;
    private $UserList;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
      $this->GroupList = new GroupList();
      $this->ContentList = new ContentList();
      $this->UserList = new UserList();
      $this->GetGroupInformation();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function DeleteGroup($groupid) {
      // recursively delete all children, content, etc.  Add this in after finishing project 1.
    } // DeleteUser
    
    public function HandleFormSubmission() {
      // No.
      if (in_array("cgroup", $this->G->ACL->GetGroupACL($_GET['id'], $_SESSION['id']))) {
        $query = "update $GLOBALS[DB_PREFIX]groups set description='{$_POST['description']}' where id={$_GET['id']}";
        $this->G->DB->Execute($query);
        print "Group has been updated.<br/>";
        $this->GetGroupInformation();
      } else {
        print "You do not have permission to make group changes.<br/>";
      }
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div 
  id="EditGroupDialog" 
>
  <br/>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> <?php print $this->Group['name']; ?> <small>("<?php print $this->GetGroupLocation($this->Group['id']); ?>")</small> Group Media:</h3>
    <input type="hidden" id="edgroup_id" name="edgroup_id" value="<?php print $this->Group['id']; ?>" />
    <form method="post" action="<?php print $GLOBALS['H_EDITGROUP_location'] . "&id=" . $this->Group['id']; ?>" id="EditGroupForm">
      <table class="formAnswer">
        <tbody>
          <tr>
            <td valign="top" width="100%">
              <a href="<?php print $GLOBALS['H_EDITGROUPDETAILS_location'] . "&id=" . $this->Group['id']; ?>">Details</a>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>

<br/>
<div class="formQuestion">
  <h3><img src="./images/icons/user/user_edit.png" alt="" /> Edit Related Content:</h3>
  <p>Content <a href="">Media</a> <a href="">Templates</a> <a href="">Stylesheets</a></p>
  <table class="formAnswer">
    <tbody>
      <tr>
        <td>
          <div class="containerX">
            <?php $this->ContentList->PrintForm($this->gid); ?>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<?php
    } // PrintForm
    
  } // EditGroup
  
  $EditGroup = new EditGroup();
  
  if (isset($_POST['submit'])) {
    $EditGroup->HandleFormSubmission();
    $EditGroup->PrintForm();
  } else {
    if (isset($_POST['delete'])) {
      // $EditGroup->DeleteGroup();
      print "Will not delete group due to design specification.<br/>";
      $EditGroup->PrintForm();
    } else {
      $EditGroup->PrintForm();
    }
  } 
?>