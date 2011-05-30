<?php

  // Here, we get a list of groups.
  
  include_once($GLOBALS['I_BASE']);

  class MyGroupList extends Base {
    public $G;
    public $printedGroups;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->printedGroups = array();
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct
    
    public function PrintGroups() {
      $query  = "select * from $GLOBALS[DB_PREFIX]user_groups where user_id='{$_SESSION['id']}'";
      $rs     = $this->G->DB->Execute($query);
      $this->printedGroups = array();
      foreach ($rs as $r => $record) {
        if (!in_array($record['group_id'], $this->printedGroups)) {
          if ($this->PrintGroup($record['group_id']) != -1) {
            if (!in_array($record['group_id'], $this->printedGroups)) {
              array_push($this->printedGroups, $record['group_id']);
            }
          }
        }
      }
    } // PrintGroups
    
    public function PrintGroup($gid) {
      $query = "select * from $GLOBALS[DB_PREFIX]user_groups where user_id='{$_SESSION['id']}' and group_id='$gid'";
      $rs    = $this->G->DB->Execute($query);
      if ($rs->RecordCount() == 0) {
        $query = "select * from $GLOBALS[DB_PREFIX]groups where id='$gid'";
        $record = $this->G->DB->GetRow($query);
        if ($record['parent_id'] == 0) {
          return(-1);
        } else {
          return($this->PrintGroup($record['parent_id']));
        }
      }
      foreach ($rs as $record) {
        // Get the group record;
        $group = $this->GetGroupRecord($record['group_id']);
        if (
            !in_array("vcontent", explode(",", $record['access'])) &&
            $group['parent_id'] != 0
        ) {
          return($this->PrintGroup($group['parent_id']));
        } else {
          if ($group['parent_id'] == 0) {
            if (in_array("vcontent", explode(",", $record['access']))) {
              if (in_array($group['id'], $this->printedGroups)) {
                return($group['id']);
              }

              $url           = $GLOBALS['H_EDITGROUP_location'];
              $editgroup     = "javascript:document.location='{$url}&id={$group['id']}'";
              print "<tr onclick=\"$editgroup\"><td>{$group['id']}</td><td>{$group['name']}</td></tr>";
              array_push($this->printedGroups, $group['id']);
              return($group['id']);
            } else {
              return(-1);
            }
          } else {
            $rez = $this->PrintGroup($group['parent_id']);
            if ($rez == -1) {
              if (in_array("vcontent", explode(",", $record['access']))) {                
                if (in_array($group['id'], $this->printedGroups)) {
                  return($group['id']);
                }
                $url           = $GLOBALS['H_EDITGROUP_location'];
                $editgroup     = "javascript:document.location='{$url}&id={$group['id']}'";
                print "<tr onclick=\"$editgroup\"><td>{$group['id']}</td><td>{$group['name']}</td></tr>";
                array_push($this->printedGroups, $group['id']);
                return($group['id']);
              } else {
                return(-1);
              }
            } else {
              return($rez);
            }
          }
        }        
      } // foreach record
    } // PrintGroup
    
    public function PrintForm() {
?>
  Top level groups for which you are a member:
  <br /><br />
  
<div class="dbtable" align="center">
  <table 
      id="GroupList"
      cellpadding="0" 
      cellspacing="0" 
      border="0"
  >
    <thead>
      <tr>
        <th field="id" align="left" width="5%">ID</th>
        <th field="name" align="left">Name</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $this->PrintGroups();
      ?>
    </tbody>
  </table>

</div>
<?php
    } // PrintForm
  } // MyGroupList

  if ($_GET['location'] == "H_MYGROUPS") {
    $MyGroupList = new MyGroupList();
    $MyGroupList->PrintForm();
  }
  
?>