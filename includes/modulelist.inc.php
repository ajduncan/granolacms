<?php
  if (trim($_GET['activate'])) {
    $key = $_GET['activate'];
    $query  = "select * from " . DB_PREFIX . "modules where `key`='$key'";
    $rs     = $GLOBALS['Globals']->DB->Execute($query);
    // $record = $GLOBALS['Globals']->DB->GetRow($query);
    if ($rs->RecordCount() == 0) {
      $query  = "insert into " . DB_PREFIX . "modules (`key`, `status`) values ('$key', '1')";
      $GLOBALS['Globals']->DB->Query($query);
    } else {
      $query  = "update " . DB_PREFIX . "modules set status='1' where `key`='$key'";
      $GLOBALS['Globals']->DB->Query($query);
    }
    $am = $GLOBALS['Modules']->ModulesByKey(base64_decode($key));
    $am[0]->Install();
    $am[0]->SetActive(1);
  }

  if (trim($_GET['deactivate'])) {
    $key = $_GET['deactivate'];
    $query  = "select * from " . DB_PREFIX . "modules where `key`='$key'";
    $rs     = $GLOBALS['Globals']->DB->Execute($query);
    // $record = $GLOBALS['Globals']->DB->GetRow($query);
    if ($rs->RecordCount() == 0) {
      $query  = "insert into " . DB_PREFIX . "modules (`key`, `status`) values ('$key', '0')";
      $GLOBALS['Globals']->DB->Query($query);
    } else {
      $query  = "update " . DB_PREFIX . "modules set status='0' where `key`='$key'";
      $GLOBALS['Globals']->DB->Query($query);
    }
    $am = $GLOBALS['Modules']->ModulesByKey(base64_decode($key));
    // $am[0]->Uninstall();
    $am[0]->SetActive(0);
  }
  
  $GLOBALS['Modules']->PrintModuleListing();

?>