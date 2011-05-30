<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  // Here, we get a list of groups.

  if (!isset($_SESSION['authenticated'])) {
    return(1);
  }
  
  if (!isset($_GET['id'])) {
    // In the future, check to make sure group with id 1 is really the 'root node' somehow.
    $_GET['id'] = 0;
  }
  
  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['I_RS2JSON']);
  include_once($GLOBALS['H_JSONCONTENTLIST']);

  class JSONGroupList extends Base {
    public $G;
    public $JSONContentList;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->JSONContentList = new JSONContentList(0);
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct

    public function GetIconStyle($type, $filename) {
      switch($type) {
        case '1':
          if (strtolower(end(explode(".", $filename))) == "php") {
            $icon = "icon_php";
          } else {
            $icon = "icon_content";
          }
          break;
        case '2':
          $icon = "icon_media";
          break;
        case '3':
          $icon = "icon_template";
          break;
        case '4':
          $icon = "icon_stylesheet";
          break;
        default:
          $icon = "icon_content";
          break;
      }
      return($icon);
    } // GetIconStyle

    public function PrintJSON($string, $index) {
    
    } // PrintJSON

    public function PrintJSONContentList($groupnode) {
      $this->JSONContentList->PrintJSONContentList($groupnode, 0);
    } // PrintJSONContentList

    public function PrintJSONGroupList($groupnode, $depth) {
      if ($depth > 1) {
        return(1);
      }
      $query         = "select id,name from $GLOBALS[DB_PREFIX]groups where id='{$groupnode}'";
      $record        = $this->G->DB->GetRow($query);

      $url           = $GLOBALS['H_EDITGROUP_location'];
      $editgroup     = "javascript:document.location='{$url}&id={$groupnode}'";
      $query         = "select count(*) from $GLOBALS[DB_PREFIX]groups where parent_id='{$groupnode}'";
      $count         = $this->G->DB->GetRow($query);
      $child_group_count = $count['count(*)'];
      $query         = "select count(*) from $GLOBALS[DB_PREFIX]content_groups where group_id='{$groupnode}'";
      $content_count = $this->G->DB->GetRow($query);
      $content_count = $content_count['count(*)'];

      if ($child_group_count == 0) {
        if ($content_count == 0) {
          print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group'}\n";
          // Note, a comma used to follow this. ...
          $this->JSONContentList->PrintJSONContentList($groupnode, 1);
        } else {
          if ($depth > 0) {
            print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group'}\n";
          } else {
            if ($content_count > 0) {
              print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group', children:[\n";
              $this->JSONContentList->PrintJSONContentList($groupnode, 0);
              print "]}\n";
            } else {
              print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group'}\n";
            }
          }
        }
      } else {
        $query              = "select count(*) from $GLOBALS[DB_PREFIX]content_groups where group_id='{$groupnode}'";
        $content_count      = $this->G->DB->GetRow($query);
        $content_count      = $content_count['count(*)'];
        
        $query              = "select count(*) from $GLOBALS[DB_PREFIX]groups where parent_id='{$groupnode}'";
        $child_group_count  = $this->G->DB->GetRow($query);
        $child_group_count  = $child_group_count['count(*)'];

        if (
          $content_count == 0 &&
          $child_group_count == 0
        ) {
          print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group'}\n";
        } else {
          if ($depth > 0) {
            print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group'}\n";
          } else {
            if ($child_group_count > 0 || $content_count > 0) {
              print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group', children:[\n";
              if ($child_group_count > 0) {
                $query       = "select id,name from $GLOBALS[DB_PREFIX]groups where parent_id='{$groupnode}' order by name";
                $rs          = $this->G->DB->Execute($query);
                $child_count = $rs->RecordCount();
                for ($i = 0; $i < $child_count - 1; $i++) {
                  $child_group = $rs->FetchRow();
                  $this->PrintJSONGroupList($child_group['id'], $depth+1);
                  print ",";
                }
                $child_group = $rs->FetchRow();
                $this->PrintJSONGroupList($child_group['id'], $depth+1);
                if ($content_count > 0) {
                  print ",";
                }
              }
              if ($content_count > 0) {
                $this->JSONContentList->PrintJSONContentList($record['id'], 0);
              }
              print "]}";
            } else {
              print "{ id: '{$record['id']}', gid:'{$record['id']}', name:'{$record['name']}', type:'group', icon:'icon_group'}\n";
            }
          }
        }
      }
    } // PrintJSONGroupList
  } // JSONGroupList

  $JSONGroupList = new JSONGroupList();
      $head = <<<EOF
{ identifier: 'id',
  label: 'name',
  items: [
EOF;
print $head;
  // Print the Parent, "..", folder:
  $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$_GET['id']}'";
  $idgroup = $JSONGroupList->G->DB->GetRow($query);
  if ($idgroup['parent_id'] > 0) {
    $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$idgroup['parent_id']}'";
    $group = $JSONGroupList->G->DB->GetRow($query);
    print "{ id: '{$group['id']}', gid:'{$group['id']}', name:'{$group['name']}', type:'group', icon:'icon_parent_group', children:[";
  }

  $JSONGroupList->PrintJSONGroupList($_GET['id'], 0);

  if ($idgroup['parent_id'] > 0) {
    // print the closing child for group list
    print "]}\n";
  }

print "]}";
?>