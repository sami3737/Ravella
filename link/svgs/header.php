<?php
function is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}

// Example
if ( is_session_started() === FALSE ) session_start();

require(__DIR__ . '/api/mysql/Db.class.php');
require(__DIR__ . '/api/rcon/q3query.class.php');

$db = new DB();

$settings = parse_ini_file(__DIR__ . "/api/mysql/settings.ini.php");
require(__DIR__ . '/api/discord/setting.php');

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content=" - ">
		<meta name="keywords" content="rust, webshop, evolution, donate, experimental">
		<title>Social link</title>
		<link rel="stylesheet" href="css/style.css" type="text/css">
		<link rel="stylesheet" href="css/all.css" type="text/css">
        <link rel="shortcut icon" href="./img/favicon.ico" type="image/x-icon">
		<script src="./js/fontawesome.js" crossorigin="anonymous"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script type="text/javascript">
		$(".discordlink").click(
			function(){
			$.ajax({
				url: $(this).attr('href'),
				type: 'GET',
				async: false,
				cache: false,
				timeout: 30000,
				error: function(){
					return true;
				},
				success: function(msg){
				  window.location.href = msg.redirect;
				}
			});
});
</script>
	</head>
	<body>

		<div id="main">
	<?php
		include('./api/login.php');

		$query = simplexml_load_file("https://steamcommunity.com/groups/".$steamgroup."/memberslistxml/?xml=1");
		$in = false;
		$inDiscord = false;
		foreach($query->members->steamID64 as $key => $value)
		{
			if($value == $_SESSION['T2SteamID64'])
			{
				$in = true;
				break;
			}
		}

		$select = $db->query("SELECT * FROM ".$settings["table"]." WHERE steamid = :steam", Array("steam" => $_SESSION['T2SteamID64']));
    if($_SESSION != NULL){
  		if(count($select) != 0)
  		{
  			if($select[0]["discordid"] != null)
  			{
  				$inDiscord = true;
  			}

  			if($select[0]["InSteamGroup"] == 0)
  			{
  				if($in)
  				{
  					$db->query("UPDATE ".$settings["table"]." SET InSTeamGroup = 1 WHERE steamid = :steam", Array("steam" => $_SESSION['T2SteamID64']));
  				}
  			}
  		}
  		else
  		{
  			$db->query("INSERT INTO `".$settings["table"]."` (`steamid`) VALUES (:steam)", Array("steam" => $_SESSION['T2SteamID64']));
  		}
    }
    else{
      return;
    }

	?>
	<span>
    <div width="100%">
	<img src="./img/bannersocial.png"/>
</div>
	</span>
  <div>Step 2:</div>
<div><br></br></div>
	<div class="status-content">
		<table>
			<tr><td>Steam Linked </td><td><i class="fas fa-check"></i></td>
			<tr><td>In Steam group</a></td><td> <i class="fas fa-<?php echo ($in) ? 'check' :'times';?>"></i></td>
			<tr><td>Discord Linked </td><td><i class="fas fa-<?php echo ($inDiscord) ? 'check' :'times';?>"></i></td>
		</table>
	</div>
  <div><br></br></div>


	<?php

	if(!$inDiscord)
	{
		?>
    <div>
                  <a href="https://steamcommunity.com/groups/<?php echo $steamgroup; ?>" target="_blank"><img width="400px" src="./img/steamgroup.jpg"</a>

                  			<a href="<?php echo $dicordOAuth2Link; ?>" class="discordlink" target="_blank"><img width="400px" src="./img/discordgroup.jpg"</a>
                </div>
                         <a class="UnderStatusText"> When joined, Steam & Discord kit will be unlocked!</a><br />

		<?php
	}
