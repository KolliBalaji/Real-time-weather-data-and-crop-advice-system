<?php
session_start();
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FarmWeather Advisor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1470&q=80');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
    .overlay {
      background-color: rgba(244, 252, 227, 0.75);
      min-height: 100vh;
    }
  </style>
</head>
<body>
  <div class="overlay">
    <header class="bg-green-700 bg-opacity-90 text-white py-4 shadow-md">
      <div class="container mx-auto px-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">FarmWeather Advisor</h1>
        <nav class="flex flex-wrap gap-2">
          <a href="dashboard.php" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">Home</a>
          <a href="advice.html" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">Crop Advice</a>
          <a href="forecast.html" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">Forecast</a>
          <a href="history.html" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">History</a>
          <a href="fertilizer.html" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">Fertilizer</a>
          <a href="help.html" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">Help</a>
  
          <?php if (isset($_SESSION['user_name'])): ?>
            <span class="px-4 py-2 text-white rounded-md">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="logout.php" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">Logout</a>
          <?php else: ?>
            <a href="login.html" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">Login</a>
            <a href="signup.html" class="px-4 py-2 bg-yellow-500 rounded-md hover:bg-yellow-600 transition">Signup</a>
          <?php endif; ?>
        </nav>
      </div>
    </header>

    <main class="container mx-auto px-4 py-8">
      <h2 class="text-3xl font-semibold text-green-900 mb-6">Welcome, Farmer!</h2>

      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-6">
        <input id="location" type="text" placeholder="Enter your location"
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-black shadow-sm" />

        <div class="flex gap-2 w-full sm:w-auto">
          <button onclick="fetchWeatherData()" class="flex items-center gap-2 px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md transition">
            ☁️ <span>Get Weather</span>
          </button>
          <button onclick="fetchWeatherByGPS()" class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition">
            📍 <span>Use My Location</span>
          </button>
        </div>
      </div>

      <div id="weatherResult" class="space-y-6">
        <p id="placeholder" class="text-gray-700">Enter your location or use GPS to get weather info and crop advice.</p>
      </div>
    </main>
  </div>

  <script>
    const apiKey = "dabf333843332f83f4f982afee3626ac";

    async function fetchWeatherByGPS() {
      if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
      }

      navigator.geolocation.getCurrentPosition(async (position) => {
        const { latitude, longitude } = position.coords;
        try {
          const geoUrl = `https://api.openweathermap.org/geo/1.0/reverse?lat=${latitude}&lon=${longitude}&limit=1&appid=${apiKey}`;
          const geoResponse = await fetch(geoUrl);
          const geoData = await geoResponse.json();
          if (!geoData.length) throw new Error("City not found");
          const city = geoData[0].name;
          document.getElementById("location").value = city;
          fetchWeatherData(latitude, longitude, city);
        } catch (error) {
          alert("Could not determine your city. Try entering it manually.");
        }
      }, (error) => {
        alert("Location access denied.");
      });
    }

    async function fetchWeatherData(lat = null, lon = null, cityFromGPS = null) {
      const input = document.getElementById("location");
      const placeholder = document.getElementById("placeholder");
      let city = cityFromGPS;

      if (!lat || !lon) {
        city = input.value.trim();
        if (!city) return alert("Please enter a location.");

        const geoUrl = `https://api.openweathermap.org/geo/1.0/direct?q=${city}&limit=1&appid=${apiKey}`;
        const geoRes = await fetch(geoUrl);
        const geoData = await geoRes.json();
        if (!geoData.length) return alert("City not found.");

        lat = geoData[0].lat;
        lon = geoData[0].lon;
        city = geoData[0].name;
      }

      const weatherUrl = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;
      const res = await fetch(weatherUrl);
      if (!res.ok) return alert("Could not fetch weather data.");
      const data = await res.json();

      const weather = {
        city: city,
        temperature: data.main.temp,
        humidity: data.main.humidity,
        windSpeed: (data.wind.speed * 3.6).toFixed(1),
        conditions: data.weather[0].description
      };

      showWeatherData(weather);
      placeholder.style.display = "none";
    }

    function showWeatherData(weather) {
      const advice = getCropAdvice(weather);
      const alertMsg = getWeatherAlert(weather);

      document.getElementById("weatherResult").innerHTML = `
        <h2 class="text-2xl font-semibold text-green-800 text-center">${weather.city} Weather</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="font-bold mb-2">Temperature</h3>
            <p class="text-2xl">${weather.temperature}°C</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="font-bold mb-2">Humidity</h3>
            <p class="text-2xl">${weather.humidity}%</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="font-bold mb-2">Wind Speed</h3>
            <p class="text-2xl">${weather.windSpeed} km/h</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="font-bold mb-2">Conditions</h3>
            <p class="text-2xl capitalize">${weather.conditions}</p>
          </div>
        </div>
        <div class="bg-green-100 p-4 rounded-lg shadow mb-4">
          <h3 class="font-bold text-lg mb-2">Crop Advice</h3>
          <p>${advice}</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg shadow">
          <h3 class="font-bold text-lg mb-2">Weather Alert</h3>
          <p>${alertMsg}</p>
        </div>
      `;
    }

    function getCropAdvice(w) {
      if (w.temperature > 35) return "Very high temperature. Use shade nets and water early.";
      if (w.temperature > 30) return "High temperature. Increase irrigation.";
      if (w.humidity > 80) return "High humidity. Monitor for fungal issues.";
      if (w.windSpeed > 20) return "Strong winds. Secure lightweight crops.";
      if (w.conditions.includes("rain")) return "Rain forecasted. Avoid fertilizers.";
      return "Weather is good. Proceed with normal farming.";
    }

    function getWeatherAlert(w) {
      if (w.temperature > 35) return "⚠️ HEAT ALERT: Extreme heat may damage crops.";
      if (w.humidity > 85) return "⚠️ HUMIDITY ALERT: Disease risk due to excess moisture.";
      if (w.windSpeed > 25) return "⚠️ WIND ALERT: Strong winds expected.";
      if (w.conditions.includes("heavy rain")) return "⚠️ RAIN ALERT: Risk of waterlogging.";
      return "✅ No severe alerts. Ideal for farming.";
    }
  </script>
</body>
</html>
