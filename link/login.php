<?php
	//https://discord.com/developers/applications
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

	if (!function_exists('is_session_started')) {
		function is_session_started()
		{
			if (php_sapi_name() !== 'cli') {
				if (version_compare(phpversion(), '5.4.0', '>=')) {
					return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
				} else {
					return session_id() === '' ? FALSE : TRUE;
				}
			}
			return FALSE;
		}
	}
	if ( is_session_started() === FALSE ) session_start();


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

	require(__DIR__ . '/api/mysql/Db.class.php');

	$settings = parse_ini_file(__DIR__ . "/api/mysql/settings.ini.php");
	$db = new DB();

    if (isset($_GET["error"])) {
        echo json_encode(array("message" => "Authorization Error"));
    } elseif (isset($_GET["code"])) {
        $redirect_uri = "http://www.ravella.eu/link/login.php"; //edit this url
        $token_request = "https://discordapp.com/api/oauth2/token";

        $token = curl_init();

        curl_setopt_array($token, array(
            CURLOPT_URL => $token_request,
            CURLOPT_POST => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
            CURLOPT_POSTFIELDS => http_build_query(array(
                "grant_type" => "authorization_code",
                "client_id" => "747511843014180915", //put here your discord client id
                "client_secret" => "0O6Vwf6c6PjCZYKVuudrwSKO76ASxTcj", //put here your discord client secret
                "redirect_uri" => $redirect_uri,
                "code" => $_GET["code"]
            ))
        ));
        curl_setopt($token, CURLOPT_RETURNTRANSFER, true);

        $resp = json_decode(curl_exec($token));
        curl_close($token);

        if (isset($resp->access_token)) {
            $access_token = $resp->access_token;

            $info_request = "https://discordapp.com/api/users/@me";

            $info = curl_init();
            curl_setopt_array($info, array(
                CURLOPT_URL => $info_request,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer {$access_token}"
                ),
                CURLOPT_RETURNTRANSFER => true
            ));

            $user = json_decode(curl_exec($info));
            curl_close($info);

			$select = $db->query("SELECT * FROM ".$settings["table"]." WHERE steamid = :steam or adressip = :ip", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
			$settings = parse_ini_file(__DIR__ . "/api/mysql/settings.ini.php");

			if(count($select) != 0)
			{
				$db->query("UPDATE ".$settings["table"]." SET discordid = :discord WHERE steamid = :steam or adressip = :ip", Array("discord" => $user->id, "steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
			}
			else
			{
				$db->query("INSERT INTO `".$settings["table"]."` (`steamid`, `discordid`) VALUES (:steam, :discord, :ip)", Array("steam" => $_SESSION['T2SteamID64'], "discord" => $user->id, 'ip' => getUserIpAddr()));
			}
			Header('Location: /link/index.php');

        } else {
            echo json_encode(array("message" => "Authentication Error"));
        }
    }

?>
