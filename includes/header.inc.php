<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="Language" content="en" />
    <meta name="distribution" content="Global" />
    <meta name="Copyright" content="2007" />
    <meta name="Classification" content="Granola" />
    <meta name="Author" content="Duncan Ingram, Inc." />
    <title><?php ?></title>
      <?php
        if ($GLOBALS['location'] == 'H_UPLOADCONTENT') {
      ?>
      <link href="./css/swfupload.css" rel="stylesheet" type="text/css" />
      <?php
        }
      ?>
    <script type="text/javascript" src="<?php print REF_SITE; ?>js/jquery/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="<?php print REF_SITE; ?>js/index.js"></script>
    <script type="text/javascript" src="<?php print REF_SITE; ?>js/common.js"></script>
    <script type="text/javascript" src="<?php print REF_SITE; ?>js/php.js"></script>
    <?php
      $header_printed = 0;
      if (isset($GLOBALS['location'])) {
        $module = $GLOBALS['Modules']->ModulesByLocation($GLOBALS['location']);
        if (is_array($module)) {
          while(list($key,$val) = each ($module)) {
            $val->PrintHeaderSection();
            $header_printed = 1;
          }
        }
      }
      
      if ($header_printed == 0) {
    ?>
    <link href="<?php print REF_SITE; ?>css/index.css" rel="stylesheet" type="text/css" />
    <link href="<?php print REF_SITE; ?>css/stdtable.css" rel="stylesheet" type="text/css" />
    <link href="<?php print REF_SITE; ?>css/header.css" rel="stylesheet" type="text/css" />
    <link href="<?php print REF_SITE; ?>css/login.css" rel="stylesheet" type="text/css" />
    <link href="<?php print REF_SITE; ?>css/dojo.css" rel="stylesheet" type="text/css" />

    <style type="text/css">
      @import "<?php print DOJO_CDN; ?>dijit/themes/tundra/tundra.css";
      @import "<?php print DOJO_CDN; ?>dojox/grid/resources/Grid.css";
      @import "<?php print DOJO_CDN; ?>dojox/grid/resources/tundraGrid.css";
      @import "<?php print DOJO_CDN; ?>dojo/resources/dojo.css";
      @import "<?php print DOJO_CDN; ?>dojox/image/resources/image.css";
    </style>
<!--
    <script type="text/javascript" src="./yui/build/yahoo-dom-event/yahoo-dom-event.js"></script>
    <script type="text/javascript" src="./yui/build/connection/connection-min.js"></script>
    <script type="text/javascript" src="./yui/build/treeview/treeview-min.js"></script>

    <script
      type="text/javascript"
      src="<?php print DOJO_CDN; ?>dojo/dojo.xd.js"
      djConfig="
        parseOnLoad: true,
        isDebug: false,
        baseUrl: '/dojo/'
      "
    ></script>
    <script
      type="text/javascript"
      src="<?php print REF_SITE; ?>dojo/dojo/granoladojo.js"
    ></script>
-->
    <script type="text/javascript" src="<?php print DOJO_CDN; ?>dojo/dojo.js" djConfig="parseOnLoad: true, isDebug: false"></script>
    <script type="text/javascript">
        dojo.require("dojo.parser");
        dojo.require("dojo.data.ItemFileReadStore");
        dojo.require("dojo.data.ItemFileWriteStore");
        
        dojo.require("dijit.Menu");
        dojo.require("dijit.Tree");
        dojo.require("dijit.Tooltip");
        dojo.require("dijit.Dialog");
        dojo.require("dijit.form.Button");
        dojo.require("dijit.form.TextBox");
        dojo.require("dijit.form.Form");

        dojo.require("dijit.layout.LayoutContainer");
        dojo.require("dijit.layout.BorderContainer");
        dojo.require("dijit.layout.ContentPane");
        dojo.require("dijit.layout.TabContainer");

        dojo.require("dojox.grid.Grid");
        dojo.require("dojox.grid.DataGrid");
        dojo.require("dojox.grid.compat._grid.publicEvents");
        dojo.require("dojox.grid.cells.dijit");
        dojo.require("dojox.image.Gallery");
    </script>

    <?php
      if ($GLOBALS['location'] == 'H_EDITCONTENT') {
    ?>
    <script type="text/javascript" src="./fckeditor/fckeditor.js"></script>
    <?php
      }
    ?>
    <?php
      if ($GLOBALS['location'] == 'H_UPLOADCONTENT') {
    ?>
    <script type="text/javascript" src="./js/swfupload.js"></script>
    <script type="text/javascript" src="./js/swfupload.queue.js"></script>
    <script type="text/javascript" src="./js/fileprogress.js"></script>
    <script type="text/javascript" src="./js/handlers.js"></script>
    <script type="text/javascript">
      var swfu;

      window.onload = function() {
        var settings = {
          flash_url : "./swfupload/swfupload_f9.swf",
          upload_url: "<?php print REF_SITE . $GLOBALS['H_UPLOAD_location'] . "&id=" . $_GET['id'] . "&username=" . $_SESSION['username'] . "&password=" . $_SESSION['password']; ?>",
          post_params: {"PHPSESSID" : ""},
          file_size_limit : "100 MB",
          file_types : "*.*",
          file_types_description : "All Files",
          file_upload_limit : 100,
          file_queue_limit : 0,
          custom_settings : {
                  progressTarget : "fsUploadProgress",
                  cancelButtonId : "btnCancel"
          },
          debug: false,

          // The event handler functions are defined in handlers.js
          file_queued_handler : fileQueued,
          file_queue_error_handler : fileQueueError,
          file_dialog_complete_handler : fileDialogComplete,
          upload_start_handler : uploadStart,
          upload_progress_handler : uploadProgress,
          upload_error_handler : uploadError,
          upload_success_handler : uploadSuccess,
          upload_complete_handler : uploadComplete,
          queue_complete_handler : queueComplete	// Queue plugin event
        };

        swfu = new SWFUpload(settings);
   };
    </script>

    <?php
      }      
    ?>

  <?php
      } // if ! header printed
  ?>
  </head>

<?php
  $body_printed = 0;
  if (isset($GLOBALS['location'])) {
    $module = $GLOBALS['Modules']->ModulesByLocation($GLOBALS['location']);
    if (is_array($module)) {
      while(list($key,$val) = each ($module)) {
        $val->PrintBodySection();
        $body_printed = 1;
      }
    }
  }
  
  if ($body_printed == 0) {
?>
  <body class="tundra" id="body">
<?php
  }
?>
