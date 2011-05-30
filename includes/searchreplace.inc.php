<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
    
  // include_once($GLOBALS['I_BASE']);
  
  class SearchReplace extends Base {
    public $G;
    public $gid;
    public $Group;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
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

    public function HandleFormSubmission() {
      // No.
      if (in_array("cgroup", $this->G->ACL->GetGroupACL($_GET['id'], $_SESSION['id']))) {
        $search = $_POST['search'];
        $replace = $_POST['replace'];
        
        print "Got: $search and $replace<br/>";
        
        // For the current group:
        $query = "select * from $GLOBALS[DB_PREFIX]content_groups where group_id='{$this->Group['id']}'";
        $result = $this->G->DB->Query($query);
        while ($row = $this->G->DB->Fetch($result)) {
          
        }
        
        print "Group has been updated.<br/>";
        $this->GetGroupInformation();
      } else {
        print "You do not have permission to make group changes.<br/>";
      }
    } // HandleFormSubmission
    
    public function PrintForm() {
      $action = $GLOBALS['H_SEARCHREPLACE_location'] . "&id=" . $this->Group['id'];
?>
<form method="post" action="<?php print $action ?>" id="EditSearchReplace" dojoType="dijit.form.Form">
  <table class="formAnswer">
    <tbody>
      <tr>
        <td>Search for:</td>
        <td>
          <input
            type="text"
            name="search"
            value=""
          />
        </td>
      </tr><tr>
        <td>Replace with:</td>
        <td>
          <input
            type="text"
            name="replace"
            value=""
          />
        </td>
      </tr><tr>
        <td colspan="2" valign="top">
          <button type="submit" name="submit" value="submit">
            <img src="./images/icons/database/database_save.png" alt="Search &amp; Replace">Search &amp; Replace</img>
          </button>
        </td>
    </tbody>
  </table>
</form>
<?php
    }
  } // SearchReplace

  $SearchReplace = new SearchReplace();

  if (isset($_POST['submit'])) {
    $SearchReplace->HandleFormSubmission();
    $SearchReplace->PrintForm();
  } else {
    $SearchReplace->PrintForm();
  } 
?>