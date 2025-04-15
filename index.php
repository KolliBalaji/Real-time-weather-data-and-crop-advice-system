`<?php
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
          <a href="index.php" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">Home</a>
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
    const stateCropData = {
        andhra: {
            mainCrops: ["Rice", "Cotton", "Sugarcane"],
            rainfall: "800-1200mm",
            soilType: "Alluvial and Black",
            irrigation: "Canal and Tank"
        },
        karnataka: {
            mainCrops: ["Ragi", "Jowar", "Coffee"],
            rainfall: "700-1000mm",
            soilType: "Red and Black",
            irrigation: "Tank and Well"
        },
        maharashtra: {
            mainCrops: ["Jowar", "Bajra", "Cotton"],
            rainfall: "600-900mm",
            soilType: "Black and Red",
            irrigation: "Well and Canal"
        }
    };

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
          const state = geoData[0].state || "Unknown";
          document.getElementById("location").value = city;
          fetchWeatherData(latitude, longitude, city, state);
        } catch (error) {
          alert("Could not determine your city. Try entering it manually.");
        }
      }, (error) => {
        alert("Location access denied.");
      });
    }

    async function fetchWeatherData(lat = null, lon = null, cityFromGPS = null, stateFromGPS = null) {
      const input = document.getElementById("location");
      const placeholder = document.getElementById("placeholder");
      let city = cityFromGPS;
      let state = stateFromGPS;

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
        state = geoData[0].state || "Unknown";
      }

      const weatherUrl = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;
      const forecastUrl = `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;
      
      const [weatherRes, forecastRes] = await Promise.all([
        fetch(weatherUrl),
        fetch(forecastUrl)
      ]);

      if (!weatherRes.ok || !forecastRes.ok) return alert("Could not fetch weather data.");

      const [weatherData, forecastData] = await Promise.all([
        weatherRes.json(),
        forecastRes.json()
      ]);

      const weather = {
        city: city,
        state: state,
        temperature: weatherData.main.temp,
        humidity: weatherData.main.humidity,
        windSpeed: (weatherData.wind.speed * 3.6).toFixed(1),
        conditions: weatherData.weather[0].description,
        pressure: weatherData.main.pressure,
        visibility: (weatherData.visibility / 1000).toFixed(1),
        forecast: forecastData.list.slice(0, 5).map(item => ({
          time: new Date(item.dt * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          temp: item.main.temp,
          condition: item.weather[0].description
        }))
      };

      showWeatherData(weather);
      placeholder.style.display = "none";
    }

    function showWeatherData(weather) {
      const advice = getCropAdvice(weather);
      const alertMsg = getWeatherAlert(weather);
      const stateInfo = getStateInfo(weather.state);

      document.getElementById("weatherResult").innerHTML = `
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
          <h2 class="text-2xl font-semibold text-green-800 text-center mb-4">${weather.city}, ${weather.state} Weather</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-bold mb-2">Temperature</h3>
              <p class="text-2xl">${weather.temperature}°C</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-bold mb-2">Humidity</h3>
              <p class="text-2xl">${weather.humidity}%</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-bold mb-2">Wind Speed</h3>
              <p class="text-2xl">${weather.windSpeed} km/h</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-bold mb-2">Visibility</h3>
              <p class="text-2xl">${weather.visibility} km</p>
            </div>
          </div>
          
          <div class="mb-6">
            <h3 class="font-bold text-lg mb-2">5-Hour Forecast</h3>
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
              ${weather.forecast.map(item => `
                <div class="bg-green-50 p-3 rounded-lg text-center">
                  <p class="font-semibold">${item.time}</p>
                  <p class="text-xl">${item.temp}°C</p>
                  <p class="text-sm capitalize">${item.condition}</p>
                </div>
              `).join('')}
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-green-100 p-4 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-2">Crop Advice</h3>
            <p>${advice}</p>
          </div>
          <div class="bg-yellow-100 p-4 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-2">Weather Alert</h3>
            <p>${alertMsg}</p>
          </div>
        </div>

        ${stateInfo ? `
        <div class="mt-6 bg-blue-50 p-4 rounded-lg shadow">
          <h3 class="font-bold text-lg mb-2">State Agricultural Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="font-semibold">Main Crops:</p>
              <p>${stateInfo.mainCrops.join(', ')}</p>
            </div>
            <div>
              <p class="font-semibold">Average Rainfall:</p>
              <p>${stateInfo.rainfall}</p>
            </div>
            <div>
              <p class="font-semibold">Soil Type:</p>
              <p>${stateInfo.soilType}</p>
            </div>
            <div>
              <p class="font-semibold">Irrigation Methods:</p>
              <p>${stateInfo.irrigation}</p>
            </div>
          </div>
        </div>
        ` : ''}
      `;
    }

    function getStateInfo(state) {
      const stateKey = state.toLowerCase().replace(/\s+/g, '');
      return stateCropData[stateKey];
    }

    function getCropAdvice(w) {
      let advice = [];
      
      if (w.temperature > 35) {
        advice.push("Very high temperature. Use shade nets and water early.");
        advice.push("Consider heat-tolerant crop varieties.");
      }
      if (w.temperature > 30) {
        advice.push("High temperature. Increase irrigation frequency.");
        advice.push("Apply mulch to conserve soil moisture.");
      }
      if (w.humidity > 80) {
        advice.push("High humidity. Monitor for fungal issues.");
        advice.push("Ensure proper spacing for air circulation.");
      }
      if (w.windSpeed > 20) {
        advice.push("Strong winds. Secure lightweight crops.");
        advice.push("Consider windbreaks for sensitive crops.");
      }
      if (w.conditions.includes("rain")) {
        advice.push("Rain forecasted. Avoid fertilizers.");
        advice.push("Check drainage systems.");
      }
      
      return advice.length > 0 ? advice.join('\n') : "Weather is good. Proceed with normal farming.";
    }

    function getWeatherAlert(w) {
      let alerts = [];
      
      if (w.temperature > 35) {
        alerts.push("⚠️ HEAT ALERT: Extreme heat may damage crops.");
      }
      if (w.humidity > 85) {
        alerts.push("⚠️ HUMIDITY ALERT: Disease risk due to excess moisture.");
      }
      if (w.windSpeed > 25) {
        alerts.push("⚠️ WIND ALERT: Strong winds expected.");
      }
      if (w.conditions.includes("heavy rain")) {
        alerts.push("⚠️ RAIN ALERT: Risk of waterlogging.");
      }
      if (w.visibility < 1) {
        alerts.push("⚠️ FOG ALERT: Reduced visibility may affect operations.");
      }
      
      return alerts.length > 0 ? alerts.join('\n') : "✅ No severe alerts. Ideal for farming.";
    }
  </script>
</body>
</html>
