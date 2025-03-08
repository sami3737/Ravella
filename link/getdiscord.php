<?php
	function is_session_started(){
		if ( php_sapi_name() !== 'cli' ) {
			if ( version_compare(phpversion(), '5.4.0', '>=') ) {
				return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
			} else {
				return session_id() === '' ? FALSE : TRUE;
			}
		}
		return FALSE;
	}

	if ( is_session_started() === FALSE ) session_start();
	
	require(__DIR__ . '/api/mysql/Db.class.php');
	require(__DIR__ . '/api/rcon/q3query.class.php');

	$db = new DB();

	if($_SESSION == null || $_SESSION['T2SteamID64'] == null )
	{ 
		echo 'false';
		exit();
	}
	
	$settings = parse_ini_file(__DIR__ . "/api/mysql/settings.ini.php");
	require(__DIR__ . '/api/discord/setting.php');
	
	$select = $db->query("SELECT * FROM ".$settings["table"]." WHERE steamid = :steam", Array("steam" => $_SESSION['T2SteamID64']));
	if(count($select) != 0)
	{
		if($select[0]["discordid"] != null)
		{ 
			echo 'true';
			exit();
		}
	}

	echo 'false';
?>
