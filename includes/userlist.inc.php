<?php

  // Here, we get a list of users.
  
  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['I_RS2JSON']);

  class UserList extends Base {
    public $G;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct
    
    public function PrintGACL($gid) {
      $query = "select * from $GLOBALS[DB_PREFIX]user_groups where group_id='$gid'";
      $rs    = $this->G->DB->Execute($query);
?>
<div class="dbtable" align="center">
  <table 
      id="userlist"
      cellpadding="0" 
      cellspacing="0" 
      border="0"
  >
    <thead>
      <tr>
        <th field="name"  align="left">Name</th>
        <th field="parent" align="left">Access</th>
      </tr>
    </thead>
    <tbody>
<?php
      foreach ($rs as $record) {
        $user_contact = $this->GetContactByUserID($record['user_id']);
	$url           = $GLOBALS['H_EDITGACL_location'];
	$edit          = "javascript:document.location='{$url}&id={$_GET['id']}&user_group_id={$record['id']}'";
	$accessArray   = split(",", $record['access']);
	$access_images = $this->ImageListFromACL($accessArray);

	print "<tr onclick=\"$edit\"><td>{$user_contact['fname']} {$user_contact['lname']}</td><td>{$access_images}</td></tr>";
      }
?>
    </tbody>
  </table>
</div>
  <?php
    if (in_array("auser", $this->G->ACL->GetGroupACL($gid, $_SESSION['id']))) {
  ?>
  <br />
  <div id="GACLCommands">
    <button dojoType="dijit.form.Button" id="GACLAdd" type="button" onclick="location='<?php print $GLOBALS['H_ADDGACL_location'] . "&id=" . $_GET['id']; ?>'">
      <img src="./images/icons/user/user_add.png" alt="Add GACL">Add GACL</img>
    </button>  
  </div>
  
  <?php
    }
  ?>

<?php
    } // PrintGACL
    
    public function PrintUsers() {
      $query = "select * from $GLOBALS[DB_PREFIX]users";
      $rs    = $this->G->DB->Execute($query);
      foreach ($rs as $record) {
        if ($record['parent_id'] != 0) {
          $parent = $this->GetContactByUserID($record['parent_id']);
        } else {
          $parent['fname'] = "NONE";
          $parent['lname'] = "SET";
        }        
        $user_contact = $this->GetContactByUserID($record['id']);
	$url          = $GLOBALS['H_EDITUSER_location'];
	$edituser     = "javascript:document.location='{$url}&id={$record['id']}'";
	print "<tr onclick=\"$edituser\"><td>{$record['id']}</td><td>{$user_contact['fname']} {$user_contact['lname']}</td><td>{$parent['fname']} {$parent['lname']}</td><td>{$record['email']}</td></tr>";
      } // foreach record
    } // PrintUsers
    
    public function PrintForm() {
?>
<div class="dbtable" align="center">
  <table 
      id="userlist"
      cellpadding="0" 
      cellspacing="0" 
      border="0"
  >
    <thead>
      <tr>
        <th field="id" datatype="String" align="left">ID</th>
        <th field="name" datatype="String" align="left">Name</th>
        <th field="parent" datatype="String" align="left">Parent</th>
        <th field="email" datatype="String" align="left">E-Mail</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $this->PrintUsers();
      ?>
    </tbody>
  </table>
</div>
<div id="userlistCommands">
  <button dojoType="dijit.form.Button" id="userlistAdd" onclick="location='<?php print $GLOBALS['H_ADDUSER_location']; ?>'">
    <img src="./images/icons/user/user_add.png" alt="Add User">Add User</img>
  </button>  
</div>
<?php
    } // PrintForm
    
  } // UserList 
  
  if ($_GET['location'] == 'H_USERLIST') {
    $UserList = new UserList();
    $UserList->PrintForm();
  }
?>
