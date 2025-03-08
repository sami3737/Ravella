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

    var steamconnected = false;
		var validatesteamrepeat;
		$( document ).ready(function() {
			function validatesteam(){
				return $.ajax({
					url: 'getsteam.php',
					type: 'POST',
					async: false,
					dataType: 'json',
					success: function(data){
						if(data == true)
						{
							document.getElementById("steam").classList.remove('fa-check');
							document.getElementById("steam").classList.remove('fa-times');
							document.getElementById("steam").classList.add('fa-check');
							clearInterval(validatesteamrepeat);
              $("#steamauth").hide();
              steamconnected = true;

						}
            if(data == false)
            {
              $("#steamauth").show();
            }
					}
				});
			}
			validatesteamrepeat = setInterval(validatesteam, 3000);
		});

		var validatediscordrepeat;
		$( document ).ready(function() {
			function validatediscord(){
				return $.ajax({
					url: 'getdiscord.php',
					type: 'POST',
					async: false,
					dataType: 'json',
					success: function(data){
						if(data == true)
						{
							document.getElementById("discord").classList.remove('fa-check');
							document.getElementById("discord").classList.remove('fa-times');
							document.getElementById("discord").classList.add('fa-check');
							clearInterval(validatediscordrepeat);
              $("#discordauth").hide();
						}
            if(data == false)
            {
              if(steamconnected)
                $("#discordauth").show();
            }
					}
				});
			}
			validatediscordrepeat = setInterval(validatediscord, 3001);
		});
</script>
	</head>
	<body>
		<div id="main">
	<?php
		include('./api/login.php');

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
		$inDiscord = false;
		foreach($query->members->steamID64 as $key => $value)
		{
			if($value == $_SESSION['T2SteamID64'])
			{
				$in = true;
				break;
			}
		}

		$select = $db->query("SELECT * FROM ".$settings["table"]." WHERE steamid = :steam or adressip = :ip", Array("steam" => $_SESSION['T2SteamID64'], 'ip' => getUserIpAddr()));
    if($_SESSION != NULL){
		if(count($select) != 0)
		{
			if($select[0]["discordid"] != null)
			{
				$inDiscord = true;
				echo '<script type="text/javascript">
						$( document ).ready(function() {
							document.getElementById("discord").classList.add(\'fa-check\');
						});
					</script>';
			}

			if($select[0]["insteamgroup"] == 1)
			{
				echo '<script type="text/javascript">
						$( document ).ready(function() {
							document.getElementById("steam").classList.add(\'fa-check\');
						});
					</script>';
			}
		}
	}
    else{
      return;
    }
	if($in)
	{
		echo '<script type="text/javascript">
				$( document ).ready(function() {
					document.getElementById("steam").classList.add(\'fa-check\');
				});
			</script>';
	}
	if($inDiscord)
	{
		echo '<script type="text/javascript">
				$( document ).ready(function() {
					document.getElementById("discord").classList.add(\'fa-check\');
				});
			</script>';
	}

	?>

  <div width="100%">
<img class="loginimg" src="./img/bannersocial.png"/>
</div>

<a><br></a>

	<span class="status-title">
	Link Status
	</span>
	<div class="status-content">
		<table>
			<tr><td><b>Steam Linked:</b></td><td><i class="fas fa-check"></i></td>
			<tr><td><b>In Steam Group:</b></a></td><td> <i id="steam" class="fas"></i></td>
			<tr><td><b>Discord Linked:</b></td><td><i id="discord" class="fas"></i></td>
		</table>
	</div>
<?php
if($select[0]['insteamgroup'] == 0){
?>
		<div id="steamauth">
      <a><br></a>
      <a href="https://steamcommunity.com/groups/<?php echo $steamgroup; ?>" target="_blank"><img width="100%" max-width="400px" src="./img/steamgroup.png"</a>
		</div>
<?php
}
if($select[0]['discordid'] == NULL && $select[0]['insteamgroup'] == 1){
?>
    <div id="discordauth">
      <a><br></a>
      <a href="<?php echo $dicordOAuth2Link; ?>" class="discordlink" target="_blank"><img width="100%" max-width="400px" src="./img/discordgroup.png"</a>
    </div>
  <?php
} elseif($select[0]['discordid'] == NULL && $select[0]['insteamgroup'] == 0)
{
  ?>
      <div id="discordauth" style="display: none;">
        <a><br></a>
        <a href="<?php echo $dicordOAuth2Link; ?>" class="discordlink" target="_blank"><img width="100%" max-width="400px" src="./img/discordgroup.png"</a>
      </div>

      <?php
      }
      else
          {
            ?>
                      <div class="okdata">
                        <center><a><br>Steam & Discord kits are unlocked!</br></a><br /></center>
                        <center><a class="UnderStatusText">Please note this process can take up to max 5 minutes (You can close this window)</a></center>
                      </div>


      <?php
      	}
