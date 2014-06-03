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
	* ->	Player Admin File													
	*		Administrates Players in UltraStats 
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
$content['TITLE'] .= " :: Player Admin";
// --- END CREATE TITLE


// --- Read Vars
if ( isset($_GET['start']) )
	$content['current_pagebegin'] = intval(DB_RemoveBadChars($_GET['start']));
else
	$content['current_pagebegin'] = 0;

if ( isset($_GET['playerfilter']) && strlen($_GET['playerfilter']) > 0 )
{
	$content['playerfilter'] = DB_RemoveBadChars($_GET['playerfilter']);
	$content['playersqlwhere'] = " WHERE " . STATS_ALIASES . ".Alias LIKE '%" . $content['playerfilter'] . "%' ";
}
else
{
	$content['playerfilter'] = "";
	$content['playersqlwhere'] = ""; 
}

if ( isset($_GET['playerop']) )
{
	if ( isset($_GET['playerguid']) && is_numeric($_GET['playerguid']) )
	{
		// Get PlayerID ;)
		$playerid = DB_RemoveBadChars($_GET['playerguid']);
		if ( isset($_GET['newval']) )
		{
			$newval = intval($_GET['newval']);
			if ( $newval == 0 || $newval == 1) 
			{
				// Check for Clanmember
				if ( $_GET['playerop'] == "setclanmember" ) 
				{
					// Edit the Player now!
					$result = DB_Query("UPDATE " . STATS_PLAYERS_STATIC . " SET 
										ISCLANMEMBER = " . $newval . " 
										WHERE GUID = " . $playerid );
					DB_FreeQuery($result);
				}
				else if ( $_GET['playerop'] == "setban" ) 
				{
					// Edit the Player now!
					$result = DB_Query("UPDATE " . STATS_PLAYERS_STATIC . " SET 
										ISBANNED = " . $newval . " 
										WHERE GUID = " . $playerid );
					DB_FreeQuery($result);
				}
			}
		}
	}
}

// ---

// Set Referer vars 
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
	if ($_GET['op'] == "edit") 
	{
		// Set Mode to edit
		$content['ISEDITPLAYER'] = "true";
		$content['PLAYER_FORMACTION'] = "edit";
		$content['PLAYER_SENDBUTTON'] = $content['LN_PLAYER_EDIT'];

		if ( isset($_GET['id']) && is_numeric($_GET['id']) )
		{
			//PreInit these values 
			$content['GUID'] = DB_RemoveBadChars($_GET['id']);

			$sqlquery = "SELECT " . 
						STATS_PLAYERS_STATIC . ".GUID, " . 
						STATS_PLAYERS_STATIC . ".PBGUID, " . 
						STATS_PLAYERS_STATIC . ".ISCLANMEMBER, " . 
						STATS_PLAYERS_STATIC . ".ISBANNED, " . 
						STATS_PLAYERS_STATIC . ".BanReason, " . 
						STATS_ALIASES . ".Alias, " . 
						STATS_ALIASES . ".AliasAsHtml " .
						" FROM " . STATS_PLAYERS_STATIC . 
						" INNER JOIN (" . STATS_ALIASES . 
						") ON (" . 
						STATS_PLAYERS_STATIC . ".GUID=" . STATS_ALIASES . ".PLAYERID) " . 
						" WHERE " . STATS_PLAYERS_STATIC . ".GUID = " . $content['GUID'] . 
						" GROUP BY " . STATS_PLAYERS_STATIC . ".GUID " . 
						" ORDER BY " . STATS_ALIASES . ".Alias " ; 

			$result = DB_Query($sqlquery);
			$myrow = DB_GetSingleRow($result, true);
			if ( isset($myrow['GUID'] ) )
			{
				$content['PBGUID'] = $myrow['PBGUID'];
				$content['BanReason'] = $myrow['BanReason'];
				$content['Alias'] = $myrow['Alias'];
				$content['AliasAsHtml'] = $myrow['AliasAsHtml'];

				if ( $myrow['ISCLANMEMBER'] == 1 ) 
					$content['CHECKED_ISCLANMEMBED'] = "checked";
				if ( $myrow['ISBANNED'] == 1 ) 
					$content['CHECKED_ISBANNED'] = "checked";
			}
			else
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_PLAYER_ERROR_NOTFOUND'], $content['GUID'] ); 
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_PLAYER_ERROR_INVID'];
		}
	}
	else if ($_GET['op'] == "delete") 
	{
		// Set Mode to edit
		$content['ISDELETEPLAYER'] = "true";

		if ( isset($_GET['id']) && is_numeric($_GET['id']) )
		{
			//PreInit these values 
			$content['GUID'] = DB_RemoveBadChars($_GET['id']);
			$content['AliasName'] = GetPlayerHtmlNameFromID( $content['GUID'] );

			if ( isset($_GET['verify']) || $_GET['verify'] == "yes" )
			{
				// Disable Verify few
				$content['ISVERIFY'] = "false";

				// Start Deleting the User stats!
				$content['DeletedData'][0]['SQL_CMD'] = "DELETE FROM " . STATS_ALIASES .		" WHERE PLAYERID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][0]['SQL_CMD'] );
				$content['DeletedData'][0]['NAME'] = STATS_ALIASES;
				$content['DeletedData'][0]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][0]['cssclass'] = "line1";

				$content['DeletedData'][1]['SQL_CMD'] = "DELETE FROM " . STATS_CHAT .			" WHERE PLAYERID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][1]['SQL_CMD'] );
				$content['DeletedData'][1]['NAME'] = STATS_CHAT;
				$content['DeletedData'][1]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][1]['cssclass'] = "line2";

				$content['DeletedData'][2]['SQL_CMD'] = "DELETE FROM " . STATS_PLAYER_KILLS .	" WHERE PLAYERID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][2]['SQL_CMD'] );
				$content['DeletedData'][2]['NAME'] = STATS_PLAYER_KILLS;
				$content['DeletedData'][2]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][2]['cssclass'] = "line1";

				$content['DeletedData'][3]['SQL_CMD'] = "DELETE FROM " . STATS_PLAYER_KILLS .	" WHERE ENEMYID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][3]['SQL_CMD'] );
				$content['DeletedData'][3]['NAME'] = STATS_PLAYER_KILLS;
				$content['DeletedData'][3]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][3]['cssclass'] = "line2";

				$content['DeletedData'][4]['SQL_CMD'] = "DELETE FROM " . STATS_PLAYERS .		" WHERE GUID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][4]['SQL_CMD'] );
				$content['DeletedData'][4]['NAME'] = STATS_PLAYERS;
				$content['DeletedData'][4]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][4]['cssclass'] = "line1";

				$content['DeletedData'][5]['SQL_CMD'] = "DELETE FROM " . STATS_PLAYERS_STATIC .	" WHERE GUID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][5]['SQL_CMD'] );
				$content['DeletedData'][5]['NAME'] = STATS_PLAYERS_STATIC;
				$content['DeletedData'][5]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][5]['cssclass'] = "line2";

				$content['DeletedData'][6]['SQL_CMD'] = "DELETE FROM " . STATS_PLAYERS_TOPALIASES . " WHERE GUID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][6]['SQL_CMD'] );
				$content['DeletedData'][6]['NAME'] = STATS_PLAYERS_TOPALIASES;
				$content['DeletedData'][6]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][6]['cssclass'] = "line1";

				$content['DeletedData'][7]['SQL_CMD'] = "DELETE FROM " . STATS_ROUNDACTIONS .	" WHERE PLAYERID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][7]['SQL_CMD'] );
				$content['DeletedData'][7]['NAME'] = STATS_ROUNDACTIONS;
				$content['DeletedData'][7]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][7]['cssclass'] = "line2";

				$content['DeletedData'][8]['SQL_CMD'] = "DELETE FROM " . STATS_TIME .			" WHERE PLAYERID = " . $content['GUID'];
				ProcessDeleteStatement( $content['DeletedData'][8]['SQL_CMD'] );
				$content['DeletedData'][8]['NAME'] = STATS_TIME;
				$content['DeletedData'][8]['DELETED_RECORD'] = GetRowsAffected();
				$content['DeletedData'][8]['cssclass'] = "line1";
			}
			else
			{
				// Enable Verify few
				$content['ISVERIFY'] = "true";
			}
		}
	}

	if ( isset($_POST['op']) )
	{
		if ( isset ($_POST['id']) ) { $content['GUID'] = DB_RemoveBadChars($_POST['id']); } else {$content['GUID'] = 0; }

		if ( isset ($_POST['playerpbguid']) ) { $content['PBGUID'] = DB_RemoveBadChars($_POST['playerpbguid']); } else {$content['PBGUID'] = ""; }
		if ( isset ($_POST['banreason']) ) { $content['BanReason'] = DB_RemoveBadChars($_POST['banreason']); } else {$content['BanReason'] = ""; }
		if ( isset ($_POST['isclanmember']) ) { $content['ISCLANMEMBER'] = true; } else {$content['ISCLANMEMBER'] = 0; }
		if ( isset ($_POST['isbanned']) ) { $content['ISBANNED'] = true; } else {$content['ISBANNED'] = 0; }

		// Check mandotary values
		if ( !isset($content['GUID']) || $content['GUID'] == 0 )
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_PLAYER_ERROR_PLAYERIDEMPTY'];
		}

		if ( !isset($content['ISERROR']) ) 
		{	
			if ( $_POST['op'] == "edit" )
			{
				$result = DB_Query("SELECT GUID FROM " . STATS_PLAYERS_STATIC . " WHERE GUID = " . $content['GUID']);
				$myrow = DB_GetSingleRow($result, true);
				if ( !isset($myrow[GUID]) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_PLAYER_ERROR_NOTFOUND'], $content['GUID'] ); 
				}
				else
				{
					// Edit the Player now!
					$result = DB_Query("UPDATE " . STATS_PLAYERS_STATIC . " SET 
						PBGUID = '" . $content['PBGUID'] . "', 
						ISCLANMEMBER = " . $content['ISCLANMEMBER'] . ", 
						ISBANNED = " . $content['ISBANNED'] . ", 
						BanReason = '" . $content['BanReason'] . "' 
						WHERE GUID = " . $content['GUID']);
					DB_FreeQuery($result);

					// Redirect - may with PAGER later!
					if ( strlen( $content['received_referer'] ) > 0 )
						RedirectResult( GetAndReplaceLangStr( $content['LN_PLAYER_SUCCEDIT'], $content['GUID'] ) , $content['received_referer'] );
					else
						RedirectResult( GetAndReplaceLangStr( $content['LN_PLAYER_SUCCEDIT'], $content['GUID'] ) , "players.php" );
				}
			}
		}
	}
}
else
{
	// Default Mode = List Players
	$content['LISTPLAYERS'] = "true";

	// --- First get the Count and Set Pager Variables
	$sqlquery = "SELECT " . 
				"count(" . STATS_PLAYERS_STATIC . ".GUID) as PlayersCount " . 
				" FROM " . STATS_PLAYERS_STATIC . 
				" INNER JOIN (" . STATS_PLAYERS_TOPALIASES . ", " . STATS_ALIASES .
				") ON (" . 
				STATS_PLAYERS_STATIC . ".GUID=" . STATS_PLAYERS_TOPALIASES . ".GUID AND " . 
				STATS_PLAYERS_TOPALIASES . ".ALIASID=" . STATS_ALIASES . ".ID " . 
				") " . 
				$content['playersqlwhere'] . 
				" GROUP BY " . STATS_PLAYERS_STATIC . ".GUID "; 
//	$result = DB_Query($sqlquery);
//	$tmpvar = DB_GetSingleRow($result, true);
//	$content['players_count'] = $tmpvar['PlayersCount'];
	$content['players_count'] = DB_GetRowCount( $sqlquery );
	if ( $content['players_count'] > $content['admin_maxplayers'] ) 
	{
		$pagenumbers = $content['players_count'] / $content['admin_maxplayers'];

		// Check PageBeginValue
		if ( $content['current_pagebegin'] > $content['players_count'] )
			$content['current_pagebegin'] = 0;

		// Enable Player Pager
		$content['players_pagerenabled'] = "true";
	}
	else
	{
		$content['current_pagebegin'] = 0;
		$pagenumbers = 0;
	}
	
	// Set text
	$content['players_count_text'] = GetAndReplaceLangStr( $content['LN_PLAYER_PLAYERCOUNT'], $content['players_count']);


	// --- 

// --- Now the final query !
	// Read all Players
	$sqlquery = "SELECT " . 
				STATS_PLAYERS_STATIC . ".GUID, " . 
				STATS_PLAYERS_STATIC . ".PBGUID, " . 
				STATS_PLAYERS_STATIC . ".ISCLANMEMBER, " . 
				STATS_PLAYERS_STATIC . ".ISBANNED, " . 
				STATS_ALIASES . ".Alias, " . 
				STATS_ALIASES . ".AliasAsHtml " .
				" FROM " . STATS_PLAYERS_STATIC . 
				" INNER JOIN (" . STATS_PLAYERS_TOPALIASES . ", " . STATS_ALIASES .
				") ON (" . 
				STATS_PLAYERS_STATIC . ".GUID=" . STATS_PLAYERS_TOPALIASES . ".GUID AND " . 
				STATS_PLAYERS_TOPALIASES . ".ALIASID=" . STATS_ALIASES . ".ID " . 
				") " . 
				$content['playersqlwhere'] . 
				" GROUP BY " . STATS_PLAYERS_STATIC . ".GUID " . 
				" ORDER BY " . STATS_ALIASES . ".Alias " .  
				" LIMIT " . $content['current_pagebegin'] . " , " . $content['admin_maxplayers'];
	$result = DB_Query($sqlquery);
	$content['PLAYERS'] = DB_GetAllRows($result, true);

	// For the eye
	$css_class = "line";
	for($i = 0; $i < count($content['PLAYERS']); $i++)
	{
		// --- Set Number
		$content['PLAYERS'][$i]['Number'] = $i+1;
		// ---

		// --- Set Image for IsClanMember
		if ( $content['PLAYERS'][$i]['ISCLANMEMBER'] ) 
		{
			$content['PLAYERS'][$i]['is_clanmember_string'] = $content['MENU_SELECTION_ENABLED'];
			$content['PLAYERS'][$i]['set_clanmember'] = 0;
		}
		else
		{
			$content['PLAYERS'][$i]['is_clanmember_string'] = $content['MENU_SELECTION_DISABLED'];
			$content['PLAYERS'][$i]['set_clanmember'] = 1;
		}
		// ---

		// --- Set Image for IsBanned
		if ( $content['PLAYERS'][$i]['ISBANNED'] ) 
		{
			$content['PLAYERS'][$i]['is_banned_string'] = $content['MENU_SELECTION_ENABLED'];
			$content['PLAYERS'][$i]['set_banned'] = 0;
		}
		else
		{
			$content['PLAYERS'][$i]['is_banned_string'] = $content['MENU_SELECTION_DISABLED'];
			$content['PLAYERS'][$i]['set_banned'] = 1;
		}
		// ---


		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['PLAYERS'][$i]['cssclass'] = "line1";
		else
			$content['PLAYERS'][$i]['cssclass'] = "line2";
		// --- 
	}

	// --- Now we create the Pager ;)!
		// Fix for now of the list exceeds $CFGCOD4['MAX_PAGES_COUNT'] pages
		if ($pagenumbers > $content['admin_maxpages'])
		{
			$content['PLAYERS_MOREPAGES'] = GetAndReplaceLangStr( $content['LN_ADMIN_MOREPAGES'], $content['admin_maxpages'] ); 
			$pagenumbers = $content['admin_maxpages'];
		}
		else
			$content['PLAYERS_MOREPAGES'] = "&nbsp;";

		for ($i=0 ; $i < $pagenumbers ; $i++)
		{
			$content['PLAYERPAGES'][$i]['mypagebegin'] = ($i * $content['admin_maxplayers']);

			if ($content['current_pagebegin'] == $content['PLAYERPAGES'][$i]['mypagebegin'])
				$content['PLAYERPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
			else
				$content['PLAYERPAGES'][$i]['mypagenumber'] = $i+1;

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['PLAYERPAGES'][$i]['cssclass'] = "line1";
			else
				$content['PLAYERPAGES'][$i]['cssclass'] = "line2";
			// --- 
		}
	// ---
}

// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/players.html");
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