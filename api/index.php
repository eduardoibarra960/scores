<?php

header("Access-Control-Allow-Origin: *");
error_reporting(E_ERROR | E_PARSE);
$db = new SQLite3('/tmp/scores.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

if ($_GET['x'] == "install")
{
	$db->query('CREATE TABLE IF NOT EXISTS "scores" (
		"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		"custom_id" VARCHAR,
		"name" VARCHAR,
		"score" INTEGER,
		"time" DATETIME,
		"game_finished" BOOLEAN
	)');
	
	echo "Database created successfully.";
}
else if ($_GET['x'] == "submit_score")
{
	$myId = $_POST["id"];
	$myName = $_POST["name"];
	$myScore = $_POST["score"];
	$myGameFinished = ($_POST["game_finished"] == "1" ? true : false);
	$statement = $db->prepare('INSERT INTO "scores" ("name", "custom_id", "score", "time", "game_finished") VALUES (:name, :custom_id, :score, :time, :game_finished)');
	$statement->bindValue(':name', $myName);
	$statement->bindValue(':custom_id', $myId);
	$statement->bindValue(':score', $myScore);
	$statement->bindValue(':time', date('Y-m-d H:i:s'));
	$statement->bindValue(':game_finished', $myGameFinished);
	$statement->execute();
	echo $myId;
}
else if ($_GET['x'] == "view_scores")
{
	$xYou = array();
	$xScores = array();
	$xNames = array();
	$xTimes = array();
	$xFinished = array();
	
	$length = 0;
	$myId = $_POST["id"];
	$myQueryNumber = (is_numeric($_POST["max"]) ? $_POST["max"] : 0);
	$limitString = ($myQueryNumber > 0 ? " LIMIT " . $myQueryNumber : "");
	$res = $db->query('SELECT custom_id, name, score, time, game_finished FROM scores' . $limitString);
	while ($row = $res->fetchArray())
	{
		$length ++;
		$xYou[] = ($row['custom_id'] == $myId ? true : false);
		$xNames[] = $row['name'];
		$xScores[] = $row['score'];
		$xTimes[] = $row['time'];
		$xFinished[] = $row['game_finished'];
	}
	
	$array = array
	(
		'length' => $length,
		'score' => $xScores,
		'name' => $xNames,
		'time' => $xTimes,
		'game_finished' => $xFinished,
		'you' => $xYou,
	);
	
	$myJSON = json_encode($array, JSON_PRETTY_PRINT);
	echo $myJSON;
}
else
{
	?>
	<!doctype html>
	<html lang="en">
	  <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">

		<title></title>

		<!-- Bootstrap core CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom styles for this template -->
		<style>
			body {
			  padding-top: 5rem;
			}
			.starter-template {
			  padding: 3rem 1.5rem;
			  text-align: center;
			}
		</style>

	  </head>

	  <body>
		<main role="main" class="container">
			<table class="table table-dark">
			  <thead>
				<tr>
				  <th scope="col">#</th>
				  <th scope="col">Name</th>
				  <th scope="col">Score</th>
				  <th scope="col">Date</th>
				  <th scope="col">Game Finished</th>
				</tr>
			  </thead>
			  <tbody>
				<?php
					$c = 0;
					$res = $db->query('SELECT custom_id, name, score, time, game_finished FROM scores ORDER BY score DESC' . $limitString);
					while ($row = $res->fetchArray())
					{
						$c ++;
				?>
						<tr>
						  <th scope="row"><?php echo $c; ?></th>
						  <td><?php echo $row["name"]; ?></td>
						  <td><?php echo $row["score"]; ?></td>
						  <td><?php echo $row["time"]; ?></td>
						  <td><?php echo ($row["game_finished"] == 1 ? "Yes" : "No"); ?></td>
						</tr>
				<?php
					}
				?>
			  </tbody>
			</table>
		</main><!-- /.container -->
		
		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"><\/script>')</script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
	  </body>
	</html>
	<?php
}

$db->close();
?>
