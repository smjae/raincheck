// URL is the URL of the server
// THIS FILE IS ONLY FOR DEVELOPMENT AND TESTING PURPOSES
const heading = document.createElement("h1");
heading.innerHTML = "Raincheck";
const subheading = document.createElement("h2");
subheading.innerHTML = "Wie wird das Wetter heute in Chur?";

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
  if (Array.isArray(data)) {
    displayData(data);
    displayMovement(data);
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
console.log(data);
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
console.log("yay");
  // const canvas = document.createElement("canvas");
  // document.body.appendChild(canvas);
  // const ctx = canvas.getContext("2d");

  // const labels = data.map((entry) => entry.datum);
  // const dataset = data.map((entry) => entry.timestamp);

  // new Chart(ctx, {
  //   type: "line",
  //   data: {
  //     labels: labels,
  //     datasets: [
  //       {
  //         label: "Bewegungssensordaten",
  //         data: dataset,
  //         borderColor: "rgba(255, 99, 132, 1)",
  //         backgroundColor: "rgba(255, 99, 132, 0.2)",
  //         fill: false,
  //       },
  //     ],
  //   },
  //   options: {
  //     responsive: true,
  //     scales: {
  //       x: {
  //         display: true,
  //         title: {
  //           display: true,
  //           text: "Time",
  //         },
  //       },
  //       y: {
  //         display: true,
  //         title: {
  //           display: true,
  //           text: "Value",
  //         },
  //       },
  //     },
  //   },
  // });
}
