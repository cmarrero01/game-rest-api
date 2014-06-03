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
	* ->	Medal Consolidator File
	*		Helper functions to optimice the database and keep it fast!
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/




function SetLastUpdateTime( $serverid )
{
	global $content;

	// Run a simple update for the Server
	ProcessUpdateStatement("UPDATE " . STATS_SERVERS . " SET 
								LastUpdate = " . time() . "
								WHERE ID = " . $serverid, true); 

	// Run a simple insert globally !
	InsertOrUpdateMedalValue(	"global_lastupdate", 
								"Last Stats Update", 
								-1, 
								"global_lastupdate", 
								time(), 
								"", 
								0,
								0 );
}

function RunServerConsolidation( $serverid )
{
	global $myserver, $content;

	// Now we create overall Medals!
	if ( $serverid != -1 ) 
		{
		//PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Starting Server Consolidation Calculation ...");
		}
	else
	{
		//PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Starting Total Consolidation Calculation ...");
		
		// Init thise case we delete the total stats for this server first!
		ProcessDeleteStatement( "DELETE FROM " . STATS_CONSOLIDATED . " " . "WHERE SERVERID = " . $serverid . " AND NAME LIKE 'server_total%' " );
		//PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Deleted '" . GetRowsAffected() . "' Consolidation data ...");
	}

	if ( $serverid != -1 )
	{
		$wheresinglesql = " WHERE SERVERID = " . $serverid;
		$whereaddsql = " AND SERVERID = " . $serverid;
		$groupbysql = " GROUP BY SERVERID "; 
	}
	else
	{
		$wheresinglesql = "";
		$whereaddsql = "";
		$groupbysql = "";
	}

	// Clean up Server Stats
	ProcessDeleteStatement("DELETE FROM " . STATS_CONSOLIDATED . " WHERE NAME LIKE 'server_%' AND SERVERID = " . $serverid);

	// ========================== Top Values =================================
	// --- Calc: server_top_map
	$sqlquery =	"SELECT " .
				"count(" . STATS_ROUNDS . ".ID) as MapCount, " .
				STATS_ROUNDS . ".MAPID, " .
				STATS_MAPS . ".MAPNAME " . 
				" FROM " . STATS_ROUNDS .
				" INNER JOIN (" . STATS_MAPS . 
				") ON (" . 
				STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID)" . 
				$wheresinglesql .
				" GROUP BY " . STATS_ROUNDS . ".MAPID " . 
				" ORDER BY MapCount DESC LIMIT 1";
	//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_top_map: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MapCount']) )
		InsertOrUpdateMedalValue(	"server_top_map", 
									"Top played map", 
									$serverid, 
									"server_top_map", 
									$topvalue['MapCount'], 
									$topvalue['MAPNAME'], 
									0,
									0 );
	//else
		//PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_top_map is empty!" );
	// --- 


	// --- Calc: server_top_gametype
	$sqlquery =	"SELECT " .
				"count(" . STATS_ROUNDS . ".ID) as GametypeCount, " .
				STATS_ROUNDS . ".GAMETYPE, " .
				STATS_GAMETYPES . ".NAME as GameTypeName " . 
				" FROM " . STATS_ROUNDS . 
				" INNER JOIN (" . STATS_GAMETYPES . 
				") ON (" . 
				STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE) " . 
				$wheresinglesql .
				" GROUP BY " . STATS_ROUNDS . ".GAMETYPE " . 
				" ORDER BY GametypeCount DESC LIMIT 1";
	//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_top_gametype: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['GametypeCount']) )
		InsertOrUpdateMedalValue(	"server_top_gametype", 
									"Top played gametype", 
									$serverid, 
									"server_top_gametype", 
									$topvalue['GametypeCount'], 
									$topvalue['GameTypeName'], 
									0,
									1 );
	//else
		//PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_top_gametype is empty!" );
	// --- 
	// ==========================            =================================


	// ========================== Total Values =================================
	// --- Calc: server_total_rounds
	$sqlquery =	"SELECT " .
				"count(" . STATS_ROUNDS . ".ID) as MyCount " .
				" FROM " . STATS_ROUNDS . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyCount DESC LIMIT 1";
	//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_rounds: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyCount']) )
		InsertOrUpdateMedalValue(	"server_total_rounds", 
									"Total played rounds", 
									$serverid, 
									"server_total_rounds", 
									$topvalue['MyCount'], 
									"", 
									0,
									0 );
	//else
	//	PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_rounds is empty!" );
	// --- 

	// --- Calc: server_total_players
	$sqlquery =	"SELECT " .
				"count(" . STATS_PLAYERS . ".GUID) as MyCount " .
				" FROM " . STATS_PLAYERS . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyCount DESC LIMIT 1";
	//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_players: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyCount']) )
		InsertOrUpdateMedalValue(	"server_total_players", 
									"Total Players", 
									$serverid, 
									"server_total_players", 
									$topvalue['MyCount'], 
									"", 
									0,
									1 );
	//else
	//	PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_players is empty!" );
	// --- 


	// --- Calc: server_total_kills
	$sqlquery =	"SELECT " .
				"count(" . STATS_PLAYER_KILLS . ".ID) as MyCount " .
				" FROM " . STATS_PLAYER_KILLS . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyCount DESC LIMIT 1";
	//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_kills: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyCount']) )
		InsertOrUpdateMedalValue(	"server_total_kills", 
									"Total Kills", 
									$serverid, 
									"server_total_kills", 
									$topvalue['MyCount'], 
									"", 
									0,
									2 );
	//else
	//	PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_kills is empty!" );
	// --- 


	// --- Calc: server_total_ratio
	$sqlquery =	"SELECT " .
				STATS_PLAYERS . ".GUID, " .
				"sum(" . STATS_PLAYERS . ".Kills) as Kills, " .
				"sum(" . STATS_PLAYERS . ".Deaths) as Deaths " .
				" FROM " . STATS_PLAYERS . 
				$wheresinglesql .
				" GROUP BY " . STATS_PLAYERS . ".GUID ";
	$result = DB_Query($sqlquery);
	$tmpplayers = DB_GetAllRows($result, true);
	if ( isset($tmpplayers) )
	{
		//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_ratio: " . $sqlquery );
		for($i = 0; $i < count($tmpplayers); $i++)
		{
			// Calc current ration
			if ( $tmpplayers[$i]['Deaths'] > 0 )
				$tmpplayers[$i]['KillRatio'] = round($tmpplayers[$i]['Kills'] / $tmpplayers[$i]['Deaths'], 2);
			else
				$tmpplayers[$i]['KillRatio'] = $tmpplayers[$i]['Kills'];
			// ---

			if ( !isset($bestration['KillRatio']) || $tmpplayers[$i]['KillRatio'] > $bestration['KillRatio'] )
			{
				// Set new best player
				$bestration['GUID'] = $tmpplayers[$i]['GUID'];
				$bestration['KillRatio'] = $tmpplayers[$i]['KillRatio'];
			}
		}

		// Insert now
		InsertOrUpdateMedalValue(	"server_total_ratio", 
									"Best Ratio", 
									$serverid, 
									"server_total_ratio", 
									($bestration['KillRatio'] * 100), // *100 to save last 2 numbers behind the , !
									"", 
									$bestration['GUID'],
									3 );
	}
	//else
	//	PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_ratio is empty!" );
	// --- 


	// --- Calc: server_total_time
	$sqlquery =	"SELECT " .
				"sum(" . STATS_TIME . ".TIMEPLAYED) as MyTime " .
				" FROM " . STATS_TIME . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyTime DESC LIMIT 1";
	//PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_time: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyTime']) )
		InsertOrUpdateMedalValue(	"server_total_time", 
									"Total time played", 
									$serverid, 
									"server_total_time", 
									$topvalue['MyTime'], 
									"", 
									0,
									4 );
	//else
	//	PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_time is empty!" );
	// --- 


	// ==========================              =================================

	// Finished
	//PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Finished Consolidation Calculation...");
}

function RunDamagetypeKillsConsolidation( $serverid ) 
{
	global $myserver, $content;

	// Now we create overall Medals!
	if ( $serverid == -1 )
	{
		// Get ServerDetails
		$sqlquery = "SELECT ID, Name " . 
					"FROM " . STATS_SERVERS . " " . 
					"ORDER BY ID";
		$result = DB_Query($sqlquery, true); 
		$content['serverlist'] = DB_GetAllRows($result, true);
		if ( isset($content['serverlist']) )
		{
			//PrintHTMLDebugInfo( DEBUG_INFO, "RunDamagetypeKillsConsolidation", "Start consolidating damagetype data...");
			foreach ( $content['serverlist'] as $myServerRecord)
			{
				// Call function with valid ServerID now
				RunDamagetypeKillsConsolidation( $myServerRecord['ID'] );
			}
			//PrintHTMLDebugInfo( DEBUG_INFO, "RunDamagetypeKillsConsolidation", "Finished consolidating damagetype data ...");
		}
		//else
		//	PrintHTMLDebugInfo( DEBUG_ERROR, "RunDamagetypeKillsConsolidation", "Error no server records found!");
	}
	else
	{
		//PrintHTMLDebugInfo( DEBUG_INFO, "RunDamagetypeKillsConsolidation", "Consolidation Damagetype data for ServerID '" . $serverid . "', this may take a while ...");

		// Get available month and years for this Server!
		$sqlquery = " SELECT DISTINCT " . 
						STATS_TIME . ".Time_Year, " . 
						STATS_TIME . ".Time_Month " . 
					" FROM " . STATS_TIME . 
					" WHERE " . STATS_TIME . ".SERVERID = " . $serverid . 
					" ORDER BY " . STATS_TIME . ".Time_Year AND " . STATS_TIME . ".Time_Month";
		
		$result = DB_Query($sqlquery);
		$content['timeresults'] = DB_GetAllRows($result, true);
		if ( isset($content['timeresults']) )
		{
			// Process each month!
			foreach( $content['timeresults'] as $myTimeRecord)
			{
				// Set variables for timefilter
				SetUnixTimeStampFilters($myTimeRecord['Time_Year'], $myTimeRecord['Time_Month']);

				// Now the real Query stuff starts ;)!
				$sqlquery = "SELECT " .
									STATS_DAMAGETYPES . ".ID as DAMAGETYPEID, " .
									STATS_DAMAGETYPES . ".DAMAGETYPE, " . 
									"count(DISTINCT " . STATS_PLAYER_KILLS . ".PLAYERID) as PlayerCount, " . 
									"sum(" . STATS_PLAYER_KILLS . ".Kills) as DamageKills " . 
									" FROM " . STATS_DAMAGETYPES . 
									" INNER JOIN (" . STATS_PLAYER_KILLS . ", " . STATS_ROUNDS . ") " .
									" ON (" . 
									STATS_PLAYER_KILLS . ".DAMAGETYPEID =" . STATS_DAMAGETYPES . ".ID AND " . 
									STATS_PLAYER_KILLS . ".ROUNDID =" . STATS_ROUNDS . ".ID " . 
									") " . 
									" WHERE " . STATS_PLAYER_KILLS . ".SERVERID = " . $serverid .
									GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
									GetTimeWhereQueryStringForRoundTable() . 
									" GROUP BY " . STATS_DAMAGETYPES . ".ID ";
				$result = DB_Query($sqlquery);
				$content['damagetypeslist'] = DB_GetAllRows($result, true);
				if ( isset($content['damagetypeslist']) )
				{
					// Process each damagetype and insert data!
					foreach( $content['damagetypeslist'] as $myDamageType)
					{
						// Insert or Update damagetype record
						InsertOrUpdateDamagetypekills( $serverid, $myTimeRecord['Time_Year'], $myTimeRecord['Time_Month'], $myDamageType );
					}
				}
			}
		}
	}
}

function RunWeaponKillsConsolidation( $serverid ) 
{
	global $myserver, $content;

	// Now we create overall Medals!
	if ( $serverid == -1 )
	{
		// Get ServerDetails
		$sqlquery = "SELECT ID, Name " . 
					"FROM " . STATS_SERVERS . " " . 
					"ORDER BY ID";
		$result = DB_Query($sqlquery, true); 
		$content['serverlist'] = DB_GetAllRows($result, true);
		if ( isset($content['serverlist']) )
		{
			//PrintHTMLDebugInfo( DEBUG_INFO, "RunWeaponKillsConsolidation", "Start consolidating weapons data...");
			foreach ( $content['serverlist'] as $myServerRecord)
			{
				// Call function with valid ServerID now
				RunWeaponKillsConsolidation( $myServerRecord['ID'] );
			}
			//PrintHTMLDebugInfo( DEBUG_INFO, "RunWeaponKillsConsolidation", "Finished consolidating weapons data ...");
		}
		//else
			//PrintHTMLDebugInfo( DEBUG_ERROR, "RunWeaponKillsConsolidation", "Error no server records found!");
	}
	else
	{
		//PrintHTMLDebugInfo( DEBUG_INFO, "RunWeaponKillsConsolidation", "Consolidation weapons data for ServerID '" . $serverid . "', this may take a while ...");

		// Get available month and years for this Server!
		$sqlquery = " SELECT DISTINCT " . 
						STATS_TIME . ".Time_Year, " . 
						STATS_TIME . ".Time_Month " . 
					" FROM " . STATS_TIME . 
					" WHERE " . STATS_TIME . ".SERVERID = " . $serverid . 
					" ORDER BY " . STATS_TIME . ".Time_Year AND " . STATS_TIME . ".Time_Month";
		
		$result = DB_Query($sqlquery);
		$content['timeresults'] = DB_GetAllRows($result, true);
		if ( isset($content['timeresults']) )
		{
			// Process each month!
			foreach( $content['timeresults'] as $myTimeRecord)
			{
				// Set variables for timefilter
				SetUnixTimeStampFilters($myTimeRecord['Time_Year'], $myTimeRecord['Time_Month']);

				// Now the real Query stuff starts ;)!
				$sqlquery = "SELECT " .
									STATS_WEAPONS . ".ID as WEAPONID, " .
									"count(DISTINCT " . STATS_PLAYER_KILLS . ".PLAYERID) as PlayerCount, " . 
									"sum(" . STATS_PLAYER_KILLS . ".Kills) as WeaponKills " . 
									" FROM " . STATS_WEAPONS . 
									" INNER JOIN (" . STATS_PLAYER_KILLS . ", " . STATS_ROUNDS . ") " .
									" ON (" . 
									STATS_PLAYER_KILLS . ".WEAPONID =" . STATS_WEAPONS . ".ID AND " . 
									STATS_PLAYER_KILLS . ".ROUNDID =" . STATS_ROUNDS . ".ID " . 
									") " . 
									" WHERE " . STATS_PLAYER_KILLS . ".SERVERID = " . $serverid .
									GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
									GetTimeWhereQueryStringForRoundTable() . 
									" GROUP BY " . STATS_WEAPONS . ".ID ";
				$result = DB_Query($sqlquery);
				$content['weaponslist'] = DB_GetAllRows($result, true);
				if ( isset($content['weaponslist']) )
				{
					// Process each damagetype and insert data!
					foreach( $content['weaponslist'] as $myWeaponType)
					{
						// Insert or Update damagetype record
						InsertOrUpdateWeaponkills( $serverid, $myTimeRecord['Time_Year'], $myTimeRecord['Time_Month'], $myWeaponType );
					}
				}
			}
		}
	}

}

function InsertOrUpdateWeaponkills( $myServerID, $timeyear, $timemonth, $myWeaponTypeRecord )
{
	//PrintHTMLDebugInfo( DEBUG_DEBUG, "InsertOrUpdateWeaponkills", "Adding/Updating Record for Weapon '" . $myWeaponTypeRecord['WEAPONID'] . "', Server '" . $myServerID . "'");
	$wherequery =	" WHERE WEAPONID = " . $myWeaponTypeRecord['WEAPONID'] . " AND " . 
					" SERVERID = " . $myServerID . " AND " . 
					" Time_Year = " . $timeyear . " AND " . 
					" Time_Month = " . $timemonth . ""; 

	$result = DB_Query("SELECT WEAPONID FROM " . STATS_WEAPONS_KILLS . " " . $wherequery );
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		// Update Calc
		ProcessUpdateStatement(	" UPDATE " . STATS_WEAPONS_KILLS . " SET " . 
								" Kills = " . $myWeaponTypeRecord['WeaponKills'] . ", " . 
								" PlayersCount = " . $myWeaponTypeRecord['PlayerCount'] . 
								$wherequery, true );
	}
	else
	{
		// Insert New
		ProcessInsertStatement("INSERT INTO " . STATS_WEAPONS_KILLS . " (WEAPONID, SERVERID, Time_Year, Time_Month, Kills, PlayersCount) 
		VALUES (
			 " . $myWeaponTypeRecord['WEAPONID'] . ", 
			 " . $myServerID . ", 
			 " . $timeyear . ", 
			 " . $timemonth . ", 
			 " . $myWeaponTypeRecord['WeaponKills'] . ", 
			 " . $myWeaponTypeRecord['PlayerCount'] . ")");
	}
}

function InsertOrUpdateDamagetypekills( $myServerID, $timeyear, $timemonth, $myDamageTypeRecord )
{
	//PrintHTMLDebugInfo( DEBUG_DEBUG, "InsertOrUpdateDamagetypekills", "Adding/Updating Record for Damagetype '" . $myDamageTypeRecord['DAMAGETYPEID'] . "', Server '" . $myServerID . "'");
	$wherequery =	" WHERE damagetypeid = " . $myDamageTypeRecord['DAMAGETYPEID'] . " AND " . 
					" SERVERID = " . $myServerID . " AND " . 
					" Time_Year = " . $timeyear . " AND " . 
					" Time_Month = " . $timemonth . ""; 

	$result = DB_Query("SELECT damagetypeid FROM " . STATS_DAMAGETYPES_KILLS . " " . $wherequery );
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		// Update Calc
		ProcessUpdateStatement(	" UPDATE " . STATS_DAMAGETYPES_KILLS . " SET " . 
								" Kills = " . $myDamageTypeRecord['DamageKills'] . ", " . 
								" PlayersCount = " . $myDamageTypeRecord['PlayerCount'] . 
								$wherequery, true );
	}
	else
	{
		// Insert New
		ProcessInsertStatement("INSERT INTO " . STATS_DAMAGETYPES_KILLS . " (damagetypeid, SERVERID, Time_Year, Time_Month, Kills, PlayersCount) 
		VALUES (
			 " . $myDamageTypeRecord['DAMAGETYPEID'] . ", 
			 " . $myServerID . ", 
			 " . $timeyear . ", 
			 " . $timemonth . ", 
			 " . $myDamageTypeRecord['DamageKills'] . ", 
			 " . $myDamageTypeRecord['PlayerCount'] . ")");
	}
}

?>