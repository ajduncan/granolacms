<?php  

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
    
  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class EditGroup extends Base {
    public $G;
    public $gid;
    public $Group;
    private $GroupList;
    private $ContentList;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
      $this->GroupList = new GroupList();
      $this->ContentList = new ContentList();
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
        $query = "update $GLOBALS[DB_PREFIX]groups set description='{$_POST['description']}', header='{$_POST['header_template']}', footer='{$_POST['footer_template']}', stylesheet='{$_POST['stylesheet']}' where id={$_GET['id']}";
        // print "Got query: $query<br/>";
        $this->G->DB->Execute($query);
        print "Group has been updated.<br/>";
        $this->GetGroupInformation();
      } else {
        print "You do not have permission to make group changes.<br/>";
      }
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<form dojoType="dijit.form.Form" method="post" action="<?php print $GLOBALS['H_EDITGROUPDETAILS_location'] . "&id=" . $this->Group['id']; ?>" id="EditGroupForm">
  <table class="formAnswer">
    <tbody>
      <tr>
        <td width="50" valign="top">Location:</td>
        <td><?php print $this->GetGroupLocation($this->Group['id']); ?></td>
      </tr><tr>
        <td>Name:</td>
        <td><?php print $this->Group['name']; ?></td>
      </tr><tr>
        <td width="30%">Default Header Template:</td>
        <td>
          <select name="header_template">
            <option value="0" <?php $this->G->PrintDBSelectedValue("header_template", "0", $this->Group['header'], ""); ?>>Group Inherit</option>
            <?php $this->ContentList->PrintTemplateOptions($this->Group['id'], "header_template", $this->Group['header']);  ?>
            <option value="-1">----------</option>
            <?php $this->ContentList->PrintGlobalTemplates("header_template", $this->Group['header']); ?>
          </select>
        </td>
      </tr><tr>
        <td width="30%">Default Footer Template:</td>
        <td>
          <select name="footer_template">
            <option value="0" <?php $this->G->PrintDBSelectedValue("footer_template", "0", $this->Group['footer'], ""); ?>>Group Inherit</option>
            <?php $this->ContentList->PrintTemplateOptions($this->Group['id'], "footer_template", $this->Group['footer']);  ?>
            <option value="-1">----------</option>
            <?php $this->ContentList->PrintGlobalTemplates("header_template", $this->Group['header']); ?>
          </select>
        </td>
      </tr><tr>
        <td width="30%">Stylesheet:</td>
        <td>
          <select name="stylesheet">
            <option value="0" <?php $this->G->PrintDBSelectedValue("stylesheet", "0", $this->Group['stylesheet'], "0"); ?>>Group Inherit</option>
            <?php $this->ContentList->PrintStylesheetOptions($this->Group['id'], "stylesheet", $this->Group['stylesheet']);  ?>
          </select>
        </td>
      </tr><tr>
        <td>Parent:</td>
        <td>
          <?php
            if ($this->Group['parent_id'] != 0) {
              $url  = $GLOBALS['H_EDITGROUP_location'] . "&id=" . $this->Group['parent_id'];
              $name = $this->GetParentNameByGroupID($this->Group['id']);
              print "<a href=\"$url\">" . $name['name'] . "</a>";
            } else {
              print "No Parent.";
            }
          ?>
        </td>
      </tr><tr>
        <td width="30%">Access:</td>
        <td><?php
              $accessArray   = $this->ACL->GetGroupACL($this->Group['id'], $_SESSION['id']);
              $access_images = $this->ImageListFromACL($accessArray);
              print $access_images;

              // print implode(",", $this->ACL->GetGroupACL($this->Group['id'], $_SESSION['id']));
              
          ?></td>
      </tr><tr>
        <td valign="top">Description:</td>
        <td>
          <?php
            if (in_array("cgroup", $this->G->ACL->GetGroupACL($this->Group['id'], $_SESSION['id']))) {
          ?>
          <input type="text" size="50" name="description" value="<?php $this->G->PrintDBTextValue('description', $this->Group['description'], ''); ?>" />
          <?php
            } else {
              print $this->Group['description'];
            }
          ?>
        </td>
      </tr><tr>
        <td colspan="2" align="left">
          <?php
            if (in_array("cgroup", $this->G->ACL->GetGroupACL($this->Group['id'], $_SESSION['id']))) {
          ?>
            <button type="submit" name="submit" value="submit" dojoType="dijit.form.Button">
              <img src="./images/icons/database/database_save.png" alt="Save">Save</img>
            </button>
          <?php
            }
          ?>
        </td>
      </tr>
    </tbody>
  </table>
</form>
<?php
    } // PrintForm
    
  } // EditGroup
?>
<?php
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