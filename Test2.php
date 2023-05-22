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

  // Check if the oldest data in the database is outdated
  $oldest_row = mysqli_fetch_assoc($result);
  $oldest_date = strtotime($oldest_row['date']);
  $current_date = strtotime(date('Y-m-d'));
  if ($current_date > $oldest_date) {
    // Fetch new weather data from the API for the missing dates
    $start_date = date('Y-m-d', strtotime('-6 days')); // Get the start date for fetching missing data
    $end_date = date('Y-m-d', strtotime('-1 day')); // Get the end date for fetching missing data
    $new_url = "https://api.openweathermap.org/data/2.5/forecast?q={$city}&appid=daee0ae2ee355e95216a143b6c38f70c&units=metric";
    $new_response = @file_get_contents($new_url); // Adding error suppression operator (@) to suppress warnings

    // Check if the API request is successful
    if ($new_response) {
      $new_data = json_decode($new_response, true);

      // Check if new data is received from the API
      if ($new_data) {
        // Delete existing weather data for the missing dates
        $delete_sql = "DELETE FROM weather WHERE `city`='$city' AND `date` BETWEEN '$start_date' AND '$end_date'";
        mysqli_query($conn, $delete_sql);

        // Insert new weather data for the missing dates into the database
        foreach ($new_data['list'] as $forecast) {
          $date = date('Y-m-d', strtotime($forecast['dt_txt']));
          $icon = $forecast['weather'][0]['icon'];
          $condition = $forecast['weather'][0]['description'];
          $temperature = $forecast['main']['temp'];
          $pressure = $forecast['main']['pressure'];
          $humidity = $forecast['main']['humidity'];
          $wind_speed = $forecast['wind']['speed'];

          $insert_sql = "INSERT INTO weather (`city`, `date`, `icon`, `condition`, `temperature`, `pressure`, `humidity`, `wind_speed`) VALUES ('$city', '$date', '$icon', '$condition', '$temperature', '$pressure', '$humidity', '$wind_speed')";
          mysqli_query($conn, $insert_sql);
        }
      }
    }
  }
} else {
  echo "<script>console.log('Data not found in database');</script>";

  // If weather data is not found in the database, check if the user is online
  if ($online) {
    echo "<script>console.log('Fetching data from API');</script>";

    // Fetch weather data from the API for the previous seven days
    $start_date = date('Y-m-d', strtotime('-6 days')); // Get the start date for fetching data
    $end_date = date('Y-m-d'); // Get the end date for fetching data
    $url = "https://api.openweathermap.org/data/2.5/forecast?q={$city}&appid=daee0ae2ee355e95216a143b6c38f70c&units=metric";
    $response = @file_get_contents($url); // Adding error suppression operator (@) to suppress warnings

    // Check if the API request is successful
    if ($response) {
      $data = json_decode($response, true);

      // Check if data is received from the API
      if (!$data) {
        echo "<h1>No Data Found</h1>";
      } else {
        // Insert the weather data for the previous seven days into the database
        foreach ($data['list'] as $forecast) {
          $date = date('Y-m-d', strtotime($forecast['dt_txt']));
          $icon = $forecast['weather'][0]['icon'];
          $condition = $forecast['weather'][0]['description'];
          $temperature = $forecast['main']['temp'];
          $pressure = $forecast['main']['pressure'];
          $humidity = $forecast['main']['humidity'];
          $wind_speed = $forecast['wind']['speed'];

          // Insert the weather data into the database
          $insert_sql = "INSERT INTO weather (`city`, `date`, `icon`, `condition`, `temperature`, `pressure`, `humidity`, `wind_speed`) VALUES ('$city', '$date', '$icon', '$condition', '$temperature', '$pressure', '$humidity', '$wind_speed')";
          mysqli_query($conn, $insert_sql);
        }

        // Display the weather data for the previous seven days
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

        // Fetch weather data from the database for the previous seven days
        $sql = "SELECT * FROM weather WHERE `city`='$city' AND `date` BETWEEN '$start_date' AND '$end_date' ORDER BY `date` DESC";
        $result = mysqli_query($conn, $sql);

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
      }
    } else {
      echo "<h1>API Error</h1>";
    }
  } else {
    echo "<h1>No Data Found</h1>";
  }
}

// Close the database connection
mysqli_close($conn);

// PHP code ends

?>
  <!-- Display a button to go back to the homepage -->
  <div class="homepage">
    <button><a href="/">Go back to homepage</a></button>
  </div>
</body>
</html>