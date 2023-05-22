<!-- This is an HTML page that displays previous week weather data for a given city. -->
<!-- The HTML structure begins -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Previous Week Weather</title>
  <!-- Add CSS styles -->
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
      margin-top: 20px;
    }
    input[type="text"] {
      padding: 12px;
      font-size: 18px;
      border: none;
      border-radius: 5px;
      margin-right: 10px;
      outline: none;
    }
    input[type="submit"] {
      padding: 12px 20px;
      font-size: 18px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    table {
      margin: 30px auto;
      border-collapse: collapse;
      width: 90%;
      background-color: white;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    }
    th,
    td {
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
      height: 40px;
      width: 40px;
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
  <!-- The search form to input the city -->
  <form method="get" action="">
    <input type="text" name="city" placeholder="Enter city name">
    <input type="submit" name="submit" value="Search">
  </form>
  <?php
  // PHP code begins

  // Error reporting settings
  error_reporting(E_ERROR | E_PARSE);

  // Check if the submit button is clicked
  if (isset($_GET['submit'])) {
    $city = $_GET['city'];
  } else {
    $city = "mobile";
  }

  // Database connection details
  $hostname = 'localhost';
  $username = 'root';
  $password = '';
  $dbname = 'prototype_2';

  // Establish a connection to the database
  $conn = mysqli_connect($hostname, $username, $password, $dbname);

  // Check if the connection is successful
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Check if the user is online
  $online = false;
  if (fsockopen('www.google.com', 80)) {
    $online = true;
  }

  // Fetch weather data from the database for the given city
  $sql = "SELECT * FROM weather WHERE `city`='$city' ORDER BY `date` DESC LIMIT 7";
  $result = mysqli_query($conn, $sql);

  // Check if weather data is found in the database
  if (mysqli_num_rows($result) > 0) {
    echo "<script>console.log('Data accessed from database');</script>";

    // Display the weather data in a table
    echo "<h1>Weather In {$city} (Last 7 Days Record)</h1>";
    echo "<table border='1'>";
    echo "<tr>";
    echo "<td>Date</td>";
    echo "<td>Icon</td>";
    echo "<td>Condition</td>";
    echo "<td>Temperature</td>";
    echo "<td>Pressure</td>";
    echo "<td>Humidity</td>";
    echo "<td>Wind Speed</td>";
    echo "</tr>";

    // Loop through each row of weather data and display it in the table
    while ($row = mysqli_fetch_assoc($result)) {
      $date = date('Y-m-d', strtotime($row['date']));
      $icon = $row['icon'];
      $condition = $row['condition'];
      $temperature = $row['temperature'];
      $pressure = $row['pressure'];
      $humidity = $row['humidity'];
      $wind_speed = $row['wind_speed'];

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
  } else {
    echo "<script>console.log('Data not found in database');</script>";

    // If weather data is not found in the database, check if the user is online
    if ($online) {
      echo "<script>console.log('Fetching data from API');</script>";

      // Fetch weather data from the API
      $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid=daee0ae2ee355e95216a143b6c38f70c&units=metric";
      $response = @file_get_contents($url); // Adding error suppression operator (@) to suppress warnings

      // Check if the API request is successful
      if ($response) {
        $data = json_decode($response, true);

        // Check if data is received from the API
        if (!$data) {
          echo "<script>console.log('Error: Failed to retrieve data from OpenWeatherMap API.');</script>";
          die("Error: Failed to retrieve data from OpenWeatherMap API.");
        }

        // Extract relevant weather information from the API response
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

        // Check the number of existing records for the city in the database
        $sql = "SELECT COUNT(*) AS total FROM weather WHERE `city`='$city'";
        $count_result = mysqli_query($conn, $sql);
        $count_row = mysqli_fetch_assoc($count_result);
        $total_records = $count_row['total'];

        // If there are already 7 records, delete the oldest record
        if ($total_records >= 7) {
          $sql = "DELETE FROM weather WHERE `city`='$city' ORDER BY `date` ASC LIMIT 1";
          mysqli_query($conn, $sql);
        }

        // Insert the new weather data into the database
        $sql = "INSERT INTO weather (`city`, `date`, `icon`, `condition`, `temperature`, `pressure`, `wind_speed`, `humidity`)
          VALUES ('$city_name', NOW(), '$icon', '$condition', '$temperature', '$pressure', '$wind_speed', '$humidity')";
        mysqli_query($conn, $sql);

        // Display the weather data in a table
        echo "<h1>Weather In {$city_name} (Last 7 Days Record)</h1>";
        echo "<table border='1'>";
        echo "<tr>";
        echo "<td>Date</td>";
        echo "<td>Icon</td>";
        echo "<td>Condition</td>";
        echo "<td>Temperature</td>";
        echo "<td>Pressure</td>";
        echo "<td>Humidity</td>";
        echo "<td>Wind Speed</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>".date('Y-m-d')."</td>";
        echo "<td><img src='http://openweathermap.org/img/w/{$icon}.png'></td>";
        echo "<td>{$condition}</td>";
        echo "<td>{$temperature}°C</td>";
        echo "<td>{$pressure} hPa</td>";
        echo "<td>{$humidity}%</td>";
        echo "<td>{$wind_speed} m/s</td>";
        echo "</tr>";
        echo "</table>";
      } else {
        echo "<script>console.log('Error: Failed to retrieve data from API.');</script>";
        echo "<h1>Error: Failed to retrieve data from API</h1>";
      }
    } else {
      echo "<script>console.log('User is offline');</script>";
      echo "<h1>No Data Found in Database<br>Your Status is Offline cannot Fetch Data</h1>";
    }
  }

  // Close the database connection
  mysqli_close($conn);
?>
<div class="homepage">
    <button>
        <a href="Roshan Kumar Sah_2329235.html">Homepage</a>
    </button>
</div>
</body>
</html>