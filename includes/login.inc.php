<?php
  /*
   * Copyright (c) 2003-2006 Andy Duncan
   * Copyright (c) 2007 Duncan Ingram Inc.
   * All Rights Reserved
   *
   */

  // A simple login form.

  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['I_DIALOGS']);
    
class Login extends Base {

  public $G;
  public function __construct() {
    parent::__construct();
    $this->G = $GLOBALS['Globals'];
  } // construct
  
  public function __destruct() {
    parent::__destruct();
  } // destruct

  public function valid_email($email) {
    // First, we check that there's one @ symbol, and that the lengths are right
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
      // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
      return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
       if (!ereg("^(([A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
        return false;
      }
    }  
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
      $domain_array = explode(".", $email_array[1]);
      if (sizeof($domain_array) < 2) {
          return false; // Not enough parts to domain
      }
      for ($i = 0; $i < sizeof($domain_array); $i++) {
        if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
          return false;
        }
      }
    }
    return true;
  } 

  public function PrintForm() {
    if (
      isset($_GET['location']) &&
      $_GET['location'] != "H_LOGIN" &&
      $_GET['location'] != "H_LOGOUT"
    ) {
      $url = 'http';
      if (isset($_SERVER['HTTPS'])) {
        if ($_SERVER["HTTPS"] == "on") {
          $url .= "s";
        }
      }
      $url .= "://";
      /*
      if ($_SERVER["SERVER_PORT"] != "80") {
        $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
      } else {
        $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
      }
      */
      $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
      $redirect = $url;
    } else {
      $redirect = $GLOBALS['H_MYCONTENT_location'];
    }
    /*
<script>
  $(document).ready(function() {
    $('form[name=loginform]').submit(function() {
      var username = $('[name=username]').val();
      var password = $('[name=password]').val();
      $.post(
        '<?php print $GLOBALS['AJAX_LOGIN_location']; ?>',
        {
          jsonget: '1',
          username: username,
          password: password
        },
        function(data) {
          alert('Got something');
          if (data.success) {
            alert('Logged in!');
          } else {
            alert('login failed');
          }
        },
        'json'
      );
      return false;
    });
  });
</script>

    */
?>
<div align="center"><img src="./images/granola_logo.jpg" alt="Granola" /></div>
<div align="center">
  <form id="loginform" name="loginform" method="POST" action="<?php print $GLOBALS['H_LOGIN_location']; ?>">
    <table>
      <th colspan="2">
        <b>Please Log In</b>
      </th>
      <tr>
        <td>E-Mail Address:</td>
        <td>
          <input
            dojoType="dijit.form.TextBox"
            type="text"
            name="username"
            class="input"
            value="<?php print $this->G->GetPostValue('username'); ?>"
          />
        </td>
      </tr><tr>
        <td>Password:</td>
        <td>
          <input
            dojoType="dijit.form.TextBox"
            type="password"
            name="password"
            class="input"
            value="<?php print $this->G->GetPostValue('password'); ?>"
          />
        </td>
      </tr><tr>
        <td colspan="2" align="left">
          <input
            dojoType="dijit.form.Button"
            type="submit"
            name="submit"
            value="Login &raquo;"
            label="Login &raquo;"
          />
        </td>
      </tr>
    </table>
  </form>
</div>
<?php
  } // PrintForm()
  
} // Base

$Login = new Login();

if (isset($_POST['forgot'])) {
  // check valid e-mail.
  $email = $_POST['username'];
  if ($Login->valid_email($email)) {
    print "An e-mail has been sent to {$email} if that user is part of this system.  Please check your e-mail for further instructions.<br/>";
  } else {
    print "The login you supplied was not a valid e-mail address.<br/>";
  }
}

if (isset($_POST['submit'])) {
  include_once($GLOBALS['I_AUTH']);
  $Auth = new Authentication;
  $Auth->Authenticate($_POST['username'], $_POST['password']);
  if (!isset($_SESSION['authenticated'])) {
    $Dialog = new Dialogs();
    $Dialog->PrintDialog('error', "Error Logging In", "Invalid login, please try again.");
    $Login->PrintForm();
  } else {
    // redirect to previous url:
    if (isset($_POST['redirect'])) {
      header("Location: " . $_POST['redirect']);
    }
    include_once(HTML_BODY);
  }
} else {
  $Login->PrintForm();
} // 

?>