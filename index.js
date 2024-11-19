
const heading = document.createElement("h1");
heading.innerHTML = "Raincheck";
const subheading = document.createElement("h2");
subheading.innerHTML = "Wie wird das Wetter heute in Chur?";

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
  if (Array.isArray(data)) {
    displayData(data);
    displayLEDs(data);
  } else {
    console.error("Fetched data is not an array:", data);
  }
});

const tables = document.querySelectorAll("table");

tables.forEach((table, index) => {
  const rows = Array.from(table.querySelectorAll("tr"));
  const data = rows.map((row) => {
    const cells = Array.from(row.querySelectorAll("td"));
    return cells.map((cell) => cell.textContent);
  });

  const div = document.createElement("div");
  div.classList.add(index);

  document.body.appendChild(div);
});

async function fetchData() {
  try {
    const response = await fetch("https://raincheck.ch/php/unload.php");
    const result = await response.json();
    console.log("Fetched data:", result);
    return result.data.wettervorhersage; // Assuming you want to use the 'wettervorhersage' data
  } catch (error) {
    console.error(error);
  }
}

// Calculate and display latest weather data
function displayData(data) {
  const datenContainer = document.querySelector(".infoBox");
  const infos = document.createElement("div");
  infos.innerHTML = `<h3>Das heutige Wetter:</h3>
    <p>Höchsttemperatur: ${data[0].temperatur} °C</p>
    <p>Regenfallmenge: ${data[0].tagesniederschlag_sum} mm</p>
    <p>Schneemenge: ${data[0].schneefall_sum} cm</p>
    <p>Maximale Windstärke: ${data[0].windgeschwindigkeit_max} km/h</p>`;
  datenContainer.append(infos);
}

function displayLEDs(data) {
//get data and put into variables to control LEDs on the website
let temp = data[0].temperatur;
let rain = data[0].tagesniederschlag_sum;
let snow = data[0].schneefall_sum;
let wind = data[0].windgeschwindigkeit_max;

//get the LED elements
let kontrollLED = document.getElementById("led-red-off");
let regenLED = document.getElementById("led-blue-off");
let schneeLED = document.getElementById("led-white-off");
let tempLED = document.getElementById("led-yellow-off");
let windLED = document.getElementById("led-purple-off");
let regenschutzLED = document.getElementById("led-green-off");

//check if let i = 1, if so, turn on kontrollLED
let i = 1;

if (i == 1) {
  kontrollLED.id = "led-red-on";
}

//check regenfallmenge and turn on regenLED
if (rain > 1) {
  regenLED.id = "led-blue-on";
}

//check schneefallmenge and turn on schneeLED
if (snow > 0) {
  schneeLED.id = "led-white-on";
}

//check temperature and turn on tempLED
if (temp < 12) {
  tempLED.id = "led-yellow-on";
}

//check windgeschwindigkeit and turn on windLED
if (wind > 20) {
  windLED.id = "led-purple-on";
}

//check if regenschutz is needed and turn on regenschutzLED
if (rain > 1 && wind > 30) {
  regenschutzLED.id = "led-green-on";
}
}

// Display movement chart
function displayMovement(data) {
  const canvas = document.querySelector("#myChart");
  const ctx = canvas.getContext("2d");
  if (!ctx) {
    console.error("Canvas rendering context not found!");
  }

  // Filter data for the last week and count entries with movement: 1 for each day
  const oneWeekAgo = new Date();
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);

  const filteredData = data.filter(entry => new Date(entry.detection_time) >= oneWeekAgo);
  console.log(filteredData);

  const countsByDay = {};
  filteredData.forEach(entry => {
    const date = new Date(entry.detection_time).toISOString().split('T')[0];
    countsByDay[date] = (countsByDay[date] || 0) + 1;
  });

  const labels = Object.keys(countsByDay);
  const dataset = Object.values(countsByDay);

  console.log("Labels:", labels);
  console.log("Dataset:", dataset);

  new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Movement Sensor Data",
          data: dataset,
          borderColor: "rgba(75, 192, 192, 1)",
          backgroundColor: "rgba(75, 192, 192, 0.2)",
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        x: {
          display: true,
          title: {
            display: true,
            text: "Date",
          },
        },
        y: {
          display: true,
          title: {
            display: true,
            text: "Count",
          },
        },
      },
    },
  });
}

document.querySelector("#abfragen").addEventListener("click", async () => {
  if (document.querySelector("#prognose").classList.contains("round-button-active")) {
    document.querySelector("#prognose").classList.remove("round-button-active");
    document.querySelector("#prognose").classList.add("round-button");
    document.querySelector("#abfragen").classList.remove("round-button");
    document.querySelector("#abfragen").classList.add("round-button-active");
    document.querySelector(".prognose").style = "display: none";
    document.querySelector(".abfragen").style = "display: block";
    const data = await fetchData(); // Ensure data is fetched
    displayMovement(data); // Initialize chart when visible
  }
});

document.querySelector("#prognose").addEventListener("click", async () => {
  if (document.querySelector("#abfragen").classList.contains("round-button-active")) {
    document.querySelector("#abfragen").classList.remove("round-button-active");
    document.querySelector("#abfragen").classList.add("round-button");
    document.querySelector("#prognose").classList.remove("round-button");
    document.querySelector("#prognose").classList.add("round-button-active");
    document.querySelector(".abfragen").style = "display: none";
    document.querySelector(".prognose").style = "display: block";
  }
});