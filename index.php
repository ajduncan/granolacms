<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  if (isset($_GET['location'])) {
    $GLOBALS['location'] = $_GET['location'];
  } else {
    $GLOBALS['location'] = "";
  }


  // Use a secure site, if in production;
  if ($_SERVER['HTTP_HOST'] == "") {
    if ( !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on' ) {
      header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
      exit();
    }
  } //

  if (isset($_GET['location'])) {
    switch($_GET['location']) {
      default:
        case 'H_CONTENTPREVIEW':
        case 'H_UPLOAD':
        case 'H_JSONGROUPLIST':
          ob_start();
          break;
    }
  } else {
    ob_start();
  }

  if (
    (isset($_POST['jsonget'])) ||
    (isset($_GET['action'])) ||
    $GLOBALS['location'] == 'H_JSONGROUPLIST' ||
    $GLOBALS['location'] == 'H_JSONHANDLER'
  )
  {
    header("Content-type: text/plain");
    // header("Content-type: text/json");
    // header("mimetype: text/json-comment-filtered");
  } else {
    header("Content-type: text/html; charset=UTF-8");
    header("Pragma: no-cache");
    header("Expires: 0");
  }

  include_once("./config.inc.php");
  include_once("./includes/globals.inc.php");
  include_once($GLOBALS['I_AUTH']);

  // Modules must come after, not before, globals:
  include_once("./includes/modules.inc.php");
  $GLOBALS['Modules'] = new Modules();

  if (DEBUG == 1) {
    $GLOBALS['DEBUG_FH'] = fopen(DEBUG_FILE, 'w+');
    fwrite($GLOBALS['DEBUG_FH'], "Processing started.");
  }

  // SafePostVariables();

  // retract authentication
  if (isset($_GET['location'])) {
    $logout = $_GET['location'];
    if ($logout == "R_LOGOUT") {
      session_name("granola");
      session_start();
      unset($_COOKIE[session_name()]);
      $_SESSION = array();
      session_destroy();
      $location = $GLOBALS['H_LOGIN_location'];
      header("Location: " . $location);
      return(1);
    }
  } // retract authentication

  if (!session_id()) {
    session_cache_expire(1);
    session_name("granola");
    session_cache_limiter("must-revalidate");
    session_start();
    $lifetime = (60 * 300);
    $timenow = time();
    if (
      (!empty($_SESSION['last_access'])) &&
      ($_SESSION['last_access'] >= ($timenow + $lifetime))
    ) {
      unset($_COOKIE[session_name()]);
      $_SESSION = array();
      session_destroy();
      session_cache_expire(1);
      session_name("granola");
      session_start();
    } else {
      $_SESSION['last_access'] = time();
    }
  }

  // Make sure errors are kept from confusing the end-user
  define("FATAL", E_USER_ERROR);
  define("ERROR", E_USER_WARNING);
  define("WARNING", E_USER_NOTICE);

  // set the error reporting level for this script
  // error_reporting(FATAL | ERROR | WARNING);

  // Set authentication:
  if (isset($_GET['username']) && isset($_GET['password'])) {
    $Authentication = new Authentication($_GET['username'], $_GET['password']);
    $Authentication->Authenticate($_GET['username'], $_GET['password']);
  }

  if (isset($_POST['login'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
      $Authentication = new Authentication($_POST['username'], $_POST['password']);
      $Authentication->Authenticate();
    }
  }
  // END authentication

  if (isset($Authentication)) {
    $Authenticated = $Authentication->Authenticated;
  } else {
    $Authenticated = 0;
  }

  if (
    isset($_SESSION['authenticated']) ||
    $Authenticated == 1
  ) {

    // We're authenticated, create a global modules reference;
    $GLOBALS['Modules']->LoadModules();

    // Handle special cases, authenticated JSON data requests, for example:
    if (
      (isset($_POST['jsonget'])) ||
      (isset($_GET['action']))
    )
      {
        include_once(JSON_HANDLER);
      } else {
        if (trim($_GET['location'])) {
          if (
                $_GET['location'] != 'H_CONTENTPREVIEW' &&
                $_GET['location'] != 'H_UPLOAD' &&
                $_GET['location'] != 'H_JSONGROUPLIST' &&
                $_GET['location'] != 'H_JSONCONTENTLIST' &&
                $_GET['location'] != 'H_JSONHANDLER' &&
                $_GET['location'] != 'H_AJAXD'
          ) {
            // Generate the HEADER html:
            include_once(HTML_HEADER);
          }
        } else {
          // Generate the HEADER html:
          include_once(HTML_HEADER);
        }

        // include the body if authenticated
        include_once(HTML_BODY);

        if (trim($_GET['location'])) {
          if (
                $_GET['location'] != 'H_CONTENTPREVIEW' &&
                $_GET['location'] != 'H_UPLOAD' &&
                $_GET['location'] != 'H_JSONGROUPLIST' &&
                $_GET['location'] != 'H_JSONCONTENTLIST' &&
                $_GET['location'] != 'H_JSONHANDLER' &&
                $_GET['location'] != 'H_AJAXD'
          ) {
            // Generate the footer:
            include_once(HTML_FOOTER);
          }
        } else {
          // Generate the footer:
          include_once(HTML_FOOTER);
        }
      }

  } else {
    // Generate the HEADER html:
    include_once(HTML_HEADER);

    //
    include_once(HTML_LOGIN);

    // Generate the footer:
    include_once(HTML_FOOTER);

  } // if (isset($_SESSION['authenticated']))

  if (isset($GLOBALS['location'])) {
    switch($GLOBALS['location']) {
      case 'H_CONTENTPREVIEW':
      case 'H_UPLOAD':
      case 'H_JSONGROUPLIST':
      case 'H_JSONCONTENTLIST':
        ob_end_flush();
        break;
      default:
        ob_end_flush();
        break;
    }
  } else {
    ob_end_flush();
  }

  if (DEBUG == 1) {
    fwrite($GLOBALS['DEBUG_FH'], "End of processing.");
    fclose($GLOBALS['DEBUG_FH']);
  }

?>
