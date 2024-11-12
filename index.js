// URL is the URL of the server
// THIS FILE IS ONLY FOR DEVELOPMENT AND TESTING PURPOSES
const heading = document.createElement("h1");
heading.innerHTML = "Raincheck";
const subheading = document.createElement("h2");
subheading.innerHTML = "Wie wird das Wetter heute in Chur?";

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
  processData(data);
  displayAverages(data);
});

const tables = document.querySelectorAll("table");

tables.forEach((table, index) => {
  const rows = Array.from(table.querySelectorAll("tr"));
  const data = rows.map(row => {
    const cells = Array.from(row.querySelectorAll("td"));
    return cells.map(cell => cell.textContent);
  });

  const div = document.createElement("div");
  div.classList.add(index);

  document.body.appendChild(div);
});

async function fetchData() {
  try {
    const response = await fetch("https://raincheck.ch/php/unload.php");
    const result = await response.json();
    return result.data; // Assuming you want to use the 'wettervorhersage' data
  } catch (error) {
    console.error(error);
  }
}

// Process the retrieved data
async function processData(data) {
  // show data in charts: https://www.chartjs.org/docs/latest/
  // chart 1: rain sum over time with bar chart, temperature with line chart
  const ctx1 = document.getElementById('chart1').getContext('2d');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: data.map(entry => entry.datum),
      datasets: [
        {
          label: 'Rain Sum',
          data: data.map(entry => entry.tagesniederschlag_sum),
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        },
        {
          label: 'Temperature',
          data: data.map(entry => entry.temperatur),
          type: 'line',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  // chart 2: show wind speed over time with bar chart
  const ctx2 = document.getElementById('chart2').getContext('2d');
  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: data.map(entry => entry.datum),
      datasets: [
        {
          label: 'Wind Speed',
          data: data.map(entry => entry.windgeschwindigkeit_max),
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

// Calculate and display averages
function displayAverages(data) {
  const averageTemperature = (data.reduce((sum, entry) => sum + entry.temperatur, 0) / data.length).toFixed(2);
  const averageRainSum = (data.reduce((sum, entry) => sum + entry.tagesniederschlag_sum, 0) / data.length).toFixed(2);
  const averageSnowfallSum = (data.reduce((sum, entry) => sum + entry.schneefall_sum, 0) / data.length).toFixed(2);
  const averageWindSpeed = (data.reduce((sum, entry) => sum + entry.windgeschwindigkeit_max, 0) / data.length).toFixed(2);

  const averagesContainer = document.createElement('div');
  averagesContainer.innerHTML = `
    <h3>Average Weather Data</h3>
    <p>Average Temperature: ${averageTemperature} °C</p>
    <p>Average Rain Sum: ${averageRainSum} mm</p>
    <p>Average Snowfall Sum: ${averageSnowfallSum} cm</p>
    <p>Average Wind Speed: ${averageWindSpeed} km/h</p>
  `;
  document.body.appendChild(averagesContainer);
}
