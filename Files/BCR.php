<?php
/*
 *  This file is part of 'BCR Card reader integration'.
 *
 *  'BCR Card reader integration' is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation.
 *
 *  'BCR Card reader integration' is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with 'BCR Card reader integration'.  If not, see http://www.gnu.org/licenses/gpl.html.
 *
 * Copyright March 2014 Olivier Nepomiachty - All rights reserved.
 */
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$db = DBManagerFactory::getInstance();

// usage: http://<your_domain>/index.php?entryPoint=BCR&u=sally&p=sally

/* check the user's credentials
 *  we passed the login and password through the url which is not a best practice 
 *  but easier for the end user when setting up the connector.
 *  In a future release I will implement a system using tokens.
*/
$user_id='';
if ( isset($_GET['u']) && isset($_GET['p']) ) {
	$GLOBALS['log']->debug("BCR: Authentication user + password");
	$u=$_GET['u'];
	$pwd=$_GET['p'];
	$user = new User();
	$u=$user->db->quote($u);
	$sql="select id,user_hash from users where user_name='$u' and deleted=0 and status='Active'";
	$result = $db->query($sql);
	$r = $db->fetchByAssoc($result);
	if ($r==null) {
		$GLOBALS['log']->debug("BCR: Error. User $u not found.");
		die('Security error #1');
	}

	if((!$user->checkPassword($pwd, $r['user_hash']))) {
		$GLOBALS['log']->debug("BCR: Error. user: $u. password does not match");
		die('Security error #2');
	}
	$user_id=$r['id'];
}
else if ( isset($_GET['u']) && isset($_GET['t']) ) {
	$GLOBALS['log']->debug("BCR: Authentication user + token");
	$u=$_GET['u'];
	$token=$_GET['t'];
	$user = new User();
	$u=$user->db->quote($u);
	$token=$user->db->quote($token);
	$sql=
		"select u.id " .
		"from users u " .
		"left join users_cstm uc on u.id=uc.id_c " . 
		"where (uc.bcrtoken_c='$token' and u.user_name='$u' and u.deleted=0 and u.status='Active') " . 
		"group by u.id";
	$result = $db->query($sql);
	$r = $db->fetchByAssoc($result);
	if ($r==null) {
		$GLOBALS['log']->debug("BCR: Error. User $u + token $token not found.");
		die('Security error #3');
	}
	$user_id=$r['id'];
}
else {
	$GLOBALS['log']->debug("BCR: Error. Please provide a user name + password or token");
	die('Error. Please provide a user name + password or token');
}

// Get the inbound data
$v=$_POST;
if (!isset($v['companyName'])) {
	$GLOBALS['log']->debug("BCR: Error. no data");
	die('Error: no data');
}

// Account
$companyName=$v['companyName'];
$first_name=$v['personFirstName'];
$last_name=$v['personLastName'];
// we do not use "retrieveBean" because the ACL don't work with a non authentified entry point
$acc = new Account();
$sql="select id from accounts where name like '".$acc->db->quote($companyName)."' and deleted=0";
$result = $db->query($sql);
$r = $db->fetchByAssoc($result);
$sql='';
if ($r!=null) { // found at least one account with the same name
	$acc->retrieve($r['id']); 
	$acc=BeanFactory::retrieveBean('Accounts', $r['id'], array('disable_row_level_security' => true));	
	$GLOBALS['log']->debug("BCR: match with an existing account: ".$acc->id);
	$sql_where_contact=" and account_id='".$r['id']."' ";
	$sql=
		"select co.id ".
		"from contacts co ". 
		"left join accounts_contacts ac on ac.contact_id=co.id ".
		"where co.first_name like '".$acc->db->quote($first_name)."' and ".
		"co.last_name like '".$acc->db->quote($last_name)."' and ac.account_id='".$r['id']."' and co.deleted=0 and ac.deleted=0 ".
		"group by co.id";
}
else {  // create a new account
	$GLOBALS['log']->debug("BCR: create new account");
	$acc->name=$companyName;
	if (isset($v['addresses'])) {
		$acc->billing_address_street=$v['addresses'][0]['street'];
		$acc->billing_address_city=$v['addresses'][0]['city'];
		$acc->billing_address_country=$v['addresses'][0]['country'];
	}
	$acc->assigned_user_id=$user_id;
	$acc->modified_user_id=$user_id;
	$acc->team_id='1';
	$acc->save();
	$GLOBALS['log']->debug("BCR: new account created: ".$acc->id);
}

// Contact
$con = new Contact();
if ($sql=='') 
	$sql="select id from contacts where first_name like '".$con->db->quote($first_name)."' and ".
	"last_name like '".$con->db->quote($last_name)."' and deleted=0";
$result = $db->query($sql);
$r = $db->fetchByAssoc($result);
if ($r!=null) { // found at least one account with the same name
	// we do not use "retrieveBean" because the ACL don't work with a non authentified entry point
	$con=BeanFactory::retrieveBean('Contacts', $r['id'], array('disable_row_level_security' => true));	
	$GLOBALS['log']->debug("BCR: match with an existing contact: ".$con->id);
}
else {
	$GLOBALS['log']->debug("BCR: create new contact");
	$con->assigned_user_id=$user_id;
	$con->account_id=$acc->id;
	$con->team_id='1';
}
$con->modified_user_id=$user_id;
$con->first_name=$first_name;
$con->last_name=$last_name;
if (isset($v['addresses'])) {
	$con->primary_address_street=$v['addresses'][0]['street'];
	$con->primary_address_city=$v['addresses'][0]['city'];
	$con->primary_address_country=$v['addresses'][0]['country'];
}
if (isset($v['otherInfo'])) {
	if ($con->description!='') $con->description.="\n";
	$con->description.=$v['otherInfo'][0];
}
$GLOBALS['log']->debug("BCR: contact description: ".$con->description);
if (isset($v['phones']['work'][0])) $con->phone_work=$v['phones']['work'][0];
if (isset($v['phones']['work'][1])) $con->phone_other=$v['phones']['work'][1];
$con->title=$v['job'];

$con->save(); // Create or update

if (isset($v['otherInfo'])) {
	preg_match("/[a-zA-Z\._-]+@[a-zA-Z\.-]+\.[a-zA-Z\.-]+/", $v['otherInfo'][0], $matches);
	if (isset($matches)) {
		$sea = new SugarEmailAddress;
		// Add a primary email address
		$sea->addAddress($matches[0], true); 
		// Associate the email address with the given module and record
		$sea->save($con->id, 'Contacts');
		$GLOBALS['log']->debug("BCR: found an email address in otherInfo: ".$matches[0]);
	}
}
$GLOBALS['log']->debug("BCR: contact id: ".$con->id);

echo "OK";
?>
