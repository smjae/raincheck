// Erstellen von Elementen für die Anzeige der Wetterdaten
const datenContainer = document.querySelector(".infoBox");
const infos = document.createElement("div");

// LEDs auswählen, sind per default ausgeschaltet
let kontrollLED = document.getElementById("led-red-off");
let regenLED = document.getElementById("led-blue-off");
let schneeLED = document.getElementById("led-orange-off");
let tempLED = document.getElementById("led-yellow-off");
let windLED = document.getElementById("led-purple-off");
let regenschutzLED = document.getElementById("led-green-off");

// Funktion beim Laden der Seite: Daten abfragen, displayData und displayLEDs aufrufen
document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
  // wenn Daten vorhanden sind und das erwartete Format haben
  if (data && data.wettervorhersage) {
    displayData(data.wettervorhersage);
    displayLEDs(data.wettervorhersage);
  } else {
    // falls keine Daten vorhanden sind oder das Format nicht stimmt, blinken alle LEDs
    regenschutzLED.id = "led-green-blink";
    windLED.id = "led-purple-blink";
    tempLED.id = "led-yellow-blink";
    schneeLED.id = "led-orange-blink";
    regenLED.id = "led-blue-blink";
    kontrollLED.id = "led-red-blink";
    // die Anzeige links unten wird angepasst
    infos.innerHTML = `<p><strong>Keine aktuellen Daten verfügbar!</strong></p>`;
    datenContainer.append(infos);
    // die Grafik mit den Abfragen & die Buttons dazu werden ausgeblendet, da ja keine Daten vorhanden sind
    document.querySelector(".abfragen").style.display = "none";
    document.querySelector(".selector").style.display = "none";
  }
});

async function fetchData() {
  try {
    const response = await fetch("https://raincheck.ch/php/unload.php");
    const result = await response.json();
    return result.data;
  } catch (error) {
    console.error("Error fetching data:", error);
  }
}

// Dynamisch erstellte Elemente für die Anzeige der Wetterdaten
function displayData(data) {
  infos.innerHTML = `<h3>Das heutige Wetter:</h3>
    <p>Höchsttemperatur: ${data.temperatur} °C</p>
    <p>Regenfallmenge: ${data.tagesniederschlag_sum} mm</p>
    <p>Schneemenge: ${data.schneefall_sum} cm</p>
    <p>Maximale Windstärke: ${data.windgeschwindigkeit_max} km/h</p>`;
  datenContainer.append(infos);
}

function displayLEDs(data) {
  // Daten aus Datenbank in Variablen speichern
  let temp = data.temperatur;
  let rain = data.tagesniederschlag_sum;
  let snow = data.schneefall_sum;
  let wind = data.windgeschwindigkeit_max;

  // Kontrollleuchte leuchtet immer, wenn Daten vorhanden sind
  let i = 1;

  if (i == 1) {
    kontrollLED.id = "led-red-on";
  }

  // Wenn mehr als 1mm Regen fällt, leuchtet die Regen-LED
  if (rain > 1) {
    regenLED.id = "led-blue-on";
  }

  // Wenn mehr als 15mm Regen fällt, blinkt die Regen-LED
  if (rain > 15) {
    regenLED.id = "led-blue-blink";
  }

  // Wenn mehr als 0cm Schnee fällt, leuchtet die Schnee-LED
  if (snow > 0) {
    schneeLED.id = "led-orange-on";
  }

  // Wenn die Temperatur den ganzen Tag nie über 12°C steigt, leuchtet die Temperatur-LED
  if (temp < 12) {
    tempLED.id = "led-yellow-on";
  }

  // Wenn es über 20km/h windet, leuchtet die Wind-LED
  if (wind > 20) {
    windLED.id = "led-purple-on";
  }

  // Wenn es über 30km/h windet und mehr als 1mm Regen fällt, blinkt die Regenschutz-LED
  if (rain > 1 && wind > 30) {
    regenschutzLED.id = "led-green-blink";
  }
}

// Daten umformen für Visualisierung der Abfragen des Bewegungssensors
function processData(data) {
  // die letzten 7 Tage ermitteln und in einen Array namens "letzteWoche" speichern
  let letzteWoche = [];
  let heute = new Date();
  letzteWoche.unshift("heute");
  for (let i = 1; i < 7; i++) {
    let tag = new Date();
    tag.setDate(heute.getDate() - i);
    letzteWoche.unshift(formatDate(tag)); // Unshift the formatted date
  }

  console.log("letzteWoche:", letzteWoche); // Debugging: Log the letzteWoche array

  // Anfragen des Bewegungssensors pro Tag in einem Objekt namens "countsByDate" speichern
  const countsByDate = {};
  data.forEach((item) => {
    const formattedDate = formatDate(new Date(item.date));
    countsByDate[formattedDate] = item.count;
  });

  console.log("countsByDate:", countsByDate); // Debugging: Log the countsByDate object

  // Daten für die Visualisierung vorbereiten
  const dataset = letzteWoche.map((date) => {
    if (date === "heute") {
      const todayFormatted = formatDate(heute);
      return countsByDate[todayFormatted] || 0;
    }
    return countsByDate[date] || 0;
  });

  console.log("dataset:", dataset); // Debugging: Log the dataset array

  // per DOM auf das Canvas zugreifen und mit Chart.js eine Linien-Grafik erstellen
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
          cubicInterpolationMode: 'monotone',
          tension: 0.4,
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
            text: "Datum der Abfragen",
          },
        },
        y: {
          display: true,
          title: {
            display: true,
            text: "Anzahl Meldungen",
            min: 0,
          },
        },
      },
    },
  });
}

// Helper function to format dates
function formatDate(date) {
  const options = { day: "2-digit", month: "long", year: "numeric" };
  return new Intl.DateTimeFormat("de-DE", options).format(date);
}

// Event-Listener für die Buttons, die zwischen Prognose und Abfragen wechseln
document.querySelector("#abfragen").addEventListener("click", async () => {
  if (
    document
      .querySelector("#prognose")
      .classList.contains("round-button-active")
  ) {
    // Prognose wird ausgeblendet, Button-Styles werden angepasst, processData wird gestartet
    document.querySelector("#prognose").classList.remove("round-button-active");
    document.querySelector("#prognose").classList.add("round-button");
    document.querySelector("#abfragen").classList.remove("round-button");
    document.querySelector("#abfragen").classList.add("round-button-active");
    document.querySelector(".prognose").style = "display: none";
    document.querySelector(".abfragen").style = "display: block";
    const data = await fetchData();
    processData(data.anfragen);
  }
});

// dasselbe umgekehrt
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
    // Chart wird gelöscht, damit er wieder neu erstellt werden kann
    let canvas = document.getElementById("myChart");
    let ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
});
