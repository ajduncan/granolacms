<?php

  // Here, we get a list of groups.

  if (!isset($_SESSION['authenticated'])) {
    return(1);
  }
  
  if (!isset($_GET['id'])) {
    // In the future, check to make sure group with id 1 is really the 'root node' somehow.
    $_GET['id'] = 1;
  }
  
  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['I_RS2JSON']);

  class GroupList extends Base {
    public $G;
    public $gid;

    public function __construct() {
      parent::__construct();
      $this->gid = $_GET['id'];
      $this->G = $GLOBALS['Globals'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct

    public function PrintTree($groupnode) {
?>
<script type="text/javascript">
function ProcessTreeRequest(node, details) {
  var cid       = groupStore.getValue(node.item, "cid");
  var gid       = groupStore.getValue(node.item, "gid");
  var type      = groupStore.getValue(node.item, "type");
  var filename  = groupStore.getValue(node.item, "filename");

  if (type == "Unmanaged") {
    document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->gid . "&file="; ?>' + base64_encode(filename);
  } else {
    if (cid != null) {
      if (details != null) {
        document.location='<?php print $GLOBALS['H_EDITCONTENTDETAILS_location'] . "&content_id="; ?>' + cid;
      } else {
        document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&content_id="; ?>' + cid;
      }
    }
    if (gid != null) {
      if (details != null) {
        document.location='<?php print $GLOBALS['H_EDITGROUPDETAILS_location'] . "&id="; ?>' + gid;
      } else {
        document.location='<?php print $GLOBALS['H_EDITGROUP_location'] . "&id="; ?>' + gid;
      }
    }
  }
} // ProcessTreeRequest

function DeleteTreeItem(node) {
  var cid       = groupStore.getValue(node.item, "cid");
  var gid       = groupStore.getValue(node.item, "gid");
  var type      = groupStore.getValue(node.item, "type");
  var filename  = groupStore.getValue(node.item, "filename");

  if (type == "group") {
    var message = "Are you SURE you want to delete this group, all sub groups and all content?";
  } else {
    var message = "Are you SURE you want to delete this content item?";
  }
  
  var choice = confirm(message);
  
  if (choice) {
    if (type == "Unmanaged") {
      document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->gid . "&file="; ?>' + base64_encode(filename) + "&delete=m";
    } else {
      if (cid != null) {
        document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&content_id="; ?>' + cid + '&delete=m';
      }
      if (gid != null) {
        document.location='<?php print $GLOBALS['H_EDITGROUP_location'] . "&id="; ?>' + gid + '&delete=m';
      }
    }
  } else {
    return(1);
  }  
} // PreviewTreeItem

function PreviewTreeItem(node) {
  var gid      = groupStore.getValue(node.item, "gid");
  var filename = groupStore.getValue(node.item, "filename");
  
  if (filename != null) {
    var iframe = document.getElementById('previewframe');
    iframe.src = '<?php print $this->GetGroupLocation($this->gid); ?>/' + filename;
    // iframe.reload(true);
    // var test   = document.getElementById('previewframe');
    dijit.byId('PreviewTree').show();
  }
} // PreviewTreeItem

var treeOnContextClick = function (e) {
  var menu        = dijit.getEnclosingWidget(e.target);
  var action      = menu.treeMenuAction;
  var tNode       = dijit.byId('tree2').curNode;
  var tNodeItem   = tNode.item;
  alert('Hey, got: ' + tNodeItem);
  return true;
}

</script>

  <span dojoType="dijit.Dialog" id="PreviewTree" title="Preview" style="width: 90%; height: 90%">
    <iframe id="previewframe" src="" width="99%" height="90%"></iframe><br/>
    <button dojoType="dijit.form.Button" type="submit">OK</button>
  </span>

  <div dojoType="dojo.data.ItemFileReadStore" jsId="groupStore" url="<?php print $GLOBALS['H_JSONGROUPLIST_location'] . "&id=" . $groupnode; ?>"></div>
<!--
  Define the menu that will pop up
  <li dojoType="dijit.MenuItem" iconClass="icon_edit_details" onClick="ProcessTreeRequest(dijit.byId('tree_menu').currentTreeNode, '1');">Edit Details</li>
-->
<ul dojoType="dijit.Menu" id="tree_menu" style="display: none;">
  <li dojoType="dijit.MenuItem" iconClass="icon_edit" onClick="ProcessTreeRequest(dijit.byId('tree_menu').currentTreeNode, null);">Edit</li>
  <li dojoType="dijit.MenuItem" iconClass="icon_preview" onClick="PreviewTreeItem(dijit.byId('tree_menu').currentTreeNode);">Preview</li>
  <li dojoType="dijit.MenuItem" iconClass="icon_delete" onClick="DeleteTreeItem(dijit.byId('tree_menu').currentTreeNode);">Delete</li>  
</ul>
<div dojoType="dijit.Tree" id="tree2" store="groupStore" query="{type:'group'}" labelAttr="name" typeAttr="type">
  <script type="dojo/method" event="getIconClass" args="item, opened">
    return (!item || groupStore.getValue(item, "icon"));
  </script>
  <script type="dojo/method" event="dblclick">
    alert('double-click!');
  </script>  
  <script type="dojo/connect">
    var menu = dijit.byId("tree_menu");
    // when we right-click anywhere on the tree, make sure we open the menu
    menu.bindDomNode(this.domNode);

    dojo.connect(menu, "_openMyself", this, function(e){
      // get a hold of, and log out, the tree node that was the source of this open event
      var tn = dijit.getEnclosingWidget(e.target); // this._domElement2TreeNode(e.target);
      menu.currentTreeNode = tn;
      console.debug(tn);

      // now inspect the data store item that backs the tree node:
      console.debug(tn.item);
      
      // contrived condition: if this tree node doesn't have any children, disable all of the menu items
      // menu.getChildren().forEach(function(i){ i.setDisabled(!tn.item.children); });
    });
    dojo.connect(dijit.byId("tree2").domNode, "ondblclick", function(e){
      var menu        = dijit.getEnclosingWidget(e.target);
      var tNode       = dijit.getEnclosingWidget(e.target); // dijit.byId('tree2').curNode;
      var cid       = groupStore.getValue(tNode.item, "cid");
      var gid       = groupStore.getValue(tNode.item, "gid");
      var type      = groupStore.getValue(tNode.item, "type");
      var filename  = groupStore.getValue(tNode.item, "filename");
    
      if (type == "Unmanaged") {
        document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&id=" . $this->gid . "&file="; ?>' + base64_encode(filename);
      } else {
        if (cid != null) {
          document.location='<?php print $GLOBALS['H_EDITCONTENT_location'] . "&content_id="; ?>' + cid;
        }
        if (gid != null) {
          document.location='<?php print $GLOBALS['H_EDITGROUP_location'] . "&id="; ?>' + gid;
        }
      }
      return true;
    }); 
  </script>
</div>
<?php
    } // PrintTree
    
    public function PrintGroups($groupnode) {
      $query = "select * from $GLOBALS[DB_PREFIX]groups where id='$groupnode'";
      $rs    = $this->G->DB->Execute($query);
      foreach ($rs as $record) {
        if ($record['parent_id'] != 0) {
          $parent = $this->GetParentNameByGroupID($record['parent_id']);
        } else {
          $parent['name'] = "no parent";
        }        
	$url           = $GLOBALS['H_EDITGROUP_location'];
	$editgroup     = "javascript:document.location='{$url}&id={$record['id']}'";
	print "<tr onclick=\"$editgroup\"><td>{$record['id']}</td><td>{$record['name']}</td><td>{$parent['name']}</td></tr>";
      } // foreach record
    } // PrintGroups
    
    public function PrintForm($groupnode) {
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
        <th field="parent" align="left">Parent</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $this->PrintGroups($groupnode);
      ?>
    </tbody>
  </table>
</div>
  <?php
    if (in_array("cgroup", $this->G->ACL->GetGroupACL($groupnode, $_SESSION['id']))) {
  ?>
  <br />
  <div id="GroupListCommands">
    <button id="GroupListAdd" onclick="javascript:document.location='<?php print $GLOBALS['H_ADDGROUP_location'] . "&parent_id=" . $_GET['id']; ?>'">
      <img src="./images/icons/user/user_add.png" alt="Add Group">Add Group</img>
    </button>  
  </div>
  <?php
    }
  ?>
<?php
    } // PrintForm
  } // GroupList

  if ($_GET['location'] == "H_GROUPLIST") {
    // default top level group id is 1 
    if (!isset($_GET['id'])) {
      $_GET['id'] = 1;
    }

    $GroupList = new GroupList();
    if (isset($_GET['tree'])) {
      $GroupList->PrintTree($_GET['tree']);
    } else {
      $GroupList->PrintForm($_GET['id']);
    }
  }
  
?>