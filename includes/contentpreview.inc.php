<?php  
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  if (!isset($_GET['id'])) {
    if (!isset($_GET['content_id'])) {
      print "Sorry, you must have either a group or content id set.<br/>";
      exit;
    } else {
      // Need to set the group id;
      $query  = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='{$_GET['content_id']}'";
      $record = $GLOBALS['Globals']->DB->GetRow($query);
      $_GET['id'] = $record['group_id'];
    }
  }

  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class PreviewContent extends Base {
    public $G;
    public $gid;
    public $content_id;
    public $Group;
   
    public $revision;
    public $new_revision;
    
    private $GroupList;
    private $ContentList;
    public $usefile;
    private $filename;
    private $name;
    private $filepath;
    private $content_revision;
    private $content_header_id;
    private $content_footer_id;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
      $this->gid = $_GET['id'];
      
      if (isset($_GET['content_id'])) {
        $this->content_id = $_GET['content_id'];
      }
      
      if (isset($_GET['revision'])) {
        $this->revision = $_GET['revision'];
      }

      $query  = "select * from $GLOBALS[DB_PREFIX]content where id='$this->content_id'";
      $result = $this->G->DB->GetRow($query);
      $this->content_header_id = $result['header'];
      $this->content_footer_id = $result['footer'];
      
      $this->Group = array();
      $this->GetGroupInformation();
      $this->usefile = 0;
      $this->ContentList = new ContentList();
      
      $query = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='$this->content_id'";
      $content_group = $this->G->DB->GetRow($query);
      $query = "select * from $GLOBALS[DB_PREFIX]groups where id='{$content_group['group_id']}'";
      $this->Group = $this->G->DB->GetRow($query);
      
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $this->G->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation
    
    private function GetContentHeader() {
      $content = $this->GetHeaderTemplate($this->Group['id'], $this->content_header_id, $this->revision);
      $content = base64_decode($content['text']);
      ob_start();
      eval($content);
      $contents = ob_get_contents();
      ob_end_clean();
      print "Got: $contents";
      return($contents);
    } // GetContentHeader
    
    private function GetContentBody() {
      if (trim($this->revision)) {
        $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' and revision='{$this->revision}'";
      } else {
        $query = "select * from $GLOBALS[DB_PREFIX]content_revisions where content_id='$this->content_id' order by revision DESC";
      }
      return($this->G->DB->GetRow($query));
    } // GetContentHeader

    private function GetContentFooter() {
      return($this->GetFooterTemplate($this->Group['id'], $this->content_footer_id, $this->revision));
    } // GetContentHeader

    public function GetSiteLocation($gid) {
      $location = $this->GetGroupLocation($gid);
      $location = str_replace("/content", "", $location);
      return($location);
    } // GetSiteLocation
    
    public function HandleFormSubmission() {
    } // HandleFormSubmission

    public function LoadContentModules($content) {
      // Read the modules config file, then load up and insert modules in the order they are specified.
      // Look up the last published revision, get the owner etc.

// pdf conversion:

// print friendly:


?>
<div align="left" id="content_modules">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
      <tr>
        <td width="50%">&nbsp;</td>
        <td align="right">
          <a href="<?php print "?location=" . $_GET['location']; ?>&print=yes">Print Friendly</a>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php
    }

    public function LoadContentPreview() {
      $content_id = $this->content_id;
      
      $query            = "select
                            $GLOBALS[DB_PREFIX]content.id as content_id,
                            $GLOBALS[DB_PREFIX]content_groups.group_id as group_id
                          from
                            $GLOBALS[DB_PREFIX]content_groups,
                            $GLOBALS[DB_PREFIX]content
                          where
                            $GLOBALS[DB_PREFIX]content_groups.content_id  = $GLOBALS[DB_PREFIX]content.id and
                            $GLOBALS[DB_PREFIX]content.id                 = $content_id";
      // print "Got query: $query<br/>";
      $record = $this->G->DB->GetRow($query);
      if (count($record) <= 0) {
        header("Status: 404 Not Found");
        header("HTTP/1.1 404 Not Found");
        print "Resource not found.<br/>";          
        exit;
      }
      $content_id = $record['content_id'];
      $page       = $this->GetPageContents($content_id);
      $this->PrintPage($page);
    } // LoadLocation
  
    public function GetPageContents($content_id) {
      $query      = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
      $record     = $this->G->DB->GetRow($query);
      $query      = "select * from $GLOBALS[DB_PREFIX]content_groups where content_id='$content_id'";
      $g_record   = $this->G->DB->GetRow($query);
      $parent_group_id = $g_record['group_id'];
  
      // if $record['public'] = 0, we need to do ACL lookups.  otherwise, it's all good.
      
      $header_template  = $record['header'];
      $footer_template  = $record['footer'];
      $header           = "";
      $footer           = "";
      $content          = "";
      
      // Get header
      $header_record  = $this->GetHeaderTemplate($parent_group_id, $header_template, "");
      $cpath          = $this->GetContentPath($header_record['content_id']);
      if (trim($cpath)) {
        // $url            = WEB_SITE . CONTENT_URL . $cpath;
        $url            = "http://localhost" . CONTENT_URL . $cpath;
        $header         = file_get_contents($url);
      }

      // Get content
      // $cpath          = $this->GetContentPath($content_id);
      // if (trim($cpath)) {
      //  $url            = "http://" . $_SERVER['HTTP_HOST'] . CONTENT_URL . $cpath;
      //  $content        = file_get_contents($url);
      // }
      $b_content    = $this->GetContentBody();
      $content      = base64_decode($b_content['text']);

      // Get footer
      $footer_record  = $this->GetFooterTemplate($parent_group_id, $footer_template, "");
      $cpath          = $this->GetContentPath($footer_record['content_id']);
      if (trim($cpath)) {
        // $url            = WEB_SITE . CONTENT_URL . $cpath;
        $url            = "http://localhost" . CONTENT_URL . $cpath;
        $footer         = file_get_contents($url);
      }
  
      return(array("header" => $header, "content" => $content, "footer" => $footer));
    } // GetPageContents
    
    public function PrintPage($page) {
      if (isset($_GET['print'])) {
              if ($_GET['print'] == "yes") {
                      print "<body onload=\"window.print();\">";
              } else {
                print $page['header'];
              }
      } else {
        print $page['header'];
      }
      
      print $page['content'];

      if (!isset($_GET['print'])) {
        $this->LoadContentModules($record);
      }

      if (isset($_GET['print'])) {
              if ($_GET['print'] == "yes") {
              } else {
                print $page['footer'];
              }
      } else {
        print $page['footer'];
      }      
    } // PrintPage

    public function PrintPreview() {
      $this->ResetPostVariables();
      $this->content_revision = $this->GetContentRevision($this->content_id, $_GET['revision']);
      $user_contact = $this->GetContactByUserID($this->content_revision['user_id']);
      $user_name    = $user_contact['fname'] . " " . $user_contact['lname'];
      $h_content    = $this->GetContentHeader();
      $b_content    = $this->GetContentBody();
      $f_content    = $this->GetContentFooter();
      $this->name   = $content['name'];
      
      print base64_decode($h_content['text']) . base64_decode($b_content['text']) . base64_decode($f_content['text']);
    }
    
  } // PreviewContent
  $PreviewContent = new PreviewContent();
  // $PreviewContent->PrintPreview();
  $PreviewContent->LoadContentPreview();
?>
