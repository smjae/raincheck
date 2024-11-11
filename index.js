// URL is the URL of the server
// THIS FILE IS ONLY FOR DEVELOPMENT AND TESTING PURPOSES
const heading = document.createElement("h1");
heading.innerHTML = "Raincheck";
const subheading = document.createElement("h2");
subheading.innerHTML = "Wie wird das Wetter heute in Chur?";

// const button = document.createElement("button");
// button.textContent = "Fetch Data";
document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchData();
});

// let response = document.createElement("p");
// response.addClassName = "response";

// document.body.appendChild(heading);
// document.body.appendChild(subheading);
// document.body.appendChild(button);
// document.body.appendChild(response);

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
    }
);

async function fetchData() {
  try {
    const response = await fetch("https://raincheck.ch/unload.php");
    const data = await response.json();
    console.log(data);
    processData(data);
  } catch (error) {
    console.error(error);
  }
}

// Process the retrieved data
async function processData(data) {
  // response.innerHTML = `Current Temperature: ${data.data.currentTemperature[0]}°C, 
  //   Precipitation Probability: ${data.data.daily_precipitation_probability_max[0]}%, 
  //   Measured at: ${data.data.currentTime[0]}`;
  //   await fetch("https://raincheck.ch/post.php", {
  //     method: "POST",
  //     headers: {
  //       "Content-Type": "application/json",
  //     },
  //     body: JSON.stringify(data),
  //   });
  //   console.log("Data sent to server", data);
  console.log(data);
}
