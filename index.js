console.log("Hello World!");
// URL is the URL of the server
// THIS FILE IS ONLY FOR DEVELOPMENT AND TESTING PURPOSES
const heading = document.createElement('h1');
heading.innerHTML = 'Hello World!';
const button = document.createElement('button');
button.textContent = 'Fetch Data';
button.addEventListener('click', async () => {
    const data = await fetchData();
    console.log(data);
});
let response = document.createElement('p');
response.addClassName = 'response';

document.body.appendChild(button);
document.body.appendChild(response);

async function fetchData() {
    try {
        const response = await fetch('https://raincheck.ch/endpoint.php');
        const data = await response.json();
        console.log(data);
        response.innerHTML = JSON.stringify(data);
        processData(data);
    } catch (error) {
        console.error(error);
    }
}

  
  // Process the retrieved data
  async function processData(data) {
    response.innerHTML = `Current Temperature: ${data.temperature}°C, Precipitation Probability: ${data.tagesniederschlag_max}%`;
  }