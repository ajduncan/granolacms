<?php

// this id corresponds to the current group id, always will.
if (!isset($_GET['id'])) {
  // In the future, check to make sure group with id 1 is really the 'root node' somehow.
  $_GET['id'] = 1;
}

include_once($GLOBALS['I_BASE']);
include_once($GLOBALS['I_RS2JSON']);

class ContentList extends Base {
  public $G;
  public $gid;
  public $content_type;

  public function __construct() {
    parent::__construct();
    $this->G = $GLOBALS['Globals'];
    $this->gid = $_GET['id'];
    if (isset($_GET['type'])) {
      $this->content_type = $_GET['type'];
    } else {
      $this->content_type = 1; // abstract default.
    }
  } // construct

  public function __destruct() {
    parent::__destruct();
  } // destruct

  public function GetAlphaListing($directory) {
    $files          = array();
    $directories    = array();
    if ($handle = opendir($directory)) {
      while (false !== ($file = readdir($handle))) {
        if (
              $file != "." &&
              $file != ".." &&
              stristr($file, '.inc.php')
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

  public function PrintDirectoryContents($path) {
    $arrayListing = $this->GetAlphaListing($path);
    $url          = $GLOBALS['H_EDITCONTENT_location'];

    foreach ($arrayListing as $file) {
      if (!is_dir($path . "/" . $file)) {
        // does this file exist in content?
        $name = split("\.", $file);
        $content_id = $this->GetContentIDFromFile($name[0]);
        $type       = $this->GetContentTypeFromID($content_id);
        if ($content_id == -1) {
          $encoded_filename = base64_encode($file);
          $editcontent   = "javascript:document.location='{$url}&id={$_GET['id']}&file=yes&name=$encoded_filename'";
          print "<tr onclick=\"$editcontent\"><td>FILE</td><td>$file</td><td>None</td><td>&nbsp;</td></tr>";
        }
      }
    } // foreach
  } // PrintDirectoryContents

  public function PrintGlobalTemplates($name, $value) {
    $query   = "select * from $GLOBALS[DB_PREFIX]content where flags='global' and type='3'";
    $rs      = $this->G->DB->Execute($query);
    foreach($rs as $content) {
      $content_id     = $content['id'];
      $file           = $content['name'];
      $type           = $content['type'];
      $query          = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='$content_id'";
      $content_group  = $this->G->DB->GetRow($query);
      $gid            = $content_group['group_id'];
      $group_path     = $this->GetGroupLocation($gid);

      if ($type == 3) {
        print "<option value=\"{$content_id}\" ";
        $this->G->PrintDBSelectedValue($name, $value, $content_id, "");
        print ">$file ($group_path)</option>";
      }
    }
  }

  public function PrintTemplateOptions($groupnode, $name, $value) {
    $url          = $GLOBALS['H_EDITCONTENT_location'];

    $query = "select * from $GLOBALS[DB_PREFIX]content_groups where group_id='$groupnode'";
    $rs    = $this->G->DB->Execute($query);
    foreach ($rs as $record) {
      $query   = "select * from $GLOBALS[DB_PREFIX]content where id='{$record['content_id']}'";
      $content = $this->G->DB->GetRow($query);
      $content_id = $content['id'];
      $file    = $content['name'];
      $type    = $content['type'];

      if ($type == 3) {
        print "<option value=\"{$content_id}\" ";
        $this->G->PrintDBSelectedValue($name, $value, $content_id, "");
        print ">$file</option>";
      }
    } // foreach
  } // PrintTemplateContents

  public function PrintStylesheetOptions($groupnode, $name, $value) {
    $url          = $GLOBALS['H_EDITCONTENT_location'];

    $query = "select * from $GLOBALS[DB_PREFIX]content_groups where group_id='$groupnode'";
    $rs    = $this->G->DB->Execute($query);
    foreach ($rs as $record) {
      $query   = "select * from $GLOBALS[DB_PREFIX]content where id='{$record['content_id']}'";
      $content = $this->G->DB->GetRow($query);
      $content_id = $content['id'];
      $file    = $content['name'];
      $type    = $content['type'];

      if ($type == 4) {
        print "<option value=\"{$content_id}\" ";
        $this->G->PrintDBSelectedValue($name, $value, $content_id, "");
        print ">$file</option>";
      }
    } // foreach
  } // PrintTemplateContents

  public function PrintContentGrid($groupnode) {
    // http://www.sitepen.com/blog/2008/07/14/dojo-12-grid/
?>
<script type="text/javascript">

  var contentLayout = [
    {name: 'ID', width: "1em", field: 'cid'},
    {name: 'Name', width: "15em", field: "name"},
    {name: 'Type', width: "5em", field: "type"},
    {name: 'Content Directory', width: "15em", field: "directory"},
    {name: 'File Name', width: "20em", field: "filename"},
    {name: 'Published', width: "5em", field: "published"}
  ];

  function contentDblClick(e) {
    var grid 		= dijit.byId('grid')
    var items   = grid.selection.getSelected();
    var content_id, filename, type;
    if (items.length) {
        dojo.forEach(items, function(selectedItem) {
            if (selectedItem !== null) {
                content_id = grid.store.getValues(selectedItem, 'cid');
                filename = grid.store.getValues(selectedItem, 'filename');
                type = grid.store.getValues(selectedItem, 'type');
            }
        });
    }

    if (type == "Unmanaged") {
      document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->gid . "&file="; ?>' + base64_encode(filename);
    } else {
      document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&content_id="; ?>' + content_id;
    }
  }
</script>
  <div dojoType="dojo.data.ItemFileReadStore" jsId="contentStore" url="<?php print $GLOBALS['H_JSONCONTENTLIST_location'] . "&id=$groupnode&print=1"; ?>"></div>
  <div dojoType="dojox.grid.data.DojoData" jsId="contentModel" rowsPerPage="20" store="contentStore" query="{}" clientSort="true"></div>
  <div dojoType="dojox.grid.DataGrid" jsId="grid" id="grid" store="contentStore" query="{}" structure="contentLayout" onDblClick="contentDblClick" style="width: 70em; height: 350px;">
    <!-- onRowDblClick="contentDblClick" -->
    <script type="dojo/connect">
      var grid = dijit.byId("grid");
      // when we right-click anywhere on the tree, make sure we open the menu
      // grid.bindDomNode(this.domNode);

      dojo.connect(grid, "_loadMyself", this, function(e){
        // get a hold of, and log out, the tree node that was the source of this open event
        var tn = dijit.getEnclosingWidget(e.target); // this._domElement2TreeNode(e.target);
        menu.currentTreeNode = tn;
        console.debug(tn);

        // now inspect the data store item that backs the tree node:
        console.debug(tn.item);

        // contrived condition: if this tree node doesn't have any children, disable all of the menu items
        // menu.getChildren().forEach(function(i){ i.setDisabled(!tn.item.children); });
      });
    </script>
  </div>
<?php
  } // PrintContentGrid

  public function PrintGroupContent($groupnode) {
    $path         = CONTENT_PATH . $this->GetGroupLocation($groupnode) . "/";
    $url          = $GLOBALS['H_EDITCONTENT_location'];

    if ($this->content_type == -1 || $this->content_type == 5) {
      $this->PrintDirectoryContents($path);
    } else {
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
                    $GLOBALS[DB_PREFIX]content_groups.group_id = '$groupnode'
                  order by
                    $GLOBALS[DB_PREFIX]content.name
                ";
      $rs    = $this->G->DB->Execute($query);
      foreach ($rs as $record) {
        $content_id = $record['content_id'];
        $type       = $this->GetContentTypeFromID($content_id);
        $query      = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
        $c_record   = $this->G->DB->GetRow($query);
        $type       = $c_record['type'];
        $file       = $c_record['filename'];
        $name       = $c_record['name'];
        $ext        = substr($file, strrpos($file, '.') + 1);

        if ($this->content_type == $type) {
          if ($this->content_type == 2) {
            if (
            $ext == "png" ||
            $ext == "jpg" ||
            $ext == "gif" ||
            $ext == "jpeg"
            ) {
              $size   = getimagesize(CONTENT_PATH . $file);
              $height = $size[0];
              $width  = $size[1];
              $imagepath = CONTENT_URL . $this->GetGroupLocation($groupnode) . "/" . basename($file);

              $img =
                "<span dojoType=\"dijit.Tooltip\" connectId=\"$name\">
                  <img src=\"$imagepath\" alt=\"$name\" />
                </span>
                <a id=\"$name\" href=\"#img\">Graphic</a>";
            }
          }
          if ($this->content_type == 1) {
            $content_revision = $this->GetContentRevision($content_id, "");
            $img = $content_revision['timestamp'];
          }
          $owner = $this->GetContentOwner($content_id);
          $editcontent   = "javascript:document.location='{$url}&id={$_GET['id']}&content_id={$content_id}'";            
          print "<tr onclick=\"$editcontent\"><td>$content_id</td><td>$name</td><td>$owner</td><td>$img</td></tr>";
        }

  // Add this 'file' to the list of files that are in the array.
      }
    }
    // First show all the files that exist but aren't part of the CMS;
  } // PrintGroupContent

  public function PrintCACL($cid) {
    $query = "select * from $GLOBALS[DB_PREFIX]user_content where content_id='$cid'";
    $rs    = $this->G->DB->Execute($query);
?>
<div class="dbtable" align="center">
  <table
      id="cacl"
      cellpadding="0"
      cellspacing="0"
      border="0"
  >
    <thead>
      <tr>
        <th field="name"  align="left">Name</th>
        <th field="parent" align="left">Access</th>
      </tr>
    </thead>
    <tbody>
<?php
    foreach ($rs as $record) {
      $user_contact  = $this->GetContactByUserID($record['user_id']);
      $url           = $GLOBALS['H_EDITCACL_location'];
      $edit          = "javascript:document.location='{$url}&id={$_GET['id']}&user_content_id={$record['id']}'";
      $access_images = $this->ImageListFromACL(explode(",", $record['access']));
      print "<tr onclick=\"$edit\"><td width=\"50%\">{$user_contact['fname']} {$user_contact['lname']}</td><td width=\"50%\">{$access_images}</td></tr>";
    }
?>
    </tbody>
  </table>
</div>
<br/>
<div id="CACLCommands">
  <button id="CACLAdd" type="button" onclick="location='<?php print $GLOBALS['H_ADDCACL_location'] . "&content_id=" . $_GET['content_id']; ?>'">
    <img src="./images/icons/user/user_add.png" alt="Add CACL">Add CACL</img>
  </button>
</div>
<?php
  } // PrintCACL

  public function PrintForm($groupnode) {
    $this->PrintContentGrid($groupnode);
    return(1);
?>
<div class="dbtable" align="center">
  <table
      id="ContentList"
      cellpadding="0"
      cellspacing="0"
      border="0"
  >
    <thead>
      <tr>
        <th field="id" align="left">ID</th>
        <th field="name" align="left">Name</th>
        <th field="parent" align="left">Parent</th>
	<th field="info" align="left">Info</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $this->PrintGroupContent($groupnode);
      ?>
    </tbody>
  </table>
</div>
<?php
  } // PrintForm
} // ContentList

if ($_GET['location'] == "H_CONTENTLIST") {
  $ContentList = new ContentList();
  $ContentList->PrintForm($_GET['id']);
}
?>
