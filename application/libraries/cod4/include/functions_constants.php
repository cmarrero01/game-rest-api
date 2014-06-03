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
	* ->	Constants Helper File
	*		This file contains all needed constants for UltraStat s
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/

// --- 

// --- Some custom defines
define('RUNMODE_COMMANDLINE', 1);
define('RUNMODE_WEBSERVER', 2);

define('DEBUG_ULTRADEBUG', 5);
define('DEBUG_DEBUG', 4);
define('DEBUG_INFO', 3);
define('DEBUG_WARN', 2);
define('DEBUG_ERROR', 1);
define('DEBUG_ERROR_WTF', 0);

define('STR_DEBUG_ULTRADEBUG', "UltraDebug");
define('STR_DEBUG_DEBUG', "Debug");
define('STR_DEBUG_INFO', "Information");
define('STR_DEBUG_WARN', "Warning");
define('STR_DEBUG_ERROR', "Error");
define('STR_DEBUG_ERROR_WTF', "WTF OMFG");
// --- 

// --- Game Version defines
define('COD', 0);
define('CODUO', 1);
define('COD2', 2);
define('COD4', 3);
define('CODWW', 4);

define('LN_GEN_COD', "Call of Duty");
define('LN_GEN_CODUO', "Call of Duty: United Offensive");
define('LN_GEN_COD2', "Call of Duty 2");
define('LN_GEN_COD4', "Call of Duty 4: Modern Warfare");
define('LN_GEN_CODWW', "Call of Duty: World at War");
// --- 

// ---
define('PARSEBY_GUIDS', 0);
define('PARSEBY_PLAYERNAME', 1);

define('LN_GEN_PARSEBY_GUIDS', "Guids");
define('LN_GEN_PARSEBY_PLAYERNAME', "Playername");
// ---

// --- Some CONSTANT Values for the Parser
define('PARSER_TYPE', 0);
define('PARSER_GUID', 1);

define('JOIN_CLIENTID', 2);
define('JOIN_CLIENTNAME', 3);

define('JOINTEAM_CLIENTID', 2);
define('JOINTEAM_CLIENTTEAM', 3);
define('JOINTEAM_CLIENTNAME', 4);

define('QUIT_CLIENTID', 2);
define('QUIT_CLIENTNAME', 3);

define('CHAT_CLIENTID', 2);
define('CHAT_CLIENTNAME', 3);
define('CHAT_MESSAGE', 4);

define('KILL_OPFER_GUID', 1);
define('KILL_OPFER_ID', 2);
define('KILL_OPFER_TEAM', 3);
define('KILL_OPFER_NAME', 4);
define('KILL_ATTACKER_GUID', 5);
define('KILL_ATTACKER_ID', 6);
define('KILL_ATTACKER_TEAM', 7);
define('KILL_ATTACKER_NAME', 8);
define('KILL_ATTACKER_WEAPON', 9);
define('KILL_DAMAGE', 10);
define('KILL_DAMAGE_TYPE', 11);
define('KILL_DAMAGE_LOCATION', 12);

define('DAMAGE_OPFER_GUID', 1);
define('DAMAGE_OPFER_ID', 2);
define('DAMAGE_OPFER_TEAM', 3);
define('DAMAGE_OPFER_NAME', 4);
define('DAMAGE_ATTACKER_GUID', 5);
define('DAMAGE_ATTACKER_ID', 6);
define('DAMAGE_ATTACKER_TEAM', 7);
define('DAMAGE_ATTACKER_NAME', 8);

define('RWIN_TEAM', 1);
define('RLOS_TEAM', 1);

define('RWINLOSS_GUID', 1);
define('RWINLOSS_ID', 2);
define('RWINLOSS_NAME', 3);

define('ACTION_CLIENTID', 2);
define('ACTION_CLIENT_TEAM', 3);
define('ACTION_CLIENT_NAME', 4);
define('ACTION_THEACTION', 5);

define('ACTIONV2_GUID', 1);
define('ACTIONV2_CLIENTID', 2);
define('ACTIONV2_CLIENT_NAME', 3);

define('PAM4_ACTION_CLIENTID', 3);
define('PAM4_ACTION_CLIENT_NAME', 2);
define('PAM4_ACTION_THEACTION', 4);
// --- 

// --- Constants for the processing
define('PLAYER_GUID', 0);
define('PLAYER_ID', 1);
define('PLAYER_NAME', 2);
define('PLAYER_TEAM', 3);
define('PLAYER_KILLS', 4);
define('PLAYER_DEATHS', 5);
define('PLAYER_TKS', 6);
define('PLAYER_SUICIDES', 7);
define('PLAYER_DBID', 8);
define('PLAYER_PBGUID', 9);

define('ROUND_GUID', 0);
define('ROUND_TIMESTAMP', 1);
define('ROUND_TIMEYEAR', 2);
define('ROUND_TIMEMONTH', 3);
define('ROUND_GAMETYPE', 4);
define('ROUND_MAPID', 5);
define('ROUND_SERVERCVARS', 6);
define('ROUND_AXIS_WINS', 7);
define('ROUND_AXIS_GUIDS', 8);
define('ROUND_ALLIES_WINS', 9);
define('ROUND_ALLIES_GUIDS', 10);
define('ROUND_DBID', 11);
define('ROUND_TOTALKILLS', 12);
define('ROUND_DURATION', 13);
define('ROUND_MODVERSION', 14);

define('DBKILLS_ATTACKERGUID', 0);
define('DBKILLS_OPFERGUID', 1);
define('DBKILLS_WEAPONID', 2);
define('DBKILLS_DAMAGETYPE', 3);
define('DBKILLS_HITLOCATION', 5);
define('DBKILLS_COUNT', 6);

define('TEAM_ALLIES', "allies");
define('TEAM_AXIS', "axis");
define('TEAM_WTF', "WTFOMFGBBQ");

define('MYSQLPATH_LINUX', "/usr/bin/mysql");			// For *nix:	mysql -u username -ppasswort database < stats.sql 
define('MYSQLPATH_WINDOWS', "D:\mysql\bin\mysql.exe");		// For Windows:	mysqld.exe -u username -ppasswort database < stats.sql 
// --- 

// --- TRANSFER Constants
define('FTP_TIMEOUT', 10);

define('TRANSFERTYPE_FTP', 0);
define('TRANSFERTYPE_SCP', 1);
define('TRANSFERTYPE_HTTP', 2);
// --- 

// --- WeaponTypes
define('WEAPONTYPE_MACHINEGUN', 0);			// MachineGun, Maschinenpistolen
define('WEAPONTYPE_SNIPER', 1);				// Sniper, Scharfschützengewehre
define('WEAPONTYPE_PISTOL', 2);				// Pistol, Pistole
define('WEAPONTYPE_GRENADE', 3);			// Grenade, Granaten
define('WEAPONTYPE_STANDWEAPON', 4);		// Stand-MG, Stand-MG's
define('WEAPONTYPE_TANK', 5);				// Tanks, Panzer
define('WEAPONTYPE_MISC', 6);				// Misc, Sonstiges
define('WEAPONTYPE_ASSAULT', 7);			// Assault Rifles, Sturmgewehre
define('WEAPONTYPE_LIGHTMACHINEGUN', 8);	// Light MachineGun, Leichte Maschinengewehre
define('WEAPONTYPE_SHOTGUN', 9);			// Shotgun, Schrotflinten
define('WEAPONTYPE_SPECIAL', 10);			// Special weapons like Claymore, RPG etc.
define('WEAPONTYPE_RIFLES', 11);			// Rifles Weapons like M1 Garand or K98
define('WEAPONTYPE_HEAVYWEAPONS', 12);		// Heavy Weapons like rocket launchers.
define('WEAPONTYPE_SECONDARYGRENADE', 13);	// Secondary Grenade
// --- 

// --- Config Level defines
define('CFGLEVEL_GLOBAL', 0);
define('CFGLEVEL_GROUP', 1);
define('CFGLEVEL_USER', 2);
// --- 

?>