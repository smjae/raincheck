// URL is the URL of the server
// THIS FILE IS ONLY FOR DEVELOPMENT AND TESTING PURPOSES
const heading = document.createElement("h1");
heading.innerHTML = "Raincheck";
const subheading = document.createElement("h2");
subheading.innerHTML = "Wie wird das Wetter heute in Chur?";

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
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
