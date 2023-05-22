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
			margin-top: 30px;
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
	    .homepage {
	      display: flex;
	      justify-content: center;
	      margin-top: 20px;
	    }
	    .homepage button {
	      padding: 12px 20px;
	      background-color: red;
	      color: #ffffff;
	      border: none;
	      border-radius: 5px;
	      font-size: 18px;
	      text-decoration: none;
	      cursor: pointer;
	      transition: background-color 0.3s ease;
	    }
	    .homepage button:hover {
	      background-color: darkred;
	    }
	    .homepage button a {
	      color: inherit;
	      text-decoration: none;
	    }
	</style>
</head>
<body>

<!-- HTML form for user input -->
<form method="get" action="">
  <input type="text" name="city" placeholder="Enter city name">
  <input type="submit" name="submit" value="Search">
</form>

<?php
// PHP code for retrieving weather data, storing it in the database, and displaying it on the page

// Retrieve the city name from the form or set a default value
$city = isset($_GET['submit']) ? $_GET['city'] : "mobile";

// Construct the API URL for retrieving weather data based on the city name
$url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid=daee0ae2ee355e95216a143b6c38f70c&units=metric";

// Retrieve the weather data from the API
$response = file_get_contents($url);
$data = json_decode($response, true);

// Check if the data retrieval was successful and handle errors
if (!$data || $data['cod'] != 200) {
  die("Error: Failed to retrieve data from OpenWeatherMap API. <br> 
  Please enter a valid city name.");
}

// Extract the relevant weather information from the retrieved data
$city_name = $data['name'];
$condition = $data['weather'][0]['main'];
$icon = $data['weather'][0]['icon'];
$temperature = $data['main']['temp'];
$pressure = $data['main']['pressure'];
$humidity = $data['main']['humidity'];
$wind_speed = $data['wind']['speed'];

// Database connection details
$host = 'sql311.epizy.com';
$username = 'epiz_34175099';
$password = 'Txao0EYv1n5MB';
$dbname = 'epiz_34175099_prototype_2';

// Create a connection to the database
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$dataFromDatabase = false; // Variable to track if data is fetched from the database

// Check if there is existing weather data for the current date and city
$sql = "SELECT * FROM weather WHERE `city`='$city_name' AND DATE(`date`) = CURDATE()";
$result = mysqli_query($conn, $sql);

// If there is existing data, update it in the database
if (mysqli_num_rows($result) > 0) {
    $sql = "UPDATE weather SET `condition`='$condition', `icon`='$icon', `pressure`='$pressure', `temperature`='$temperature', `humidity`='$humidity', `wind_speed`='$wind_speed' WHERE `city`='$city_name' AND `date`=NOW()";
    $dataFromDatabase = true; // Data is fetched from the database
} else {
  // If there is no existing data, insert a new row into the database
  $sql = "INSERT INTO weather (city, `condition`, icon, temperature, pressure, humidity, wind_speed,  date) 
  VALUES ('$city_name', '$condition', '$icon', '$temperature', '$pressure', '$humidity', '$wind_speed', NOW())";
}

// Execute the SQL query
mysqli_query($conn, $sql);

// Retrieve the weather data for the past 7 days from the database
$sql = "SELECT * FROM weather WHERE `city`='$city_name' ORDER BY `date` DESC LIMIT 7";
$result = mysqli_query($conn, $sql);

// Display the weather data in an HTML table
echo "<table border='1'>";
echo "<tr>";
echo "<th>Date</th>";
echo "<th>City</th>";
echo "<th>Condition</th>";
echo "<th>Icon</th>";
echo "<th>Pressure</th>";
echo "<th>Temperature</th>";
echo "<th>Humidity</th>";
echo "<th>Wind Speed</th>";
echo "</tr>";

// Iterate over the retrieved weather data and populate the table rows
while ($row = mysqli_fetch_assoc($result)) {
  $city = $row['city'];
  $date = date('Y-m-d', strtotime($row['date']));
  $condition = $row['condition'];
  $icon = $row['icon'];
  $temperature = $row['temperature'];
  $humidity = $row['humidity'];
  $wind_speed = $row['wind_speed'];

  echo "<tr>";
  echo "<td>{$date}</td>";
  echo "<td>{$city}</td>";
  echo "<td>{$condition}</td>";
  echo "<td><img src='http://openweathermap.org/img/w/{$icon}.png'></td>";
  echo "<td>{$pressure}hPa</td>";
  echo "<td>{$temperature}°C</td>";
  echo "<td>{$humidity}%</td>";
  echo "<td>{$wind_speed} m/s</td>";
  echo "</tr>";
}

// Display a message if there is incomplete weather data
if (mysqli_num_rows($result) < 7) {
  echo "<tr><td colspan='8'>Some of the weather data are only found.</td></tr>";
}
echo "</table>";

// Display a message indicating the source of the weather data
if ($dataFromDatabase) {
  echo "<script>console.log('Weather data accessed from database.');</script>";
} else {
  echo "<script>console.log('Weather data fetched from internet.');</script>";
}

// Close the database connection
mysqli_close($conn);
?>

<!-- Homepage button -->
<div class="homepage">
  <button>
    <a href="index.html">Go back to homepage</a>
  </button>
</div>

</body>
</html>