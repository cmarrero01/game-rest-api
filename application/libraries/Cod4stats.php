<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cod4stats {

	public function parse_stats($dataServer)
	{
		
		$myserver = $dataServer;	
		
		global $CFGCOD4, $gl_root_path, $content;
		
		$gl_root_path = 'C:\xampp/htdocs/restest/application/libraries/cod4/';
		
		include($gl_root_path . 'include/functions_common.php');
		
		// Obtain max_execution_time
		$MaxExecutionTime = ini_get("max_execution_time");
		
		// --- Check necessary PHP Extensions!
		$loadedExtensions = get_loaded_extensions();
		
		// Get and Set RunTime Informations
		
		global $content;
		
		$content['MYSQL_BULK_MODE'] = false;
		
		// Set the default line sep
		
		
		$RUNMODE == RUNMODE_WEBSERVER;
		$linesep = "<br>";
		
		// Start Session environment
		
		if ( !isset($_SESSION['SESSION_STARTED']) )$_SESSION['SESSION_STARTED'] = "true";
		
		
		// Will init the config file!
		global $CFGCOD4, $gl_root_path, $content;
		
		if ( file_exists($gl_root_path . 'config.php') && GetFileLength($gl_root_path . 'config.php') > 0 )
		{
			
			// Include the main config
			include_once($gl_root_path . 'config.php');
			
			// Easier DB Access
			$myPref = GetConfigSetting("TBPref", "cod4_");
			
			define('STATS_ALIASES',				$myPref . "aliases");
			define('STATS_CHAT',				$myPref . "chat");
			define('STATS_CONFIG',				$myPref . "config");
			define('STATS_CONSOLIDATED',		$myPref . "consolidated");
			define('STATS_GAMEACTIONS',			$myPref . "gameactions");
			define('STATS_DAMAGETYPES',			$myPref . "damagetypes");
			define('STATS_DAMAGETYPES_KILLS',	$myPref . "damagetypes_kills");
			define('STATS_ATTACHMENTS',			$myPref . "attachments");
			define('STATS_GAMETYPES',			$myPref . "gametypes");
			define('STATS_HITLOCATIONS',		$myPref . "hitlocations");
			define('STATS_LANGUAGE_STRINGS',	$myPref . "language_strings");
			define('STATS_MAPS',				$myPref . "maps");
			define('STATS_PLAYER_KILLS',		$myPref . "player_kills");
			define('STATS_PLAYERS',				$myPref . "players");
			define('STATS_ROUNDS',				$myPref . "rounds");
			define('STATS_ROUNDACTIONS',		$myPref . "roundactions");
			define('STATS_SERVERS',				$myPref . "servers");
			define('STATS_TIME',				$myPref . "time");
			define('STATS_USERS',				$myPref . "users");
			define('STATS_WEAPONS',				$myPref . "weapons");
			define('STATS_WEAPONS_KILLS',		$myPref . "weapons_kills");
			define('STATS_WEAPONS_PERSERVER',	$myPref . "weapons_perserver");
			define('STATS_PLAYERS_STATIC',		$myPref . "players_static");
			define('STATS_PLAYERS_TOPALIASES',	$myPref . "players_topalias");
			
			// --- Now Copy all entries into content variable
			//foreach ($CFGCOD4 as $key => $value )
			//	$content[$key] = $value;
			// --- 
			
			// For ShowPageRenderStats
			if ( GetConfigSetting("ShowPageRenderStats", 1) == 1 )
			{
				$content['ShowPageRenderStats'] = "true";
				InitPageRenderStats();
			}
			
		}
		
		global $link_id, $CFGCOD4;
		
		//TODO: Check variables first
		
		$dbServer = 'localhost';
		$dbUser = 'root';
		$dbPass = '';
		$dbName= 'cod4';
		$link_id = @mysql_connect($dbServer,$dbUser,$dbPass);
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
		
		// check if database exists!
		$db_selected = @mysql_select_db($dbName, $link_id);
		if(!$db_selected) DB_PrintError("Cannot use database '" . $dbName . "'", true);
		
		global $content, $gl_root_path, $LANG;
		
		$result = DB_Query("SELECT * FROM " . STATS_CONFIG);
		$rows = DB_GetAllRows($result, true, true);
		
		if ( isset($rows ) )
		{
			for($i = 0; $i < count($rows); $i++)
			{
				$content[ $rows[$i]['name'] ] = $rows[$i]['value'];
				$CFGCOD4[ $rows[$i]['name'] ] = $rows[$i]['value']; // Also copy into CFG Array!
			}
		}
		// General defaults 
		// --- Language Handling
		if ( !isset($content['gen_lang']) ) { $content['gen_lang'] = "en"; }
		if ( VerifyLanguage($content['gen_lang']) )
			$LANG = $content['gen_lang'];
		else
		{
			// Fallback!
			$LANG = "en";
			$content['gen_lang'] = "en";
		}
		
		// Now check for custom LANG!
		if ( isset($_SESSION['CUSTOM_LANG']) && VerifyLanguage($_SESSION['CUSTOM_LANG']) )
		{
			$content['user_lang'] = $_SESSION['CUSTOM_LANG'];
			$LANG = $content['user_lang'];
		}
		else
			$content['user_lang'] = $content['gen_lang'];
		// --- 
		
		// --- Game Version
		// Set Default!	- TODO, set in install.php!
		if ( !isset($content['gen_gameversion']) ) 
		{
			$content['gen_gameversion'] = COD2; 
			$content['gen_gameversion_picpath'] = "cod"; 
		}
		else
		{
			if (	$content['gen_gameversion'] == COD || 
				$content['gen_gameversion'] == CODUO || 
				$content['gen_gameversion'] == COD2 ||
				$content['gen_gameversion'] == CODWW )
				$content['gen_gameversion_picpath'] = "cod"; 
			else if($content['gen_gameversion'] == COD4)
				$content['gen_gameversion_picpath'] = "cod4"; 
		}
		
		// --- SQL Workaround
		if ( !isset($content['gen_bigselects']) ) { $content['gen_bigselects'] = "no"; }
		if ( $content['gen_bigselects'] == "yes")
			EnableBigSelects();
		// --- 
		// --- Parseby Type
		// Set Default!	- TODO, set in install.php!
		if ( !isset($content['gen_parseby']) ) { $content['gen_parseby'] = PARSEBY_GUIDS; }
		// --- 
		
		// --- PHP Debug Mode
		if ( !isset($content['gen_phpdebug']) ) { $content['gen_phpdebug'] = "no"; }
		// --- 
		
		// --- Set DEFAULT GZIP Output!
		if ( !isset($content['gen_gzipcompression']) ) { $content['gen_gzipcompression'] = "yes"; }
		// --- 
		
		// --- Default Script Timeout!
		if ( !isset($content['gen_maxexecutiontime']) ) { $content['gen_maxexecutiontime'] = 30; }
		// --- 
		
		// Web defaults 
		// --- Theme Handling
		if ( !isset($content['web_theme']) ) { $content['web_theme'] = "codww"; }
		if ( isset($_SESSION['CUSTOM_THEME']) && VerifyTheme($_SESSION['CUSTOM_THEME']) )
			$content['user_theme'] = $_SESSION['CUSTOM_THEME'];
		else
			$content['user_theme'] = $content['web_theme'];
		
		// --- Init Theme About Info ^^
		InitThemeAbout($content['user_theme']);
		// --- 
		
		// --- Handle HTML Injection stuff
		if ( strlen(GetConfigSetting("InjectHtmlHeader", false)) > 0 ) 
			$content['EXTRA_HTMLHEAD'] .= $CFGCOD4['InjectHtmlHeader'];
		else
			$content['InjectHtmlHeader'] = ""; // Init Option
		if ( strlen(GetConfigSetting("InjectBodyHeader", false)) > 0 ) 
			$content['EXTRA_HEADER'] .= $CFGCOD4['InjectBodyHeader'];
		else
			$content['InjectBodyHeader'] = ""; // Init Option
		if ( strlen(GetConfigSetting("InjectBodyFooter", false)) > 0 ) 
			$content['EXTRA_FOOTER'] .= $CFGCOD4['InjectBodyFooter'];
		else
			$content['InjectBodyFooter'] = ""; // Init Option
		// --- 
		
		// --- Handle Optional Logo URL!
		if ( strlen(GetConfigSetting("UltrastatsLogoUrl", false)) > 0 ) 
			$content['EXTRA_ULTRASTATS_LOGO'] = $CFGCOD4['UltrastatsLogoUrl'];
		else
			$content['UltrastatsLogoUrl'] = ""; // Init Option
		// --- 
		
		// --- Init main langauge file now!
		IncludeLanguageFile( $gl_root_path . '/lang/' . $LANG . '/main.php' );
		// --- 
		
		if ( !isset($content['web_toprounds']) ) { $content['web_toprounds'] = 50; }
		if ( !isset($content['web_mainpageplayers']) ) { $content['web_mainpageplayers'] = 20; }
		if ( !isset($content['web_topplayers']) ) { $content['web_topplayers'] = 50; }
		if ( !isset($content['web_detaillistsplayers']) ) { $content['web_detaillistsplayers'] = 20; }
		if ( !isset($content['web_minkills']) ) { $content['web_minkills'] = 25; }
		if ( !isset($content['web_mintime']) ) { $content['web_mintime'] = 600; }
		if ( !isset($content['web_maxpages']) ) { $content['web_maxpages'] = 25; }
		if ( !isset($content['web_maxmapsperpage']) ) { $content['web_maxmapsperpage'] = 10; }
		if ( !isset($content['web_medals']) ) { $content['web_medals'] = "yes"; }
		
		// Set default Player models!
		if ( !isset($content['web_playermodel_killer']) ) { $content['web_playermodel_killer'] = "marine"; }
		if ( !isset($content['web_playermodel_killedby']) ) { $content['web_playermodel_killedby'] = "german"; }
		
		// Admin Interface
		if ( !isset($content['admin_maxplayers']) ) { $content['admin_maxplayers'] = 30; }
		if ( !isset($content['admin_maxpages']) ) { $content['admin_maxpages'] = 20; }
		
		// Parser defaults 
		if ( !isset($content['parser_debugmode']) ) { $content['parser_debugmode'] = STR_DEBUG_INFO; } SetDebugModeFromString( $content['parser_debugmode'] );
		if ( !isset($content['parser_disablelastline']) ) { $content['parser_disablelastline'] = "no"; }
		if ( !isset($content['parser_chatlogging']) ) { $content['parser_chatlogging'] = "yes"; }
		
		// Database Version Checker! 
		if ( $content['database_internalversion'] > $content['database_installedversion'] )
		{	
			// Database is out of date, we need to upgrade
			$content['database_forcedatabaseupdate'] = "yes"; 
		}
		
		$result = DB_Query("SELECT count(id) as servercount FROM " . STATS_SERVERS);
		$rows = DB_GetAllRows($result, true);
		
		if ( isset($rows ) )
			$content['NUMSERVERS'] = $rows[0]['servercount'];
		else
			$content['NUMSERVERS'] = "0";
		
		
		
		
		global $content;
		if ( isset($content['last_dbupdate']) )
			$content['LASTDBUPDATE'] = print date('Y-m-d h:i:s', $content['last_dbupdate']);
		else
			$content['LASTDBUPDATE'] = "Never";
		
		global $content;
		
		// --- gen_gameversion
		$content['PARSEBYTYPES'][PARSEBY_GUIDS]['parsebytype'] = PARSEBY_GUIDS;
		$content['PARSEBYTYPES'][PARSEBY_GUIDS]['parsebytypetitle'] = LN_GEN_PARSEBY_GUIDS;
		if ( $content['gen_parseby'] == $content['PARSEBYTYPES'][PARSEBY_GUIDS]['parsebytype'] ) { $content['PARSEBYTYPES'][PARSEBY_GUIDS]['selected'] = "selected"; } else { $content['PARSEBYTYPES'][PARSEBY_GUIDS]['selected'] = ""; }
		
		$content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['parsebytype'] = PARSEBY_PLAYERNAME;
		$content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['parsebytypetitle'] = LN_GEN_PARSEBY_PLAYERNAME;
		if ( $content['gen_parseby'] == $content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['parsebytype'] ) { $content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['selected'] = "selected"; } else { $content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['selected'] = ""; }
		
		// --- Created Banned Players Filter
		
		
		
		
		
		global $content;
		
		// Get Weapons from DB!
		$sqlquery = "SELECT " .
		STATS_PLAYERS_STATIC . ".GUID, " . 
		STATS_PLAYERS_STATIC . ".ISBANNED, " . 
		STATS_PLAYERS_STATIC . ".BanReason " . 
		" FROM " . STATS_PLAYERS_STATIC . 
		" WHERE " . STATS_PLAYERS_STATIC . ".ISBANNED = 1 ";
		$result = DB_Query($sqlquery);
		$content['bannedplayers'] = DB_GetAllRows($result, true);
		
		
		
		if ( isset($content['bannedplayers']) )
		{
			//--- Set Displayname!
			for ( $i = 0; $i < count($content['bannedplayers']); $i++ )
			{
				if ( isset($content['bannedplayers_guids']) )
					$content['bannedplayers_guids'] .= ", " . $content['bannedplayers'][$i]['GUID'];
				else
					$content['bannedplayers_guids'] = $content['bannedplayers'][$i]['GUID'];
			}
			//---
		}
		else
			$content['bannedplayers_guids'] = "";
		// --- 
		
		// --- Created available years and month, which can be used for filtering
		
		
		
		
		global $content;
		
		// NOT SURE if this is a good idea xD
	//	$content['TIMETABLE'][ "ALL-TIME" ] = array ( "Year" => "", "Month" => "");
		
		// Get available month and years from DB!
		$sqlquery = " SELECT DISTINCT " . 
		STATS_TIME . ".Time_Year, " . 
		STATS_TIME . ".Time_Month " . 
		" FROM " . STATS_TIME . 
		" ORDER BY " . STATS_TIME . ".Time_Year DESC, " . STATS_TIME . ".Time_Month DESC";
		
		$result = DB_Query($sqlquery);
		$content['dbresults'] = DB_GetAllRows($result, true);
		if ( isset($content['dbresults']) )
		{
			// This enables the time filter within the stats
			$content['ENABLETIMEFILTER'] = true;
			$content['ENABLETIMEFILTER_MONTH'] = false;
			
			foreach ($content['dbresults'] as $myDate)
			{
				if ( !isset($content['TIMEYEARS'][ $myDate['Time_Year'] ]) ) 
				{
					$content['TIMEYEARS'][ $myDate['Time_Year'] ]['ID'] = $myDate['Time_Year'];
					$content['TIMEYEARS'][ $myDate['Time_Year'] ]['DisplayName'] = $myDate['Time_Year'];
					
					// Set selected state!
					if ( isset($_SESSION['TIME_SELECTEDYEAR']) && $_SESSION['TIME_SELECTEDYEAR'] == $myDate['Time_Year'] )
					{
						$content['TIMEYEARS'][ $myDate['Time_Year'] ]['selected'] = "selected"; 
						
						// Activate Month filter as well!
						$content['ENABLETIMEFILTER_MONTH'] = true;
					}
					else
						$content['TIMEYEARS'][ $myDate['Time_Year'] ]['selected'] = ""; 
					
				}
				
				// Add to MONTH array!
				if ( $content['ENABLETIMEFILTER_MONTH'] && isset($_SESSION['TIME_SELECTEDYEAR']) && $_SESSION['TIME_SELECTEDYEAR'] == $myDate['Time_Year'] )
				{
					$content['TIMEMONTHS'][ $myDate['Time_Month'] ]['ID'] = $myDate['Time_Month'];
					$content['TIMEMONTHS'][ $myDate['Time_Month'] ]['DisplayName'] = GetReadAbleMonth( $myDate['Time_Month'] );
					
					// Set selected state!
					if ( isset($_SESSION['TIME_SELECTEDMONTH']) && $_SESSION['TIME_SELECTEDMONTH'] == $myDate['Time_Month'] )
						$content['TIMEMONTHS'][ $myDate['Time_Month'] ]['selected'] = "selected"; 
					else
						$content['TIMEMONTHS'][ $myDate['Time_Month'] ]['selected'] = ""; 
				}
			}
		}
		else
		{
			// This disables the time filter within the stats
			$content['ENABLETIMEFILTER'] = false;
			$content['ENABLETIMEFILTER_MONTH'] = false;
		}
		
		// Set Unix Filter Timestamps now!
	//define('STATS_SERVERS', "cod4_servers ");
		include($gl_root_path . 'include/functions_parser.php');
		include($gl_root_path . 'include/functions_parser-helpers.php');
		
		$parse = RunParserNow($myserver);
		return $parse;
	}
}

?>