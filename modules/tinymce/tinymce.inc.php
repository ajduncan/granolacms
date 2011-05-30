<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   * Module using: http://developer.yahoo.com/yui/calendar/
   */

/*
 *
 * CREATE TABLE `granola_group_calendar` (
 *  `id` int(10) unsigned NOT NULL auto_increment,
 *  `group_id` int(10) unsigned NOT NULL,
 *  `date` date NOT NULL,
 *  `text` text character set utf8,
 *  PRIMARY KEY  (`id`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 * 
 */
  include_once("./modules/base.inc.php");

  class tinymce extends BaseModules {
    private $gid;
    private $Group;
    
    public function __construct() {
      parent::__construct();
      $this->module_name        = "TinyMCE";
      $this->module_description = "TinyMCE WYSIWYG editor.";
      $this->module_location    = "H_EDITCONTENT_WYSIWYG";
      $this->module_linked_from = "H_EDITCONTENT_WYSIWYG";
      $this->module_path        = "./modules/tinymce/tinymce.inc.php";
      
      if (trim($_GET['id'])) {
        $this->gid = $_GET['id'];
        $this->GetGroupInformation();
      }
      
      // Finally, is this module active or not?
      $key    = base64_encode($this->module_path);
      $query  = "select `status` from " . DB_PREFIX . "modules where `key`='$key'";
      $rs     = $GLOBALS['Globals']->DB->Execute($query);
      if ($rs->RecordCount() == 1) {
        $record = $rs->FetchRow();
        if ($record['status'] == 1) {
          $this->active = 1;
        } else {
          $this->active = 0;
        }
      } else {
        $this->active = 0;
      }

    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct

    public function Install() {
      $query = "";
      print "Installing DB Table: " . $query . "<br/>";
      // $GLOBALS['Globals']->DB->Query($query);
    }
    
    public function GetGroupInformation() {
      $query  = "select * from $GLOBALS[DB_PREFIX]groups where id={$this->gid}";
      $record = $GLOBALS['Globals']->DB->GetRow($query);
      $this->Group = $record;
    } // GetGroupInformation

    public function HandleFormSubmission() {
    } // HandleFormSubmission
    
    public function PrintHeaderSection() {
?>
<?php
    }
    
    public function PrintBodySection() {
?>
<?php
			if (trim($_GET['location'])) {
				if ($_GET['location'] == "H_EDITCONTENT") {
					$aModule = $GLOBALS['Modules']->ModulesByLocation("H_EDITCONTENT");
					while(list($key,$val) = each($aModule)) {
						if ($val->module_name == "TinyMCE") {
							$val->PrintForm();
						}
					}
				}
			}
    }
    
    public function PrintFooterSection() {
      
    }

    
    public function PrintForm() {
?>
<script type="text/javascript" src="./modules/tinymce/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	// General options
	mode : "exact",
	elements : "content",
	theme : "advanced",
	plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",

	// Theme options
	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertimage",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,

	// Example content CSS (should be your site CSS)
	content_css : "css/example.css",
  external_image_list_url : "./modules/tinymce/tinymce/imagelist.php"

	// Drop lists for link/image/media/template dialogs
	// template_external_list_url : "js/template_list.js",
	// external_link_list_url : "js/link_list.js",
	// external_image_list_url : "js/image_list.js",
	// external_image_list_url : "<?php print $GLOBALS['H_JSONCONTENTLIST_location'] . "&id=" . $this->Group['id'] . "&imagelist=1&print=1"; ?>",
	// media_external_list_url : "js/media_list.js",
});
</script>
<?php
    } // PrintForm 
  } // Calendar
	
	if (isset($_POST['html'])) {
?>
<div id="result"><?php print "Got: " . $_POST['html']; ?></div>
<?php
	}
?>
