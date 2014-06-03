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
	* ->	String Editor File													
	*		The string editor helps you to easily add, edit and remove
	*		strings from the languagestrings table. 
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
CheckForUserLogin( false );
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- BEGIN CREATE TITLE
$content['TITLE'] = InitPageTitle();
$content['TITLE'] .= " :: String Editor ";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// --- Read Vars
if ( isset($_GET['start']) )
	$content['current_pagebegin'] = intval(DB_RemoveBadChars($_GET['start']));
else
	$content['current_pagebegin'] = 0;

if ( isset($_GET['strfilter']) && strlen($_GET['strfilter']) > 0 )
{
	$content['strfilter'] = DB_RemoveBadChars($_GET['strfilter']);
	$content['strsqlwhere'] = " WHERE " . STATS_LANGUAGE_STRINGS . ".STRINGID LIKE '%" . $content['strfilter'] . "%' ";
}
else
{
	$content['strfilter'] = "";
	$content['strsqlwhere'] = ""; 
}
// ---

// --- Set Referer vars 
if ( isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 0 )
	$content['encoded_referer'] = urlencode($_SERVER['HTTP_REFERER']);
else
	$content['encoded_referer'] = "";

if ( isset($_POST['referer']) && strlen($_POST['referer']) > 0 )
	$content['received_referer'] = urldecode($_POST['referer']);
else
	$content['received_referer'] = "";
// --- 

// --- BEGIN Custom Code
if ( isset($_GET['op']) )
{
	if ($_GET['op'] == "add") 
	{
		// Set Mode to add
		$content['ISEDITSTRING'] = "true";
		$content['STRING_FORMACTION'] = "add";
		$content['STRING_SENDBUTTON'] = $content['LN_STRING_ADD'];

		//PreInit these values 
		$content['STRINGID'] = "";
		$content['TEXT'] = "";

		// Set default language ID and set the right one to selected!
		$content['LANG'] = "EN";
		$content['FORM_LANGUAGES'] = $content['LANGUAGES'];
		for($i = 0; $i < count($content['FORM_LANGUAGES']); $i++)
		{
			if ( strtoupper($content['FORM_LANGUAGES'][$i]['langcode']) == strtoupper($content['LANG']) )
				$content['FORM_LANGUAGES'][$i]['is_selected'] = "selected";
			else
				$content['FORM_LANGUAGES'][$i]['is_selected'] = "";
		}

	}
	else if ($_GET['op'] == "edit") 
	{
		// Set Mode to edit
		$content['ISEDITSTRING'] = "true";
		$content['STRING_FORMACTION'] = "edit";
		$content['STRING_SENDBUTTON'] = $content['LN_STRING_EDIT'];

		if ( isset($_GET['id']) && isset($_GET['lang']) )
		{
			//PreInit these values 
			$content['STRINGID'] = DB_RemoveBadChars($_GET['id']);
			$content['LANG'] = DB_RemoveBadChars($_GET['lang']);

			$sqlquery = "SELECT " . 
						STATS_LANGUAGE_STRINGS . ".LANG, " . 
						STATS_LANGUAGE_STRINGS . ".STRINGID, " . 
						STATS_LANGUAGE_STRINGS . ".TEXT " . 
						" FROM " . STATS_LANGUAGE_STRINGS . 
						" WHERE " . STATS_LANGUAGE_STRINGS . ".STRINGID = '" . $content['STRINGID'] . "' 
						  AND " . STATS_LANGUAGE_STRINGS . ".LANG = '" . $content['LANG'] . "'";
			$result = DB_Query($sqlquery);
			$myrow = DB_GetSingleRow($result, true);
			if ( isset($myrow['STRINGID'] ) )
			{
				$content['STRINGID'] = $myrow['STRINGID'];
				$content['TEXT'] = $myrow['TEXT'];
				
				// Get language ID and set the right to selected!
				$content['LANG'] = $myrow['LANG'];
				$content['FORM_LANGUAGES'] = $content['LANGUAGES'];
				for($i = 0; $i < count($content['FORM_LANGUAGES']); $i++)
				{
					if ( strtoupper($content['FORM_LANGUAGES'][$i]['langcode']) == strtoupper($content['LANG']) )
						$content['FORM_LANGUAGES'][$i]['is_selected'] = "selected";
					else
						$content['FORM_LANGUAGES'][$i]['is_selected'] = "";
				}
			}
			else
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_STRING_ERROR_NOTFOUND'], $content['STRINGID'] ); 
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_STRING_ERROR_INVID'];
		}
	}
	else if ($_GET['op'] == "delete") 
	{
		// Set Mode to edit
		$content['ISDELETESTRING'] = "true";

		if ( isset($_GET['id'])  && isset($_GET['lang']) )
		{
			//PreInit these values 
			$content['STRINGID'] = DB_RemoveBadChars($_GET['id']);
			$content['LANG'] = DB_RemoveBadChars($_GET['lang']);

			if ( isset($_GET['verify']) && $_GET['verify'] == "yes" )
			{
				// Disable Verify few
				$content['ISVERIFY'] = "false";

				// Start Deleting the User stats!
				$sqlquery = "DELETE FROM " . STATS_LANGUAGE_STRINGS .	" WHERE STRINGID = '" . $content['STRINGID'] . "' AND LANG = '" . $content['LANG'] . "'";
				ProcessDeleteStatement( $sqlquery );

				// For confirmation
				RedirectResult( GetAndReplaceLangStr( $content['LN_STRING_DELETEDSTRING'], $content['STRINGID'] ), "stringeditor.php?strfilter=" . $content['strfilter'] . "&start=" . $content['current_pagebegin'] );
			}
			else
			{
				// Enable Verify few
				$content['ISVERIFY'] = "true";
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_STRING_ERROR_INVID'];
		}
	}

	if ( isset($_POST['op']) )
	{
		if ( isset ($_POST['id']) ) { $content['STRINGID'] = DB_RemoveBadChars($_POST['id']); } else {$content['STRINGID'] = ""; }
		if ( isset ($_POST['langcode']) ) { $content['LANG'] = DB_RemoveBadChars($_POST['langcode']); } else {$content['LANG'] = "EN"; }
		if ( isset ($_POST['text']) ) { $content['TEXT'] = DB_RemoveBadChars($_POST['text']); } else {$content['TEXT'] = ""; }
		if ( isset ($_POST['oldid']) ) { $content['OLDSTRINGID'] = DB_RemoveBadChars($_POST['oldid']); } else {$content['OLDSTRINGID'] = $content['STRINGID']; }
		if ( isset ($_POST['oldlang']) ) { $content['OLDLANG'] = DB_RemoveBadChars($_POST['oldlang']); } else {$content['OLDLANG'] = $content['LANG']; }
		$content['LANG'] = strtoupper($content['LANG']);
		$content['OLDLANG'] = strtoupper($content['OLDLANG']);

		// Check mandotary values
		if ( !isset($content['STRINGID']) || strlen($content['STRINGID']) <= 0 )
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_STRING_ERROR_IDEMPTY'];
		}

		if ( !isset($content['ISERROR']) ) 
		{	
			if ( $_POST['op'] == "add" )
			{
				$result = DB_Query("SELECT Name FROM " . STATS_LANGUAGE_STRINGS . " WHERE 
					STRINGID = '" . $content['STRINGID'] . "' AND
					LANG = '" . $content['LANG'] . "'");
				$rows = DB_GetAllRows($result, true);
				if ( isset($rows) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = $content['LN_STRING_ERROR_ALREADYEXISTS'];
				}
				else
				{
					// Add new Server now!
					$result = DB_Query("INSERT INTO " . STATS_LANGUAGE_STRINGS . " (STRINGID, LANG, TEXT) 
					VALUES ('" . $content['STRINGID'] . "', 
							'" . $content['LANG'] . "',
							'" . $content['TEXT'] . "' 
							)");
					DB_FreeQuery($result);
					
					// Redirect!
					RedirectResult( GetAndReplaceLangStr( $content['LN_STRING_SUCCADDED'], $content['STRINGID'] ) , "stringeditor.php" );
				}
			}
			else if ( $_POST['op'] == "edit" )
			{
				$result = DB_Query("SELECT STRINGID FROM " . STATS_LANGUAGE_STRINGS . " WHERE STRINGID = '" . $content['OLDSTRINGID'] . "' AND LANG = '" . $content['OLDLANG'] . "' ");
				$myrow = DB_GetSingleRow($result, true);
				if ( !isset($myrow['STRINGID']) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_STRING_ERROR_NOTFOUND'], $content['STRINGID'] ); 
				}
				else
				{
					// Edit the String now!
					$sqlquery ="UPDATE " . STATS_LANGUAGE_STRINGS . " SET 
								STRINGID = '" . $content['STRINGID'] . "', 
								LANG = '" . $content['LANG'] . "', 
								TEXT = '" . $content['TEXT'] . "' 
								WHERE STRINGID = '" . $content['OLDSTRINGID'] . "' AND LANG = '" . $content['OLDLANG'] . "'";
					$result = DB_Query( $sqlquery );
					DB_FreeQuery($result);

					// Redirect - may with PAGER later!
					if ( strlen( $content['received_referer'] ) > 0 )
						RedirectResult( GetAndReplaceLangStr( $content['LN_STRING_SUCCEDIT'], $content['STRINGID'] ) , $content['received_referer'] );
					else
						RedirectResult( GetAndReplaceLangStr( $content['LN_STRING_SUCCEDIT'], $content['STRINGID'] ) , "stringeditor.php" );
				}
			}
		}
	}
}
else
{
	// Default Mode = List Players
	$content['LISTSTRINGS'] = "true";

	// --- First get the Count and Set Pager Variables
	$sqlquery = "SELECT " . 
				"count(" . STATS_LANGUAGE_STRINGS . ".STRINGID) as StringCount " . 
				" FROM " . STATS_LANGUAGE_STRINGS . 
				$content['strsqlwhere'] . 
				" GROUP BY " . STATS_LANGUAGE_STRINGS . ".STRINGID ";
	$content['string_count'] = DB_GetRowCount( $sqlquery );
	if ( $content['string_count'] > $content['admin_maxplayers'] ) 
	{
		$pagenumbers = $content['string_count'] / $content['admin_maxplayers'];

		// Check PageBeginValue
		if ( $content['current_pagebegin'] > $content['string_count'] )
			$content['current_pagebegin'] = 0;

		// Enable Player Pager
		$content['string_pagerenabled'] = "true";
	}
	else
	{
		$content['current_pagebegin'] = 0;
		$pagenumbers = 0;
	}
	// --- 

// --- Now the final query !
	// Read all Strings
	$sqlquery = "SELECT " . 
				STATS_LANGUAGE_STRINGS . ".LANG, " . 
				STATS_LANGUAGE_STRINGS . ".STRINGID, " . 
				STATS_LANGUAGE_STRINGS . ".TEXT " . 
				" FROM " . STATS_LANGUAGE_STRINGS . 
				$content['strsqlwhere'] . 
				" ORDER BY " . STATS_LANGUAGE_STRINGS . ".STRINGID " .  
				" LIMIT " . $content['current_pagebegin'] . " , " . $content['admin_maxplayers'];
	$result = DB_Query($sqlquery);
	$content['STRINGS'] = DB_GetAllRows($result, true);

	// For the eye
	$css_class = "line0";
	for($i = 0; $i < count($content['STRINGS']); $i++)
	{
		// --- Set Number
		$content['STRINGS'][$i]['Number'] = $i+1;
		// ---
		
		// --- Trunscate string for display
		$content['STRINGS'][$i]['TEXT_TRUNS'] = strlen($content['STRINGS'][$i]['TEXT']) > 40 ? substr( $content['STRINGS'][$i]['TEXT'], 0, 40) . " ..." : $content['STRINGS'][$i]['TEXT'];
		// --- 

		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['STRINGS'][$i]['cssclass'] = "line1";
		else
			$content['STRINGS'][$i]['cssclass'] = "line2";
		// --- 
	}

	// --- Now we create the Pager ;)!
		// Fix for now of the list exceeds $CFGCOD4['MAX_PAGES_COUNT'] pages
		if ($pagenumbers > $content['admin_maxpages'])
		{
			$content['PLAYERS_MOREPAGES'] = "*(More then " . $content['admin_maxpages'] . " pages found)";
			$pagenumbers = $content['admin_maxpages'];
		}
		else
			$content['PLAYERS_MOREPAGES'] = "&nbsp;";

		for ($i=0 ; $i < $pagenumbers ; $i++)
		{
			$content['STRINGPAGES'][$i]['mypagebegin'] = ($i * $content['admin_maxplayers']);

			if ($content['current_pagebegin'] == $content['STRINGPAGES'][$i]['mypagebegin'])
				$content['STRINGPAGES'][$i]['mypagenumber'] = "<B>".($i+1)."</B>";
			else
				$content['STRINGPAGES'][$i]['mypagenumber'] = $i+1;

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['STRINGPAGES'][$i]['cssclass'] = "line1";
			else
				$content['STRINGPAGES'][$i]['cssclass'] = "line2";
			// --- 
		}
	// ---
}

// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/stringeditor.html");
$page -> output(); 
// --- 

// --- Helper function

function ProcessDeleteStatement( $sqlStatement )
{
	$result = DB_Query( $sqlStatement );
	if ($result == FALSE)
		return false;
	DB_FreeQuery($result);

	// Done
	return true;
}
// ---
?>