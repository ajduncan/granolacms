<?php
 /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  // A database connector to contextual help with added features for web formatting.

  include_once($GLOBALS['I_BASE']);
    
class HelpList extends Base {

  public $G;
  public function __construct() {
    parent::__construct();
    $this->G = $GLOBALS['Globals'];    
  } // construct
  
  public function __destruct() {
    parent::__destruct();
  } // destruct
  
  // Print Help categories that are related to a particular parent:
  public function PrintHelpCategories($parent_id) {
?>
<b>Help Categories</b>
<div class="dbtable" align="center">
  <table 
      id="hclist"
      cellpadding="0" 
      cellspacing="0" 
      border="0"
  >
    <thead>
      <tr>
        <th lign="left"  width="20%">Name</th>
        <th align="left" width="80%">Description</th>
      </tr>
    </thead>
    <tbody>
<?php
    $query  = "select * from $GLOBALS[DB_PREFIX]help_category where parent=$parent_id";
    $rs    = $this->G->DB->Execute($query);
    foreach ($rs as $record) {
      $category_id          = $record['id'];
      $category_name        = $record['name'];
      if (strlen($record['description']) > 40) { 
        $category_description = substr($record['description'], 0, 40) . " ...";
      } else {
        $category_description = $record['description'];
      }
      $url                  = $GLOBALS['H_HELPLIST_location'];
      $link                 = "javascript:document.location='{$url}&category_id={$category_id}'";
      print "<tr onclick=\"$link\"><td>{$category_name}</td><td>{$category_description}</td></tr>";
    }
?>
    </tbody>
  </table>
</div>
<br/>
<?php
    $this->PrintHelpItems($parent_id);
  } // PrintHelpCategories
  
  // Print Help Items related to a category id
  public function PrintHelpItems($category_id) {
    $query  = "select count(*) from $GLOBALS[DB_PREFIX]help where category_id=$category_id";
    $record = $this->G->DB->GetRow($query);
    if ($record['count(*)'] > 0) {
?>
<b>Help Items</b>
<div class="dbtable" align="center">
  <table 
      id="hilist"
      cellpadding="0" 
      cellspacing="0" 
      border="0"
  >
    <thead>
      <tr>
        <th lign="left"  width="20%">Name</th>
        <th align="left" width="80%">Title</th>
      </tr>
    </thead>
    <tbody>
<?php
      $query  = "select * from $GLOBALS[DB_PREFIX]help where category_id=$category_id";
      $rs    = $this->G->DB->Execute($query);
      foreach ($rs as $record) {
        $help_id           = $record['id'];
        $help_title        = $record['title'];
        if (strlen($record['description']) > 40) { 
          $help_description = substr($record['description'], 0, 40) . " ...";
        } else {
          $help_description = $record['description'];
        }
        $url               = $GLOBALS['H_HELPLIST_location'];
        $link              = "javascript:document.location='{$url}&help_id={$help_id}'";
        print "<tr onclick=\"$link\"><td>{$help_title}</td><td>{$help_description}</td></tr>";
      }
?>
    </tbody>
  </table>
</div>
<div align="right">
  <input type="button" onclick="location='<?php print $GLOBALS['H_ADDHELP_location'] . "&category_id=" . $category_id; ?>'" value="Add Help" />
</div>
<?php
    } // if ($record[0] > 0) {
  } // PrintHelpItems
  
} // HelpList

$HelpList = new HelpList();

if (isset($_GET['category_id'])) {
  $HelpList->PrintHelpCategories($_GET['category_id']);  
} else {
  $HelpList->PrintHelpCategories(0);
}

?>