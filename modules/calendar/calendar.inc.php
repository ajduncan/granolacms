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

  class calendar extends BaseModules {
    private $gid;
    private $Group;
    
    public function __construct() {
      parent::__construct();
      $this->module_name        = "Calendar";
      $this->module_description = "Basic Calendar module, which allows for group-level calendar scheduling.";
      $this->module_location    = "H_EDITGROUPCALENDAR";
			$this->module_linked_from = "H_EDITGROUP";
      $this->module_path        = "./modules/calendar/calendar.inc.php";
			
			// Need to register the H_EDITGROUPCALENDAR
			$GLOBALS['Globals']->AddLocation("H_EDITGROUPCALENDAR", $this->module_path, array('admin', 'normal'));
		  $GLOBALS['CONST_NO_MENU'][] = "H_EDITGROUPCALENDAR";
      
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
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "group_calendar` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `group_id` int(10) unsigned NOT NULL,
          `date` date NOT NULL,
          `text` text character set utf8,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
      ";
      print "Installing DB Table: " . $query . "<br/>";
      $GLOBALS['Globals']->DB->Query($query);
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
<!--aggregate file path for reset-fonts-grids.css-->
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/reset-fonts-grids/reset-fonts-grids.css"> 
<!--aggregate file path for YUI Sam Skin CSS for UI controls-->
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/assets/skins/sam/container.css">
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/menu/assets/skins/sam/menu.css"> 
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/button/assets/skins/sam/button.css"> 
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/calendar/assets/skins/sam/calendar.css">
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/editor/assets/skins/sam/editor.css">
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/resize/assets/skins/sam/resize.css">
<link rel="stylesheet" type="text/css" href="<?php print YUI_CDN; ?>build/layout/assets/skins/sam/layout.css">
<style type="text/css">
/*margin and padding on body element
  can introduce errors in determining
  element position and are not recommended;
  we turn them off as a foundation for YUI
  CSS treatments. */

body {
	margin:0;
	padding:0;
}
#demo .yui-resize-handle-br {
    height: 11px;
    width: 11px;
    background-position: -20px -60px;
    background-color: transparent;
}
#demo .yui-calcontainer,
#demo .yui-editor-container {
    border: none;
}
#demo .yui-layout {
    background-color: transparent;
}
#status img {
    padding: 4px;
    float: left;
}
#demo textarea {
    visibility: hidden;
}
#demo .yui-toolbar .bd {
    padding: 0;
    border: none;
    background-color: #fff;
}

</style>
<script type="text/javascript" src="<?php print YUI_CDN; ?>build/utilities/utilities.js"></script>
<script type="text/javascript" src="<?php print YUI_CDN; ?>build/container/container-min.js"></script> 
<script type="text/javascript" src="<?php print YUI_CDN; ?>build/calendar/calendar-min.js"></script>
<script type="text/javascript" src="<?php print YUI_CDN; ?>build/resize/resize-min.js"></script>
<script type="text/javascript" src="<?php print YUI_CDN; ?>build/layout/layout-min.js"></script> 
<script type="text/javascript" src="<?php print YUI_CDN; ?>build/editor/simpleeditor-min.js"></script> 
<!-- <script type="text/javascript" src="./yui/build/editor/simpleeditor-min.js"></script> -->
<?php
    }
    
    public function PrintBodySection() {
?>
<body class="yui-skin-sam">
<?php
			if (trim($_GET['location'])) {
				if ($_GET['location'] == "H_EDITGROUPCALENDAR") {
					$aModule = $GLOBALS['Modules']->ModulesByLocation("H_EDITGROUPCALENDAR");
					// Get the first registered module that matches our 'config' name;
					while(list($key,$val) = each($aModule)) {
						if ($val->module_name == "Calendar") {
							$val->PrintForm();
						}
					}
				}
			}
?>
<script>

var _global = this;

(function() {

    var successResult = function callBack(o) {
      // alert(o.responseText);
      layout.getUnitByPosition('bottom').set('body', o.responseText);
    }

    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event,
        layout = null,
        cal = null,
        editor = null,
        conn = null,
        panel = null,
        dateSelected = null,
        editorData = {};

    panel = new YAHOO.widget.Panel('demo', {
        width: '700px',
        underlay: 'none',
        close: false,
        xy: [50, 50]
    });
    panel.setHeader('Calendar for "<?php print $this->Group['name']; ?>" Group');
    panel.setBody('<div id="layout"></div>');
    panel.renderEvent.subscribe(function() {
        Event.onAvailable('layout', function() {
            layout = new YAHOO.widget.Layout('layout', {
                height: 400,
                units: [
                    { position: 'top', height: 25, header: 'Date Editor', gutter: '2' },
                    { position: 'left', width: 205, body: '', gutter: '0 5 0 2' },
                    { position: 'bottom', height: 25, id: 'status', body: 'Status', gutter: '2' },
                    { position: 'center', body: 'Select a date..', gutter: '0 2 0 0' }
                ]
            });
            
            layout.on('render', function() {
                var c = layout.getUnitByPosition('center');
                c.set('body', '<textarea id="caleditor"></textarea>');
                _global.gEditor = new YAHOO.widget.SimpleEditor('caleditor', {
                    height: '305px',
                    width: c.get('width') + 'px',
                    dompath: false,
                    animate: false,
                    focusAtStart: true,
                    toolbar: {
                        grouplabels: false,
                        buttons: [
                            { group: 'textstyle', label: 'Font Style',
                                buttons: [
                                    { type: 'select', label: 'Arial', value: 'fontname', disabled: true,
                                        menu: [
                                            { text: 'Arial', checked: true },
                                            { text: 'Arial Black' },
                                            { text: 'Comic Sans MS' },
                                            { text: 'Courier New' },
                                            { text: 'Lucida Console' },
                                            { text: 'Tahoma' },
                                            { text: 'Times New Roman' },
                                            { text: 'Trebuchet MS' },
                                            { text: 'Verdana' }
                                        ]
                                    },
                                    { type: 'spin', label: '13', value: 'fontsize', range: [ 9, 75 ], disabled: true },
                                    { type: 'separator' },
                                    { type: 'push', label: 'Bold', value: 'bold' },
                                    { type: 'push', label: 'Italic', value: 'italic' },
                                    { type: 'push', label: 'Underline', value: 'underline' },
                                    { type: 'separator' },
                                    { type: 'color', label: 'Font Color', value: 'forecolor', disabled: true },
                                    { type: 'color', label: 'Background Color', value: 'backcolor', disabled: true }
                                ]
                            }
                        ]
                    }
                });
                _global.gEditor.on('editorContentLoaded', function() {
                    var d = new Date();
                    var today = d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear();
                    dateSelected = [today];
                    layout.getUnitByPosition('top').set('header', 'Editing Date: ' + today);
                    _global.gCal.cfg.setProperty('selected', today);
                    _global.gCal.render();
                });
                _global.gEditor.render();
                var l = layout.getUnitByPosition('left');
                var el = document.createElement('div');
                l.body.appendChild(el);
                _global.gCal = new YAHOO.widget.Calendar(el);
                _global.gCal.selectEvent.subscribe(function(ev, args) {
                    if (dateSelected) {
                        //Connection Manager
                        layout.getUnitByPosition('bottom').set('body', '<img src="assets/progress.gif"> Sending Data...');
                        var html = _global.gEditor.getEditorHTML();
                        var url = '<?php print $GLOBALS['H_JSONHANDLER_location'] . "&action=setGroupCalendarDate&gid=" . $_GET['id'] . "&date="; ?>' + dateSelected;
                        var postdata = 'text=' + encodeURIComponent(html);
                        editorData[dateSelected] = html;
                        conn = YAHOO.util.Connect.asyncRequest('POST', url, {
                            success: successResult,
                            failure: function() {}},
                            postdata
                        );
                    }
                    var d = args[0][0];
                    dateSelected = d[1] + '/' + d[2] + '/' + d[0];

                    // Request data for the selected date, if it exists
                    url = '<?php print $GLOBALS['H_JSONHANDLER_location'] . "&action=getGroupCalendarDate&gid=" . $_GET['id'] . "&date="; ?>' + dateSelected;
                    conn = YAHOO.util.Connect.asyncRequest('GET', url, {
                        success: function(o) {
                            // layout.getUnitByPosition('bottom').set('body', o.responseText);
                            _global.gEditor.setEditorHTML(o.responseText);
                        },
                        failure: function() {
                        }
                    });

                    layout.getUnitByPosition('top').set('header', 'Editing Date: ' + d[1] + '/' + d[2] + '/' + d[0]);

                    var dates = [dateSelected];
                    for (var i in editorData) {
                        dates[dates.length] = i;
                    }
                    _global.gCal.cfg.setProperty('selected', dates.join(','));
                    _global.gCal.render();
                });

                // Request data for the selected date, if it exists
                 var d = new Date();
                 var today = d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear();
                 dateSelected = [today];
                 var url = '<?php print $GLOBALS['H_JSONHANDLER_location'] . "&action=getGroupCalendarDate&gid=" . $_GET['id'] . "&date="; ?>' + dateSelected;
                 conn = YAHOO.util.Connect.asyncRequest('GET', url, {
                     success: function(o) {
                       _global.gEditor.setEditorHTML(o.responseText);
                     },
                     failure: function() {
                     }
                 });
                
                _global.gCal.render();
            });
            layout.render();
        });
    });
    panel.render(document.body);
    resize = new YAHOO.util.Resize('demo', {
        handles: ['br'],
        autoRatio: true,
        status: true,
        proxy: true,
        useShim: true,
        minWidth: 700,
        minHeight: 400
    });
    resize.on('resize', function(args) {
        var h = args.height;
        var hh = this.header.clientHeight;
        var padding = ((10 * 2) + 2); //Sam's skin applied a 10px padding and a 1 px border to the element..
        var bh = (h - hh - padding);
        Dom.setStyle(this.body, 'height', bh + 'px');
        layout.set('height', bh);
        layout.set('width', (args.width - padding));
        layout.resize();

        //Editor Resize
        var th = (_global.gEditor.toolbar.get('element').clientHeight + 2); //It has a 1px border..
        var eH = (h - th);
        _global.gEditor.set('width', args.width + 'px');
        _global.gEditor.set('height', eH + 'px');
    }, panel, true);
    resize.on('endResize', function() {
        //Fixing IE's calculations
        this.innerElement.style.height = '';
        //Focus the Editor so they can type.
        _global.gEditor._focusWindow();
    }, panel, true);
})();

(function() {
})();
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
