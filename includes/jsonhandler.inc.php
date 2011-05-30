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
  
  if (!isset($_GET['id'])) {
    // In the future, check to make sure group with id 1 is really the 'root node' somehow.
    $_GET['id'] = 0;
  }

  include_once($GLOBALS['I_BASE']);
  include_once($GLOBALS['I_RS2JSON']);

  class JSONHandler extends Base {  
    public $G;
    
    public function __construct() {
      parent::__construct();
      $this->G = $GLOBALS['Globals'];   
    } // construct
    
    public function __destruct() {
      parent::__destruct();
      
    } // destruct
    
    public function GetEditUser($id) {
      try {
        $query = "
            select 
              {$GLOBALS['DB_PREFIX']}users.email as email,
              {$GLOBALS['DB_PREFIX']}users.password as password,
              {$GLOBALS['DB_PREFIX']}users.type as type,
              {$GLOBALS['DB_PREFIX']}contact_information.fname as fname,
              {$GLOBALS['DB_PREFIX']}contact_information.lname as lname,
              {$GLOBALS['DB_PREFIX']}contact_information.mname as mname,
              {$GLOBALS['DB_PREFIX']}contact_information.phone1 as phone1,
              {$GLOBALS['DB_PREFIX']}contact_information.phone2 as phone2,
              {$GLOBALS['DB_PREFIX']}contact_information.fax as fax,
              {$GLOBALS['DB_PREFIX']}contact_information.cell as cell
            from
              {$GLOBALS['DB_PREFIX']}users,
              {$GLOBALS['DB_PREFIX']}contact_information
            where
              {$GLOBALS['DB_PREFIX']}users.contact_id = {$GLOBALS['DB_PREFIX']}contact_information.id and
              {$GLOBALS['DB_PREFIX']}users.id='$id'
        ";
        $rs    = $this->G->DB->Execute($query);
        if ($this->G->DB->ErrorNo() == 0) {
          if ($rs->numrows() > 0) {
            return(rs2json($rs));
          } else {
            return("[{\"error\":\"No record found!\"}]");
          }
        } else {
          return("[{\"error\":\"Error in SQL query.\"}]");
        }
      } catch (exception $e) {
        $e = str_replace('"', "'", $e);
        return("[{\"error\":\"Exception in GetEditUser: $e\"}]");
      }
    } // GetEditUser
    
    public function UpdateUser($jsondata) {
      $query      = "update {$GLOBALS['DB_PREFIX']}users set email='{$jsondata['eduser_email']}', password='{$jsondata['eduser_password']}' where id='{$jsondata['eduser_id']}'";
      $this->G->DB->Execute($query);
      if ($this->G->DB->ErrorNo() != 0) 
      {
        return("[{\"status\":\"failure\"}]");
      }
      
      if (
        ($jsondata['eduser_id'] != $_SESSION['id']) &&
        ($_SESSION['type'] == 'admin')
      ) 
      {
        $query      = "update {$GLOBALS['DB_PREFIX']}users set type='{$jsondata['eduser_type']}' where id='{$jsondata['eduser_id']}'";
        $this->G->DB->Execute($query);
        if ($this->G->DB->ErrorNo() != 0) 
        {
          return("[{\"status\":\"failure\"}]");
        }
      } // if updated user is not me.
      
      if ($this->G->DB->ErrorNo() != 0) 
      {
        return("[{\"status\":\"failure\"}]");
      }
      $query      = "select contact_id from {$GLOBALS['DB_PREFIX']}users where id='{$jsondata['eduser_id']}'";
      $contact_id = $this->G->DB->GetOne($query);
      $query      = "update {$GLOBALS['DB_PREFIX']}contact_information set fname='{$jsondata['eduser_fname']}', lname='{$jsondata['eduser_lname']}', mname='{$jsondata['eduser_mname']}', phone1='{$jsondata['eduser_phone1']}', phone2='{$jsondata['eduser_phone2']}' where id='{$contact_id}'";
      $this->G->DB->Execute($query);
      if ($this->G->DB->ErrorNo() != 0) {
        return("[{\"status\":\"failure\"}]");       
      }
      return("[{\"status\":\"ok\"}]");
    } // UpdateUser 

    public function DeleteUser($jsondata) {      
        $query      = "select parent_id from {$GLOBALS['DB_PREFIX']}users where id='{$jsondata['eduser_id']}'";
        $parent_id = $this->G->DB->GetOne($query);      
        if ($this->G->DB->ErrorNo() != 0) 
        {
          return("[{\"status\":\"failure\"}]");
        }

      if (
        (
          ($_SESSION['type'] == 'admin') &&
          ($jsondata['eduser_id'] != $_SESSION['id'])
         ) ||
        (
          ($parent_id == $_SESSION['id']) &&
          ($jsondata['eduser_id'] != $_SESSION['id'])
        )
      ) 
      {
        $query      = "select contact_id from {$GLOBALS['DB_PREFIX']}users where id='{$jsondata['eduser_id']}'";
        $contact_id = $this->G->DB->GetOne($query);      
        if ($this->G->DB->ErrorNo() != 0) 
        {
          return("[{\"status\":\"failure\"}]");
        }
        $query      = "delete from {$GLOBALS['DB_PREFIX']}contact_information where id='{$contact_id}'";
        $this->G->DB->Execute($query);
        if ($this->G->DB->ErrorNo() != 0) 
        {
          return("[{\"status\":\"failure\"}]");
        }
        $query      = "delete from {$GLOBALS['DB_PREFIX']}users where id='{$jsondata['eduser_id']}'";
        $this->G->DB->Execute($query);
        if ($this->G->DB->ErrorNo() != 0) 
        {
          return("[{\"status\":\"failure\"}]");
        }
        return("[{\"status\":\"ok\"}]");
      } // if you're owner or admin
      return("[{\"status\":\"failure\"}]");
    } // DeleteUser 

    public function AddUser($jsondata) {
      $query      = "insert into {$GLOBALS['DB_PREFIX']}users (email, password) values ('{$jsondata['adduser_email']}', '{$jsondata['adduser_password']}')";
      $this->G->DB->Execute($query);
      if ($this->G->DB->ErrorNo() != 0) 
      {
        return("[{\"status\":\"failure\"}]");
      }
      // Get the newly added id:
      $adduser_id = $this->G->DB->Insert_ID();
      
      if ($_SESSION['type'] == 'admin') {
        $query      = "update {$GLOBALS['DB_PREFIX']}users set type='{$jsondata['adduser_type']}' where id='{$adduser_id}'";
        $this->G->DB->Execute($query);
        if ($this->G->DB->ErrorNo() != 0) 
        {
          return("[{\"status\":\"failure\"}]");
        }
      } // if updated user is not me.
      
      // Why is this here?  redundant?
      if ($this->G->DB->ErrorNo() != 0) 
      {
        return("[{\"status\":\"failure\"}]");
      }
      $query      = "insert into {$GLOBALS['DB_PREFIX']}contact_information (fname, mname, lname, phone1, phone2) values ('{$jsondata['adduser_fname']}', '{$jsondata['adduser_mname']}', '{$jsondata['adduser_lname']}', '{$jsondata['adduser_phone1']}', '{$jsondata['adduser_phone2']}')";
      $this->G->DB->Execute($query);
      if ($this->G->DB->ErrorNo() != 0) {
        return("[{\"status\":\"failure\"}]");       
      }
      
      $contact_id = $this->G->DB->Insert_ID();
      $query      = "update {$GLOBALS['DB_PREFIX']}users set contact_id='{$contact_id}' where id='{$adduser_id}'";
      $contact_id = $this->G->DB->GetOne($query);
      if ($this->G->DB->ErrorNo() != 0) {
        return("[{\"status\":\"failure\"}]");       
      }
      
      return("[{\"status\":\"ok\"}]");
    } // AddUser 
    
    public function setGroupCalendarDate($gid, $date, $text) {
      if (in_array("cgroup", $this->G->ACL->GetGroupACL($gid, $_SESSION['id']))) {
        list($m,$d,$y) = split('/', $date);
        $date = "$y-$m-$d";

        $query  = "delete from {$GLOBALS['DB_PREFIX']}group_calendar where group_id='$gid' and `date`='$date'";
        $this->G->DB->Query($query);
        
        if (trim($text) && $text != "&nbsp;") {
          $query  = "insert into {$GLOBALS['DB_PREFIX']}group_calendar (`group_id`, `date`, `text`) values ('$gid', '$date', '$text')";
          $this->G->DB->Query($query);
        }
      }
    }
    
    public function getGroupCalendarDate($gid, $date) {
      list($m,$d,$y) = split('/', $date);
      $date = "$y-$m-$d";      
      $query  = "select * from {$GLOBALS['DB_PREFIX']}group_calendar where group_id=$gid and `date`='$date'";
      $record = $this->G->DB->GetRow($query);
      if ($record != null) {
        print $record['text'];
      }
      
    } // getGroupCalendarDate
    
    public function getGroupChildren($node) {
  		$returnArray = null;
  		$sql = null;	
  		
  		$node->objectId;
  		
  		if ($node->objectId == "root") {
  			$query    = "select * from {$GLOBALS['DB_PREFIX']}groups where parent_id='0'";
  			$objectId = "department";
  			$isFolder = true;
  		} 
  		
      if ($node->objectId == "department" ) {
  			$objectId = "song";
  			$widgetId = $node->widgetId;	
  			$query    = "select * from {$GLOBALS['DB_PREFIX']}groups where parent_id='$widgetId'";	
  			$isFolder = true;
  		}
  		
  		$i    = 0;
  		$rs   = $this->G->DB->Execute($query);
  		while ($row = $rs->FetchRow()) {
  		  // find out if it has children;
  		  $query    = "select count(*) from {$GLOBALS['DB_PREFIX']}groups where parent_id='{$row['id']}'";
  		  $children = $this->G->DB->GetOne($query);
  		  if ($children <= 0) {
  		    $isFolder = false;
  		  }
  		 	$node = array(
   				"title"        => "" . $row['name'],
   				"description"  => "" . $row['description'],
   				"widgetId"     => "" . $row['id'],
   				"objectId"     => "" . $objectId,
   				"isFolder"     => $isFolder,
   			);
   		 	$returnArray[$i] = $node;
  			$i++;
  		
  		}
   		return $returnArray;	

    } // getGroupChildren
    
    public function GetUserListing() {
      $query = "
        SELECT
        	me.id AS id,
        	concat(mec.fname, ' ', mec.lname) AS name,
        		IF (ISNULL(pc.id), NULL, concat(pc.fname, ' ', pc.lname)) as parent,
        	me.email AS email
        FROM
        	{$GLOBALS['DB_PREFIX']}users me
        	JOIN {$GLOBALS['DB_PREFIX']}contact_information mec ON (me.contact_id = mec.id)
        	LEFT OUTER JOIN {$GLOBALS['DB_PREFIX']}users p ON (me.parent_id = p.id)
        	LEFT OUTER JOIN {$GLOBALS['DB_PREFIX']}contact_information pc on (p.contact_id = pc.id)
      ";
      $rs    = $this->G->DB->Execute($query);
      return(rs2json($rs));
    }
    
  } // JSONHandler
  
  if (
    (isset($_POST['jsonget'])) ||
    (isset($_GET['action']))
  ) {
    $JSONHandler = new JSONHandler();
    
    if (isset($_POST['edituser'])) {
      print $JSONHandler->GetEditUser($_POST['edituser']);
      exit;
    } // edituser

    if (isset($_POST['deleteuser'])) {
      $_POST['deleteuser'] = str_replace("\\\"", "\"", $_POST['deleteuser']);
      $_POST['deleteuser'] = str_replace("\n", "", $_POST['deleteuser']);
      $_POST['deleteuser'] = str_replace("\r", "", $_POST['deleteuser']);
      print $JSONHandler->DeleteUser($_POST['deleteuser']);
      exit;
    } // deleteuser
    
    if (isset($_POST['updateuser'])) {
      // clean up the json data;
      $_POST['updateuser'] = str_replace("\\\"", "\"", $_POST['updateuser']);
      $_POST['updateuser'] = str_replace("\n", "", $_POST['updateuser']);
      $_POST['updateuser'] = str_replace("\r", "", $_POST['updateuser']);
      print $JSONHandler->UpdateUser(json_decode($_POST['updateuser'], true));
      exit;
    } // updateuser
    
    if (isset($_POST['adduser'])) {
      // clean up the json data;
      $_POST['adduser'] = str_replace("\\\"", "\"", $_POST['adduser']);
      $_POST['adduser'] = str_replace("\n", "", $_POST['adduser']);
      $_POST['adduser'] = str_replace("\r", "", $_POST['adduser']);
      print $JSONHandler->AddUser(json_decode($_POST['adduser'], true));
      exit;    
    } // adduser

    if (isset($_GET['action'])) {
      if ($_GET['action'] == "getChildren") {
        $_GET['data'] = str_replace("\\\"", "\"", $_GET['data']);
        $_GET['data'] = str_replace("\n", "", $_GET['data']);
        $_GET['data'] = str_replace("\r", "", $_GET['data']);
     		$jsonArray = json_decode($_GET['data']);
     		$nodeArray = $JSONHandler->getGroupChildren($jsonArray->node);
     		print json_encode($nodeArray);
        exit;
     	}
      if ($_GET['action'] == "getGroupCalendarDate") {
     		$JSONHandler->getGroupCalendarDate($_GET['gid'], $_GET['date']);
        exit;        
      }
      if ($_GET['action'] == "setGroupCalendarDate") {
     		print $JSONHandler->setGroupCalendarDate($_GET['gid'], $_GET['date'], $_POST['text']);
        exit;        
      }
   		exit;
    } // getGroupChildren
        
    if (isset($_POST['userlisting'])) {
      print $JSONHandler->GetUserListing();
      exit;
    } // userlisting
    
  } 

?>
