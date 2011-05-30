<?php
  include_once($GLOBALS['I_MENU']);
  include_once($GLOBALS['I_LOCATION']);
  
  $Location = new Location();
  
  if ($_GET['location'] != "H_HELP") {  
    include_once($GLOBALS['H_HELP']);
    $Help     = new Help();
  }
  
  if (in_array($_GET['location'], $GLOBALS['CONST_NO_MENU'])) {
    $Location->LoadLocation();    
    return(1);
  }
    
  $Menu = new Menu();
  $Menu->AddItem("Home", $GLOBALS['H_BODY_location']);
  $Menu->AddItem("User Manager", $GLOBALS['H_USERLIST_location']);
  $Menu->AddItem("Group Manager", $GLOBALS['H_GROUPLIST_location']);
  $Menu->AddItem("My Groups", $GLOBALS['H_MYGROUPS_location']);
  $Menu->AddItem("My Content", $GLOBALS['H_MYCONTENT_location']);
  // $Menu->AddItem("My Messages", $GLOBALS['H_MYMESSAGES_location']);
  $Menu->AddItem("Modules", $GLOBALS['H_MODULELIST_location']);
  $Menu->AddItem("Help", $GLOBALS['H_HELP_location']);
  $Menu->AddItem("Logout", $GLOBALS['R_LOGOUT_location']);
?>

<div class="logo">
  <span dojoType="dijit.Tooltip" connectId="mainhelp">
<?php
  if ($_GET['location'] != "H_HELP") {
    $Help->PrintHelpItem(1);
  } else {
    print "You're at the main help site.<br/>";
  }
?>
  </span>
  <table width="100%">
    <tbody>
      <tr>
        <td valign="top">
          <div class="header-top">
            <?php $Menu->PrintMenu(); ?>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<br />

<div id="main-content">
  <?php
    // load content;
    if (
      $_SESSION['authenticated'] == "1" &&
      $_GET['location'] == "H_LOGIN"
    ) {
      $_GET['location'] = "H_WELCOME";
    }

    $Location->LoadLocation();

  ?>
</div>
  
<div class="logo">
  <table width="100%">
    <tbody>
      <tr>
        <td valign="top" align="left">
          <div align="left">
            <a name="mainhelp" href="<?php print $GLOBALS['H_HELP_location'] . "&help_id=1"; ?>"><img src="./images/help.jpg" alt="Help" /></a>
          </div>
        </td>
        <td valign="top" align="right"><img src="./images/granola_logo_small.jpg" alt="Granola" /></td>
      </tr>
    </tbody>
  </table>
</div>
