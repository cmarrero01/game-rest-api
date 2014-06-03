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
	* ->	Main language strings in ENGLISH
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/

global $content;

// Global Stuff
$content['LN_MAINTITLE'] = "Main UltraStats";
$content['LN_MAIN_SELECTSTYLE'] = "Select a Style";
$content['LN_GEN_LANGUAGE'] = "Select Language";
$content['LN_GEN_SKINMADEBY'] = "Skin made by";
$content['LN_NODESCRIPTION'] = "No description available";
$content['LN_MAIN_SELECTTIMESPAN'] = "Select time span";
$content['LN_ERROR_DETAILS'] = "Error Details";
$content['LN_WARN_DETAILS'] = "Warning Details";
$content['LN_ERROR_NOSTATSDATAFOUND'] = "No stats data found using these filters.";
$content['LN_DETAIL_INFO'] = "Detail Information";
$content['LN_FOOTER_PAGERENDERED'] = "Page rendered in";
$content['LN_FOOTER_DBQUERIES'] = "DB queries";
$content['LN_FOOTER_GZIPENABLED'] = "GZIP enabled";
$content['LN_FOOTER_SCRIPTTIMEOUT'] = "Script Timeout";
$content['LN_FOOTER_SECONDS'] = "seconds";
$content['LN_WARN_NOFTPEXTENSIONS'] = "Warning! The FTP Extension have been disabled on your Webserver. UltraStats will not be able to download the gamelog over FTP.";
$content['LN_CONFIGUREDGAME'] = "Configured for: ";

// Index Site
$content['LN_MAINSELECTSERVER'] = "Select Server";
$content['LN_MAINLASTROUNDS'] = "%1 last played rounds";
$content['LN_LROUNDS_TIME'] = "Date/Time played at";
$content['LN_LROUNDS_GAMETYPE'] = "Gametype";
$content['LN_LROUNDS_MAPNAME'] = "Played Map";
$content['LN_LROUNDS_RESULTS'] = "Results";
$content['LN_LROUNDS_DURATION'] = "Played";
$content['LN_LROUNDS_DETAILS'] = "Round Details";
$content['LN_LROUNDS_WINNER'] = "Winner";
$content['LN_LROUNDS_DETAILSONLY'] = "Details";
$content['LN_MAINTOPPLAYERS'] = "Top Players";
$content['LN_TOPPLAY_NUM'] = "No.";
$content['LN_TOPPLAY_NAME'] = "Name";
$content['LN_TOPPLAY_KILLS'] = "Kills";
$content['LN_TOPPLAY_Deaths'] = "Deaths";
$content['LN_TOPPLAY_TeamKills'] = "Team Kills";
$content['LN_TOPPLAY_Suicides'] = "Suicides";
$content['LN_TOPPLAY_KillRatio'] = "Ratio";
$content['LN_TOPPLAY_PlayedTime'] = "Played Time";
$content['LN_PRO_Medals'] = "Pro Medals";
$content['LN_ANTI_Medals'] = "Anti Medals";
$content['LN_Medals'] = "Medal";
$content['LN_GLOBAL_STATS'] = "UltraStats Running for";
$content['LN_GLOBAL_LASTUPDATE'] = "Last Database Update";
$content['LN_GLOBAL_SERVERLIST'] = "Serverlist";
$content['LN_GLOBAL_LASTSERVERUPDATE'] = "Last Server Update";
$content['LN_ERROR_NOSERVERS'] = "No Servers installed! If this is a fresh installation, go to the Admin Center and add one ;)!";
$content['LN_ERROR_INSTALLFILEREMINDER'] = "Warning! You still have NOT removed the 'install.php' from your UltraStats main directory!";
$content['LN_PLAYERS'] = "Players";
$content['LN_MAP'] = "Map";
$content['LN_COUNT'] = "Count";
$content['LN_TOPPLAY_PBGUID'] = "Punkbuster GUID";
$content['LN_TOPPLAY_PBGUIDLAST8'] = "Last 8 characters only";
$content['LN_LROUNDS_SERVER'] = "Server";

// Players
$content['LN_PLAYERS_MAINLASTROUNDS'] = "Top Players sorted by ";
$content['LN_PLAYERS_ONLYLISTED'] = "Only Players listed";
$content['LN_PLAYERS_WITHMINPLAY'] = "With Minimum Playtime";
$content['LN_PLAYERS_SECANDMINKILLS'] = "seconds and minimum kills";

// Player Details
$content['LN_ERROR_INVALIDPLAYER'] = "Error, the played ID was empty or invalid.";
$content['LN_PLAYER_DETAILS'] = "Player Details";
$content['LN_PLAYER_TOPALIASES'] = "Top Aliases";
$content['LN_PLAYER_ALIASCOUNT'] = "Count";
$content['LN_PLAYER_ALIAS'] = "Alias";
$content['LN_PLAYER_FAVMAP'] = "Favourite Map";
$content['LN_PLAYER_FAVWEAPON'] = "Favourite Weapon";
$content['LN_PLAYER_PLAYED'] = "Played: ";
$content['LN_PLAYER_TOPWEAPONS'] = "Top used Weapons";
$content['LN_PLAYER_WEAPON'] = "Weapon";
$content['LN_PLAYER_KILLS'] = "Kills";
$content['LN_PLAYER_RATIO'] = "Ratio";
$content['LN_PLAYER_TOPVICTIMS'] = "Top Victims";
$content['LN_PLAYER_KILLED'] = "Played killed: ";
$content['LN_PLAYER_VICTIM_NAME'] = "Victim Name: ";
$content['LN_PLAYER_TOPKILLEDBY'] = "Top killed by";
$content['LN_PLAYER_KILLER_NAME'] = "Killer Name: ";
$content['LN_PLAYER_TOPPLAYEDMAPS'] = "Top played Maps";
$content['LN_PLAYER_MAP'] = "Map";
$content['LN_PLAYER_MAPNAME'] = "Map Name";
$content['LN_PLAYER_COUNT'] = "Played Count";
$content['LN_PLAYER_TOPHITLOCATIONS'] = "Top Hitlocations where you hit others";
$content['LN_PLAYER_TOPHITLOCATIONS_KILLED'] = "Top Hitlocations where you got killed by others";
$content['LN_PLAYER_HITLOCATION'] = "Hitlocation";
$content['LN_PLAYER_HITLKILLSCOUNT'] = "Killcount";
$content['LN_PLAYER_HITLMOUSEHELP'] = "To see details, scroll over the <b>Playermodel</b>";
$content['LN_PLAYER_LASTROUNDS'] = "Last played rounds";
$content['LN_PLAYER_DETAILS'] = "Details";
$content['LN_PLAYER_LASTQUOTES'] = "Last 10 Player quotes";
$content['LN_PLAYER_ERROR'] = "Error obtaining player data";
$content['LN_PLAYER_ERROR_NOPLAYERDATA'] = "Could not find data for this player";
$content['LN_PLAYER_ERROR_TIMEFILTER'] = "The player may did not play in this time span"; 
$content['LN_PLAYER_ERROR_DIDNOTPLAY'] = "The player '%1' did not play in this time span"; 

// Rounds Stats
$content['LN_ROUNDS_ALLROUNDS'] = "All Rounds";
$content['LN_ROUNDS_BYDATE'] = "Sorted by Date";
$content['LN_ROUNDS_BYGAMETYPE'] = "Sorted by Gametype";
$content['LN_ROUNDS_ROUNDNOTFOUND'] = "The round could not be found";
$content['LN_ROUNDS_AVAILABLEGAMETYPES'] = "Available Gametypes";

// Round Details
$content['LN_ROUNDS_DETAILS'] = "Rounddetails for Round ID ";
$content['LN_ROUNDS_AXIS'] = "Axis Team";
$content['LN_ROUNDS_ALLIES'] = "Allies Team";
$content['LN_ROUNDS_POINTS'] = "Points";
$content['LN_ROUNDS_CHATLOG'] = "Chatlog of this Round";
$content['LN_ROUNDS_CHATENTRY'] = "Chatentry";
$content['LN_ROUNDS_SAID'] = " said:";
$content['LN_ROUNDS_AWARDS'] = "Round Awards";
$content['LN_ROUNDS_SUM'] = "Round Summary";
$content['LN_ROUNDS_PLAYERDIDNOTFINISH'] = "Players who did not finish the round";
$content['LN_ROUNDS_ACTIONS'] = "Round Actions";
$content['LN_ROUNDS_ACTIONNAME'] = "Action Name";

// Server Details
$content['LN_SERVER_NAME'] = "Name";
$content['LN_SERVER_IP'] = "IP / Port";
$content['LN_SERVER_Description'] = "Description";
$content['LN_SERVER_ModName'] = "ModName";
$content['LN_SERVER_AdminName'] = "AdminName";
$content['LN_SERVER_ClanName'] = "ClanName";
$content['LN_SERVER_TopValues'] = "Server Top Values for ";
$content['LN_SERVER_MostValues'] = "Server Most Values";
$content['LN_SERVER_Top_Map'] = "Top played map";
$content['LN_SERVER_Top_Gametype'] = "Top played gametype";

// Map Details
$content['LN_ERROR_INVALIDMAP'] = "Invalid Map Name";
$content['LN_MAP_DETAILS'] = "Map Details for ";
$content['LN_MAP_INFO'] = "Map Info";
$content['LN_MAP_DESCRIPTION'] = "Map Description";
$content['LN_MAP_NODESCRIPTION'] = "No Map Description found";
$content['LN_MAP_LASTROUNDS'] = "Last 20 played rounds on ";
$content['LN_MAP_INGAMENAME'] = "Ingame Mapname";

// Gametype Details
$content['LN_GAMETYPE_DETAILS'] = "Gametype Details for ";
$content['LN_GAMETYPE_NODESCRIPTION'] = "No Gametype Description found";
$content['LN_GAMETYPE_DESCRIPTION'] = "Gametype Description";
$content['LN_GAMETYPE_INGAMENAME'] = "InGame Gametype Name";
$content['LN_GAMETYPE_LASTROUNDS'] = "Last 20 played rounds in this gametype";
$content['LN_ERROR_UNKNOWNGAMETYPE'] = "Unknown Gametype Name";

// Weapon Details
$content['LN_WEAPON_NODESCRIPTION'] = "No Weapon Description found";
$content['LN_ERROR_INVALIDWEAPON'] = "Invalid Weapon Name";
$content['LN_WEAPON_DETAILS'] = "Weapon Details for ";
$content['LN_WEAPON_DESCRIPTION'] = "Weapon Description";
$content['LN_WEAPON_INGAMENAME'] = "InGame Weaponname";
$content['LN_WEAPON_TOPPLAYERS'] = "Players with most Kills";
$content['LN_WEAPON_TOPKILLEDBY'] = "Most killed by this Weapon";
$content['LN_WEAPON_EXTERNLINFO'] = "External Information";
$content['LN_WEAPON_LIST'] = "List of all weapons";
$content['LN_WEAPON_NAME'] = "Weapon name";
$content['LN_WEAPON_KILLCOUNT'] = "Kills done with this weapon";
$content['LN_WEAPON_PLAYERCOUNT'] = "Players who used this weapon";
$content['LN_WEAPON_ATTACHMENT'] = "Attachment";
$content['LN_WEAPON_READMORE'] = "Follow this link to get more Informations:";
$content['LN_WEAPON_KILLCOUNT_TEXT'] = "%1 total kills were caused by this weapon.";
$content['LN_WEAPON_PLAYERCOUNT_TEXT'] = "%1 players killed with this weapon";

// ServerStats Details
$content['LN_ERROR_NOSERVERMAPS'] = "Error, either no Server ID was given, or no maps have been played on this server!";
$content['LN_SERVER_DETAILS'] = "Details for this Server";
$content['LN_SERVER_MAPDETAILS'] = "Map Details for";
$content['LN_SERVER_LASTROUNDS'] = "Last played rounds on this map";
$content['LN_SERVER_MAPSTATS'] = "Map statistics for this server";
$content['LN_SERVER_PLAYEDCOUNT'] = "Play Count";
$content['LN_SERVER_MOSTGAMETYPE'] = "Most Played Gametype";

// Search Player 
$content['LN_SEARCH_TITLE'] = "Search for a player";
$content['LN_SEARCH_PLAYERID'] = "Search by GUID";
$content['LN_SEARCH_PLAYERALIAS'] = "Search by PlayerAlias";
$content['LN_SEARCH_STRING'] = "Search string";
$content['LN_SEARCH_BY'] = "Search by";
$content['LN_SEARCH_NOPLAYERFOUND'] = "Error, no player with this ALIAS or GUID found!";
$content['LN_SEARCH_FOUND'] = "Results found for ";
$content['LN_SEARCH_IGNORECOLORCODES'] = "Ignore Color Codes";
$content['LN_SEARCH_PLAYERPBGUID'] = "Search by Punkbuster GUID";

// Search in Chatlog
$content['LN_SEARCH_CHATSTRING'] = "Search in Chatlog for this";
$content['LN_SEARCH_CHATTITLE'] = "Search in Chatlog";
$content['LN_SEARCH_CHATNOTFOUND'] = "Error, no chats found that match your search '%1'.";
$content['LN_SEARCH_CHATTOSHORT'] = "Error, your search phrase was to short, at least three Charaters are needed.";

// Weapon Defs
$content['LN_WEAPONTYPE_MACHINEGUN'] = "Machine Gun";
$content['LN_WEAPONTYPE_SNIPER'] = "Sniper Rifles";
$content['LN_WEAPONTYPE_PISTOL'] = "Pistols";
$content['LN_WEAPONTYPE_GRENADE'] = "Primary Grenades";
$content['LN_WEAPONTYPE_SECONDARYGRENADE'] = "Secondary Grenades";
$content['LN_WEAPONTYPE_STANDWEAPON'] = "Stand Weapon";
$content['LN_WEAPONTYPE_TANK'] = "Tanks & Vehicles";
$content['LN_WEAPONTYPE_MISC'] = "Misc weapons";
$content['LN_WEAPONTYPE_SUBMACHINEGUN'] = "Sub Machine Guns";
$content['LN_WEAPONTYPE_ASSAULT'] = "Assault Rifles";
$content['LN_WEAPONTYPE_LIGHTMACHINEGUN'] = "Light Machine Guns";
$content['LN_WEAPONTYPE_SHOTGUN'] = "Shotguns";
$content['LN_WEAPONTYPE_SPECIAL'] = "Special weapons";
$content['LN_WEAPONTYPE_RIFLES'] = "Rifles";
$content['LN_WEAPONTYPE_HEAVYWEAPONS'] = "Rocket Launchers";
$content['LN_WEAPONTYPE_CATEGORY'] = "Weapon Category";

// Medals site
$content['LN_ERROR_INVALIDMEDAL'] = "Error, this medal does not exist.";
$content['LN_MEDAL_DETAILS'] = "Details for the '%1' medal";
$content['LN_MEDAL_DESCRIPTION'] = "Description of the medal";
$content['LN_MEDAL_TOPPLAYERS'] = "Top Players";
$content['LN_MEDAL_NODESCRIPTION'] = "No medal description available";

// DamageTypes Site!
$content['LN_DAMAGETYPE_NODESCRIPTION'] = "No damagetype description available";
$content['LN_ERROR_INVALIDDAMAGETYPE'] = "Error, this damagetype does not exist.";
$content['LN_DAMAGETYPE_DETAILS'] = "Details for the '%1' damagetype";
$content['LN_DAMAGETYPE_DESCRIPTION'] = "Description of the damagetype";
$content['LN_DAMAGETYPE_INGAMENAME'] = "Ingame name";
$content['LN_DAMAGETYPE_TOPPLAYERS'] = "Players with most '%1' Kills";
$content['LN_DAMAGETYPE_TOPKILLEDBY'] = "Most killed by this damagetype";
$content['LN_DAMAGETYPE_LIST'] = "List of all damagetypes";
$content['LN_DAMAGETYPE_NAME'] = "Damagetype name";
$content['LN_DAMAGETYPE_KILLCOUNT'] = "Kills caused by this damagetype";
$content['LN_DAMAGETYPE_PLAYERCOUNT'] = "Players who killed with this damagetype";
$content['LN_DAMAGETYPE_KILLCOUNT_TEXT'] = "%1 total kills were caused by this damagetype.";
$content['LN_DAMAGETYPE_PLAYERCOUNT_TEXT'] = "%1 players killed with this damagetype";

//Install Site
$content['LN_CFG_PARAMMISSING'] = "The following parameter were missing: ";
$content['LN_CFG_DATABASE'] = "Database Settings";
$content['LN_CFG_DBSERVER'] = "Database Host";
$content['LN_CFG_DBPORT'] = "Database Port";
$content['LN_CFG_DBNAME'] = "Database Name";
$content['LN_CFG_DBPREF'] = "Table prefix";
$content['LN_CFG_DBUSER'] = "Database User";
$content['LN_CFG_DBPASSWORD'] = "Database Password";
$content['LN_CFG_GAMEVER'] = "Gameversion";
$content['LN_INSTALL_TITLETOP'] = "Installing UltraStats Version %1 - Step %2";
$content['LN_INSTALL_TITLE'] = "Installer Step %1";
$content['LN_INSTALL_ERRORINSTALLED'] = 'UltraStats is already configured/installed!<br><br> If you want to reinstall UltraStats, either delete the current <B>config.php</B> or replace it with an empty file.<br>Click <A HREF="index.php">here</A> to return to UltraStats start page.';
$content['LN_INSTALL_FILEORDIRNOTWRITEABLE'] = "At least one file or directory (or more) is not writeable, please check the file permissions.<br>On Linux you can use this commands:<br>Create an empty config.php: '%1'<br>Set write access to config.php: '%2'<br>Set write access to directories: '%3'";
$content['LN_INSTALL_SAMPLECONFIGMISSING'] = "The sample configuration file '%1' is missing. You may have not fully uploaded UltraStats.";
$content['LN_INSTALL_ERRORCONNECTFAILED'] = "Database connect to '%1' failed! Please check Servername, Port, User and Password!";
$content['LN_INSTALL_ERRORACCESSDENIED'] = "Cannot use the database  '%1'! If the database does not exists, create it or check user access permissions!";
$content['LN_INSTALL_ERRORINVALIDDBFILE'] = "Error, invalid Database definition file (to short!), the file name is '%1'! Please check if the file was correctly uploaded.";
$content['LN_INSTALL_ERRORINSQLCOMMANDS'] = "Error, invalid Database definition file (no sql statements found!), the file name is '%1'!<br> Please check if the file was not correctly uploaded, or contact the UltraStats forums for assistance!";
$content['LN_INSTALL_MISSINGUSERNAME'] = "Username needs to be specified";
$content['LN_INSTALL_PASSWORDNOTMATCH'] = "Either the password does not match or is to short!";
$content['LN_INSTALL_FAILEDCREATECFGFILE'] = "Coult not create the configuration file in '%1'! Please verify the file permissions!";
$content['LN_INSTALL_STEP1'] = "Step 1 - Prerequisites";
$content['LN_INSTALL_STEP2'] = "Step 2 - Verify File Permissions";
$content['LN_INSTALL_STEP3'] = "Step 3 - Database Configuration";
$content['LN_INSTALL_STEP4'] = "Step 4 - Create Tables";
$content['LN_INSTALL_STEP5'] = "Step 5 - Check SQL Results";
$content['LN_INSTALL_STEP6'] = "Step 6 - Creating the firts Adminuser";
$content['LN_INSTALL_STEP7'] = "Step 7 - Done";
$content['LN_INSTALL_STEP1_TEXT'] = 'Before you start installing UltraStats, the Installer setup has to check a few things first.<br>You may have to correct some file permissions. <br><br>Click on <input type="submit" value="Next"> to start the Test!';
$content['LN_INSTALL_STEP2_TEXT'] = "The following file permissions have been checked. Verify the results below! <br>You may use the <B>configure.sh</B> script from the <B>contrib</B> folder to set the permissions for you.";
$content['LN_INSTALL_STEP3_TEXT'] = "In this step, you have to configure the basic database settings for UltraStats. <br>Without this database connection, you cannot step further in the installation of UltraStats!";
$content['LN_INSTALL_STEP4_TEXT'] = 'If you reached this step, the database connection has been successfully verified!<br><br> The next step will create the necessary database tables used by UltraStats. This might take a while!<br> <b>WARNING</b>, if you have an existing UltraStats installation in this database with the same tableprefix, all your data will be <b>OVERWRITTEN</b>! Make sure you are using a fresh database, or you want to overwrite your old UltraStats database. <br><br><b>Click on <input type="submit" value="Next"> to start the creation of the tables</b>';
$content['LN_INSTALL_STEP5_TEXT'] = "All tables have been created. Check the List below for possible Error's.";
$content['LN_INSTALL_STEP6_TEXT'] = "You are now about to create the first UltraStats Adminuser account.<br> You will need this user in order to login and administrate your UltraStats installation in the Admin Center!";
$content['LN_INSTALL_STEP7_TEXT'] = 'Congratulations! You have successfully installed UltraStats :)! <br><br>Click <a href="index.php">here</a> to view your installation.';
$content['LN_INSTALL_WARNGAMESEL'] = "UltraStats can process Stats for ONE Game! It is __NOT__ possible to change this in a later step, so choose wisely ;)!";
$content['LN_INSTALL_SUCCESSSTATEMENTS'] = "Successfully executed statements:";
$content['LN_INSTALL_FAILEDSTATEMENTS'] = "Failed statements:";
$content['LN_INSTALL_STEP5_TEXT_NEXT'] = "If everything is fine, go to the next step to create the first UltraStats admin user.";
$content['LN_INSTALL_STEP5_TEXT_FAILED'] = "At least one statement failed,see error reasons below";
$content['LN_INSTALL_ERRORMSG'] = "Error Message";
$content['LN_INSTALL_SQLSTATEMENT'] = "SQL Statement";
$content['LN_INSTALL_CREATEUSER'] = "Create User Account";
$content['LN_INSTALL_PASSWORD'] = "Password";
$content['LN_INSTALL_PASSWORDREPEAT'] = "Repeat Password";
$content['LN_INSTALL_SUCCESSCREATED'] = "Successfully created User";
$content['LN_INSTALL_RECHECK'] = "ReCheck";
$content['LN_INSTALL_FINISH'] = "Finish!";
$content['LN_INSTALL_PROGRESS'] = "Install Progress: ";
$content['LN_INSTALL_MISSINGDBFILE'] = "The database definition file '%1' is missing.";
$content['LN_INSTALL_USERNAME'] = "Username";

// Main MENU
$content['LN_MENU_HOME'] = "Home";
$content['LN_MENU_ROUNDSTATS'] = "Roundstats";
$content['LN_MENU_ROUNDS_BYDATE'] = "Sorted by Date";
$content['LN_MENU_ROUNDS_BYGAMETYPE'] = "Sorted by Gametype";
$content['LN_MENU_ABOUT'] = "About UltraStats";
$content['LN_MENU_PLAYERSTATS'] = "Player Stats";
$content['LN_MENU_PLAYER_BYKILL'] = "Top Players by Kills";
$content['LN_MENU_PLAYER_BYDEATH'] = "Top Players by Deaths";
$content['LN_MENU_PLAYER_BYTEAMKILLS'] = "Top Players by Teamkills";
$content['LN_MENU_PLAYER_BYSUICIDES'] = "Top Players by Suicides";
$content['LN_MENU_PLAYER_RATIO'] = "Top Players by Ratio";
$content['LN_MENU_SEARCH'] = "Search";
$content['LN_MENU_SEARCH_PLAYERS'] = "Search for Players";
$content['LN_MENU_SEARCH_CHAT'] = "Search in Chatlog";
$content['LN_MENU_WEAPONSTATS'] = "Weapon Stats";
$content['LN_MENU_DAMAGESTATS'] = "Damagetype Stats";
$content['LN_MENU_SERVERSTATS'] = "Server Stats";
$content['LN_MENU_ADMINCENTER'] = "Admin Center";
$content['LN_MENU_SERVERLIST'] = "List Servers";
$content['LN_MENU_SERVERPLAYEDMAPS'] = "Show Played Maps";
$content['LN_MENU_CLICKTOEXPANDMENU'] = "Click on the Icon to expand the menu";

// Update notification
$content['LN_UPDATE_AVAILABLE'] = "UltraStats Update available";
$content['LN_UPDATE_AVAILABLETEXT'] = "An update for UltraStats is available. Your current installed Version is '%1'.<br>Version '%2' is available for update.";
$content['LN_UPDATE_LINK'] = "Click here to get the UltraStats Update";

?>