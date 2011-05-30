<?php  
  include_once($GLOBALS['I_BASE']);

  class AddUser extends Base {
    public $G;
    public $uid;
    public $U;
    public $C;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct

    public function HandleFormSubmission() {
      $query = "insert into $GLOBALS[DB_PREFIX]contact_information (fname, lname, mname, phone1, phone2) values ('{$_POST['fname']}', '{$_POST['lname']}', '{$_POST['mname']}', '{$_POST['phone1']}', '{$_POST['phone2']}')";
      $this->G->DB->Execute($query);
      $contact_id = mysql_insert_id();
      $query = "insert into $GLOBALS[DB_PREFIX]users (email, password, type, contact_id) values ('{$_POST['email']}', '{$_POST['password']}', '{$_POST['type']}', '$contact_id')";
      $this->G->DB->Execute($query);
      $id = mysql_insert_id();
      print "User has been added.<br/>";
      header("Location: " . $GLOBALS['H_EDITUSER_location'] . "&id=" . $id);
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div 
  id="AddUserDialog" 
>
  <h3><img src="./images/icons/user/user_add.png" alt="" /> Add New User:</h3>
  <div class="formQuestion">
    <form method="post" action="<?php print $GLOBALS['H_ADDUSER_location']; ?>" id="AddUserForm">
      <table class="formAnswer" style="width: 100%;">
        <tbody>
          <tr>
            <td width="30%"><font color="red">*</font>E-Mail:</td>
            <td><input type="text" id="email" name="email" value="<?php $this->G->PrintTextValue('email', ''); ?>" /></td>
          </tr><tr>
            <td><font color="red">*</font>Password:</td>
            <td><input type="text" id="password" name="password" value="<?php $this->G->PrintTextValue('password', ''); ?>" /></td>
          </tr><tr>
            <td><font color="red">*</font>Account Type:</td>
            <td>
              <select id="type" name="type">
                <option value="admin" <?php $this->G->PrintSelectedValue('type', 'admin', ''); ?>>Administrator</option>
                <option value="normal" <?php $this->G->PrintSelectedValue('type', 'normal', ''); ?>>Normal User</option>
              </select>
            </td>
          </tr><tr>			
            <td>First Name:</td>
            <td><input type="text" id="fname" name="fname" value="<?php $this->G->PrintTextValue('fname', ''); ?>" /></td>
          </tr><tr>
            <td>Last Name:</td>
            <td><input type="text" id="lname" name="lname" value="<?php $this->G->PrintTextValue('lname', ''); ?>" /></td>
          </tr><tr>
            <td>Middle Initial</td>
            <td><input type="text" id="mname" name="mname" value="<?php $this->G->PrintTextValue('mname', ''); ?>" /></td>
          </tr><tr>
            <td>Phone 1</td>
            <td><input type="text" id="phone1" name="phone1" value="<?php $this->G->PrintTextValue('phone1', ''); ?>" /></td>
          </tr><tr>
            <td>Phone 2</td>
            <td><input type="text" id="phone2" name="phone2" value="<?php $this->G->PrintTextValue('phone2', ''); ?>" /></td>
          </tr><tr>
            <td colspan="2" align="center">
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
    
  } // AddUser
  
  $AddUser = new AddUser();
  
  if (isset($_POST['submit'])) {
    $AddUser->HandleFormSubmission();
  } else {
    $AddUser->PrintForm();
  } 
?>