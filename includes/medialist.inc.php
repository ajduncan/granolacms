<?php  

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */
      
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
  
  class MediaList extends Base {
    public $G;
    public $gid;
    public $Group;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      $this->Group = array();
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
    
    public function PrintForm() {
?>
<div jsId="imageItemStore" dojoType="dojo.data.ItemFileReadStore" url="<?php print $GLOBALS['H_JSONCONTENTLIST_location'] . "&id=" . $this->Group['id'] . "&imagelist=1&print=1"; ?>"></div>
<div id="gallery1" dojoType="dojox.image.Gallery">
  <script type="dojo/connect">
    var itemRequest = {
      query: {},
      count: 20
    };
    var itemNameMap = {
      imageThumbAttr: "thumb",
      imageLargeAttr: "large"
    };
    this.setDataStore(imageItemStore, itemRequest, itemNameMap);
  </script>
</div>

<?php
    } // PrintForm
    
  } // MediaList
  
  $MediaList = new MediaList();  
  $MediaList->PrintForm();
?>