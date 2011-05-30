<?php
  
  if (!isset($_GET['id'])) {
    print "No user ID supplied.<br/>";
    exit;
  }
  
  include_once($GLOBALS['I_BASE']);

  class EditUser extends Base {
    public $G;
    public $uid;
    public $U;
    public $C;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->uid = $_GET['id'];
      $this->U = array();
      $this->C = array();
      $this->GetUserInformation();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct
    
    public function GetUserInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]users where id={$this->uid}";
      $record = $this->G->DB->GetRow($query);
      $this->U = $record;
      
      if ($record['parent_id'] != 0) {
        $parent = $this->GetContactByUserID($record['parent_id']);
      } else {
        $parent['fname'] = "NONE";
        $parent['lname'] = "SET";
      }
      $this->C = $this->GetContactByUserID($record['id']);
    } // GetUserInformation

    public function DeleteUser() {
      $query = "delete from $GLOBALS[DB_PREFIX]contact_information where id='{$this->C['id']}'";
      $this->G->DB->Execute($query);
      $query = "delete from $GLOBALS[DB_PREFIX]users where id='{$_GET['id']}'";  
      $this->G->DB->Execute($query);
    } // DeleteUser
    
    public function HandleFormSubmission() {
      $query = "update $GLOBALS[DB_PREFIX]users set email='{$_POST['email']}', password='{$_POST['password']}', type='{$_POST['type']}' where id={$_GET['id']}";
      $this->G->DB->Execute($query);
      $query = "update $GLOBALS[DB_PREFIX]contact_information set fname='{$_POST['fname']}', lname='{$_POST['lname']}', mname='{$_POST['mname']}', phone1='{$_POST['phone1']}', phone2='{$_POST['phone2']}' where id={$this->C['id']}";
      $this->G->DB->Execute($query);
      print "User has been updated.<br/>";
      $this->GetUserInformation();
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div 
  id="edituserDialog" 
>
  <div class="formQuestion">
    <h3><img src="./images/icons/user/user_edit.png" alt="" /> Edit Existing User:</h3>
    <form method="post" action="<?php print $GLOBALS['H_EDITUSER_location'] . "&id=" . $this->U['id']; ?>" id="edituserForm">
      <input type="hidden" id="eduser_id" name="eduser_id" value="<?php print $this->U['id']; ?>" />
      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td width="30%"><font color="red">*</font>E-Mail:</td>
            <td><input type="text" id="email" name="email" value="<?php $this->G->PrintDBTextValue('email', $this->U['email'], ''); ?>" /></td>
          </tr><tr>
            <td><font color="red">*</font>Password:</td>
            <td><input type="text" id="password" name="password" value="<?php $this->G->PrintDBTextValue('password', $this->U['password'], ''); ?>" /></td>
          </tr><tr>
            <td><font color="red">*</font>Account Type:</td>
            <td>
              <select id="type" name="type">
                <option value="admin" <?php $this->G->PrintDBSelectedValue('type', 'admin', $this->U['type'], ''); ?>>Administrator</option>
                <option value="normal" <?php $this->G->PrintDBSelectedValue('type', 'normal', $this->U['type'], ''); ?>>Normal User</option>
              </select>
            </td>
          </tr><tr>			
            <td>First Name:</td>
            <td><input type="text" id="fname" name="fname" value="<?php $this->G->PrintDBTextValue('fname', $this->C['fname'], ''); ?>" /></td>
          </tr><tr>
            <td>Last Name:</td>
            <td><input type="text" id="lname" name="lname" value="<?php $this->G->PrintDBTextValue('lname', $this->C['lname'], ''); ?>" /></td>
          </tr><tr>
            <td>Middle Initial</td>
            <td><input type="text" id="mname" name="mname" value="<?php $this->G->PrintDBTextValue('mname', $this->C['mname'], ''); ?>" /></td>
          </tr><tr>
            <td>Phone 1</td>
            <td><input type="text" id="phone1" name="phone1" value="<?php $this->G->PrintDBTextValue('phone1', $this->C['phone1'], ''); ?>" /></td>
          </tr><tr>
            <td>Phone 2</td>
            <td><input type="text" id="phone2" name="phone2" value="<?php $this->G->PrintDBTextValue('phone2', $this->C['phone2'], ''); ?>" /></td>
          </tr><tr>
            <td colspan="2" align="center">
              <button type="submit" name="delete" value="delete">
                <img src="./images/icons/user/user_delete.png" alt="Delete">Delete</img>
              </button>  				  
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
    
  } // EditUser
  
  $EditUser = new EditUser();
  
  if (isset($_POST['submit'])) {
    $EditUser->HandleFormSubmission();
    $EditUser->PrintForm();
  } else {
    if (isset($_POST['delete'])) {
      $EditUser->DeleteUser();
      print "User has been deleted.";
    } else {
      $EditUser->PrintForm();
    }
  } 
?>