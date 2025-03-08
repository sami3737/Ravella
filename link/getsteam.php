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
	require (__DIR__ . '/api/steam/apikey.php');

	$settings = parse_ini_file(__DIR__ . "/api/mysql/settings.ini.php");

	$db = new DB();

	if($_SESSION == null || $_SESSION['T2SteamID64'] == null )
	{
		echo 'false';
		exit();
	}
	function getUserIpAddr(){
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	$query = simplexml_load_file("https://steamcommunity.com/groups/".$steamgroup."/memberslistxml/?xml=1");
	$in = false;
	foreach($query->members->steamID64 as $key => $value)
	{
		if($value == $_SESSION['T2SteamID64'])
		{
			$select = $db->query("SELECT * FROM ".$settings["table"]." WHERE steamid = :steam or adressip = :ip", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
			if($_SESSION != NULL){
				if(count($select) != 0)
				{
					if($select[0]["insteamgroup"] == 0)
					{
						$db->query("UPDATE ".$settings["table"]." SET insteamgroup = 1 WHERE steamid = :steam or adressip = :ip", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
						echo 'true';
						exit();
					}
					else
					{
							echo 'true';
							exit();
					}
				}
				else
				{
					$db->query("INSERT INTO `".$settings["table"]."` (`steamid`, `adressip`) VALUES (:steam, :ip)", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
					$db->query("UPDATE ".$settings["table"]." SET insteamgroup = 1 WHERE steamid = :steam or adressip = :ip", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
					echo 'true';
					exit();
				}
			}

			break;
		}
	}
	$select = $db->query("SELECT * FROM ".$settings["table"]." WHERE steamid = :steam or adressip = :ip", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
	if(count($select) == 0)
	{
		$db->query("INSERT INTO `".$settings["table"]."` (`steamid`, `adressip`) VALUES (:steam, :ip)", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
		echo "false";
		exit();
	}
	elseif(count($select) != 0)
	{
		if($select[0]["insteamgroup"] == 1)
			echo "true";
			exit();
	}
