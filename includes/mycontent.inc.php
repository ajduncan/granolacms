<?php

  // Here, we get a list of groups.
  
  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['H_MYGROUPS']);

  class MyContentList extends Base {
    public $G;
    public $printedGroups;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct
    
    public function PrintContent() {
      $printed = array();
      $query = "select * from $GLOBALS[DB_PREFIX]content where owner_id='{$_SESSION['id']}'";
      $rs    = $this->G->DB->Execute($query);
      foreach ($rs as $record) {
        $url           = $GLOBALS['H_EDITCONTENT_location'];
        $editgroup     = "javascript:document.location='$url&content_id={$record['id']}'";
        print "<tr onclick=\"$editgroup\"><td>{$record['id']}</td><td>{$record['name']}</td></tr>";
        array_push($printed, $record['id']);
      } // foreach record
      $query = "select * from $GLOBALS[DB_PREFIX]user_content where user_id={$_SESSION['id']} and FIND_IN_SET('ucontent', access) > 0";
      $rs    = $this->G->DB->Execute($query);
      foreach ($rs as $record) {
        if (!in_array($record['content_id'], $printed)) {
          $query         = "select * from $GLOBALS[DB_PREFIX]content where id={$record['content_id']}";
          $content_record = $this->G->DB->GetRow($query);
          $url           = $GLOBALS['H_EDITCONTENT_location'];
          $editgroup     = "javascript:document.location='$url&content_id={$record['content_id']}'";
          print "<tr onclick=\"$editgroup\"><td>{$record['content_id']}</td><td>{$content_record['name']}</td></tr>";
        }
      } // foreach record      
    } // PrintGroup
    
    public function PrintForm() {
?>
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
        $this->PrintContent();
      ?>
    </tbody>
  </table>

</div>
<?php
    } // PrintForm
  } // MyContentList

  if ($_GET['location'] == "H_MYCONTENT") {
    $MyGroupList = new MyGroupList();
    $MyContentList = new MyContentList();
    print "<div style=\"width: 99%\">";
    print "<div class=\"containerX\">";
    $MyGroupList->PrintForm();
    print "</div>";
    print "<h1>Your Content</h1>";
    print "<div class=\"containerX\">";
    $MyContentList->PrintForm();
    print "</div>";
    print "</div>";
  }
  
?>