<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  class ACL {

    public function __construct() {
    } // construct

    public function __destruct() {
    } // destruct

    public function GetGroupACL($gid, $uid, $acl = array()) {
      $query = "select * from $GLOBALS[DB_PREFIX]user_groups where group_id='$gid' and user_id='$uid'";
      $rs    = $GLOBALS['Globals']->DB->Execute($query);
      if ($rs->RecordCount() == 0) {
        // Does this group have parents?
        $query  = "select parent_id from $GLOBALS[DB_PREFIX]groups where id='$gid'";
        $record = $GLOBALS['Globals']->DB->GetRow($query);
        if (isset($record['parent_id'])) {
          $parent_id = $record['parent_id'];
        } else {
          $parent_id = "";
        }

        $record = $rs->FetchRow();

        if ($parent_id == 0) {
          if (isset($record['access'])) {
            $acl = explode(",", $record['access']);
          }
          return($acl);
        } else {
          // If it does have a parent, then check the ACL for this user in the group above.
          $c_acl = explode(",", $record['access']);
          return($this->GetGroupACL($record['parent_id'], $uid, array_merge($c_acl, $acl)));
        }
      } else {
        // Traverse up the tree entirely.
        $record = $rs->FetchRow();
        $c_acl = explode(",", $record['access']);
        $query  = "select parent_id from $GLOBALS[DB_PREFIX]groups where id='$gid'";
        $record = $GLOBALS['Globals']->DB->GetRow($query);
        if ($record['parent_id'] == 0) {
          return(array_merge($c_acl, $acl));
        } else {
          return($this->GetGroupACL($record['parent_id'], $uid, array_merge($c_acl, $acl)));          
        }
      }
    } // GetGroupACL
    
    public function GetContentACL($cid, $uid) {
      if (!isset($cid)) {
        return(array("ucontent", "pcontent", "dcontent", "vcontent"));
      } else {
        $query = "select * from $GLOBALS[DB_PREFIX]user_content where content_id='$cid' and user_id='$uid'";
        $rs    = $GLOBALS['Globals']->DB->Execute($query);
        // Note, content is specific to group, can't have the same content_id in multiple groups
        $query = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='$cid'";
        $record = $GLOBALS['Globals']->DB->GetRow($query);
        if ($rs->RecordCount() == 0) {
          return($this->GetGroupACL($record['group_id'], $uid, array()));
        } else {
          // If you're the owner, you should be able to do anything.
          $group_id = $record['group_id'];
          $record = $rs->FetchRow();         
          return(
            $this->GetGroupACL(
              $group_id,
              $uid,
              explode(",", $record['access'])
            )
          );
        }
      }
    } // GetContentACL


  } // ACL
   
?>
