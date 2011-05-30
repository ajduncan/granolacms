<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  // Base class containing functions useful to all classes.
  include_once($GLOBALS['I_ACL']);

  class Base {
  
    public $ACL;
    public $C_ACCESS;
    public $G_ACCESS;
    
    public function __construct() {
      $this->ACL = new ACL();
      if (isset($_GET['content_id']) && isset($_SESSION['id'])) {
        $this->C_ACCESS = $this->ACL->GetContentACL($_GET['content_id'], $_SESSION['id']);
      } else {
        $this->C_ACCESS = array();
      }
      if (
        isset($_GET['id']) &&
        isset($_SESSION['id'])
      ) {
        $this->G_ACCESS = $this->ACL->GetGroupACL($_GET['id'], $_SESSION['id']);
      }
    } // construct
    
    public function __destruct() {
    } // destruct

    public function GetContentRevision($content_id, $revision) {
      if (trim($revision)) {
        $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$content_id' and revision='{$revision}'";
      } else {
        $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$content_id' order by revision DESC";
      }
      return($this->G->DB->GetRow($query));
    } // GetContentRevision

    public function GetStylesheet($group_id, $content_id, $revision) {
      if ($content_id == 0) {
        // Go through groups to find stylesheet.
        $query = "select * from $GLOBALS[DB_PREFIX]groups where id='$group_id'";
        $group = $this->G->DB->GetRow($query);
        if ($group['stylesheet'] != 0) {
          return($this->GetContentRevision($group['stylesheet'], ""));
        } else {
          if ($group['parent_id'] != 0) {
            return($this->GetStylesheet($group['parent_id'], 0, ""));
          }
        }
      } else {
        return($this->GetContentRevision($content_id, $revision));
      }
    }

    public function DeleteContent($content_id, $usefile, $filepath) {
      if ($usefile != 1) {
        $query      = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
        $content    = $this->G->DB->GetRow($query);
        $content_id = $content['id'];
        $name       = $content['name'];
        $type       = $content['type'];
        $filename   = "." . $content['filename'];
        
        $query = "delete from $GLOBALS[DB_PREFIX]content_revisions where content_id='$content_id'";
        $this->G->DB->Execute($query);
        $query = "delete from $GLOBALS[DB_PREFIX]user_content where content_id='$content_id'";
        $this->G->DB->Execute($query);
        $query = "delete from $GLOBALS[DB_PREFIX]content_groups where content_id='$content_id'";
        $this->G->DB->Execute($query);
        
        unlink($filename);
  
        // $this->content_id = -1;
        return(1);
      } else {
        
        if (!unlink($filepath)) {
          // error deleting.
          return(-1);
        }
        return(1);
      }
    } // DeleteContent
    
    public function DeleteGroup($groupid)
    {
      $this->DeleteGroupChildren($groupid);
      $this->DeleteGroupContent($groupid);     
      $directory = CONTENT_PATH . $this->GetGroupLocation($groupid) . "/";
      
      // Unlink the directory?
      rmdir($directory);
      
      // Finally, delete all instances of this group from the db:
      
      $query = "delete from $GLOBALS[DB_PREFIX]groups where id=$groupid";
      $this->G->DB->Execute($query);
      $query = "delete from $GLOBALS[DB_PREFIX]content_groups where group_id=$groupid";
      $this->G->DB->Execute($query);
      $query = "delete from $GLOBALS[DB_PREFIX]user_groups where group_id=$groupid";
      $this->G->DB->Execute($query);
      
    } // DeleteGroup
    
    public function DeleteGroupChildren($groupid) {
      
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where parent_id='$groupid'";
      $rs     = $this->G->DB->Execute($query);
      $rows   = $rs->GetRows();
      foreach ($rows as $row) {
        $this->DeleteGroupContent($row['id'], 0, null);
        $this->DeleteGroupChildren($row['id']);
        $directory = CONTENT_PATH . $this->GetGroupLocation($row['id']) . "/";
        rmdir($directory);      
      }
    } // DeleteGroupChildren
    
    public function DeleteGroupContent($groupid) {
      // First, delete the db file content:
      $query  = "select * from $GLOBALS[DB_PREFIX]content_groups where group_id='$groupid'";
      $rs     = $this->G->DB->Execute($query);
      $rows   = $rs->GetRows();
      foreach ($rows as $row) {
        $this->DeleteContent($row['content_id'], 0, null);
      }
      // Now all unmanaged content:
      $directory = CONTENT_PATH . $this->GetGroupLocation($this->Group['id']) . "/";
      if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
          if (
                $file != "." &&
                $file != ".."
          ) {
            if (!is_dir($directory . $file)) {
              unlink($directory . $file);
            }
          }
        }
        closedir($handle);
      }
    } // DeleteGroupContent

    public function GetHeaderTemplate($group_id, $content_id, $revision) {
      if ($content_id == 0) {
        // Go through groups to find stylesheet.
        $query = "select * from $GLOBALS[DB_PREFIX]groups where id='$group_id'";
        $group = $this->G->DB->GetRow($query);
        if ($group['header'] != 0) {
          return($this->GetContentRevision($group['header'], ""));
        } else {
          if ($group['parent_id'] != 0) {
            return($this->GetHeaderTemplate($group['parent_id'], 0, ""));
          }
        }
      } else {
        return($this->GetContentRevision($content_id, $revision));
      }
    }

    public function GetFooterTemplate($group_id, $content_id, $revision) {
      if ($content_id == 0) {
        // Go through groups to find stylesheet.
        $query = "select * from $GLOBALS[DB_PREFIX]groups where id='$group_id'";
        $group = $this->G->DB->GetRow($query);
        if ($group['footer'] != 0) {
          return($this->GetContentRevision($group['footer'], ""));
        } else {
          if ($group['parent_id'] != 0) {
            return($this->GetFooterTemplate($group['parent_id'], 0, ""));
          }
        }
      } else {
        return($this->GetContentRevision($content_id, $revision));
      }
    }

    public function PrintContentRevisionList() {
?>
<select name="contentrevisions" onchange="location.href=this.options[this.selectedIndex].value" size="10">
<?php
    $query = "select published_revision from $GLOBALS[DB_PREFIX]content where id='$this->content_id'";
    $pr    = $this->G->DB->GetRow($query);
    $query = "select id, user_id, timestamp, revision from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' order by revision DESC";
    $rs  = $this->G->DB->Execute($query);
    $rows = $rs->GetRows();
    $published = "";
    foreach ($rows as $row) {
      $contact = $this->GetContactByUserID($row['user_id']);
      $user = $contact['fname'] . " " . $contact['lname'];
      $timestamp = $row['timestamp'];
      $url = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->Group['id'] . "&content_id=" . $this->content_id . "&revision=" . $row['revision'];
      if ($pr['published_revision'] == $row['id']) {
        $published = " PUBLISHED";
      } else {
        $published = "";
      }
      print "<option value=\"$url\">Revision #" . $row['revision'] . ", (" . $user . " @ " . $timestamp . ")" . $published . "</option>";
    }
?>
</select>
<?php
    } // PrintContentRevisionList

    public function ImageListFromACL($accessArray) {
      if (!is_array($accessArray)) {
        return("");
      }
      
      // Todo: Abstract this section.
      $access_images = "";
      if (in_array("cgroup", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/group_add.png\" alt=\"Add Group\" />";
      }
      if (in_array("dgroup", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/group_delete.png\" alt=\"Delete Group\" />";
      }
      if (in_array("auser", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/user_add.png\" alt=\"Add User\" />";
      }
      if (in_array("duser", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/user_delete.png\" alt=\"Delete User\" />";
      }
      if (in_array("vcontent", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/content.png\" alt=\"View Content\" />";
      }
      if (in_array("ucontent", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/content_edit.png\" alt=\"Update Content\" />";
      }
      if (in_array("pcontent", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/content_publish.png\" alt=\"Publish Content\" />";
      }
      if (in_array("dcontent", $accessArray)) {
        $access_images .= "<img src=\"./images/icons/acl/content_delete.png\" alt=\"Delete Content\" />";
      }
      
      return($access_images);

    } // ImageListFromACL

    // Clean this up for internationalization.
    public function FormatContentName($name) {
      $name = strtolower($name);
      $name = str_replace(" ", "_", $name);
      $name = str_replace("!", "", $name);
      $name = str_replace("@", "", $name);
      $name = str_replace("#", "", $name);
      $name = str_replace("$", "", $name);
      $name = str_replace("%", "", $name);
      $name = str_replace("^", "", $name);
      $name = str_replace("&", "", $name);
      $name = str_replace("*", "", $name);
      $name = str_replace("(", "", $name);
      $name = str_replace(")", "", $name);
      $name = str_replace("=", "", $name);
      $name = str_replace("+", "", $name);
      $name = str_replace("/", "", $name);
      $name = str_replace("\\", "", $name);
      $name = str_replace("?", "", $name);
      return($name);
    } 

    public function ResetPostVariables() {
      while(list($key,$val) = each($_POST)) {
        unset($_POST[$key]);
      }
    }

    public function GetGroupRecord($groupid) {
      $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$groupid}'";
      return ($this->G->DB->GetRow($query));
    } // GetGroupRecord
    
    public function GetGroupLocation($groupid) {      
      $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$groupid}'";
      $rs    = $this->G->DB->GetRow($query);
      $name  = $rs['name'];
      $parent_id = $rs['parent_id'];
      
      if ($parent_id == 0) {
        return("/" . $name);
      } else {
        $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$parent_id}'";
        $rs    = $this->G->DB->GetRow($query);
        $parent_name  = $rs['name'];
        if ($rs['parent_id'] != 0) {
          return($this->GetGroupLocation($rs['parent_id']) . "/" . $parent_name . "/" . $name);
        } else {
          // Should never get to this case?
          return("/" . $parent_name . "/" . $name);
        }
      }
    } // GetParents

    public function IsValidGroupID($group_id) {
      $query   = "select count(*) from " . $GLOBALS['DB_PREFIX'] . "groups where id='$group_id'";
      $rs      = $this->G->DB->GetOne($query);
      if ($rs[0] > 0) {
        return (true);
      } else {
        return (false);
      }
    } // IsValidGroupID

    public function CreateNewGroup($name, $parent_id, $description) {
      $query = "insert into $GLOBALS[DB_PREFIX]groups (name, parent_id, description) values ('$name', '$parent_id', '$description')";
      $this->G->DB->Execute($query);
      return($this->G->DB->Insert_ID());
    } // CreateNewGroup
    
    public function GetParentNameByGroupID($group_id) {
      try {
        $query   = "select * from $GLOBALS[DB_PREFIX]groups where id='$group_id'";
        $group   = $this->G->DB->GetRow($query);

        if ($group['parent_id'] != 0) {
          // Ensure that information exists;
          $query  = "select count(*) from $GLOBALS[DB_PREFIX]groups where id={$group['parent_id']}";
          $record = $this->G->DB->Execute($query);
          if ($record->RecordCount() == 0) {
            $query = "insert into $GLOBALS[DB_PREFIX]groups (name, description, id) values ('Undefined', 'Please rename this group.', '$group[parent_id]')";
            $this->G->DB->Execute($query);
          }
          $query   = "select * from " . $GLOBALS['DB_PREFIX'] . "groups where id='$group[parent_id]'";
          $grouprs = $this->G->DB->GetRow($query);
          return($grouprs);
        } else {
          return(array("name" => $group['name']));
        }
      } catch (exception $e) {
        print_r($e);
        return(-1);
      } // except      
    } // GetParentNameByGroupID

    public function GetContent($content_id) {
      $query = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
      return($this->G->DB->GetRow($query));
    } // GetContent
    
    public function GetContentIDFromFile($file) {
      $query           = "
                          select
                            $GLOBALS[DB_PREFIX]content.id as id
                          from
                            $GLOBALS[DB_PREFIX]content,
                            $GLOBALS[DB_PREFIX]content_groups
                          where
                            $GLOBALS[DB_PREFIX]content.name='$file' and
                            $GLOBALS[DB_PREFIX]content.id=$GLOBALS[DB_PREFIX]content_groups.content_id and
                            $GLOBALS[DB_PREFIX]content_groups.group_id=$this->gid
                        ";
      $result = $this->G->DB->GetRow($query);
      if (trim($result['id'])) {
        return($result['id']);
      } else {
        return(-1);
      }
    } // GetContentIDFromFile

    public function GetContentTypeFromID($content_id) {
      $query = "select type from $GLOBALS[DB_PREFIX]content where id='$content_id'";
      $result = $this->G->DB->GetRow($query);
      return($result['type']);
    }

    public function GetContentOwner($content_id) {
      $query = "select owner_id from $GLOBALS[DB_PREFIX]content where id=$content_id";
      $result = $this->G->DB->GetRow($query);
      if (trim($result['owner_id'])) {
        $contact = $this->GetContactByUserID($result['owner_id']);
        return($contact['fname'] . " " . $contact['lname']);
      } else {
        return("None Found");
      }
    } // GetContentOwner
    
    public function GetContactByUserID($user_id) {
      try {
        $query   = "select * from " . $GLOBALS['DB_PREFIX'] . "users where id='$user_id'";
        $user    = $this->G->DB->GetRow($query);

        // Ensure that information exists;
        $query  = "select count(*) from $GLOBALS[DB_PREFIX]contact_information where id='{$user['contact_id']}'";
        $record = $this->G->DB->Execute($query);
        if ($record->RecordCount() == 0) {
          $query = "insert into $GLOBALS[DB_PREFIX]contact_information (fname, lname, id) values ('NOT', 'SET', '$user[contact_id]')";
          $this->G->DB->Execute($query);
        }
        $query   = "select * from " . $GLOBALS['DB_PREFIX'] . "contact_information where id='{$user['contact_id']}'";
        $contact = $this->G->DB->GetRow($query);
        return($contact);  
      } catch (exception $e) {
        print_r($e);
        return(-1);
      } // except
    } // GetContactByUserID

    public function GetSiteLocation($gid) {
      $location = $this->GetGroupLocation($gid);
      $location = str_replace(MAIN_GROUP_PATH, "", $location);
      return($location);
    } // GetSiteLocation

    public function PrintError($error) {
      $data = <<< EOF
<div id="error">
$error;
</div>
EOF;
      print $data;
    } // PrintError

    public function GetContentPath($content_id) {
      // Get the name of this content (filename).
      $query          = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
      $content        = $this->G->DB->GetRow($query);
      $filename       = $content['filename'];
      return($filename);
    }

  } // Base

?>
