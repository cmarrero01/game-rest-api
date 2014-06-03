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
	* ->	Result File													
	*		Shows results 
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
require_once($gl_root_path . 'include/functions_common.php');

// Set PAGE to be ADMINPAGE!
define('IS_ADMINPAGE', true);
$content['IS_ADMINPAGE'] = true;

InitUltraStats();
// WTF OMFG WHY !!!!! $_SESSION is empty here :S:S:S:S! How the fuck can this be :S
CheckForUserLogin( false );
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );

// Hardcoded atm
$content['REDIRSECONDS'] = 1;
// ***					*** //

// --- BEGIN Custom Code
if ( isset($_GET['redir']) )
{
	$content['EXTRA_METATAGS'] = '<meta HTTP-EQUIV="REFRESH" CONTENT="' . $content['REDIRSECONDS'] . '; URL=' . urldecode($_GET['redir']) . '">';
	$content['SZREDIR'] = urldecode($_GET['redir']);
}
else
{
	$_GET['redir'] = "index.php";
}

if ( isset($_GET['msg']) )
	$content['SZMSG'] = urldecode($_GET['msg']);
else
	$content['SZMSG'] = "*Unknown State";

// Create redir txt
$content['RESULT_REDIRTXT'] = GetAndReplaceLangStr( $content['LN_RESULT_REDIRTXT'], $content['SZREDIR'], $content['REDIRSECONDS']); 
// --- 

// --- BEGIN CREATE TITLE
$content['TITLE'] = InitPageTitle();
$content['TITLE'] .= " :: " . GetAndReplaceLangStr( $content['LN_RESULT_REDIRTITLE'], $content['REDIRSECONDS']);
// --- END CREATE TITLE

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/result.html");
$page -> output(); 
// --- 

?>