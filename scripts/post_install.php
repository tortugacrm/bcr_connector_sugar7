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
function post_install()
{
	// set the tokens value for each user
	// if the token already exists or is an empty string, left unchanged 
	require_once('modules/Administration/Administration.php');
	$focus = new Administration();
	$query = 
		"insert into users_cstm(id_c, bcrtoken_c) ".
		"select u.id, LEFT(UUID(), 8) ".
		"from users u ".
		"left join users_cstm uc on u.id=uc.id_c ".
		"where (uc.id_c is null or (uc.id_c is not null and uc.bcrtoken_c is null)) ".
		"group by u.id"; 
	$focus->db->query($query, true);

	// create the email template
	$http_protocol=(isset($_SERVER['HTTPS']))?'https':'http';  // get the protocol
	//$url=$http_protocol.'://'.$_SERVER['SERVER_NAME']; // get the domain name
	require('config.php');
	$url=$sugar_config['site_url'];
	require_once('modules/EmailTemplates/EmailTemplate.php');
	$em = new EmailTemplate();
	$em->name='BCR token email';
	$em->description='BCR token email sent to the end user providing the url for the BCR API';
	$em->subject='Business Card Reader set up';
	$em->body_html=
		"<p>Dear \$contact_user_full_name,</p>" .
		"<p>in order to save your business cards from the mobile app Business Card Reader to Sugar, please configure the BCR API settings with this url:<br />" .
		$url."/index.php?entryPoint=BCR&u=\$contact_user_user_name&t=\$contact_user_bcrtoken_c</p>" .
		"<p> Have a great day.</p>" .
		"<p>Your Sugar administrator.</p>";
	$em->body=
		"Dear \$contact_user_full_name,\n" .
		"in order to save your business cards from the mobile app Business Card Reader to Sugar, please configure the BCR API settings with this url:\n" .
		$url."/index.php?entryPoint=BCR&u=\$contact_user_user_name&t=\$contact_user_bcrtoken_c\n" .
		"Have a great day.\n" .
		"Your Sugar administrator.\n";
	$em->save();

}
?>
