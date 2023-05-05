const apikey = "daee0ae2ee355e95216a143b6c38f70c";
const apiurl = "https://api.openweathermap.org/data/2.5/weather?units=metric&q=";

const searchBox = document.querySelector(".search input");
const searchBtn = document.querySelector(".search button");

const weatherIcon = document.querySelector(".weather-icon");

let city = "mobile";

async function checkWeather(city){
    if (document.querySelector(".error").style.display == "block") {
        document.querySelector(".error").style.display = "none";
        document.querySelector(".weather").style.display = "block";
        document.querySelector(".details").style.display = "flex";
    }

    const response = await fetch(apiurl + city + `&appid=${apikey}`);

    if(response.status == 404){
        document.querySelector(".error").style.display = "block";
        document.querySelector(".weather").style.display = "none";
        document.querySelector(".details").style.display = "none";
    }
    else{
        var data = await response.json();

        console.log(data);

        const options = { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' };
        document.querySelector(".date").innerHTML = new Date().toLocaleDateString('en-US', options);

        document.querySelector(".temp").innerHTML = data.main.temp + "&deg;C";
        document.querySelector(".city").innerHTML = data.name + ", " + data.sys.country;
        document.querySelector(".pressure").innerHTML = data.main.pressure + " hPa" + "<br>Pressure";
        document.querySelector(".humidity").innerHTML = data.main.humidity + "%" +"<br>Humidity";
        document.querySelector(".wind").innerHTML = data.wind.speed + " Km/h" +"<br>Wind Speed";
        document.querySelector(".icon-name").innerHTML = data.weather[0].main;
        document.querySelector(".rainfall").innerHTML = data.rain ? data.rain["1h"] + " mm/h"+"<br>Rainfall" : "N/A"+"<br>Rainfall";

        document.querySelector(".condition").innerHTML = data.weather[0].description + "<br>Condition";
        document.querySelector(".conditionimg").innerHTML = `<img src="http://openweathermap.org/img/w/${data.weather[0].icon}.png" alt="${data.weather[0].description}">`;

        if(data.weather[0].main == "Clouds"){
            weatherIcon.src = "Roshan Kumar Sah_2329235_Clouds.png";
        }
        else if(data.weather[0].main == "Clear"){
            weatherIcon.src = "Roshan Kumar Sah_2329235_Clear.png";
        }
        else if(data.weather[0].main == "Rain"){
            weatherIcon.src = "Roshan Kumar Sah_2329235_Rain.png";
        }
        else if(data.weather[0].main == "Drizzle"){
            weatherIcon.src = "Roshan Kumar Sah_2329235_Drizzle.png";
        }
        else if(data.weather[0].main == "Mist"){
            weatherIcon.src = "Roshan Kumar Sah_2329235_Mist.png";
        }
        document.querySelector(".error").style.display = "none";
    }  
}

checkWeather(city);

searchBtn.addEventListener("click", ()=>{
    checkWeather(searchBox.value);
})