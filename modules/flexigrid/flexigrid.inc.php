<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   * Module using: http://www.flexigrid.info/
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

  class FlexiGrid extends BaseModules {
    private $gid;
    private $Group;
    
    public function __construct() {
      parent::__construct();
      $this->module_name        = "FlexiGrid";
      $this->module_description = "Grid list using jquery enabled FlexiGrid.";
      $this->module_location    = "H_CONTENTLIST_FLEXIGRID";
			$this->module_linked_from = "H_EDITGROUP";
      $this->module_path        = "./modules/flexigrid/flexigrid.inc.php";
			
			// Need to register the H_CONTENTLIST_FLEXIGRID
			$GLOBALS['Globals']->AddLocation("H_CONTENTLIST_FLEXIGRID", $this->module_path, array('admin', 'normal'));
		  $GLOBALS['CONST_NO_MENU'][] = "H_CONTENTLIST_FLEXIGRID";
      
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
      $query = "
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "content_flexigrid` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `group_id` int(10) unsigned NOT NULL,
          `date` date NOT NULL,
          `text` text character set utf8,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
      ";
      // print "Installing DB Table: " . $query . "<br/>";
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
  <link rel="stylesheet" type="text/css" href="modules/flexigrid/flexigrid/css/flexigrid/flexigrid.css">
  <script type="text/javascript" src="modules/flexigrid/flexigrid/lib/jquery/jquery.js"></script>
  <script type="text/javascript" src="modules/flexigrid/flexigrid/flexigrid.js"></script>

<style type="text/css">
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
		
	.flexigrid div.fbutton .add {
		background: url(modules/flexigrid/flexigrid/css/images/add.png) no-repeat center left;
	}	

	.flexigrid div.fbutton .delete {
		background: url(modules/flexigrid/flexigrid/css/images/close.png) no-repeat center left;
	}	
</style>
<?php
    }
    
    public function PrintBodySection() {
?>
<body class="">
<?php
			if (trim($_GET['location'])) {
				if ($_GET['location'] == "H_CONTENTLIST_FLEXIGRID") {
					$aModule = $GLOBALS['Modules']->ModulesByLocation("H_CONTENTLIST_FLEXIGRID");
					// Get the first registered module that matches our 'config' name;
					while(list($key,$val) = each($aModule)) {
						if ($val->module_name == "FlexiGrid") {
							$val->PrintForm();
						}
					}
				}
			}
?>
<b>This is a sample implementation attached to a form, to add additional parameters</b>

<form id="sform">
	<p>
	The values you entered will be place in name column for demo's sake.<br />
	Value 1 : <input type="text" name="val1" value="" autocomplete="off" /><br />
    Value 2 : Is a hidden input with value 3<input type="hidden" name="val2" value="3" /><br />
    Value 3 : 
    <select name="val3">

    	<option value="1">One</option>
        <option value="2">Two</option>
        <option value="3">Three</option>
        <option value="4">Four</option>
        <option value="5">Five</option>
    </select><br />

    Value 4 : <input type="checkbox" name="val4" id="val4" value="4" /><label for="val4">This will pass a value 4 if checked</label>
    </p>
    <p>
    <input type="reset" value="Reset" />
    <input type="submit" value="Submit" />
    </p>
    
</form>

<table id="flex1" style="display:none"></table>

<script type="text/javascript">

			$("#flex1").flexigrid
			(
			{
			url: '?location=H_JSONCONTENTLIST&id=<?php print "2"; ?>&print=1',
			dataType: 'json',
			colModel : [
				{display: 'ID', name : 'id', width : 40, sortable : true, align: 'center'},
				{display: 'Name', name : 'name', width : 180, sortable : true, align: 'left'},
				{display: 'File Name', name : 'filename', width : 120, sortable : true, align: 'left'},
				{display: 'Saved', name : 'saved', width : 130, sortable : true, align: 'left', hide: true},
				{display: 'Type', name : 'type', width : 80, sortable : true, align: 'right'}
				],
			searchitems : [
				{display: 'Name', name : 'name'},
				{display: 'File Name', name : 'filename', isdefault: true}
				],
			sortname: "name",
			sortorder: "asc",
			usepager: true,
			title: 'Content',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 700,
			onSubmit: addFormData,
			height: 200
			}
			);




//This function adds paramaters to the post of flexigrid. You can add a verification as well by return to false if you don't want flexigrid to submit			
function addFormData()
	{
	
	//passing a form object to serializeArray will get the valid data from all the objects, but, if the you pass a non-form object, you have to specify the input elements that the data will come from
	var dt = $('#sform').serializeArray();
	$("#flex1").flexOptions({params: dt});
	return true;
	}
	
$('#sform').submit
(
	function ()
		{
			$('#flex1').flexOptions({newp: 1}).flexReload();
			return false;
		}
);						

	
</script>
<?php
    }
    
    public function PrintFooterSection() {
      
    }

    
    public function PrintForm() {
?>
<div id="demo"></div>
<?php
    } // PrintForm 
  } // Calendar
	
	if (isset($_POST['html'])) {
?>
<div id="result"><?php print "Got: " . $_POST['html']; ?></div>
<?php
	}
?>
