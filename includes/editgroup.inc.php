<?php  

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
    
  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  include_once($GLOBALS['H_USERLIST']);
  
  // include_once($GLOBALS['I_BASE']);

if (!isset($_GET['id'])) {
  print "Sorry, you must have a group ID set to go here.<br/>";
  exit;
}

  $accessArray   = $this->ACL->GetGroupACL($_GET['id'], $_SESSION['id']);
  if (sizeof($accessArray) <= 0) {
    print "Sorry, you have no access to view this group.<br/>";
    exit;
  }
  
  class EditGroup extends Base {
    public $G;
    public $gid;
    public $Group;
    private $GroupList;
    private $ContentList;
    private $UserList;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
      $this->GroupList = new GroupList();
      $this->ContentList = new ContentList();
      $this->UserList = new UserList();
      $this->GetGroupInformation();

    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function HandleDeleteGroup($groupid) {
      // recursively delete all children, content, etc.  Add this in after finishing project 1.
      if (in_array("dgroup", $this->G->ACL->GetGroupACL($groupid, $_SESSION['id']))) {
        $this->DeleteGroup($groupid);
        print "Group has been deleted.<br/>";
        // $this->GetGroupInformation();
      } else {
        print "You do not have permission to delete this group.<br/>";
      }
    } // HandleDeleteGroup
    
    public function HandleFormSubmission() {
      // No.
      if (in_array("cgroup", $this->G->ACL->GetGroupACL($_GET['id'], $_SESSION['id']))) {
        $query = "update $GLOBALS[DB_PREFIX]groups set description='{$_POST['description']}' where id={$_GET['id']}";
        $this->G->DB->Execute($query);
        print "Group has been updated.<br/>";
        $this->GetGroupInformation();
      } else {
        print "You do not have permission to make group changes.<br/>";
      }
    } // HandleFormSubmission
    
    public function PrintForm() {
?>
<div id="EditGroupTabContainer" dojoType="dijit.layout.TabContainer" style="width: 99%; height: 45em;">
  <div dojoType="dijit.layout.ContentPane" title="Group">
    <table class="formAnswer">
      <tbody>
        <tr>
          <td colspan="2">
            <div
              dojoType="dijit.layout.BorderContainer"
              design="sidebar"
              liveSplitters="false"
              style="border: 1px solid #bfbfbf; float: left; width: 100%; height: 400px;"
            >
              <div dojoType="dijit.layout.ContentPane" region="left" splitter="true" style="width: 400px;">
                <?php
                  $this->GroupList->PrintTree($this->gid);
                ?>
              </div>
              <div dojoType="dijit.layout.ContentPane" region="center">
                <?php
                  $this->ContentList->PrintForm($this->gid);
                ?>
              </div>
            </div>
          </td>
        </tr><tr>
          <td colspan="2" align="right">
<?php
  $module = $GLOBALS['Modules']->ModulesByLinkLocation("H_EDITGROUP");
  if (is_array($module)) {
    while(list($key,$val) = each ($module)) {
?>
  <button dojoType="dijit.form.Button" id="GroupListCMD<?php print $key; ?>" onclick="javascript:new_window('mod', '<?php print $GLOBALS[$val->module_location . '_location'] . "&id=" . $_GET['id']; ?>', 800, 600);">
    <img src="./images/icons/user/user_add.png" alt="<?php print $val->module_name; ?>"><?php print $val->module_name; ?></img>
  </button>
<?php
    }
  }
?>
            <div dojoType="dijit.form.Button" align="right">
                Publish Selected
                <script type="dojo/method" event="onClick" args="evt">
                  var http_request = false;
                  function makePOSTRequest(url, parameters) {
                    http_request = false;
                    if (window.XMLHttpRequest) { // Mozilla, Safari,...
                      http_request = new XMLHttpRequest();
                      if (http_request.overrideMimeType) {
                        // set type accordingly to anticipated content type
                        //http_request.overrideMimeType('text/xml');
                        http_request.overrideMimeType('text/html');
                      }
                    } else if (window.ActiveXObject) { // IE
                      try {
                        http_request = new ActiveXObject("Msxml2.XMLHTTP");
                      } catch (e) {
                        try {
                          http_request = new ActiveXObject("Microsoft.XMLHTTP");
                        } catch (e) {}
                      }
                    }
                    if (!http_request) {
                      alert('Cannot create XMLHTTP instance');
                      return false;
                    }
                    
                    http_request.onreadystatechange = alertContents;
                    http_request.open('POST', url, true);
                    http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    http_request.setRequestHeader("Content-length", parameters.length);
                    http_request.setRequestHeader("Connection", "close");
                    http_request.send(parameters);
                    return true;
                  }
                  
                  function alertContents() {
                    if (http_request.readyState == 4) {
                      if (http_request.status == 200) {
                        //alert(http_request.responseText);
                        result = http_request.responseText;
                        document.getElementById('ajaxdresponse').innerHTML = result;
                      } else {
                        alert('There was a problem with the request.');
                      }
                      
                      // var theGrid  = dijit.byId('grid');
                      // var theStore = new dojo.data.ItemFileReadStore({url: '<?php print $GLOBALS['H_JSONCONTENTLIST_location'] . "&id=$this->gid&print=1"; ?>'});
                      // theGrid.setStore(theStore, {}, null);
                    }
                  }

                    function publishContentListItem(cid) {
                       var poststr = 'cid=' + cid + '&submit=Submit&publish_content=1';
                       // encodeURI( document.getElementById("mytextarea1").value ) + "&mytextarea2=" + encodeURI( document.getElementById("mytextarea2").value );
                       makePOSTRequest('<?php print $GLOBALS['H_AJAXD_location']; ?>', poststr);
                    }

                    var grid  = dijit.byId('grid');
                    var items = grid.selection.getSelected();
                    if (items.length) {
                        dojo.forEach(items, function(selectedItem) {
                            if (selectedItem !== null) {
                                var value = grid.store.getValues(selectedItem, 'cid');
                                // alert('attribute: id ' + ', value: ' + value);
                                publishContentListItem(value);
                            }
                        });
                        
                      var theGrid  = dijit.byId('grid');
                      var theStore = new dojo.data.ItemFileReadStore({url: '<?php print $GLOBALS['H_JSONCONTENTLIST_location'] . "&id=$this->gid&print=1"; ?>'});
                      theGrid.setStore(theStore, {}, null);

                    }
                </script>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <span name="ajaxdresponse" id="ajaxdresponse"></span>

  </div>

  <div dojoType="dijit.layout.ContentPane" title="Details">
    <div
      dojoType="dijit.layout.ContentPane"
      class="box"
      hasShadow="true"
      id="cpEditGroupDetails"
      href="<?php print $GLOBALS['H_EDITGROUPDETAILS_location'] . "&id=" . $this->Group['id']; ?>"
    ></div>
  </div>

  <div
    dojoType="dijit.layout.ContentPane"
    title="Add Group"
    class="box"
    hasShadow="true"
    id="cpAddGroup"
    href="<?php print $GLOBALS['H_ADDGROUP_location'] . "&parent_id=" . $_GET['id']; ?>"
  ></div>

  <div
    dojoType="dijit.layout.ContentPane"
    title="Add Content"
    class="box"
    hasShadow="true"
    id="cpAddContent"
    href="<?php print $GLOBALS['H_ADDCONTENT_location'] . "&id=" . $_GET['id']; ?>"
  ></div>  
  
  <div
    dojoType="dijit.layout.ContentPane"
    title="Upload Content"
    class="box"
    hasShadow="true"
    id="cpUploadContent"
    href="<?php print $GLOBALS['H_UPLOADCONTENT_location'] . "&id=" . $_GET['id']; ?>"
  ></div>
    
  <?php
    if (in_array("auser", $this->G->ACL->GetGroupACL($this->Group['id'], $_SESSION['id']))) {
  ?>
  <div dojoType="dijit.layout.ContentPane" title="Group Permissions">
    <table class="formAnswer">
      <tbody>
        <tr>
          <td><b>User Group Permissions</b> [<a class="help" href="<?php print $GLOBALS['H_HELP_location'] . "&help_id=1"; ?>">♥</a>]</td>
        </tr><tr>
          <td>
            <div class="containerX">
              <?php $this->UserList->PrintGACL($this->gid); ?>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
    <?php
      }
    ?>
  <div
    dojoType="dijit.layout.ContentPane"
    title="Media List"
    class="box"
    hasShadow="true"
    id="cpEditGroupMediaList"
    href="<?php print $GLOBALS['H_MEDIALIST_location'] . "&id=" . $this->Group['id'] . "&type=2"; ?>"
  ></div>
  <div
    dojoType="dijit.layout.ContentPane"
    title="Search &amp; Replace"
    class="box"
    hasShadow="true"
    id="cpEditGroupOperations"
    href="<?php print $GLOBALS['H_SEARCHREPLACE_location'] . "&id=" . $this->Group['id']; ?>"
  ></div>
  <div
    dojoType="dijit.layout.ContentPane"
    title="Remote Publishing"
    class="box"
    hasShadow="true"
    id="cpEditGroupRemotePublishing"
    href="<?php print $GLOBALS['H_EDITREMOTEDETAILS_location'] . "&id=" . $this->Group['id']; ?>"
  ></div>
<?php
    } // PrintForm
    
  } // EditGroup
  
  $EditGroup = new EditGroup();
  
  if (isset($_POST['submit'])) {
    $EditGroup->HandleFormSubmission();
    $EditGroup->PrintForm();
  } else {
    if (isset($_POST['delete']) || isset($_GET['delete'])) {
      $EditGroup->HandleDeleteGroup($_GET['id']);
      // print "Will not delete group due to design specification.<br/>";
      $EditGroup->PrintForm();
    } else {
      $EditGroup->PrintForm();
    }
  } 
?>