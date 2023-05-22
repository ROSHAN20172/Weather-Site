// Define the API key and URL for fetching weather data
const apikey = "daee0ae2ee355e95216a143b6c38f70c";
const apiurl = "https://api.openweathermap.org/data/2.5/weather?units=metric&q=";

// Select DOM elements
const searchBox = document.querySelector(".search input");
const searchBtn = document.querySelector(".search button");
const weatherIcon = document.querySelector(".weather-icon");

// Function to check weather for a city
async function checkWeather(city) {
  // Hide error message and display weather and details sections
  if (document.querySelector(".error").style.display === "block") {
    document.querySelector(".error").style.display = "none";
    document.querySelector(".weather").style.display = "block";
    document.querySelector(".details").style.display = "flex";
  }

  // Check if cached weather data exists for the city
  const cachedWeatherData = localStorage.getItem(city);
  if (cachedWeatherData) {
    // Retrieve cached data and timestamp
    const { data, timestamp } = JSON.parse(cachedWeatherData);
    updateWeatherData(data, "Data Accessed from Local Storage");

    // Check if device is online and cached data is old
    if (navigator.onLine && isDataOld(timestamp)) {
      fetchAndUpdateWeatherData(city);
    }
  } else if (navigator.onLine) {
    // Fetch weather data from the API and update the cache
    fetchAndUpdateWeatherData(city);
  } else {
    showError("No internet connection. Failed to fetch weather data.");
  }
}

// Function to check if cached data is old (older than 10 minutes)
function isDataOld(timestamp) {
  const TEN_MINUTES = 10 * 60 * 1000; // 10 minutes in milliseconds
  const currentTime = new Date().getTime();
  return currentTime - timestamp >= TEN_MINUTES;
}

// Function to fetch weather data from the API and update the cache
async function fetchAndUpdateWeatherData(city) {
  try {
    const response = await fetch(apiurl + city + `&appid=${apikey}`);
    if (response.status === 404) {
      showError("City not found.");
      console.log("Invalid city name");
    } else {
      const data = await response.json();
      const timestamp = new Date().getTime();
      localStorage.setItem(city, JSON.stringify({ data, timestamp }));
      updateWeatherData(data, "Data Accessed from Internet");
    }
  } catch (error) {
    showError("Failed to fetch weather data.");
  }
}

// Function to update the weather data on the webpage
function updateWeatherData(data, source) {
  // Display the current date
  const options = {
    weekday: "long",
    month: "short",
    day: "numeric",
    year: "numeric",
  };
  document.querySelector(".date").innerHTML = new Date().toLocaleDateString(
    "en-US",
    options
  );

  // Update the weather details on the webpage
  document.querySelector(".temp").innerHTML = data.main.temp + "&deg;C";
  document.querySelector(".city").innerHTML =
    data.name + ", " + data.sys.country;
  document.querySelector(".pressure").innerHTML =
    data.main.pressure + " hPa" + "<br>Pressure";
  document.querySelector(".humidity").innerHTML =
    data.main.humidity + "%" + "<br>Humidity";
  document.querySelector(".wind").innerHTML =
    data.wind.speed + " Km/h" + "<br>Wind Speed";
  document.querySelector(".icon-name").innerHTML = data.weather[0].main;
  document.querySelector(".rainfall").innerHTML = data.rain
    ? data.rain["1h"] + " mm/h" + "<br>Rainfall"
    : "N/A" + "<br>Rainfall";

  document.querySelector(".condition").innerHTML =
    data.weather[0].description + "<br>Condition";
  document.querySelector(
    ".conditionimg"
  ).innerHTML = `<img src="http://openweathermap.org/img/w/${data.weather[0].icon}.png" alt="${data.weather[0].description}">`;

  // Set the weather icon based on the weather condition
  if (data.weather[0].main === "Clouds") {
    weatherIcon.src = "Roshan Kumar Sah_2329235_Clouds.png";
  } else if (data.weather[0].main === "Clear") {
    weatherIcon.src = "Roshan Kumar Sah_2329235_Clear.png";
  } else if (data.weather[0].main === "Rain") {
    weatherIcon.src = "Roshan Kumar Sah_2329235_Rain.png";
  } else if (data.weather[0].main === "Drizzle") {
    weatherIcon.src = "Roshan Kumar Sah_2329235_Drizzle.png";
  } else if (data.weather[0].main === "Mist") {
    weatherIcon.src = "Roshan Kumar Sah_2329235_Mist.png";
  }

  // Hide the error message
  document.querySelector(".error").style.display = "none";
  console.log(source);
}

// Function to show error message
function showError(message) {
  document.querySelector(".error").style.display = "block";
  document.querySelector(".weather").style.display = "none";
  document.querySelector(".details").style.display = "none";
  document.querySelector(".error").innerHTML = message;
}

// Initial weather check for a specific city
checkWeather("mobile");

// Event listener for search button
searchBtn.addEventListener("click", () => {
  const city = searchBox.value;
  if (city.trim() !== "") {
    checkWeather(city);
  }
});

// Event listener for Enter key press
searchBox.addEventListener("keyup", (event) => {
  if (event.key === "Enter") {
    const city = searchBox.value;
    if (city.trim() !== "") {
      checkWeather(city);
    }
  }
});