<?php
	require __DIR__ . '/SourceQuery/SourceQuery.class.php';
	
	// Edit this ->
	define( 'SQ_SERVER_ADDR', 'gs2.my-run.de' );
	define( 'SQ_SERVER_PORT', 27015 );
	define( 'SQ_TIMEOUT',     1 );
	define( 'SQ_ENGINE',      SourceQuery :: SOURCE );
	// Edit this <-
	
	$Timer = MicroTime( true );
	$Query = new SourceQuery( );
	
	$Info    = Array( );
	$Rules   = Array( );
	$Players = Array( );
	
	try
	{
		$Query->Connect( SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE );
		
		$Info    = $Query->GetInfo( );
		$Players = $Query->GetPlayers( );
		$Rules   = $Query->GetRules( );
	}
	catch( Exception $e )
	{
		$Exception = $e;
	}
	
	$Query->Disconnect( );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Source Query PHP Class</title>
	
	<link rel="stylesheet" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css">
	<style type="text/css">
		footer {
			padding: 30px 0;
			margin-top: 60px;
			border-top: 1px solid #E5E5E5;
			background-color: whiteSmoke;
		}
		footer p {
			margin-bottom: 0;
			color: #777;
		}
		.page-header h1 {
			font-size: 60px;
			font-weight: 200;
			line-height: 1;
			letter-spacing: -1px;
		}
	</style>
</head>

<body>
    <div class="container">
    	<div class="page-header">
			<h1>Source Query PHP Class</h1>
		</div>

<?php if( isset( $Exception ) ): ?>
		<div class="alert alert-error">
			<h4 class="alert-heading"><?php echo Get_Class( $Exception ); ?> at line <?php echo $Exception->getLine( ); ?></h4>
			<?php echo htmlspecialchars( $Exception->getMessage( ) ); ?>
		</div>
		
		<h3>Stack trace</h3>
		<pre><?php echo $e->getTraceAsString(); ?></pre>
<?php else: ?>
		<div class="row">
			<div class="span6">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th colspan="2">Server Info</th>
						</tr>
					</thead>
					<tbody>
<?php if( Is_Array( $Rules ) ): ?>
<?php foreach( $Info as $InfoKey => $InfoValue ): ?>
						<tr>
							<td><?php echo htmlspecialchars( $InfoKey ); ?></td>
							<td><?php
	if( Is_Array( $InfoValue ) )
	{
		echo "<pre>";
		print_r( $InfoValue );
		echo "</pre>";
	}
	else
	{
		echo htmlspecialchars( $InfoValue );
	}
?></td>
						</tr>
<?php endforeach; ?>
<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div class="span6">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Players</th>
						</tr>
					</thead>
					<tbody>
<?php if( Is_Array( $Players ) ): ?>
<?php foreach( $Players as $Player ): ?>
						<tr>
							<td><?php echo htmlspecialchars( $Player[ 'Name' ] ); ?></td>
						</tr>
<?php endforeach; ?>
<?php else: ?>
						<tr>
							<td>No players in da house!</td>
						</tr>
<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<table class="table table-condensed table-bordered table-striped">
					<thead>
						<tr>
							<th colspan="2">Rules</th>
						</tr>
					</thead>
					<tbody>
<?php if( Is_Array( $Rules ) ): ?>
<?php foreach( $Rules as $Rule => $Value ): ?>
						<tr>
							<td><?php echo htmlspecialchars( $Rule ); ?></td>
							<td><?php echo htmlspecialchars( $Value ); ?></td>
						</tr>
<?php endforeach; ?>
<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
<?php endif; ?>
	</div>
	
	<footer>
		<div class="container">
			<p class="pull-right">Generated in <span class="badge badge-success"><?php echo Number_Format( ( MicroTime( true ) - $Timer ), 4, '.', '' ); ?>s</span></p>
			
			<p>Made by <a href="http://xpaw.ru" target="_blank">xPaw</a>, source code available on <a href="https://github.com/xPaw/PHP-Source-Query-Class" target="_blank">GitHub</a></p>
			<p>Code licensed under the <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/" target="_blank">CC BY-NC-SA 3.0</a></p>
		</div>
	</footer>
</body>
</html>
