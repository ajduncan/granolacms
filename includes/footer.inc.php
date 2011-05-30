<?php
  if (isset($GLOBALS['location'])) {
    $module = $GLOBALS['Modules']->ModulesByLocation($GLOBALS['location']);
    if (is_array($module)) {
      while(list($key,$val) = each ($module)) {
        $val->PrintFooterSection();
      }
    }
  }
?>
  </body>
</html>
