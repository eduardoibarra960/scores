<?php

header("Access-Control-Allow-Origin: *");
error_reporting(E_ERROR | E_PARSE);

$host = 'remotemysql.com:3306';
$username = 'Wc89mjbD4r';
$password = 'R9JP07iElm';
$database = 'Wc89mjbD4r';
$sqlConnection = mysqli_connect($host, $username, $password, $database);
if (mysqli_connect_errno())
{
	die("Cannot connect to database");
}

function mysqlExists($conexion, $tabla, $condicion)
{
	$sql = "SELECT * FROM " . $tabla . " WHERE " . $condicion;
	$sqlResult = mysqli_query($conexion, $sql);
	$n = mysqli_num_rows($sqlResult);
	if ($n > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function base64_to_jpeg($base64_string, $output_file)
{
	$imageData = base64_decode($base64_string);
	$source = imagecreatefromstring($imageData);
	$rotate = imagerotate($source, $angle, 0);
	$imageSave = imagejpeg($rotate, $output_file, 100);
	imagedestroy($source);
}

$app = $_POST['app'];
$validApp = preg_match("/^[a-zA-Z0-9]+$/", $app);
if ($_GET['x'] == "install")
{
	if ($validApp)
	{
		if (mysqli_query($sqlConnection, 'CREATE TABLE IF NOT EXISTS scores_' . $app . ' (
			id INT AUTO_INCREMENT PRIMARY KEY,
			custom_id TEXT,
			name TEXT,
			score INT,
			time TEXT,
			game_finished INT
		)'))
		{
			echo "Database created successfully. " . $app;
		}
		else
		{
			echo "Error.";
		}
	}
}
else if ($_GET['x'] == "submit_score")
{
	if ($validApp)
	{
		$myId = $_POST["id"];
		$myName = $_POST["name"];
		$myScore = $_POST["score"];
		$myTime = date('Y-m-d H:i:s');
		$myGameFinished = ($_POST["game_finished"] == "1" ? 1 : 0);
		
		$stmt = $sqlConnection->prepare('INSERT INTO scores_' . $app . ' (name, custom_id, score, time, game_finished) VALUES (?, ?, ?, ?, ?)');
		$stmt->bind_param('ssisi', $myName, $myId, $myScore, $myTime, $myGameFinished);
		$stmt->execute();
		$newId = $sqlConnection->insert_id;
		echo $newId;
	}
}
else if ($_GET['x'] == "update_score")
{
	if ($validApp)
	{
		$dbId = $_POST["db_id"];
		$myId = $_POST["id"];
		$myName = $_POST["name"];
		$myScore = $_POST["score"];
		$myTime = date('Y-m-d H:i:s');
		$myGameFinished = ($_POST["game_finished"] == "1" ? 1 : 0);
		
		$stmt = $sqlConnection->prepare('UPDATE scores_' . $app . ' SET name = ?, custom_id = ?, score = ?, time = ?, game_finished = ? WHERE id = ?');
		$stmt->bind_param('ssisii', $myName, $myId, $myScore, $myTime, $myGameFinished, $dbId);
		$stmt->execute();
		echo $myId;
	}
}
else if ($_GET['x'] == "view_scores")
{
	if ($validApp)
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
		if ($stmt = $sqlConnection->prepare('SELECT custom_id, name, score, time, game_finished FROM scores_' . $app . ' ORDER BY score DESC' . $limitString))
		{
			$stmt->execute();
			$stmt->bind_result($resultId, $resultName, $resultScore, $resultTime, $resultGameFinished);
			while ($stmt->fetch())
			{
				$length ++;
				$xYou[] = ($resultId == $myId ? true : false);
				$xNames[] = $resultName;
				$xScores[] = $resultScore;
				$xTimes[] = $resultTime;
				$xFinished[] = $resultGameFinished;
			}
		}
		
		$array = array
		(
			'count' => $length,
			'score' => $xScores,
			'player_name' => $xNames,
			'time' => $xTimes,
			'game_finished' => $xFinished,
			'you' => $xYou,
		);
		
		$myJSON = json_encode($array, JSON_PRETTY_PRINT);
		echo $myJSON;
	}
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
					if ($stmt = $sqlConnection->prepare('SELECT custom_id, name, score, time, game_finished FROM scores_plantfrens ORDER BY score DESC' . $limitString))
					{
						$stmt->execute();
						$stmt->bind_result($resultId, $resultName, $resultScore, $resultTime, $resultGameFinished);
						while ($stmt->fetch())
						{
							$c ++;
				?>
						<tr>
						  <th scope="row"><?php echo $c; ?></th>
						  <td><?php echo $resultName; ?></td>
						  <td><?php echo $resultScore; ?></td>
						  <td><?php echo $resultTime; ?></td>
						  <td><?php echo ($resultGameFinished == 1 ? "Yes" : "No"); ?></td>
						</tr>
				<?php
						}
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


?>