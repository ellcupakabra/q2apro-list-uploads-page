<?php
/*
	Plugin Name: Image Manager / List Uploads
	Plugin URI: https://github.com/echteinfachtv/q2a-list-uploads-page
	Plugin Description: Displays the newest image uploads on a separate page
	Plugin Version: 0.5
	Plugin Date: 2012-12-29
	Plugin Author: q2apro.com
	Plugin Author URI: http://www.q2apro.com
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: https://raw.github.com/echteinfachtv/q2a-list-uploads-page/master/qa-plugin.php

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
	
*/

if ( !defined('QA_VERSION') )
{
	header('Location: ../../');
	exit;
}

// page
qa_register_plugin_module('page', 'qa-list-uploads-page.php', 'qa_list_uploads_page', 'New Uploads Page');

// language file
qa_register_plugin_phrases('qa-list-uploads-lang.php', 'qa_list_uploads_lang');



/*
	Omit PHP closing tag to help avoid accidental output
*/