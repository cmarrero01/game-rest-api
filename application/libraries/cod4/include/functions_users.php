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
	* ->	User Include File
	*		Contains functions for the user management
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/


// --- BEGIN Usermanagement Function --- 
function CheckForUserLogin( $isloginpage, $isUpgradePage = false )
{
	global $content; 

	if ( isset($_SESSION['SESSION_LOGGEDIN']) )
	{
		if ( !$_SESSION['SESSION_LOGGEDIN'] ) 
			RedirectToUserLogin();
		else
		{
			$content['SESSION_LOGGEDIN'] = "true";
			$content['SESSION_USERNAME'] = $_SESSION['SESSION_USERNAME'];
		}

		if ( isset($_SESSION['UPDATEAVAILABLE']) && $_SESSION['UPDATEAVAILABLE'] ) 
		{
			// Check Version numbers again to avoid update notification if update was done during meantime!
			if ( CompareVersionNumbers($content['BUILDNUMBER'], $_SESSION['UPDATEVERSION']) )
			{
				$content['isupdateavailable'] = true;
				$content['isupdateavailable_updatelink'] = $_SESSION['UPDATELINK'];
				$content['UPDATE_AVAILABLETEXT'] = GetAndReplaceLangStr($content['LN_UPDATE_AVAILABLETEXT'], $content['BUILDNUMBER'], $_SESSION['UPDATEVERSION']);
			}
		}

		// New, Check for database Version and may redirect to updatepage!
		if (	isset($content['database_forcedatabaseupdate']) && 
				$content['database_forcedatabaseupdate'] == "yes" && 
				$isUpgradePage == false 
			)
				RedirectToDatabaseUpgrade();
	}
	else
	{
		if ( $isloginpage == false )
			RedirectToUserLogin();
	}

}

function CreateUserName( $username, $password, $access_level )
{
	$md5pass = md5($password);
	$result = DB_Query("SELECT username FROM " . STATS_USERS . " WHERE username = '" . $username . "'");
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		DieWithFriendlyErrorMsg( "User $username already exists!" );

		// User not created!
		return false;
	}
	else
	{
		// Create User
		$result = DB_Query("INSERT INTO " . STATS_USERS . " (username, password, access_level) VALUES ('$username', '$md5pass', $access_level)");
		DB_FreeQuery($result);

		// Success
		return true;
	}
}

// Helper function to compare versions
function CompareVersionNumbers( $oldVer, $newVer )
{
	// Split version numbers
	$currentVersion = explode(".", $oldVer);
	$newVersion = explode(".", $newVer);

	// Check if the format is correct!
	if ( count($newVersion) != 3 )
		return false;

	// check for update
	if		( isset($newVersion[0]) && $newVersion[0] > $currentVersion[0] )
		return true;
	else if	( isset($newVersion[1]) && $newVersion[0] == $currentVersion[0] && $newVersion[1] > $currentVersion[1] )
		return true;
	else if ( isset($newVersion[2]) && $newVersion[0] == $currentVersion[0] && $newVersion[1] == $currentVersion[1] && $newVersion[2] > $currentVersion[2] )
		return true;
	else
		return false;
}

function CheckUserLogin( $username, $password )
{
	global $content, $CFGCOD4;

	// TODO: SessionTime and AccessLevel check
	$md5pass = md5($password);
	$sqlselect = "SELECT access_level FROM " . STATS_USERS . " WHERE username = '" . $username . "' and password = '" . $md5pass . "'";
	$result = DB_Query($sqlselect);
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		$_SESSION['SESSION_LOGGEDIN'] = true;
		$_SESSION['SESSION_USERNAME'] = $username;
		$_SESSION['SESSION_ACCESSLEVEL'] = $rows[0]['access_level'];
		
		$content['SESSION_LOGGEDIN'] = "true";
		$content['SESSION_USERNAME'] = $username;

		// --- Now we check for an UltraStats Update
		$myHandle = @fopen($content['UPDATEURL'], "r");
		
		if( $myHandle ) 
		{
			$myBuffer = "";
			while (!feof ($myHandle))
				$myBuffer .= fgets($myHandle, 4096);
			fclose($myHandle);

			$myLines = explode("\n", $myBuffer);

			// Compare Version numbers!
			if ( CompareVersionNumbers($content['BUILDNUMBER'], $myLines[0]) )
			{	
				// True means new version available!
				$_SESSION['UPDATEAVAILABLE'] = true;
				$_SESSION['UPDATEVERSION'] = $myLines[0];
				if ( isset($myLines[1]) ) 
					$_SESSION['UPDATELINK'] = $myLines[1];
				else
					$_SESSION['UPDATELINK'] = "http://www.ultrastats.org";
			}
		}
		// --- 

		// Success !
		return true;
	}
	else
	{
		if ($ShowDebugMsg == 1 )
			DieWithFriendlyErrorMsg( "Debug Error: Could not login user '" . $username . "' <br><br><B>Sessionarray</B> <pre>" . var_export($_SESSION, true) . "</pre><br><B>SQL Statement</B>: " . $sqlselect );
		
		// Default return false
		return false;
	}
}


function autologin( $username, $password )
{
	global $content, $CFGCOD4;

	// TODO: SessionTime and AccessLevel check
	$md5pass = md5($password);
	$sqlselect = "SELECT access_level FROM " . STATS_USERS . " WHERE username = '" . $username . "' and password = '" . $md5pass . "'";
	$result = DB_Query($sqlselect);
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		$_SESSION['SESSION_LOGGEDIN'] = true;
		$_SESSION['SESSION_USERNAME'] = $username;
		$_SESSION['SESSION_ACCESSLEVEL'] = $rows[0]['access_level'];
		
		$content['SESSION_LOGGEDIN'] = "true";
		$content['SESSION_USERNAME'] = $username;

		
	}
	else
	{
		if ( $ShowDebugMsg == 1 )
			DieWithFriendlyErrorMsg( "Debug Error: Could not login user '" . $username . "' <br><br><B>Sessionarray</B> <pre>" . var_export($_SESSION, true) . "</pre><br><B>SQL Statement</B>: " . $sqlselect );
		
		// Default return false
		return false;
	}
}

function DoLogOff()
{
	global $content;

	unset( $_SESSION['SESSION_LOGGEDIN'] );
	unset( $_SESSION['SESSION_USERNAME'] );
	unset( $_SESSION['SESSION_ACCESSLEVEL'] );

	// Redir to Index Page
	RedirectPage( "index.php");
}

function RedirectToUserLogin()
{
	// TODO Referer
	header("Location: login.php?referer=" . $_SERVER['PHP_SELF']);
	exit;
}

function RedirectToDatabaseUpgrade()
{
	// TODO Referer
	header("Location: upgrade.php"); // ?referer=" . $_SERVER['PHP_SELF']);
	exit;
}

/*
* Helper function to print a secure check!
*/
function PrintSecureUserCheck( $warningtext, $yesmsg, $nomsg )
{
	global $content, $page;

	// Copy properties
	$content['warningtext'] = $warningtext;
	$content['yesmsg'] = $yesmsg;
	$content['nomsg'] = $nomsg;

	// Handle GET and POST input!
	$content['form_url'] = $_SERVER['SCRIPT_NAME'] . "?";
	foreach ($_GET as $varname => $varvalue)
		$content['form_url'] .= $varname . "=" . $varvalue . "&";
	$content['form_url'] .= "verify=yes"; // Append verify!

	foreach ($_POST as $varname => $varvalue)
		$content['POST_VARIABLES'][] = array( "varname" => $varname, "varvalue" => $varvalue );

	// --- BEGIN CREATE TITLE
	$content['TITLE'] = InitPageTitle();
	$content['TITLE'] .= " :: Confirm Action";
	// --- END CREATE TITLE

	// --- Parsen and Output
	InitTemplateParser();
	$page -> parser($content, "admin/admin_securecheck.html");
	$page -> output(); 
	// --- 
	
	// Exit script execution
	exit;
}

// --- END Usermanagement Function --- 
?>