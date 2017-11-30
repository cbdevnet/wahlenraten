<?php
	$db = new PDO("sqlite:wahl.db3");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->query("PRAGMA foreign_keys = ON");

	if(isset($_GET["p"]) && !empty($_GET["p"])){
		try{
			$poll = $db->prepare("SELECT id, total, fullname, stepunit FROM polls WHERE [name] = :short;");
			$picks = $db->prepare("SELECT picks.id AS id, timestamp, name, results.option AS option, options.option AS opt, result FROM picks JOIN results ON (picks.id = results.pick) JOIN options ON (results.option = options.id) WHERE picks.poll = :poll ORDER BY timestamp ASC, pick ASC, [order] ASC, results.option ASC;");
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

		if(!$picks->execute(array($pollinfo["id"]))){
			$error = "Konnte keine Tipps aus der Datenbank laden.";
		}

		$results = $picks->fetchAll(PDO::FETCH_ASSOC);
		if(!$results || sizeof($results) == 0){
			$error = "Noch keine Ergebnisse zu diesem Spiel.";
		}
	}
	else{
		header("Location: index.php");
		die();
	}

	$rows = array();

	//preprocess pick data
	foreach($results as $entry){
		$rows[$entry["id"]]["name"] = $entry["name"];
		$rows[$entry["id"]]["timestamp"] = $entry["timestamp"];
		$rows[$entry["id"]]["options"][] = $entry["result"];
	}

	//TODO css export
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Wahlspiel-Ergebnisse <?= $pollinfo["fullname"] ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" href="static/favicon.svg" type="image/svg" />
		<link rel="shortcut icon" href="static/favicon.svg" type="image/svg" />
		<link rel="stylesheet" type="text/css" href="static/wahlspiel.css" />
		<meta name="description" content="Ergebnisse des Wahlspiels zu <?= $pollinfo["fullname"] ?>." />
		<meta name="robots" content="index,follow" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
	<?php
		if(!isset($error)){
	?>
		<div id="topbar">
			<h1>Tippspiel <?= $pollinfo["fullname"] ?></h1>
			<div id="welcome">
				Gib deinen Tip auf den Ausgang von Wahlen und Abstimmungen ab und vergleiche deine Prognose mit der von anderen.
			</div>
		</div>
		<div id="results">
			<table id="result-table">
				<tr>
					<th>Einsender</th>
					<th>Zeitpunkt</th>
					<?php
						//FIXME this is ugly
						for($pick = $results[0]["id"], $i = 0; $pick == $results[$i]["id"] && $i < sizeof($results); $i++){
							print("<th>".$results[$i]["opt"]."</th>");
						}
					?>
				</tr>
				<?php
					foreach($rows as $row){
						?>
							<tr>
								<td><?= $row["name"] ?></td>
								<td><?= $row["timestamp"] ?></td>
								<?php
									foreach($row["options"] as $tip){
										print("<td>".$tip."</td>");
									}
								?>
							</tr>
						<?php
					}
				?>
			</table>
		</div>
	<?php
		}
		else{
	?>
		<div id="topbar">
			<h1>Das Wahltippspiel</h1>
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
