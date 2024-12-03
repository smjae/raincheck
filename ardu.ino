// # Autoren       : Simea Minder, Raphael Schnell
// # Datum         : 02.12.2024

// # Produktname : RainCheck - Visueller Indikator zum aktuellen Wetter
// # Version      : 1.0

// # Beschreibung:
// # Dieses Programm ist für das ESP32 Dev Kit entwickelt und nutzt einen PIR-Motion-Sensor zur Bewegungserkennung. Bei registrierter Bewegung werden die LED-Indikatoren aktiviert und visualisieren die aktuellen Wetterbedingungen. Hierbei wird ein “RainCheck” durchgeführt, um spezifische Wetterparameter wie Regen- bzw. Schneewahrscheinlichkeit, Sturm oder Temperatur anzuzeigen.

#include <WiFi.h>
#include <HTTPClient.h>
#include <esp_task_wdt.h>
#include <ArduinoJson.h>
#include <time.h>

int sensorPin = 21;
// int indicator = 5; // Die Indikator LED zeigt an, wenn Bewegung registriert wurde. Den Bewegungsindikator haben wir aktuell deaktiviert, da in der finalen Version dazu keine LED verbaut wurde.
int Kontrollleuchte = 12;
int Regenwahrscheinlichkeit = 27;
int Schneewahrscheinlichkeit = 25;
int Temperaturanzeige = 32;
int Sturm = 26;
int Regenschirmverbot = 33;

bool blinkRegenwahrscheinlichkeit = false;
bool blinkRegenschirmverbot = false;
unsigned long startTimeLedAnzeigeAktiviert; // Variable zur Speicherung der Startzeit
bool ledAnzeigeAktiviert = false;

// WLAN Konfiguration
const char* ssid = "WLAN";
const char* pass = "PASSWORT";
const char* serverUrl = "https://raincheck.ch/php/endpoint.php"; // endpoint.php
const char* serverURLLoad = "https://raincheck.ch/php/load.php"; // load.php

WiFiServer server(80);

unsigned long lastFetchTime = 0;  // Speichert den Zeitpunkt der letzten Abfrage
// INTERVAL Zwischen Visuellen Darstelleungen einstellen // 60000 = 1min // 300000 = 5min // 5 Minuten in Millisekunden (5 * 60 * 1000)
const unsigned long fetchDelay = 60000;

// Strukturdefinition für Wetterdaten
struct WeatherData {
  float rain;          // Regenmenge in mm
  float snow;          // Schneemenge in mm
  float temperature;   // Temperatur in °C
  float windSpeed;     // Windgeschwindigkeit in km/h
  bool isCurrentDay;   // Sind die Daten vom aktuellen Tag?
  bool isError;        // Fehlerstatus
  String datum;        // Datum
};

// Globale Variable für den LED-Blinkzustand
bool blinkState = false; // Blinkzustand aus
unsigned long lastBlinkTime = 0; // Zeit seit letztem Blinken auf 0
const unsigned long BLINK_INTERVAL = 500; // Blinkintervall in Millisekunden

void setup()
{
  pinMode(sensorPin, INPUT);
  // pinMode(indicator, OUTPUT); // Den Bewegungsindikator haben wir aktuell deaktiviert, da in der finalen Version dazu keine LED verbaut wurde.
  pinMode(Kontrollleuchte, OUTPUT);
  pinMode(Regenwahrscheinlichkeit, OUTPUT);
  pinMode(Schneewahrscheinlichkeit, OUTPUT);
  pinMode(Temperaturanzeige, OUTPUT);
  pinMode(Sturm, OUTPUT);
  pinMode(Regenschirmverbot, OUTPUT);
  Serial.begin(115200);

  // Watchdog Timer aktivieren
  esp_task_wdt_config_t wdtConfig = {
    .timeout_ms = 30000,              // 30 Sekunden
    .idle_core_mask = (1 << 0),       // CPU 0
    .trigger_panic = true             // wenn true, dann wir der ESP automatisch neugestartet
  };
  esp_task_wdt_init(&wdtConfig);
  esp_task_wdt_add(NULL); // Erwartet von loop ab und zu ein Lebenszeichen, wie z.B. esp_task_edt_reset()

  // WLAN Verbindung herstellen
  WiFi.begin(ssid, pass);
  
  // Timeout für WLAN-Verbindung
  int wifiTimeout = 0;
  bool connected = false;
  
  while (!connected) {
    // Versuche eine Verbindung herzustellen
    while (WiFi.status() != WL_CONNECTED && wifiTimeout < 20) {
      delay(500);
      Serial.print(".");
      wifiTimeout++;
      esp_task_wdt_reset();
    }
    
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\nWiFi verbunden!");
      connected = true;
      // NTP Zeit synchronisieren -> funktioniert leider nicht immer sauber, weshalb es 2x durchgeführt wird.
      for (int i = 0; i < 2; i++) { // Zwei Versuche
            configTime(3600, 0, "pool.ntp.org", "time.nist.gov"); // UTC+1
            delay(2000);
      }
      time_t now;
      // Datum und Uhrzeit abrufen + speichern des Rückgabewerts in formattedTime. FormattedTime verwenden wir später zum prüfen, ob die Daten der API aktuell sind.
      String formattedTime = holeDatumUndZeitMitSekunden();
    } else {
      Serial.println("\nWLAN-Verbindung fehlgeschlagen! Versuche erneut in 5 Sekunden...");
      blinkAllLeds();
      // 5 Sekunden warten
      delay(5000);
      // Verbindung neu starten
      WiFi.begin(ssid, pass);
      // Timeout zurücksetzen
      wifiTimeout = 0;
      esp_task_wdt_reset();
    }
  }

}

void loop()
{
  // Watchdog zurücksetzen
  esp_task_wdt_reset();
  
  // Aufruf der Funktion und Speichern des Rückgabewerts
  int state = checkSensorState();
  
  // Serial.println(state);
  updateIndicators(state);
  // Wenn Bewegung erkannt wird (state = 1) und 5 Minuten seit der letzten Abfrage vergangen sind
  if (state == 1 && (millis() - lastFetchTime >= fetchDelay)) {
    Serial.println("Bewegung erkannt und seit der letzten Loopausführung ist es mehr als 5 Minute her!");
    Serial.println();
    lastFetchTime = millis();  // Aktualisiere den Zeitpunkt der letzten Abfrage
    activateLedAnzeige();
    LoadDataToDb(); // Daten in die Datenbank laden, dass jetzt eine Bewegung festgestellt wurde
    fetchWeatherData();  // Führe Datenbankabfrage durch
  }

  updateIndicators(state);
  printStateMessage(state);

  // delay im Loop
  delay(500);
}

int checkSensorState() {
    int state = digitalRead(sensorPin); // Sensorstatus lesen
    Serial.println(state); // Den Zustand ausgeben
    return state; // Den Zustand zurückgeben
}

// Setzt die LEDIndikatoren auf den Gewünschten Status (an oder aus)
void updateIndicators(int state)
{
  // Schlaufe für die Bewegungsindikator LED ein bzw. auszuschalten. Den Bewegungsindikator haben wir aktuell deaktiviert, da in der finalen Version dazu keine LED verbaut wurde.
  // if (state == 1) {
  // digitalWrite(indicator, state);
  // } else {
  // digitalWrite(indicator, state);
  // }
  if (ledAnzeigeAktiviert == false) { // Alle LEDs ausschalten
  digitalWrite(Kontrollleuchte, 0);
  digitalWrite(Regenwahrscheinlichkeit, 0);
  blinkRegenwahrscheinlichkeit = false;
  digitalWrite(Schneewahrscheinlichkeit, 0);
  digitalWrite(Temperaturanzeige, 0);
  digitalWrite(Sturm, 0);
  digitalWrite(Regenschirmverbot, 0);
  blinkRegenschirmverbot = false;
  }
}

void activateLedAnzeige() {
    ledAnzeigeAktiviert = true; // Aktivieren Sie die LED-Anzeige
    startTimeLedAnzeigeAktiviert = millis(); // Speichern Sie die aktuelle Zeit
}

void printStateMessage(int state)
{
  if(state == 1) Serial.println("Somebody is in this area!");
  else if(state == 0) Serial.println("No one!");
}

// Funktion zum Abrufen der Wetterdaten
void fetchWeatherData() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    
    http.begin(serverUrl);
    http.setTimeout(5000);  // 5 Sekunden Timeout
    
    esp_task_wdt_reset();  // Vor HTTP-Anfrage
    int httpResponseCode = http.GET();
    esp_task_wdt_reset();  // Nach HTTP-Anfrage
    
    Serial.println("\n----- API Response -----");
    Serial.printf("HTTP Response code: %d\n", httpResponseCode);
    if (httpResponseCode == 200) {
      String payload = http.getString();
      Serial.println("Response payload:");
      Serial.println(payload);
      Serial.println("-----------------------\n");
      WeatherData weather = parseWeatherData(payload);
      Serial.println("Daten erfolgreich abgerufen");
      visualRaincheck(weather);
    }
    else if (httpResponseCode == 404) {
      Serial.println("API-Endpunkt nicht gefunden");
      WeatherData weather = {0, 0, 0, 0, false, true, ""}; // Fehlerzustand true
      visualRaincheck(weather);
    }
    else if (httpResponseCode == 500) {
      Serial.println("Server-Fehler");
      WeatherData weather = {0, 0, 0, 0, false, true, ""}; // Fehlerzustand true
      visualRaincheck(weather);
    }
    else if (httpResponseCode == -1) {
      Serial.println("Timeout bei HTTP-Anfrage");
      WeatherData weather = {0, 0, 0, 0, false, true, ""}; // Fehlerzustand true
      visualRaincheck(weather);
    }
    else {
      Serial.print("Unbekannter Fehler: ");
      Serial.println(httpResponseCode);
      WeatherData weather = {0, 0, 0, 0, false, true, ""}; // Fehlerzustand true
      visualRaincheck(weather);
    }
    
    http.end();
  }
  else {
    Serial.println("WiFi nicht verbunden");
    WeatherData weather = {0, 0, 0, 0, false, true, ""}; // Fehlerzustand true
    visualRaincheck(weather);
  }
}

// Hilfsfunktion zum Blinken einzelner LEDs basierend auf den Bedingungen
void blinkConditionalLeds()
{
  Serial.println("blinkConditionalLeds is active!");
  while (ledAnzeigeAktiviert) {      
    // Überprüfen, ob 20 Sekunden vergangen sind, um die Schleife zu beenden
    if (millis() - startTimeLedAnzeigeAktiviert >= 60000) {  // 60000 Millisekunden = 20 Sekunden
        ledAnzeigeAktiviert = false; // Setzen Sie ledAnzeigeAktiviert auf false
        Serial.println("LED-Anzeige deaktiviert nach 20 Sekunden.");
        break;  // Schleife beenden
    }

    // Blinken der LEDs
    if (blinkRegenschirmverbot) {
        digitalWrite(Regenschirmverbot, HIGH); // LED für Regenschirmverbot einschalten
    } else {
        digitalWrite(Regenschirmverbot, LOW); // LED für Regenschirmverbot ausschalten
    }

    if (blinkRegenwahrscheinlichkeit) {
        digitalWrite(Regenwahrscheinlichkeit, HIGH); // LED für Regenwahrscheinlichkeit einschalten
    } else {
        digitalWrite(Regenwahrscheinlichkeit, LOW); // LED für Regenwahrscheinlichkeit ausschalten
    }

    // Blinken der LEDs
    if (blinkRegenschirmverbot || blinkRegenwahrscheinlichkeit) {
        // Wenn eine der Bedingungen erfüllt ist, blinken
        if (millis() % 500 < 250) { // Blinkintervall von 500 ms
            if (blinkRegenschirmverbot) {
                digitalWrite(Regenschirmverbot, HIGH);
            }
            if (blinkRegenwahrscheinlichkeit) {
                digitalWrite(Regenwahrscheinlichkeit, HIGH);
            }
        } else {
            digitalWrite(Regenschirmverbot, LOW);
            digitalWrite(Regenwahrscheinlichkeit, LOW);
        }
    }
    esp_task_wdt_reset();  // Watchdog zurücksetzen während der Schleife
    }
    // LEDs am Ende ausschalten
    digitalWrite(Regenschirmverbot, LOW);
    digitalWrite(Regenwahrscheinlichkeit, LOW);
    Serial.println("blinkConditionalLeds has endet!");
    
    // Sensor nochmals auslesen, um die nächste Funktion (updateIndicators) aufrufen zu können
    int state = checkSensorState();
    // Alle LEDs ausschalten
    updateIndicators(state);
}

// Hilfsfunktion zum Blinken aller LEDs
void blinkAllLeds() {
  unsigned long startTime = millis();  // Startzeit speichern
  
  while (millis() - startTime < 3000) {  // Für 3 Sekunden ausführen
    if (millis() - lastBlinkTime >= BLINK_INTERVAL) {
      blinkState = !blinkState;
      digitalWrite(Kontrollleuchte, blinkState);
      digitalWrite(Regenwahrscheinlichkeit, blinkState);
      digitalWrite(Schneewahrscheinlichkeit, blinkState);
      digitalWrite(Temperaturanzeige, blinkState);
      digitalWrite(Sturm, blinkState);
      digitalWrite(Regenschirmverbot, blinkState);
      lastBlinkTime = millis();
    }
    esp_task_wdt_reset();  // Watchdog zurücksetzen während der Schleife
  }
  
  // Alle LEDs am Ende ausschalten
  digitalWrite(Kontrollleuchte, LOW);
  digitalWrite(Regenwahrscheinlichkeit, LOW);
  digitalWrite(Schneewahrscheinlichkeit, LOW);
  digitalWrite(Temperaturanzeige, LOW);
  digitalWrite(Sturm, LOW);
  digitalWrite(Regenschirmverbot, LOW);
}

// Hilfsfunktion zum Blinken aller LEDs
void visualRaincheck(WeatherData weather) {
  // Prüft ob ein Fehler vorliegt oder die Daten veraltet sind
  // Falls ja, lässt alle LEDs blinken und beendet die Funktion
  if (weather.isError || !weather.isCurrentDay) {
    blinkAllLeds();
    return;
  }
  
  // Steuert die einzelnen LEDs basierend auf den Wetterdaten:
  // LED 1: Kontrollleuchte
  Serial.print("\nDu bist schön!");
  digitalWrite(Kontrollleuchte, HIGH);
  
  // LED 2: Regenwahrscheinlichkeit
  if (weather.rain > 15) {
    blinkRegenwahrscheinlichkeit = true;
    // blinkLed(Regenwahrscheinlichkeit);
    Serial.print("\nRegenwahrscheinlichkeit");
  } else if (weather.rain > 1) {
    digitalWrite(Regenwahrscheinlichkeit, HIGH);
  } else {
    digitalWrite(Regenwahrscheinlichkeit, LOW);
  }

  // LED 3: Schneewahrscheinlichkeit
  digitalWrite(Schneewahrscheinlichkeit, weather.snow > 0 ? HIGH : LOW);
  Serial.print("\nSchneewahrscheinlichkeit");

  // LED 4: Temperaturanzeige
  digitalWrite(Temperaturanzeige, weather.temperature < 12 ? HIGH : LOW);
  Serial.print("\nTemperaturanzeige");
  
  // LED 5: Sturm
  digitalWrite(Sturm, weather.windSpeed > 20 ? HIGH : LOW);
  Serial.print("\nSturm");
  Serial.println();
  
  // LED 6: Regenschirmverbot
  digitalWrite(Regenschirmverbot, (weather.rain > 1 && weather.windSpeed > 30) ? HIGH : LOW);
  
  if (weather.rain > 1 && weather.windSpeed > 30) {
    blinkRegenschirmverbot = true;
    // blinkLed(Regenschirmverbot);
    Serial.print("\nRegenschirmverbot");
  }
  // Funktion zum Blinken einzelner LEDs aufrufen
  blinkConditionalLeds();
}

WeatherData parseWeatherData(String payload) {
  WeatherData weather;
  
  // JSON-Dokument erstellen
  StaticJsonDocument<512> doc;
  DeserializationError error = deserializeJson(doc, payload);
  
  if (error) {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    weather.isError = true;
    return weather;
  }
  
  // JSON-Daten extrahieren
  JsonObject data = doc["data"];
  
  // Prüfe das Datum aus der API (Format sollte mit API abgestimmt werden)
  const char* apiDate = data["datum"].as<const char*>();
  Serial.println("apiDate: ");
  Serial.printf(apiDate);
  Serial.println();

  // Hole aktuelles Datum
  time_t now;
  time(&now);
  char currentDate[11];
  strftime(currentDate, sizeof(currentDate), "%Y-%m-%d", localtime(&now));
  
  // Vergleiche API Datum mit aktuellem Datum
  if (strcmp(apiDate, currentDate) == 0) {
    weather.isCurrentDay = true; // Setze auf true, wenn die Daten übereinstimmen
  } else {
    weather.isCurrentDay = false; // Setze auf false, wenn die Daten nicht übereinstimmen
  }
  
  // Rest der Daten wie gehabt
  weather.rain = data["tagesniederschlag_sum"].as<float>();
  weather.snow = data["schneefall_sum"].as<float>();
  weather.temperature = data["temperatur"].as<float>();
  weather.windSpeed = data["windgeschwindigkeit_max"].as<float>();
  weather.datum = data["datum"].as<String>();
  weather.isError = false;

  // Debug-Ausgabe aller Werte
  Serial.println("\n----- Parsed Weather Data -----");
  Serial.printf("Datum: %s\n", weather.datum.c_str());
  Serial.printf("Niederschlag: %.2f mm\n", weather.rain);
  Serial.printf("Schnee: %.2f mm\n", weather.snow);
  Serial.printf("Temperatur: %.2f °C\n", weather.temperature);
  Serial.printf("Windgeschwindigkeit: %.2f km/h\n", weather.windSpeed);
  Serial.printf("Aktuelle Daten: %s\n", weather.isCurrentDay ? "Ja" : "Nein");
  Serial.printf("Fehler Status: %s\n", weather.isError ? "Ja" : "Nein");
  Serial.println("-----------------------------\n");
  
  return weather;
}

String holeDatumUndZeitMitSekunden() {
  configTime(3600, 0, "pool.ntp.org", "time.nist.gov"); // 3600 Sekunden für UTC+1
  time_t now;
  time(&now);
  char formattedTime[20]; // Puffer für das formatierte Datum
  strftime(formattedTime, sizeof(formattedTime), "%Y-%m-%d %H:%M:%S", localtime(&now)); // Format wie gewünscht
  Serial.println(formattedTime);
  Serial.println();
  return String(formattedTime);
}

// Bewegung erkannt an die Datenbank melden
void LoadDataToDb() {
    
  // JSON-Dokument erstellen
  StaticJsonDocument<200> dataObject;
  String jsonString;

  // Daten setzen; Bewegung erkannt
  bool movement = true;
  
  // Hole aktuelles Datum und Zeit mit Sekunden
  String formattedTime = holeDatumUndZeitMitSekunden();
  
  Serial.println("Werte definiert");
  Serial.println();
  
  dataObject["movement"] = movement;
  dataObject["detectionTime"] = formattedTime;

  // Serialisieren des JSON Dokuments in einen String
  if (serializeJson(dataObject, jsonString) == 0) { // Überprüfen ob das Serialisieren erfolgreich war
    Serial.println("Fehler bei der Serialisierung des JSON");
  } else {
  Serial.println("JSON String erstellt");
  Serial.println(jsonString);
  }

  // JSON string per HTTP POST request an den Server schicken (server2db.php)
  if (WiFi.status() == WL_CONNECTED) {   // Überprüfen, ob Wi-Fi verbunden ist
    // HTTP Verbindung starten und POST-Anfrage senden
    HTTPClient http;
    http.begin(serverURLLoad);
    http.addHeader("Content-Type", "application/json");
    int httpResponseCode = http.POST(jsonString);

    // Prüfen der Antwort
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.printf("HTTP Response code: %d\n", httpResponseCode);
      Serial.println("Response: " + response);
    } else {
      Serial.printf("Error on sending POST: %d\n", httpResponseCode);
    }
    
    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }
}