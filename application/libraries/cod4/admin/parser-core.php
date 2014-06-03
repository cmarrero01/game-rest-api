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
	* ->	Parser Core File													
	*		This file actually calls the parser 
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

// Set PARSERPAGE to true!
define('IS_PARSERPAGE', true);
$content['IS_PARSERPAGE'] = true;

InitUltraStats();
CheckForUserLogin( false );
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- BEGIN Custom Code
// Additional Includes
require_once($gl_root_path . 'include/functions_parser.php');
require_once($gl_root_path . 'include/functions_parser-helpers.php');
require_once($gl_root_path . 'include/functions_parser-medals.php');
require_once($gl_root_path . 'include/functions_parser-consolidation.php');

// Now the processing Part
if ( isset($_GET['op']) )
	$parseroperation = DB_RemoveBadChars($_GET['op']);
else
	$parseroperation = "";

if ( isset($_GET['op']) )
{
	if ( isset($_GET['id']) && is_numeric($_GET['id']) )
	{
		$serverid = DB_RemoveBadChars($_GET['id']);

		// Get ServerDetails first 
		$result = DB_Query("SELECT * FROM " . STATS_SERVERS . " WHERE ID = " . $serverid);
		$serverdetails = DB_GetAllRows($result, true);

		if ( isset( $serverdetails ) )
		{
			// Get Parser OP and Set myserver ref!
			$myserver = $serverdetails[0];

			// Set StartTime
			$ParserStart = microtime_float();

			// From here the Parsing Operation Starts!
			CreateHTMLHeader();

			// Set MaxExecutionTime first!
			SetMaxExecutionTime();

			// Server found - now check for the action
			if		( $parseroperation == 'updatestats' )
			{
				// Run Parser from here!
				RunParserNow();
				
				// Print finished
				print ('<br><center><a href="parser-core.php?op=runtotals"><img src="' . $content["BASEPATH"] . 'images/icons/gears_run.png">&nbsp; ' . $content["LN_RUNTOTALUPDATE"] . '</a></center>');
				
				if ( !defined('RELOADPARSER') ) 
				{
					// Print reload statement
					print ('<center><B>Automatically running ' . $content["LN_RUNTOTALUPDATE"] . ' in 10 seconds.</B><br>
							<script language="Javascript">function reload() { location = "parser-core.php?op=runtotals"; } setTimeout("reload()", 10000);</script>');
				}
			}
			else if ( $parseroperation == 'delete' )
			{
				// Delete Server
				DeleteServer();
			}
			else if ( $parseroperation == 'deletestats' )
			{
				// Delete Server Stats
				DeleteServerStats();
			}
			else if ( $parseroperation == 'resetlastlogline' )
			{
				// Reset last line
				ResetLastLine();
			}
			else if ( $parseroperation == 'getnewlogfile' )
			{
				// Reset last line
				GetLastLogFile();
			}
			else if ( $parseroperation == 'createaliases' )
			{
				//Run Calc for TOPAliases
				CreateTopAliases( $myserver['ID'] );
			}
			else
			{
				DieWithErrorMsg("Error, empty or unknown Action specified - '" . $parseroperation . "'!");
			}

			//Terminate Websitefooter
			CreateHTMLFooter();
		}
		else
			DieWithErrorMsg("Error, Server with ID '$serverid' not found in database");
	}
	else if ( 
				$parseroperation == 'runtotals' ||
				$parseroperation == 'createaliases' ||
				$parseroperation == 'calcmedalsonly' ||
				$parseroperation == 'calcdamagetypekills' ||
				$parseroperation == 'calcweaponkills' ||
				$parseroperation == 'databaseopt'
			)
	{
		// From here the Parsing Operation Starts!
		CreateHTMLHeader();

		// Set MaxExecutionTime first!
		SetMaxExecutionTime();

		if ( $parseroperation == 'runtotals' )
		{
			// To calc aliases and stuff 
			RunTotalStats();
		}
		else if ( $parseroperation == 'createaliases' )
		{
			// Set StartTime
			$ParserStart = microtime_float();

			// Create New Aliases!
			ReCreateAliases();
		}
		else if ( $parseroperation == 'calcmedalsonly' )
		{
			// Set StartTime
			$ParserStart = microtime_float();

			//Run the Medals Generation now!
			CreateAllMedals( -1 );
		}
		else if ( $parseroperation == 'calcdamagetypekills' )
		{
			// Create Damagetype Stats
			RunDamagetypeKillsConsolidation( -1 );
		}
		else if ( $parseroperation == 'calcweaponkills' )
		{
			// Create Damagetype Stats
			RunWeaponKillsConsolidation( -1 );
		}
		
		else if ( $parseroperation == 'databaseopt' )
		{
			// Optimize SQL Tables
			OptimizeAllTables();
		}

		//Terminate Websitefooter
		CreateHTMLFooter();
	}
	else
		DieWithErrorMsg("Error, no or invalid Server ID given");
}
// --- 

?>