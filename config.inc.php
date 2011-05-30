<?php
  // User tunable configuration settings.  Feel free to edit as needed.

  define("ROOT_DIRECTORY", getcwd());
  define("SITE_NAME", $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
  define("MX_HOST", "mail.duncaningram.com");
  define("MX_USERNAME", "user");
  define("MX_PASSWORD", "mypass");

  // define("DOJO_CDN", "http://ajax.googleapis.com/ajax/libs/dojo/1.2/");
  define("DOJO_CDN", "./dojo/");
  define("YUI_CDN", "http://yui.yahooapis.com/2.6.0/");

  // database;
  define("SQL_HOSTNAME", $_SERVER['HTTP_HOST']);
  define("SQL_USERNAME", "granola");
  define("SQL_PASSWORD", "granola");
  define("SQL_DATABASE", "granola");
  define("DB_PREFIX", "granola_");

  define("WEBROOT", "./");
  define("LOGO_TEXT", "Granola");

  define("CONTENT_PATH", "./content/");
  define("CONTENT_URL", "/development/granola/");
  define("CMS_SITE", "http://localhost/development/granola/");
  define("WEB_SITE", "http://localhost/");
  define("MODULES_PATH", ROOT_DIRECTORY . "/modules");

  // Debugging
  define("DEBUG_FILE", ROOT_DIRECTORY . "/debug.txt");
  define("DEBUG", "0");

  // Here we define what the top level group would be (for integration with prismatic):
  define("TOP_LEVEL_GROUP", "root");

  $GLOBALS['DB_PREFIX'] = DB_PREFIX;
  $GLOBALS['WEBROOT']   = WEBROOT;

  // e-mail;
  define("ADMIN_EMAIL", "andy@duncaningram.com");
  $CONST_EMAIL_LOOP    =  array("andy@duncaningram.com");

  if ($_SERVER['HTTP_HOST'] == CMS_SITE) {
    define("REF_SITE", "https://" . SITE_NAME . "/");
  } else {
    define("REF_SITE", "http://" . SITE_NAME . "/");
  }
  $GLOBALS['REF_SITE'] = REF_SITE;
?>
