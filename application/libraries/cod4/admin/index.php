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
	* ->	Main Admin Center File													
	*		Main Admin options go here 
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
require_once($gl_root_path . 'include/functions_frontendhelpers.php');
require_once($gl_root_path . 'include/functions_parser-medals.php');

// Set PAGE to be ADMINPAGE!
define('IS_ADMINPAGE', true);
$content['IS_ADMINPAGE'] = true;

InitUltraStats();
CheckForUserLogin( false );
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- BEGIN CREATE TITLE
$content['TITLE'] = InitPageTitle();
$content['TITLE'] .= " :: General Options";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// Create TopPlayers Array
CreateTopPlayersArray( 200, "TOPROUNDS", "web_toprounds" );
CreateTopPlayersArray( 200, "TOPPLAYERS", "web_topplayers" );
CreateTopPlayersArray( 200, "TOPLISTPLAYERS", "web_detaillistsplayers" );
CreateTopPlayersArray( 100, "WEBMAXPAGES", "web_maxpages" );
CreateTopPlayersArray( 50, "WEBMAXMAPSPERPAGE", "web_maxmapsperpage" );
CreateTopPlayersArray( 100, "MAINPAGEPLAYERS", "web_mainpageplayers" );

// Create Helper Lists for Player Models!
CreatePlayerModelList( "PLAYERMODELKILLER", $content['web_playermodel_killer'] );
CreatePlayerModelList( "PLAYERMODELKILLEDBY", $content['web_playermodel_killedby'] );

// Create DebugModes
CreateDebugModes();

// --- Init MedalSQLCode!
CreateMedalsSQLCode(-1);

// Can't use this core yet, it only works on PHP5: foreach ($content['medals'] as $key => &$medal)
foreach ($content['medals'] as $key => $medal)
{
	$content['medals'][$key]['AdminDisplayName'] = GetAndReplaceLangStr( $content['LN_ADMINMEDALSENABLE'], $medal['DisplayName']);

	if ($content[$medal['medalid']] == "yes") 
		$content['medals'][$key]['MedalChecked'] = "checked"; 
	else 
		$content['medals'][$key]['MedalChecked'] = "";
}	
// --- 

// Some other things which need to be done
if ($content['web_medals'] == "yes") { $content['web_medals_checked'] = "checked"; } else { $content['web_medals_checked'] = ""; }
if ($content['parser_disablelastline'] == "yes") { $content['parser_disablelastline_checked'] = "checked"; } else { $content['parser_disablelastline_checked'] = ""; }
if ($content['gen_phpdebug'] == "yes") { $content['gen_phpdebug_checked'] = "checked"; } else { $content['gen_phpdebug_checked'] = ""; }
if ($content['parser_chatlogging'] == "yes") { $content['parser_chatlogging_checked'] = "checked"; } else { $content['parser_chatlogging_checked'] = ""; }
if ($content['gen_gzipcompression'] == "yes") { $content['gen_gzipcompression_checked'] = "checked"; } else { $content['gen_gzipcompression_checked'] = ""; }
if ($content['gen_bigselects'] == "yes") { $content['gen_bigselects_checked'] = "checked"; } else { $content['gen_bigselects_checked'] = ""; }

// Now the processing Part
if ( isset($_POST['op']) )
{
	// Read Gen Config Vars
	if ( isset ($_POST['gen_lang']) )
	{ 
		$tmpvar = DB_RemoveBadChars($_POST['gen_lang']); 
		if ( VerifyLanguage($tmpvar) )
			$content['gen_lang'] = $tmpvar;
	}
	if ( isset ($_POST['gen_gameversion']) ) { $content['gen_gameversion'] = Intval(DB_RemoveBadChars($_POST['gen_gameversion'])); }
	if ( isset ($_POST['gen_parseby']) ) { $content['gen_parseby'] = Intval(DB_RemoveBadChars($_POST['gen_parseby'])); }
	if ( isset ($_POST['gen_phpdebug']) ) { $content['gen_phpdebug'] = "yes"; } else { $content['gen_phpdebug'] = "no"; } 
	if ( isset ($_POST['gen_gzipcompression']) ) { $content['gen_gzipcompression'] = "yes"; } else { $content['gen_gzipcompression'] = "no"; } 
	if ( isset ($_POST['gen_bigselects']) ) { $content['gen_bigselects'] = "yes"; } else { $content['gen_bigselects'] = "no"; } 
	if ( isset ($_POST['gen_maxexecutiontime']) && is_numeric($_POST['gen_maxexecutiontime']) ) { $content['gen_maxexecutiontime'] = $_POST['gen_maxexecutiontime']; }
	

	// Read Parser Config Vars
	if ( isset ($_POST['parser_debugmode']) ) { $content['parser_debugmode'] = DB_RemoveBadChars($_POST['parser_debugmode']); }
	if ( isset ($_POST['parser_disablelastline']) ) { $content['parser_disablelastline'] = "yes"; } else { $content['parser_disablelastline'] = "no"; } 
	if ( isset ($_POST['parser_chatlogging']) ) { $content['parser_chatlogging'] = DB_RemoveBadChars($_POST['parser_chatlogging']); }  else { $content['parser_chatlogging'] = "no"; } 

	// Read WEB Config Vars
	if ( isset ($_POST['web_theme']) ) { $content['web_theme'] = DB_RemoveBadChars($_POST['web_theme']); }
	if ( isset ($_POST['web_toprounds']) ) { $content['web_toprounds'] = intval(DB_RemoveBadChars($_POST['web_toprounds'])); } 
	if ( isset ($_POST['web_topplayers']) ) { $content['web_topplayers'] = intval(DB_RemoveBadChars($_POST['web_topplayers'])); } 
	if ( isset ($_POST['web_mainpageplayers']) ) { $content['web_mainpageplayers'] = intval(DB_RemoveBadChars($_POST['web_mainpageplayers'])); } 
	if ( isset ($_POST['web_detaillistsplayers']) ) { $content['web_detaillistsplayers'] = intval(DB_RemoveBadChars($_POST['web_detaillistsplayers'])); } 
	if ( isset ($_POST['web_minkills']) && is_numeric($_POST['web_minkills']) ) { $content['web_minkills'] = DB_RemoveBadChars($_POST['web_minkills']); }
	if ( isset ($_POST['web_mintime']) && is_numeric($_POST['web_mintime'])) { $content['web_mintime'] = DB_RemoveBadChars($_POST['web_mintime']); } 
	if ( isset ($_POST['web_maxpages']) && is_numeric($_POST['web_maxpages'])) { $content['web_maxpages'] = DB_RemoveBadChars($_POST['web_maxpages']); } 
	if ( isset ($_POST['web_maxmapsperpage']) && is_numeric($_POST['web_maxmapsperpage'])) { $content['web_maxmapsperpage'] = DB_RemoveBadChars($_POST['web_maxmapsperpage']); } 
	if ( isset ($_POST['web_medals']) ) { $content['web_medals'] = "yes"; } else { $content['web_medals'] = "no"; }

	if ( isset ($_POST['web_playermodel_killer']) && CheckIfPlayerModelExists($_POST['web_playermodel_killer']) ) { $content['web_playermodel_killer'] = DB_RemoveBadChars($_POST['web_playermodel_killer']); } 
	if ( isset ($_POST['web_playermodel_killedby']) && CheckIfPlayerModelExists($_POST['web_playermodel_killedby'])) { $content['web_playermodel_killedby'] = DB_RemoveBadChars($_POST['web_playermodel_killedby']); } 

	// Read Text fields
	if ( isset ($_POST['PrependTitle']) ) { $content['PrependTitle'] = DB_RemoveBadChars($_POST['PrependTitle']); }
	if ( isset ($_POST['InjectHtmlHeader']) ) { $content['InjectHtmlHeader'] = DB_RemoveBadChars($_POST['InjectHtmlHeader']); }
	if ( isset ($_POST['InjectBodyHeader']) ) { $content['InjectBodyHeader'] = DB_RemoveBadChars($_POST['InjectBodyHeader']); }
	if ( isset ($_POST['InjectBodyFooter']) ) { $content['InjectBodyFooter'] = DB_RemoveBadChars($_POST['InjectBodyFooter']); }
	if ( isset ($_POST['UltrastatsLogoUrl']) ) { $content['UltrastatsLogoUrl'] = DB_RemoveBadChars($_POST['UltrastatsLogoUrl']); }


	// Write Gen Config Vars
	WriteConfigValue( "gen_lang" );
	WriteConfigValue( "gen_gameversion" );
	WriteConfigValue( "gen_parseby" );
	WriteConfigValue( "gen_phpdebug" );
	WriteConfigValue( "gen_gzipcompression" );
	WriteConfigValue( "gen_bigselects" );
	WriteConfigValue( "gen_maxexecutiontime" );

	// Read Parser Config Vars
	WriteConfigValue( "parser_debugmode" );
	WriteConfigValue( "parser_disablelastline" );
	WriteConfigValue( "parser_chatlogging" );

	// Write Web Config vars	
	WriteConfigValue( "web_theme" );
	WriteConfigValue( "web_toprounds" );
	WriteConfigValue( "web_mainpageplayers" );
	WriteConfigValue( "web_topplayers" );
	WriteConfigValue( "web_detaillistsplayers" );
	WriteConfigValue( "web_minkills" );
	WriteConfigValue( "web_mintime" );
	WriteConfigValue( "web_maxpages" );
	WriteConfigValue( "web_maxmapsperpage" );
	WriteConfigValue( "web_medals" );

	// Write PlayerDetail Options
	WriteConfigValue( "web_playermodel_killer" );
	WriteConfigValue( "web_playermodel_killedby" );

	// Global new options
	WriteConfigValue( "PrependTitle" );
	WriteConfigValue( "InjectHtmlHeader" );
	WriteConfigValue( "InjectBodyHeader" );
	WriteConfigValue( "InjectBodyFooter" );
	WriteConfigValue( "UltrastatsLogoUrl" );

	// Write Medal Config Vars
	foreach ($content['medals'] as $key => $medal)
	{
		if ( isset ($_POST[$key]) ) 
			$content[$key] = "yes"; 
		else 
			$content[$key] = "no";
		//Write into DB!
		WriteConfigValue( $key );
	}

	// Done and redirect
	RedirectResult( "Configuration Values have been successfully saved", "index.php" );
}
// --- 

// --- Helper functions
function CreatePlayerModelList( $szArrayItemName, $szSelectedPlayerModel)
{
	global $gl_root_path, $content;

	$alldirs = list_directories( $gl_root_path . "images/player/");
	for($i = 0; $i < count($alldirs); $i++)
	{
		// --- web_theme
		$content[$szArrayItemName][$i]['ModelName'] = $alldirs[$i];
		$content[$szArrayItemName][$i]['ModelDisplayname'] = GetPlayerModelDisplayName( $alldirs[$i] );
		if ( $szSelectedPlayerModel == $alldirs[$i] )
			$content[$szArrayItemName][$i]['selected'] = "selected";
		else
			$content[$szArrayItemName][$i]['selected'] = "";
		// ---

	}
}

function CheckIfPlayerModelExists( $szDirName ) 
{
	global $content, $gl_root_path;
	$szInfoFile = $gl_root_path . "images/player/" . $szDirName . "/info.txt";
	if ( is_file( $szInfoFile ) )
		return true;
	else
		return false;

}
function GetPlayerModelDisplayName( $szDirName ) 
{
	global $content, $gl_root_path;
	$szInfoFile = $gl_root_path . "images/player/" . $szDirName . "/info.txt";
	if ( is_file( $szInfoFile ) )
	{	
		//Read InfoFile!
		$infofile  = @fopen($szInfoFile, 'r');
		if (!feof ($infofile)) 
		{
			while (!feof ($infofile))
			{
				// Return max 32 characters
				$tmpline = fgets($infofile, 1024);
				return substr( trim($tmpline), 0, 32);
			}
		}
		fclose($infofile);
	}
	else // No Info, return ID as DisplayName
		return $szDirName;
}
// ---

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/index.html");
$page -> output(); 
// --- 

?>