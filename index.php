<?php
	$db = new PDO("sqlite:wahl.db3");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->query("PRAGMA foreign_keys = ON");

	if(!isset($_GET["p"]) || empty($_GET["p"])){
		die("No poll"); //might want to output overview page here
	}
	else{
		try{
			$poll = $db->prepare("SELECT id, total, fullname FROM polls WHERE [name] = :short;");
			$options = $db->prepare("SELECT id, option, def FROM options WHERE poll = :pollid ORDER BY [order] ASC, option ASC;");
		}
		catch(PDOException $e){
			die("Failed to prepare database statements: " . $e->getMessage());
		}

		if(!$poll->execute(array($_GET["p"]))){
			die("Poll info failed");
		}

		$pollinfo = $poll->fetch(PDO::FETCH_ASSOC);
		if(!$pollinfo){
			die("No such poll");
		}

		if(!$options->execute(array($pollinfo["id"]))){
			die("Failed to fetch options");
		}

	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Zahlenraten Deluxe</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" href="static/favicon.svg" type="image/svg" />
		<link rel="shortcut icon" href="static/favicon.svg" type="image/svg" />
		<link rel="stylesheet" type="text/css" href="static/wahlspiel.css" />
		<meta name="description" content="Gib deinen Tipp auf das Ergebnis von <?php print($pollinfo["fullname"]); ?> ab." />
		<meta name="robots" content="index,follow" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<div id="topbar">
			<h1>Tippspiel <?php printf($pollinfo["fullname"]); ?></h1>
			<div id="welcome">
				Gib deinen Tip ab, indem du die Slider unten auf dein prognostiziertes Ergebnis
				einstellst.
				Gib deinen Namen ein und klicke auf "Abgeben", um dich in die Liste einzutragen.
			</div>
		</div>
		<form action="" method="post">
			<div id="sliders">
				<?php
					foreach($options->fetchAll(PDO::FETCH_ASSOC) as $opt){
						?>
							<div id="slider-<?= $opt["id"] ?>" class="option">
								<span class="option-name"><?= $opt["option"] ?></span>
								<span class="option-value">CCCC</span>
								<input id="option-<?= $opt["id"] ?>" type ="range" min="0" max="<?= $pollinfo["total"] ?>" step="0.1" value="<?= $opt["def"] ?>" class="slider" />
							</div>
						<?php
					}
				?>
			</div>
			<div id="submit">
				<span id="name-input">Dein Name:</span> <input type="text" name="submitter" /> <input type="submit" name="tip" value="Abstimmen" />
			</div>
		</form>
	</body>
</html>
