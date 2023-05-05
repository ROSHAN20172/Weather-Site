<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Previous Week Weather</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			background-color: #f2f2f2;
			margin: 0;
			padding: 0;
		}
		h1 {
			text-align: center;
			margin-top: 30px;
		}
		form {
			display: flex;
			align-items: center;
			justify-content: center;
			margin-top: 50px;
		}
		input[type="text"] {
			padding: 10px;
			font-size: 16px;
			border: none;
			border-radius: 5px;
			margin-right: 10px;
			outline: none;
		}
		input[type="submit"] {
			padding: 10px;
			font-size: 16px;
			background-color: #4CAF50;
			color: white;
			border: none;
			border-radius: 5px;
			cursor: pointer;
		}
		table {
			margin-top: 50px;
			margin-left: auto;
			margin-right: auto;
			border-collapse: collapse;
			width: 80%;
			background-color: white;
			box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
		}
		th, td {
			padding: 10px;
			text-align: center;
			border: 1px solid #ddd;
			font-size: 16px;
		}
		th {
			background-color: #4CAF50;
			color: white;
		}
		img {
			height: 50px;
			width: 50px;
		}
	</style>
</head>
<body>

	<form method="get" action="">
		<input type="text" name="city" placeholder="Enter city name">
		<input type="submit" name="submit" value="Search">
	</form>


<?php

if (isset($_GET['submit'])) {
  $city = $_GET['city'];
} else {
  $city = "mobile";
}


$url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid=daee0ae2ee355e95216a143b6c38f70c&units=metric";

$response = file_get_contents($url);
$data = json_decode($response, true);

if (!$data) {
  die("Error: Failed to retrieve data from OpenWeatherMap API.");
}

$city_name = $data['name'];
$condition = $data['weather'][0]['main'];
$icon = $data['weather'][0]['icon'];
$temperature = $data['main']['temp'];
$pressure = $data['main']['pressure'];
$humidity = $data['main']['humidity'];
$wind_speed = $data['wind']['speed'];
$wind_direction = $data['wind']['deg'];
$cloudiness = $data['clouds']['all'];
$sunrise = date('Y-m-d H:i:s', $data['sys']['sunrise']);
$sunset = date('Y-m-d H:i:s', $data['sys']['sunset']);
$rainfall = isset($data['rain']['1h']) ? $data['rain']['1h'] : 'not given';

$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'prototype_2';

$conn = mysqli_connect($hostname, $username, $password, $dbname);

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM weather WHERE `city`='$city_name' AND DATE(`date`) = CURDATE()";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  $sql = "UPDATE weather SET `icon`='$icon', `condition`='$condition', `temperature`='$temperature', `pressure`='$pressure', `humidity`='$humidity', `wind_speed`='$wind_speed' WHERE `city`='$city_name' AND `date`= DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')";
} else {
  
  $sql = "INSERT INTO weather (`city`, `date`, `icon`, `condition`, `temperature`, `pressure`, `wind_speed`, `humidity`)
        VALUES ('$city_name', NOW(), '$icon', '$condition', '$temperature', '$pressure', '$wind_speed', '$humidity')";
}


mysqli_query($conn, $sql);

$sql = "SELECT * FROM weather WHERE `city`='$city_name' ORDER BY `date` DESC LIMIT 7";
$result = mysqli_query($conn, $sql);

echo "<h1>Weather In {$city_name} (Last 7 Days Record)</h1>";
echo "<table border='1'>";
while ($row = mysqli_fetch_assoc($result)) {
$date = date('Y-m-d', strtotime($row['date']));
$icon = $row['icon'];
$condition = $row['condition'];
$temperature = $row['temperature'];
$pressure = $row['pressure'];
$humidity = $row['humidity'];
$wind_speed = $row['wind_speed'];

echo "<td> Date";
echo "<td> Icon";
echo "<td> Condition";
echo "<td> Temperature";
echo "<td> pressure";
echo "<td> Humidity";
echo "<td> Wind Speed";

echo "<tr>";
echo "<td>{$date}</td>";
echo "<td><img src='http://openweathermap.org/img/w/{$icon}.png'></td>";
echo "<td>{$condition}</td>";
echo "<td>{$temperature}°C</td>";
echo "<td>{$pressure} hPa</td>";
echo "<td>{$humidity}%</td>";
echo "<td>{$wind_speed} m/s</td>";
echo "</tr>";
}
echo "</table>";

mysqli_close($conn);
?>
</body>
</html>