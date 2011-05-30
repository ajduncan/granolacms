<?php

  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  include_once("./modules/base.inc.php");

  class tags extends BaseModules {
    private $id;
    private $content_id;
    private $TagRecords;
    private $record;
    
    public function __construct() {
      parent::__construct();
      $this->module_name        = "Tag Manager";
      $this->module_description = "A module for associating tags with content.";
      $this->module_location    = "H_EDITTAGS";
			$this->module_linked_from = "H_EDITCONTENT";
      $this->module_path        = "./modules/tags/tags.inc.php";
			
			// Need to register the H_EDITGROUPCALENDAR
			$GLOBALS['Globals']->AddLocation("H_EDITTAGS", $this->module_path, array('admin', 'normal'));
		  $GLOBALS['CONST_NO_MENU'][] = "H_EDITTAGS";
      
      if (trim($_GET['id'])) {
        $this->id = $_GET['id'];
      }
      if (Trim($_GET['content_id'])) {
        $this->content_id = $_GET['content_id'];
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
      $query = "
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tags` (
					`id` int(10) unsigned NOT NULL auto_increment,
					`name` varchar(50) NOT NULL,
					PRIMARY KEY  (`id`),
					KEY `name` (`name`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

			";
      print "Installing DB Table: " . $query . "<br/>";
      $GLOBALS['Globals']->DB->Query($query);
      $query = "
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "content_tags` (
					`tag_id` int(10) unsigned NOT NULL,
					`content_id` int(10) unsigned NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;
			";
      print "Installing DB Table: " . $query . "<br/>";
      $GLOBALS['Globals']->DB->Query($query);

    }
    
    public function GetTags() {
      $query  = "select * from $GLOBALS[DB_PREFIX]content_tags where content_id={$this->content_id}";
      $record = $GLOBALS['Globals']->DB->Execute($query);
      $this->TagRecords = $record;
    } 

    public function GetTag($id) {
      $query  = "select * from $GLOBALS[DB_PREFIX]tags where id=$id";
      $record = $GLOBALS['Globals']->DB->Execute($query);
      $this->record = $record;
    } 

    public function HandleFormSubmission() {
    } // HandleFormSubmission
    
    public function PrintTagList() {
      $this->GetTags();
      if ($this->TagRecords->RecordCount() > 0) {
?>
<select id="taglist" name="taglist">
<?php
        foreach ($this->TagRecords as $record) {
          $onclick = "javascript:location.href='" . $GLOBALS['H_EDITTAGS_location'] . "&id={$record['id']}'";
          print "<option value=\"{$record['id']}\" onclick=\"$onclick\">{$record['name']}</option>";
        }
        reset($this->TagRecords);
?>
</select>
<?php
      }
    }

    public function GetContent($content_id) {
      $query = "select * from $GLOBALS[DB_PREFIX]content where id='$content_id'";
      return($GLOBALS['Globals']->DB->GetRow($query));
    } // GetContent
    
    public function PrintRelatedContent($id) {
?>

<?php
    }
    
    public function PrintHeaderSection() {
    }
    
    public function PrintBodySection() {
?>
<body>
<?php
			if (trim($_GET['location'])) {
				if ($_GET['location'] == "H_EDITTAGS") {
					$aModule = $GLOBALS['Modules']->ModulesByLocation("H_EDITTAGS");
					// Get the first registered module that matches our 'config' name;
					while(list($key,$val) = each($aModule)) {
						if ($val->module_name == "Tag Manager") {
							$val->PrintForm();
						}
					}
				}
			}
    }
    
    public function PrintAddTag() {
      $action = $GLOBALS['H_EDITTAGS_location'] . "&content_id=" . $this->content_id;
?>
<form method="post" action="<?php print $action ?>" id="EditContentForm" dojoType="dijit.form.Form">
  <table style="width: 100%;" border="0">
    <tbody>
      <tr>
        <td valign="top" align="center">
          Tag with:
          <input
            type="text"
            name="tagname"
          />
          <button dojoType="dijit.form.Button" type="submit" name="submit" value="submit">
            <img src="./images/icons/database/database_save.png" alt="Save">Save</img>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</form>
<?php
    }
    
    public function PrintFooterSection() {
?>
</body>
<?php
    }

    public function PrintForm() {
      if (trim($this->id)) {
        $this->GetTag($this->id);
?>
<table>
  <tbody>
    <tr><td>Examining Tag: <?php print $this->record['name']; ?></td></tr>
    <tr><td>Content associated with this tag:</td></tr>
    <?php $this->PrintRelatedContent($this->record['id']); ?>
  </tbody>
</table>
<?php
      }
      if (trim($this->content_id)) {
        $content_record = $this->GetContent($this->content_id);
?>
<table>
  <tbody>
    <tr><td>Tag information for '<?php print $content_record['name']; ?>' content</td></tr>
    <tr><td><?php $this->PrintTagList(); ?></td></tr>
    <tr><td><?php $this->PrintAddTag(); ?></td></tr>
  </tbody>
</table>
<?php
      }
    } // PrintForm 
  } // Tags
?>