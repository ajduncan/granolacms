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

  class JSONContentList extends Base {
    public $G;

    public function __construct($print) {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      if (trim($print)) {
        $this->PrintJSON();
      }
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
    } // GetIconSt2yle

    public function GetContentType($type) {
      switch($type) {
        case '1':
          $r = "Document";
          break;
        case '2':
          $r = "Media";
          break;
        case '3':
          $r = "Template";
          break;
        case '4':
          $r = "Stylesheet";
          break;
        default:
          $r = "Unknown";
      }
      return($r);
    } // GetContentType

    public function GetAlphaListing($directory) {
      $files          = array();
      $directories    = array();
      if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
          if (
                $file != "." &&
                $file != ".."
          ) {
            if (is_dir($directory . $file)) {
              $directories[] = $file;
            } else {
              $files[] = $file;
            }
          }
        }
        closedir($handle);
      }
      asort($files);
      asort($directories);
      return(array_merge($files, $directories));
    }

    public function GetContentNames($groupnode) {
      $query = "
                  select
                    $GLOBALS[DB_PREFIX]content_groups.content_id,
                    $GLOBALS[DB_PREFIX]content_groups.group_id,
                    $GLOBALS[DB_PREFIX]content.filename
                  from
                    $GLOBALS[DB_PREFIX]content_groups,
                    $GLOBALS[DB_PREFIX]content
                  where
                    $GLOBALS[DB_PREFIX]content.id = $GLOBALS[DB_PREFIX]content_groups.content_id and
                    $GLOBALS[DB_PREFIX]content_groups.group_id = '$groupnode'
                  order by
                    $GLOBALS[DB_PREFIX]content.name
                ";
      $rs    = $this->G->DB->Execute($query);
      $a     = array();
      while($r = $rs->FetchRow()) {
        array_push($a, basename($r['filename']));
      }
      return($a);
    } // GetContentNames
    
    public function PrintDirectoryContents($path, $groupnode) {
      $arrayNames   = $this->GetContentNames($groupnode);
      $arrayListing = $this->GetAlphaListing($path);
      $url          = $GLOBALS['H_EDITCONTENT_location'];
      $directory    = $this->GetGroupLocation($groupnode) . "/";
      $icon         = "icon_unmanaged";
      $index        = 0;
      $printed      = 0;
      if ($groupnode > 0) {
        $group_record = $this->GetGroupRecord($groupnode);
        $group_name   = $group_record['name'];
      } else {
        $group_name   = "undefined";
      }

      $count = sizeof($arrayListing);
      for ($i = 0; $i < $count -1; $i++) {
        $file = $arrayListing[$i];
        if (!is_dir($path . "/" . $file)) {
          // does this file exist in content?
          $name = split("\.", $file);
          $name = strtolower($name[0]);
          if (!in_array($name, $arrayNames)) {
            $index++;
            $encoded_filename = base64_encode($file);
            $editcontent   = "javascript:document.location='{$url}&id={$_GET['id']}&file=yes&name=$encoded_filename'";
            if ($printed == 0) {
              // print ",";
              $printed = 1;
            } else {
              print ",";
            }
            
            print "{ id: 'unmanaged.$group_name.$index', cid:'$index', name:'{$name}', type:'Unmanaged', directory:'$directory', filename:'$file', icon:'{$icon}', saved:'x', published:'N' }";
          }
        }
      }
      $file = $arrayListing[$i];      
      if (!is_dir($path . "/" . $file)) {
        // does this file exist in content?
        $name = split("\.", $file);
        $name = strtolower($name[0]);
        if (!in_array($name, $arrayNames)) {
          $index++;
          $encoded_filename = base64_encode($file);
          $editcontent   = "javascript:document.location='{$url}&id={$_GET['id']}&file=yes&name=$encoded_filename'";
          if ($printed == 0) {
            // print ",";
            $printed = 1;
          } else {
            print ",";
          }
          print "{ id: 'unmanaged.directory.$index', cid:'$index', name:'{$name}', type:'Unmanaged', directory:'$directory', filename:'$file', icon:'{$icon}', saved:'x', published:'N' }";
        }
      }

    } // PrintDirectoryContents

    public function PrintImageDirectoryContents($path, $groupnode) {
      $arrayNames   = $this->GetContentNames($groupnode);
      $arrayListing = $this->GetAlphaListing($path);
      $url          = $GLOBALS['H_EDITCONTENT_location'];
      $directory    = $this->GetGroupLocation($groupnode) . "/";
      $icon         = "icon_unmanaged";
      $index        = 0;
      $printed      = 0;

      $count = sizeof($arrayListing);
      for ($i = 0; $i < $count -1; $i++) {
        $file = $arrayListing[$i];
        if (!is_dir($path . "/" . $file)) {
          // does this file exist in content?
          $name = split("\.", $file);
          $name = strtolower($name[0]);
          $ext        = substr($file, strrpos($file, '.') + 1);
  
          if (
                (
                  $ext == "png" ||
                  $ext == "jpg" ||
                  $ext == "gif" ||
                  $ext == "jpeg"
                ) &&
                !in_array($name, $arrayNames)
          )
          {
            $index++;
            $encoded_filename = base64_encode($file);
            $editcontent   = "javascript:document.location='{$url}&id={$_GET['id']}&file=yes&name=$encoded_filename'";
            if ($printed == 0) {
              // print ",";
              $printed = 1;
            } else {
              print ",";
            }
            if (file_exists(CONTENT_PATH . $file)) {
              $size       = getimagesize(CONTENT_PATH . $file);
            } else {
              $size     = array(0, 0);
            }
            $height     = $size[0];
            $width      = $size[1];
            $imagepath  = CONTENT_URL . $this->GetGroupLocation($groupnode) . "/" . basename($file);
            $thumb      = $imagepath;
            $large      = $imagepath;
            $title      = $c_record['name'];
            $link       = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $groupnode . "&file=yes&name=" . $encoded_filename;
            print "{ thumb:'{$thumb}', large:'{$large}', title:'{$title}', link:'{$link}' }";
          }
        }
      }

      $file = $arrayListing[$i];      
      if (!is_dir($path . "/" . $file)) {
        $name = split("\.", $file);
        $name = strtolower($name[0]);
        $ext        = substr($file, strrpos($file, '.') + 1);

        if (
              (
                $ext == "png" ||
                $ext == "jpg" ||
                $ext == "gif" ||
                $ext == "jpeg"
              ) &&
              !in_array($name, $arrayNames)
        )
        {
          $index++;
          $encoded_filename = base64_encode($file);
          $editcontent   = "javascript:document.location='{$url}&id={$_GET['id']}&file=yes&name=$encoded_filename'";
          if ($printed == 0) {
            // print ",";
            $printed = 1;
          } else {
            print ",";
          }
          if (file_exists(CONTENT_PATH . $file)) {
            $size       = getimagesize(CONTENT_PATH . $file);
          } else {
            $size     = array(0, 0);
          }
          $height     = $size[0];
          $width      = $size[1];
          $imagepath  = CONTENT_URL . $this->GetGroupLocation($groupnode) . "/" . basename($file);
          $thumb      = $imagepath;
          $large      = $imagepath;
          $title      = $c_record['name'];
          $link       = $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $groupnode . "&file=yes&name=" . $encoded_filename;
          print ",{ thumb:'{$thumb}', large:'{$large}', title:'{$title}', link:'{$link}' }";
        }
      }
    } // PrintImageDirectoryContents
    
    public function PrintJSONImageContentList($groupnode) {
      // $path         = CONTENT_PATH . $this->GetGroupLocation($groupnode) . "/";
      $query = "
                  select
                    $GLOBALS[DB_PREFIX]content_groups.content_id,
                    $GLOBALS[DB_PREFIX]content_groups.group_id,
                    $GLOBALS[DB_PREFIX]content.name
                  from
                    $GLOBALS[DB_PREFIX]content_groups,
                    $GLOBALS[DB_PREFIX]content
                  where
                    $GLOBALS[DB_PREFIX]content.id = $GLOBALS[DB_PREFIX]content_groups.content_id and
                    $GLOBALS[DB_PREFIX]content_groups.group_id = '$groupnode' and
                    $GLOBALS[DB_PREFIX]content.type = '2'
                  order by
                    $GLOBALS[DB_PREFIX]content.name
                ";
      $rs    = $this->G->DB->Execute($query);
      $count = $rs->RecordCount();
      for ($i=0; $i < $count - 1; $i++) {
        $record = $rs->FetchRow();
        $content_id = $record['content_id'];
        $type       = $this->GetContentTypeFromID($content_id);
        $query      = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
        $c_record   = $this->G->DB->GetRow($query);
        $type       = $c_record['type'];
        $file       = $c_record['filename'];
        $name       = $c_record['name'];
        $ext        = substr($file, strrpos($file, '.') + 1);

        if (
              $ext == "png" ||
              $ext == "jpg" ||
              $ext == "gif" ||
              $ext == "jpeg"
        ) {
          if (file_exists(CONTENT_PATH . $file)) {
            $size       = getimagesize(CONTENT_PATH . $file);
          } else {
            $size     = array(0, 0);
          }
          $height     = $size[0];
          $width      = $size[1];
          $imagepath  = CONTENT_URL . $this->GetGroupLocation($groupnode) . "/" . basename($file);
          $thumb      = $imagepath;
          $large      = $imagepath;
          $title      = $c_record['name'];
          $link       = $GLOBALS['H_EDITCONTENT_location'] . "&content_id=" . $c_record['id'];
          print "{ thumb:'{$thumb}', large:'{$large}', title:'{$title}', link:'{$link}' },";
        }
      } // foreach

        $record = $rs->FetchRow();
        $content_id = $record['content_id'];
        $type       = $this->GetContentTypeFromID($content_id);
        $query      = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
        $c_record   = $this->G->DB->GetRow($query);
        $type       = $c_record['type'];
        $file       = $c_record['filename'];
        $name       = $c_record['name'];
        $ext        = substr($file, strrpos($file, '.') + 1);

        if (
              $ext == "png" ||
              $ext == "jpg" ||
              $ext == "gif" ||
              $ext == "jpeg"
        ) {
          if (file_exists(CONTENT_PATH . $file)) {
            $size       = getimagesize(CONTENT_PATH . $file);
          } else {
            $size     = array(0, 0);
          }
          $height     = $size[0];
          $width      = $size[1];
          $imagepath  = CONTENT_URL . $this->GetGroupLocation($groupnode) . "/" . basename($file);
          $thumb      = $imagepath;
          $large      = $imagepath;
          $title      = $c_record['name'];
          $link       = $GLOBALS['H_EDITCONTENT_location'] . "&content_id=" . $c_record['id'];
          print "{ thumb:'{$thumb}', large:'{$large}', title:'{$title}', link:'{$link}' }";
        }
      $path         = CONTENT_PATH . $this->GetGroupLocation($groupnode) . "/";
      $this->PrintImageDirectoryContents($path, $groupnode);
    } // PrintJSONImageContentList

    public function PrintJSONContentList($groupnode, $printed) {
      $query = "select * from $GLOBALS[DB_PREFIX]content_groups where group_id='$groupnode'";
      $rs    = $this->G->DB->Execute($query);
      $count = $rs->RecordCount();
      if ($count > 0) {
        for ($i = 0; $i < $count -1; $i++) {
          $record   = $rs->FetchRow();
          $query    = "select * from $GLOBALS[DB_PREFIX]content where id='{$record['content_id']}'";
          $content  = $this->G->DB->GetRow($query);
          $query    = "select id,revision,timestamp from $GLOBALS[DB_PREFIX]content_revisions where content_id='{$record['content_id']}' order by revision DESC limit 1";
          $content_revision = $this->G->DB->GetRow($query);
          $icon     = $this->GetIconStyle($content['type'], $content['filename']);
          $type     = $this->GetContentType($content['type']);
          $saved    = $content_revision['timestamp'];
          if ($content_revision['id'] == $content['published_revision']) {
            $published = "Y";
          } else {
            $published = "N";
          }
          $filename = end(explode("/", $content['filename']));
          $directory = str_replace($filename, "", $content['filename']);
          print "{ id: '$groupnode.{$content['id']}', cid:'{$content['id']}', name:'{$content['name']}', type:'{$type}', directory:'{$directory}', filename:'{$filename}', icon:'{$icon}', saved:'{$saved}', published:'{$published}' },";
        }
        $record   = $rs->FetchRow();
        $query    = "select * from $GLOBALS[DB_PREFIX]content where id='{$record['content_id']}'";
        $content  = $this->G->DB->GetRow($query);
        $query    = "select id,revision,timestamp from $GLOBALS[DB_PREFIX]content_revisions where content_id='{$record['content_id']}' order by revision DESC limit 1";
        $content_revision = $this->G->DB->GetRow($query);
        $icon     = $this->GetIconStyle($content['type'], $content['filename']);
        $type     = $this->GetContentType($content['type']);
        $saved    = $content_revision['timestamp'];
        if ($content_revision['id'] == $content['published_revision']) {
          $published = "Y";
        } else {
          $published = "N";
        }
        $filename = end(explode("/", $content['filename']));
        $directory = str_replace($filename, "", $content['filename']);

        print "{ id: '$groupnode.{$content['id']}', cid:'{$content['id']}', name:'{$content['name']}', type:'{$type}', directory:'{$directory}', filename:'{$filename}', icon:'{$icon}', saved:'{$saved}', published:'{$published}' }";
        $printed = 1;
      } // count > 0

      $path = CONTENT_PATH . $this->GetGroupLocation($groupnode) . "/";
      // if we have unmanaged content, then:
      if ($this->HasUnmanaged($groupnode, $path)) {
        if ($printed == 1) {
          print ",";
        }
        $this->PrintDirectoryContents($path, $groupnode);        
      }

    } // PrintJSONContentList

    public function HasUnmanaged($groupnode, $path) {
      $arrayNames   = $this->GetContentNames($groupnode);
      $arrayListing = $this->GetAlphaListing($path);
      $arrayDiff    = array_diff($arrayListing, $arrayNames);
      if (sizeof($arrayDiff) > 0) {
        return(1);
      } else {
        return(0);
      }
    } // HasUnmanaged

    public function PrintJSON() {
      if (isset($_GET['imagelist'])) {
        $head = "{ items: [\n";
        print $head;
        $this->PrintJSONImageContentList($_GET['id']);
        print "]}";
      } else {
        $head = "{ identifier: 'id',\nlabel: 'name',\nitems: [\n";
        print $head;
        $this->PrintJSONContentList($_GET['id'], 0);
        print "]}";
      }
    }

  } // JSONContentList

  if (isset($_GET['print'])) {
    $JSONContentList = new JSONContentList("1"); 
  }
?>
