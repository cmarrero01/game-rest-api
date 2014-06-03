<?php
/*
	********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						
	* -> www.ultrastats.org <-											
	* ------------------------------------------------------------------
	*
	* Use this script at your own risk!									
	*
	* ------------------------------------------------------------------
	* ->	Login File													
	*		This page does the user login
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/


// *** Default includes	and procedures *** //
define('IN_ULTRASTATS', true);
$gl_root_path = './../';
include($gl_root_path . 'include/functions_common.php');

// Set PAGE to be ADMINPAGE!
define('IS_ADMINPAGE', true);
$content['IS_ADMINPAGE'] = true;

InitUltraStats();
CheckForUserLogin( true );
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- BEGIN CREATE TITLE
$content['TITLE'] = InitPageTitle();
$content['TITLE'] .= " :: Login";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// Set Defaults
$content['uname'] = "";
$content['pass'] = "";

// Set Referer
if ( isset($_GET['referer']) )
	$szRedir = $_GET['referer'];
else
	$szRedir = "index.php"; // Default

if ( isset($_POST['op']) )
{
	// Set Referer
	if ( isset($_POST['url']) )
	{
		if ( $_POST['url'] == "" )
			$szRedir = "index.php"; // Default
		else
			$szRedir = DB_RemoveBadChars($_POST['url']);
	}
	else
		$szRedir = "index.php"; // Default


	if ( $_POST['op'] == "login" )
	{
		// TODO: $my_rememberme = $_POST['rememberme'];
		if ( isset($_POST['uname']) && isset($_POST['pass']) )
		{
			// Set Username and password
			$content['uname'] = DB_RemoveBadChars($_POST['uname']);
			$content['pass'] = DB_RemoveBadChars($_POST['pass']);

			if ( !CheckUserLogin( $content['uname'], $content['pass']) )
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = $content['LN_LOGIN_ERRORWRONGUSER'];
			}
			else
				RedirectPage( $szRedir );
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_LOGIN_ERRORUSERPASSNOTGIVEN'];
		}
	}
}

if ( isset($_GET['op']) && $_GET['op'] == "logoff" )
{
	// logoff in this case
	DoLogOff();
}

// --- Set redir var
$content['REDIR_LOGIN'] = $szRedir;

// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/login.html");
$page -> output(); 
// --- 

?>