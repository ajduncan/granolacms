<?php  
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  if (!isset($_SESSION['authenticated'])) {
    return(1);
  }

  include_once($GLOBALS['H_GROUPLIST']);
  include_once($GLOBALS['H_CONTENTLIST']);
  
  // include_once($GLOBALS['I_BASE']);

  class Dialogs extends Base {
   
    private $content_revision;

    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];
    } // construct
    
    public function __destruct() {
      parent::__destruct();
    } // destruct    

    public function HandleFormSubmission() {
    } // HandleFormSubmission

    public function PrintDialog($id, $title, $message) {
?>
<div dojoType="dijit.Dialog" id="<?php print $id; ?>" title="<?php print $title; ?>" style="width: 400px;">
  <table>
    <tbody>
      <tr>
        <td><?php print $message; ?></td>
      </tr><tr>
        <td><button dojoType="dijit.form.Button" type="submit">OK</button></td>
      </tr>
    </tbody>
  </table>
</div>
<script type="text/javascript">
  dojo.addOnLoad(function(){dijit.byId('<?php print $id;?>').show(); });
</script>
<?php
    } // PrintForm
    
  } // Dialogs
?>