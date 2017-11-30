<?php
	$db = new PDO("sqlite:wahl.db3");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->query("PRAGMA foreign_keys = ON");

	if(isset($_GET["p"]) && !empty($_GET["p"])){
		try{
			$poll = $db->prepare("SELECT id, total, fullname, stepunit, stepping FROM polls WHERE [name] = :short;");
			$options = $db->prepare("SELECT id, option, def FROM options WHERE poll = :pollid ORDER BY [order] ASC, option ASC;");
			$pick = $db->prepare("INSERT INTO picks (poll, name) VALUES (:poll, :name);");
			$result = $db->prepare("INSERT INTO results (pick, option, result) VALUES (:pick, :option, :result);");
		}
		catch(PDOException $e){
			//FIXME return from errors
			$error = "Vorbereitung der Datenbankabfragen schlug fehl: " . $e->getMessage();
		}

		if(!$poll->execute(array($_GET["p"]))){
			$error = "Konnte Abfrage zur Abstimmung nicht ausfuehren.";
		}

		$pollinfo = $poll->fetch(PDO::FETCH_ASSOC);
		if(!$pollinfo){
			$error = "Die angefragte Abstimmung ist in der Datenbank nicht auffindbar.";
		}

		if(!$options->execute(array($pollinfo["id"]))){
			$error = "Konnte Abfrage zu den Abstimmungsoptionen nicht ausfuehren.";
		}

		if(isset($_POST["tip"])){
			//check antispam method
			if(isset($_POST["password"]) && empty($_POST["password"])){
				if(!$pick->execute(array($pollinfo["id"], htmlspecialchars($_POST["submitter"])))){
					$error = "Konnte Tip nicht in Datenbank speichern.";
				}

				$pickid = $db->lastInsertId();
				
				foreach($options->fetchAll(PDO::FETCH_ASSOC) as $opt){
					//TODO check totals
					if(isset($_POST["option-" . $opt["id"]])){
						//TODO check 
						$result->execute(array($pickid, $opt["id"], floatval($_POST["option-" . $opt["id"]])));
					}
				}
			}
			header("Location: result.php?p=" . urlencode($_GET["p"]));
			die();
		}
	}
	else{
		$error = TRUE;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Das Wahlspiel</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" href="static/favicon.svg" type="image/svg" />
		<link rel="shortcut icon" href="static/favicon.svg" type="image/svg" />
		<link rel="stylesheet" type="text/css" href="static/wahlspiel.css" />
		<meta name="description" content="Gib deinen Tipp auf das Ergebnis von <?= $pollinfo["fullname"] ?> ab." />
		<meta name="robots" content="index,follow" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<script type="text/javascript">
			var stepUnit = "<?= $pollinfo["stepunit"] ?>";
		</script>
		<script type="text/javascript" src="static/wahlspiel.js"></script>
	</head>
	<body onload="wahlspiel.init();">
	<?php
		if(!isset($error)){
	?>
		<div id="topbar">
			<h1>Tippspiel <?= $pollinfo["fullname"] ?></h1>
			<div id="welcome">
				Gib deinen Tip ab, indem du die Slider unten auf dein prognostiziertes Ergebnis einstellst.
				Gib deinen Namen ein und klicke auf "Abgeben", um dich in die Liste einzutragen.
			</div>
		</div>
		<noscript>
			<div id="warning">
				Diese Seite funktioniert mit JavaScript um einiges besser, da die Slider dann automatisch
				angepasst werden.
			</div>
		</noscript>
		<a href="#" id="reset" onclick="wahlspiel.reset();">Alles zur&uuml;cksetzen</a>
		<form action="" method="post">
			<div id="sliders">
				<?php
					foreach($options->fetchAll(PDO::FETCH_ASSOC) as $opt){
						?>
							<div class="option">
								<span class="option-name"><?= $opt["option"] ?></span>
								<span class="option-value"></span>
								<input name="option-<?= $opt["id"] ?>" type ="range"
									min="0" max="<?= $pollinfo["total"] ?>"
									step="<?= $pollinfo["stepping"] ?>" value="<?= $opt["def"] ?>"
									class="slider"
									data-initial="<?= $opt["def"] ?>" />
							</div>
						<?php
					}
				?>
			</div>
			<div id="submit">
				<span id="name-input">Dein Name:</span> <input type="text" name="submitter" /> <input type="submit" name="tip" value="Abstimmen" />
			</div>
			<input id="password" name="password" />
		</form>
	<?php
		}
		else if($error === TRUE){
		//welcome page
	?>
		<div id="topbar">
			<h1>Das Wahltippsiel</h1>
			<div id="welcome">
				Gib deinen Tip auf den Ausgang von Wahlen und Abstimmungen ab und vergleiche deine Prognose mit der von anderen.
			</div>
		</div>
	<?php
		}
		else{
	?>
		<div id="topbar">
			<h1>Das Wahltippsiel</h1>
			<div id="welcome">
				Gib deinen Tip auf den Ausgang von Wahlen und Abstimmungen ab und vergleiche deine Prognose mit der von anderen.
			</div>
		</div>
		<div id="warning">
			Bei der Bearbeitung deiner Anfrage ist leider ein Fehler aufgetreten:<br/>
			<?= $error ?>
		</div>
	<?php
		}
	?>
	</body>
</html>
