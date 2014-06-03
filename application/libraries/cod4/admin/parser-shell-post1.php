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
	* ->	Parser Shell File													
	*		This file is used to run the parser on cronjobs 
	*		and for ssh access. 
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
$gl_root_path = 'C:/xampp/htdocs/cod4/';
include($gl_root_path . 'include/functions_common.php');

// Set PAGE to be ADMINPAGE!
define('IS_ADMINPAGE', true);
$content['IS_ADMINPAGE'] = true;

InitUltraStats();
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //


// --- BEGIN Custom Code
// Additional Includes
include($gl_root_path . 'include/functions_parser.php');
include($gl_root_path . 'include/functions_parser-helpers.php');
include($gl_root_path . 'include/functions_parser-medals.php');
include($gl_root_path . 'include/functions_parser-consolidation.php');


	/*	Operation Types
	*	statsandmedals	=	Will only update the stats and medals!
	*	medalsonly		=	Will only run a medal Update!
	*	downloadlog		=	Will download the logfile
	*	fullupdate		=	Will run a full update (Including LogFile download - requires additional parameters)
	*	runtotalstats	=	Should be done after all servers are updated, will calc global aliases, medals and such stuff. 
	*	resetlastlogline=	Resets the last Logline!
	*	emptystats		=	Fully deletes stats from the database!
	*						This is outsourced as it can become very slow and will heavily own the DB Server ;)
	*/

	// --- Now read the ARGs in!
	
		$operation = 'fullupdate';
	
		$serverid = 2;

		// Get ServerDetails now!
		$result = DB_Query("SELECT * FROM cod4_servers WHERE ID = " . $serverid);
		$serverdetails = DB_GetAllRows($result, true);
		
	

	// Set MaxExecutionTime first!
	SetMaxExecutionTime();
	
	// --- Operation Handling now
	if ( $operation == "statsandmedals" )
	{
		for($i = 0; $i < count($serverdetails); $i++)
		{
			// Set current Server
			$myserver = $serverdetails[$i];

			// Run Parser only
			RunParserNow();
		}
	}
	else if ( $operation == "medalsonly" )
	{

	}
	else if ( $operation == "downloadlog" )
	{
		// Get Last Downloadfile only
		GetLastLogFile( $ftppass );
	}
	else if ( $operation == "fullupdate" )
	{
		for($i = 0; $i < count($serverdetails); $i++)
		{
			// Set current Server
			$myserver = $serverdetails[$i];

			// Get Last Downloadfile first
			//GetLastLogFile( $ftppass );

			// Now run the parser!
			RunParserNow();
		}
	}
	else if ( $operation == "resetlastlogline" )
	{
		for($i = 0; $i < count($serverdetails); $i++)
		{ 
			// Set current Server 
			$myserver = $serverdetails[$i]; 

			// Reset Last Logline 
			ResetLastLine(); 
		} 
	}
	else if ( $operation == "emptystats" )
	{
		for($i = 0; $i < count($serverdetails); $i++)
		{ 
			// Set current Server 
			$myserver = $serverdetails[$i]; 

			// Reset Last Logline 
			DeleteServerStats(); 
		} 
	}
	
	else if ( $operation == "runtotalstats" )
	{
		// Now run the parser!
		RunTotalStats();
	}
	
	// --- 

// --- 

?>