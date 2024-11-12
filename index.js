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
console.log(data);
console.log(data[0].temperatur);
  const datenContainer = document.createElement("div");
  // averagesContainer.innerHTML = `
  //   <h3>Das heutige Wetter:</h3>
  //   <p>Höchsttemperatur: ${data.othertable[0].temperatur} °C</p>
  //   <p>Regenfallmenge: ${averageRainSum} mm</p>
  //   <p>Schneemenge: ${averageSnowfallSum} cm</p>
  //   <p>Maximale Windstärke: ${averageWindSpeed} km/h</p>
  // `;
  datenContainer.innerHTML = `<h1>Test</h1>`;
  document.body.appendChild(datenContainer);
}

// Display movement chart
function displayMovement(data) {
  const canvas = document.createElement("canvas");
  document.body.appendChild(canvas);
  const ctx = canvas.getContext("2d");

  const labels = data.map(entry => entry.datum);
  const dataset = data.map(entry => entry.windgeschwindigkeit_max);

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: "Wind Speed",
          data: dataset,
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
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
            text: 'Time'
          }
        },
        y: {
          display: true,
          title: {
            display: true,
            text: 'Value'
          }
        }
      }
    }
  });
}

function generateLabels() {
  const labels = [];
  for (let i = 0; i < 24; i++) {
    labels.push(i);
  }
  return labels;
}

function generateData() {
  const data = [];
  for (let i = 0; i < 24; i++) {
    data.push(Math.random() * 100);
  }
  return data;
}