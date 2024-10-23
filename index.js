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

document.body.appendChild(button);

async function fetchData() {
    try {
        const response = await fetch ('https://raincheck.ch/endpoint.php');
        const data = await response.json();
        //console.log(data);
        return data;
    } catch (error) {
        console.error(error);
    }
}