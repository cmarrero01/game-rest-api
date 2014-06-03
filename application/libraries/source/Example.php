<?php
	require __DIR__ . '/SourceQuery/SourceQuery.class.php';
	
	// For the sake of this example
	Header( 'Content-Type: text/plain' );
	
	// Edit this ->
	define( 'SQ_SERVER_ADDR', '208.167.232.112' );
	define( 'SQ_SERVER_PORT', 27035 );
	define( 'SQ_TIMEOUT',     1 );
	define( 'SQ_ENGINE',      SourceQuery :: SOURCE );
	// Edit this <-
	
	$Query = new SourceQuery( );
	
	try
	{
		$Query->Connect( SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE );
		
		print_r( $Query->GetInfo( ) );
		print_r( $Query->GetPlayers( ) );
		print_r( $Query->GetRules( ) );
        print_r( $Query->SetRconPassword('1004734'));
        print_r( $Query->Rcon('tv_stoprecord'));
        //print_r( $Query->Rcon('tv_status'));
	}
	catch( Exception $e )
	{
		echo $e->getMessage( );
	}
	
	$Query->Disconnect( );
