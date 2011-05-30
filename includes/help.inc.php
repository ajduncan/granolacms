<?php
 /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  // A database connector to contextual help with added features for web formatting.

  include_once($GLOBALS['I_BASE']);
    
class Help extends Base {

  public $G;
  public function __construct() {
    parent::__construct();
    $this->G = $GLOBALS['Globals'];
    if (isset($_GET['id'])) {
      $this->helpID = $_GET['id'];
    } else {
      $this->helpID = 1;
    }
  } // construct
  
  public function __destruct() {
    parent::__destruct();
  } // destruct

  public function PrintHelpItem($help_id) {
    $query = "select * from $GLOBALS[DB_PREFIX]help where id=$help_id";
    $record = $this->G->DB->GetRow($query);
    if (isset($record['title']) && isset($record['description'])) {
     print "<b>" . $record['title'] . "</b><br/><br/>";
     print $record['description'];
    }
  } // PrintHelpItem

  public function PrintHelpCategory($category_id, $index) {
    
    // Print out the category name and description:
    
    $query  = "select * from $GLOBALS[DB_PREFIX]help_category where id=$category_id";
    $record = $this->G->DB->GetRow($query);
    $category_name        = $record['name'];
    $category_description = $record['description'];
    
    print "<b>" . $category_name . "</b><br/><br/>";
    print $category_description . "<br/><br/>";
    
    // Next, print the links to any help documents for this direct category:
    print "<b>Related Help:</b><br/>";
    print "<ol>";
    $query = "select * from $GLOBALS[DB_PREFIX]help where category_id=$category_id order by title";
    $rs    = $this->G->DB->Execute($query);
    foreach ($rs as $record) {
      $help_id    = $record['id'];
      $help_title = $record['title'];
      $help_link  = "<a href=\"" . $GLOBALS['H_HELP_location'] . "&help_id=" . $help_id . "\">$help_title</a>";
      // $GLOBALS['REF_SITE'] . "?location=";
      print "<li>$help_link</li>";
    }
    print "</ol>";

    // Finally, print out links to the other help categories which are children of this:
    $query = "select * from $GLOBALS[DB_PREFIX]help_category where parent=$category_id order by name";
    $rs    = $this->G->DB->Execute($query);

    print "<b>Related Categories:</b><br/>";
    print "<ol>";
    foreach ($rs as $record) {
      $category_name  = $record['name'];
      $category_id    = $record['id'];
      $category_link  = "<a href=\"" . $GLOBALS['H_HELP_location'] . "&category_id=" . $category_id . "\">$category_title</a>";
      print "<li>" . $category_link . "</li>";
    }
    print "</ol>";
  } // PrintHelpCategory

  public function PrintHelp() {
    $query = "select * from $GLOBALS[DB_PREFIX]help where id=$this->helpID";
    $record = $this->G->DB->GetRow($query);
    print "<b>" . $record['title'] . "</b><br/><br/>";
    print $record['description'];
    // Search functionality.  Comprehensive level stuffs.
    
    // Get the top level help category;
    $query = "select * from $GLOBALS[DB_PREFIX]help_category where parent=0";
    $record = $this->G->DB->GetRow($query);
    
    $tlid = $record['id'];
    $this->PrintHelpCategory($tlid, 0);
  } // PrintHelp

} // Help

if ($_GET['location'] == "H_HELP") {
?>
<div style="font-size: 48pt;">[<a class="help" href="<?php print $GLOBALS['H_HELP_location'] . "&help_id=1"; ?>">♥</a>]</div>
<?php
  $Help = new Help();
  if (isset($_GET['category_id'])) {
    $Help->PrintHelpCategory($_GET['category_id'], 0);
  }
  
  if (isset($_GET['help_id'])) {
    $Help->PrintHelpItem($_GET['help_id']);
  }
  
  if ($_SESSION['type'] == "admin") {
?>
<br/>&nbsp;<br/>
<br/>&nbsp;<br/>
    <a href="<?php print $GLOBALS['H_HELPLIST_location'] . "&category_id=1"; ?>">Help List</a>
<?php
  } // if admin
} // if location not equal to help
?>