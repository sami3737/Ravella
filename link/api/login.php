<?php
	require "steam/apikey.php";
	require "steam/openid.php";

	$OpenID = new LightOpenID(""); //change to your website url
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

	if(!$OpenID->mode){
		$login = "";
		if(isset($_GET['login'])){
			$OpenID->identity = "https://steamcommunity.com/openid";
			header("Location: ".$OpenID->authUrl()."");
		}

		if(!isset($_SESSION['T2SteamAuth'])){
			$login = "<div><img class=\"loginimg\" src=\"./img/bannersocial.png\" /></div><div id=\"login\">Steam and Discord reward step 1:<a class=\"button\"  href=\"api/login.php?login\"><img class=\"loginimg\" src=\"./img/steam.png\"/></a><br /><div class=\"steamlogin\"></a></div></div>";
		}
	}
	elseif($OpenID->mode == "cancel"){
		echo "User has canceled Authentication";
		header("Location: ../index.php");
	}
	else
	{
		if(!isset($_SESSION['T2SteamAuth'])){
            $_SESSION['T2SteamAuth'] = $OpenID->validate() ? $OpenID->identity : null;
			$_SESSION['T2SteamID64'] = str_replace("https://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);

			if($_SESSION['T2SteamAuth'] != null)
			{
				$Steam64 = $_SESSION['T2SteamID64'];
				$profile = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$api}&steamids={$Steam64}");
				$buffer = fopen("../cache/{$Steam64}.json", "w+");
				fwrite($buffer, $profile);
				fclose($buffer);
			}

		}
		header("Location: ../index.php");
	}

	if(isset($_GET['logout'])){
		unset($_SESSION['T2SteamAuth']);
		unset($_SESSION['T2SteamID64']);
		unset($_SESSION['steam']);
		header("Location: ../index.php");
	}

	try {
		if(isset($_SESSION['T2SteamID64'])) {
		$content = file_get_contents("./cache/{$_SESSION['T2SteamID64']}.json");
            $_SESSION['steam'] = json_decode($content, FILE_USE_INCLUDE_PATH);
        }
		else {
            $steam = null;
        }
	}catch (Exception $e) {
		echo 'Exception reçue : '.  $e->getMessage(). "\n";
	}

	echo $login;

	if(isset($_SESSION['steam']) && $_SESSION['steam'] != null){
       /* foreach($_SESSION['steam']['response']['players'][0] as $child) {
            print_r($_SESSION['steam']['response']['players'][0]);
        }*/
    }

?>
