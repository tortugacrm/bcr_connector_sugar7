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
global $sugar_flavor;		

$manifest =array(
        'acceptable_sugar_flavors' => array('PRO','CORP','ENT','ULT'),
        'acceptable_sugar_versions' => array(
            'exact_matches' => array(),
            'regex_matches' => array('7\\.[0-9]\\.[0-9]$'),
        ),
	  //'readme'=>'readme.txt',
	  'key'=>'BCR connector',
	  'author' => 'Olivier Nepomiachty',
	  'description' => 'Business Card Reader connector',
	  'icon' => '',
	  'is_uninstallable' => true,
	  'name' => 'BCR connector',
	  'published_date' => '2014-03-14 0900',
	  'type' => 'module',
	  'version' => '1.1.10'
);
		

$installdefs = array (
    'id' => 'BCR',

    'copy' => array (
        array (
            'from' => '<basepath>/Files/BCR.php',
            'to' => 'custom/BCR.php',
            ),
        ),

    'entrypoints' => array (
        array (
            'from' => '<basepath>/Files/BCR_customEntryPoint.php',
            'to_module' => 'application',
            ),
        ),

'custom_fields' => array (
     array (
         'name' => 'bcrtoken_c',
         'label' => 'BCR token',
         'type' => 'varchar',
         'max_size' => 255,
         'require_option' => 'optional',
         'default_value' => '',
         'ext1' => '',
         'ext2' => '',
         'ext3' => '',
         'audited' => 1,
         'module' => 'Users',
     ),
 ),
 
);


