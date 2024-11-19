const heading = document.createElement("h1");
heading.innerHTML = "Raincheck";
const subheading = document.createElement("h2");
subheading.innerHTML = "Wie wird das Wetter heute in Chur?";
const datenContainer = document.querySelector(".infoBox");
const infos = document.createElement("div");

//get the LED elements
let kontrollLED = document.getElementById("led-red-off");
let regenLED = document.getElementById("led-blue-off");
let schneeLED = document.getElementById("led-orange-off");
let tempLED = document.getElementById("led-yellow-off");
let windLED = document.getElementById("led-purple-off");
let regenschutzLED = document.getElementById("led-green-off");

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
  console.log("Fetched data:", data); // Debugging: Log the fetched data
  if (data && data.wettervorhersage) {
    displayData(data.wettervorhersage);
    displayLEDs(data.wettervorhersage);
    // processData(data.anfragen); // Assuming you want to process 'anfragen' data
  } else {
    // if data is not available or not in the expected format
    regenschutzLED.id = "led-green-blink";
    windLED.id = "led-purple-blink";
    tempLED.id = "led-yellow-blink";
    schneeLED.id = "led-orange-blink";
    regenLED.id = "led-blue-blink";
    kontrollLED.id = "led-red-blink";

    infos.innerHTML = `<p><strong>Keine aktuellen Daten verfügbar!</strong></p>`;
    datenContainer.append(infos);
    document.querySelector(".abfragen").style.display = "none";
    document.querySelector(".selector").style.display = "none";
  }
});

async function fetchData() {
  try {
    const response = await fetch("https://raincheck.ch/php/unload.php");
    const result = await response.json();
    console.log("Fetched data:", result); // Debugging: Log the fetched result
    return result.data; // Return the entire data object
  } catch (error) {
    console.error("Error fetching data:", error);
  }
}

// Calculate and display latest weather data
function displayData(data) {
  console.log("Display data:", data); // Debugging: Log the data being displayed
  infos.innerHTML = `<h3>Das heutige Wetter:</h3>
    <p>Höchsttemperatur: ${data.temperatur} °C</p>
    <p>Regenfallmenge: ${data.tagesniederschlag_sum} mm</p>
    <p>Schneemenge: ${data.schneefall_sum} cm</p>
    <p>Maximale Windstärke: ${data.windgeschwindigkeit_max} km/h</p>`;
  datenContainer.append(infos);
}

function displayLEDs(data) {
  //get data and put into variables to control LEDs on the website
  let temp = data.temperatur;
  let rain = data.tagesniederschlag_sum;
  let snow = data.schneefall_sum;
  let wind = data.windgeschwindigkeit_max;

  //check if let i = 1, if so, turn on kontrollLED
  let i = 1;

  if (i == 1) {
    kontrollLED.id = "led-red-on";
  }

  //check regenfallmenge and turn on regenLED
  if (rain > 1) {
    regenLED.id = "led-blue-on";
  }

  // check ob regenfallmenge > 15mm, dann blinken
  if (rain > 15) {
    regenLED.id = "led-blue-blink";
  }

  //check schneefallmenge and turn on schneeLED
  if (snow > 0) {
    schneeLED.id = "led-orange-on";
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
    regenschutzLED.id = "led-green-blink";
  }
}

// Process the retrieved data
function processData(data) {
  let letzteWoche = [];
  let heute = new Date();
  letzteWoche.push(heute.toISOString().split("T")[0]);
  for (let i = 1; i < 7; i++) {
    let tag = new Date();
    tag.setDate(heute.getDate() - i);
    letzteWoche.push(tag.toISOString().split("T")[0]); // Push the full date in YYYY-MM-DD format
  }
  console.log(letzteWoche);

  // Create a mapping of dates to counts
  const countsByDate = {};
  data.anfragen.forEach((item) => {
    countsByDate[item.date] = item.count;
  });

  // Create the dataset array using the countsByDate mapping
  const dataset = letzteWoche.map((date) => countsByDate[date] || 0);

  console.log(dataset);

  const canvas = document.getElementById("myChart");
  const ctx = canvas.getContext("2d");

  new Chart(ctx, {
    type: "line",
    data: {
      labels: letzteWoche,
      datasets: [
        {
          label: "Anzahl Meldungen des Bewegungssensors pro Tag",
          data: dataset,
          borderColor: "rgba(255, 99, 132, 1)",
          backgroundColor: "rgba(255, 99, 132, 0.2)",
          fill: false,
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
  if (
    document
      .querySelector("#prognose")
      .classList.contains("round-button-active")
  ) {
    document.querySelector("#prognose").classList.remove("round-button-active");
    document.querySelector("#prognose").classList.add("round-button");
    document.querySelector("#abfragen").classList.remove("round-button");
    document.querySelector("#abfragen").classList.add("round-button-active");
    document.querySelector(".prognose").style = "display: none";
    document.querySelector(".abfragen").style = "display: block";
    const data = await fetchData(); // Ensure data is fetched
    processData(data); // Initialize chart when visible
  }
});

document.querySelector("#prognose").addEventListener("click", async () => {
  if (
    document
      .querySelector("#abfragen")
      .classList.contains("round-button-active")
  ) {
    document.querySelector("#abfragen").classList.remove("round-button-active");
    document.querySelector("#abfragen").classList.add("round-button");
    document.querySelector("#prognose").classList.remove("round-button");
    document.querySelector("#prognose").classList.add("round-button-active");
    document.querySelector(".abfragen").style = "display: none";
    document.querySelector(".prognose").style = "display: block";
  }
});
