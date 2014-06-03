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
	* ->	DB Functions File 
	*		Database Helper functions are in this file
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/



$link_id  = 0;
$errdesc = "";
$errno = 0;

// --- Current Database Version, this is important for automated database Updates!
$content['database_internalversion'] = "9";	// Whenever incremented, a database upgrade is needed
$content['database_installedversion'] = "0";	// 0 is default which means Prior Versioning Database
// --- 

function DB_Connect() 
{
	global $link_id, $CFGCOD4;

	//TODO: Check variables first
	$link_id = @mysql_connect($CFGCOD4['DBServer'],$CFGCOD4['User'],$CFGCOD4['Pass']);
	if (!$link_id) 
		DB_PrintError("Link-ID == false, connect to ".$CFGCOD4['DBServer']." failed", true);
	
	// --- Now, check Mysql DB Version!
	$strmysqlver = mysql_get_server_info();
	if ( strpos($strmysqlver, "-") !== false )
	{
		$sttmp = explode("-", $strmysqlver );
		$szVerInfo = $sttmp[0];
	}
	else
		$szVerInfo = $strmysqlver;

	$szVerSplit = explode(".", $szVerInfo );

	//Now check for Major Version
	if ( $szVerSplit[0] <= 3 ) 
	{
		//Unfortunatelly MYSQL 3.x is NOT Supported dude!
		DieWithFriendlyErrorMsg( "You are running an MySQL 3.x Database Server Version. Unfortunately MySQL 3.x is NOT supported by UltraStats due the limited SQL Statement support. If this is a commercial webspace, contact your webhoster in order to upgrade to a higher MySQL Database Version. If this is your own rootserver, consider updating your MySQL Server.");
	}
	// ---
	
	// check if database exists!
	$db_selected = @mysql_select_db($CFGCOD4['DBName'], $link_id);
	if(!$db_selected)
	{ 
		//DB_PrintError("Cannot use database '" . $CFGCOD4['DBName'] . "'", true);
		$error="Cannot use database";
		return $error;
	}
	// TODO: Maybe some more error checking 
}

function EnableBigSelects()
{
	// Extra command to enable BIG sql commands! And we don't care for error messages!
	@mysql_query("SET OPTION SQL_BIG_SELECTS=1");
}

function DB_Disconnect()
{
	global $link_id;
	mysql_close($link_id);
}

function DB_Query($query_string, $bProcessError = true, $bCritical = false)
{
	global $link_id, $querycount;

	$query_id = mysql_query($query_string,$link_id);
	if (!$query_id && $bProcessError) 
	{	//DB_PrintError("Invalid SQL: ".$query_string, $bCritical);
		$error="Cannot use database";
		return $error;
	}
	// For the Stats ;)
	$querycount++;
	
	return $query_id;
}

function DB_FreeQuery($query_id)
{
	if ($query_id != false && $query_id != 1 )
		mysql_free_result($query_id);
}

function DB_GetRow($query_id) 
{
	$tmp = mysql_fetch_row($query_id);
	$results[] = $tmp;
	return $results[0];
}

function DB_GetSingleRow($query_id, $bClose) 
{
	if ($query_id != false && $query_id != 1 )
	{
		$row = mysql_fetch_array($query_id,  MYSQL_ASSOC);
		
		if ( $bClose )
			DB_FreeQuery ($query_id); 

		if ( isset($row) )
		{
			// Return array
			return $row;
		}
		else
			return;
	}
}

function DB_GetAllRows($query_id, $bClose)
{
	if ($query_id != false && $query_id != 1 )
	{
		while ($row  =  mysql_fetch_array($query_id,  MYSQL_ASSOC))
			$var[]  =  $row;
		
		if ( $bClose )
			DB_FreeQuery ($query_id); 

		if ( isset($var) )
		{
			// Return array
			return $var;
		}
		else
			return;
	}
}

function DB_GetMysqlStats()
{
	global $link_id;
	$status = explode('  ', mysql_stat($link_id));
	return $status;
}

function DB_ReturnSimpleErrorMsg()
{
	// Return Mysql Error
	return "Mysql Error " . mysql_errno() . " - Description: " . mysql_error();
}

function DB_PrintError($MyErrorMsg, $DieOrNot)
{
	global $n,$HTTP_COOKIE_VARS, $errdesc, $errno, $linesep, $CFGCOD4;

	$errdesc = mysql_error();
	$errno = mysql_errno();

	$errormsg="Database error: $MyErrorMsg $linesep";
	$errormsg.="mysql error: $errdesc $linesep";
	$errormsg.="mysql error number: $errno $linesep";
	$errormsg.="Date: ".date("d.m.Y @ H:i").$linesep;
	$errormsg.="Script: ".getenv("REQUEST_URI").$linesep;
	$errormsg.="Referer: ".getenv("HTTP_REFERER").$linesep;

	if ($DieOrNot == true)
		DieWithErrorMsg( "$linesep" . $errormsg );
	else
		return $errormsg;
}

function DB_RemoveParserSpecialBadChars($myString)
{
// DO NOT REPLACD!	$returnstr = str_replace("\\","\\\\",$myString);
	$returnstr = str_replace("'","\\'",$myString);
//	$returnstr = str_replace("","",$returnstr);
	return $returnstr;
}

function DB_RemoveBadChars($myString)
{
	// Replace with internal PHP Functions!
	if ( !get_magic_quotes_runtime() )
		return addslashes($myString);
	else
		return $myString;

	/* OLD CODE!
	$returnstr = str_replace("\\","\\\\",$myString);
	$returnstr = str_replace("'","\\'",$returnstr);
	return $returnstr;
	*/
}

function DB_StripSlahes($myString)
{
	// Replace with internal PHP Functions!
	if ( !get_magic_quotes_runtime() )
		return stripslashes($myString);
	else
		return $myString;
}


function DB_GetRowCount($query)
{
	// Init num rows
	$num_rows = -1;

	if ($result = mysql_query($query)) 
	{   
		$num_rows = mysql_num_rows($result);
		mysql_free_result ($result); 
	}
	return $num_rows;
}

function DB_GetRowCountByResult($myresult)
{
	if ($myresult) 
		return mysql_num_rows($myresult);
}

function DB_Exec($query)
{
	if(mysql_query($query)) 
		return true;
	else 
		return false; 
} 

function WriteConfigValue($szValue)
{
	global $content;

	$sqlquery = "SELECT name FROM " . STATS_CONFIG . " WHERE name = '" . $szValue . "'";
	$result = DB_Query($sqlquery);
	$rows = DB_GetAllRows($result, true);
	if ( !isset($rows) )
	{
		// New Entry
		$sqlquery = "INSERT INTO  " . STATS_CONFIG . " (name, value) VALUES ( '" . $szValue . "', '" . $content[$szValue] . "')";
		$result = DB_Query($sqlquery);
		DB_FreeQuery($result);
	}
	else
	{
		// Update Entry
		$result = DB_Query("UPDATE " . STATS_CONFIG . " SET value = '" . $content[$szValue] . "' WHERE name = '" . $szValue . "'");
		DB_FreeQuery($result);
	}
} 

function GetSingleDBEntryOnly( $myqry )
{
	$result = DB_Query( $myqry );
	$row = DB_GetRow($result);
	DB_FreeQuery ($result); 

	if ( isset($row) )
		return $row[0];
	else
		return -1;
}

function GetRowsAffected()
{
	return mysql_affected_rows();
}

?>