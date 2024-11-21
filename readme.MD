# RainCheck

Kurze Beschreibung des Projekts.

### Auftrag für dieses Read.me Dokument

- UX: Dokumentation der kreativen Konzeption, z. B. Inspirationen, Designentscheidungen, Vorgehensweise bei der Planung, Prozess, Ablaufdiagramm
- Technik: Dokumentation der technischen Konzeption und Umsetzung, z. B. Verbindungsschema der eingesetzten Medienkomponenten
- Dokumentation der Kommunikation zwischen den Medienkomponenten 
- Umsetzungsprozess (welche Hilfsmittel, Fehlschläge, verworfene Lösungsansätze) 
- Known Bugs
- Lernerfolg
- Aufgabenaufteilung

neue Inputs von Jan Fiess via Email... wovon ich noch aussortiere, was wirklich benötigt wird.
Die Doku sollte so sein, dass ein unabhängiger Amateur mit deiner Doku  dein Projekt nachbauen kann, ohne dass es dazu dich braucht.
Dazu gehören z. B.

Verbindungs-Schema (mit Hardware, Software, Kommunikationswegen). Beispiel in den Slides.
Zusammenhang der verwendeten Dateien erläutern (egal ob als Test oder Grafik)
Steckschema der elektronischen Komponenten (Fritzing, Tinkercad, Wokwi helfen dir bei der Visualisierung des Steckschemas).
Ich hab noch eine alte Fritzing Version, als Fritzing noch gratis war. Damit hab ich die Grafiken für die Slides gemacht. Teile ich gerne.
Umsetzungsprozess (welche Hilfsmittel, Fehlschläge, verworfene Lösungsansätze)
-> was war geplant, was hat dicht daran gehindert, es so zu machen wie geplant, womit hast du dich aufgehalten (Hürden), womit hast du dir beholfen (Lösungsansatz), wie sind die Entwicklungsschritte, …
Toll wäre noch ein EINFACHES Video, das die Applikation in action zeigt. Einfache Smartphone-Aufnahme auf Youtube, verlinkt in der Readme.md reicht
Known Bugs

## Inhaltsverzeichnis

- [Idee](#idee)
- [Vorgehen](#vorgehen)
- [Challenges und Lernerfolge](#challengesundLernerfolge)
- [Technik](#technik)
- [Funfacts](#funfacts)
- [Lizenz](#lizenz)

## Idee
Wie kommt man auf die Idee, Wetterdaten aus einer API abzulesen und diese bei bestimmten Bedingungen auf einem LED-Board anzuzeigen? Keine Ahnung. Vermutlich, weil wir beide schon viel zu oft das Haus ohne Schirm oder Regenjacke verlassen haben, nur um dann völlig verschüttet zu werden. Ärgerlich... Daraus ist raincheck.ch entstanden: je nach Tagesprognose in Chur leuchten andere LEDs auf dem Board. Sei es besonders kalt, regnerisch, windig oder gäbe es Schnee: ab sofort wissen wir beim Verlassen der Wohnung, was uns erwartet. Mit Hilfe der Daten aus der API von open-meteo haben wir eine laufende Prognose.

## Vorgehen
Die Idee hatten wir schon relativ früh. Nach den ersten Tagen in Physical Computing, bei denen wir uns mit dem ESP32 und dem Steckbrett vertraut gemacht haben, bestellten wir auch relativ schnell den Bewegungssensor für unser Projekt. Dann giing es ans Erarbeiten des Konzepts: wann sollte welches LED leuchten? Wie bauen wir die Datenstruktur auf? Was muss in die Datenbank geschrieben werden? Wie sieht unsere Webseite aus? Nachdem wir jedoch mal die Grundstruktur gebaut hatten und erste php-Abfragen funktionierten, waren wir schon gut unterwegs. In der zweiten Blockwoche erarbeiteten wir noch unsere genaue Datenarchitektur, die uns nochmals erheblich half. Beim ESP32 war es viel Trial & Error und wir merkten, dass wir noch viel zu wenig darüber wussten. Viele Stunden später haben wir es jedoch geschafft, und man kann wirklich sagen, dass wir etwas Tolles auf die Beine gestellt haben!

## Challenges und Lernerfolge
- [] Challenges Raphi
  - [] Korrekte Uhrzeit auf dem board
  - [] Schlaufen werden nacheinander ausgeführt… mühsam zum abschalten nach gewisser zeit → timer hab ich nun bei blink eingebaut.
  - [] Kommunikation, dass alles mktgesendet wird, war noch interessant
  - [] Reihenfokge, dass alle LED‘s Zeitgleich blinken.

Challenges hatten wir definitiv genug. Da wir relativ früh ins Projekt gestartet sind und noch kaum Erfahrung mit Hardware wie dem ESP32 hatten, gerieten wir oftmals in Sackgassen und mussten nochmals einige Schritte zurück machen. Mittlerweile würden wir das ganze Projekt wohl in der Hälfte der Zeit schaffen, aber genau dort liegt ja der Lernerfolg. Eine weitere Challenge war die Arbeitsteilung. Aufgrund der physischen Komponente des Microcontrollers war es schwierig, dass wir beide gleichzeitig daran arbeiten konnten. So teilten wir uns dann auf, was jedoch dazu führte, dass wir uns oft absprechen mussten bezüglich Kommunikationswegen der Programme und bezüglich der Logik. Dies verlangsamte den Prozess, und ausserdem müssen wir jetzt trotzdem noch Zeit aufwenden, damit beide von uns das Projekt ganz verstehen und auch den jeweils anderen Teil selbst umsetzen könnten.
Bei der Software war eine Challenge, dass php & co. doch schon einige Monate zurückliegt, weshalb ein erstaunlich grosser Teil der Zeit fürs Wiedererarbeiten der Syntax draufging. Auch Debugging war viel nötig, da ich oft einfach falsche Variablennamen oder Funktionen einbaute.

## Technik
Dieses Projekt wurde mit folgenden Technologien umgesetzt:
physisch:
- ESP32
- Bewegungssensor
- LED-Board
digital:
- PHP
- MySQL
- HTML
- CSS
- JavaScript

Unsere Datenstruktur:

![Datenstruktur](assets/img/datenstruktur.png)

Unser Steckbrett:


### Kommunikation zwischen den Medienkomponenten
- Dokumentation der Kommunikation zwischen den Medienkomponenten 
??

## UX Bla Bla
- Dokumentation der kreativen Konzeption, z. B. Inspirationen, Designentscheidungen, Vorgehensweise bei der Planung, Prozess, Ablaufdiagramm
Zusätzlich zu unserem LED-Board haben wir uns dazu entschieden, eine Webseite zu erstellen, auf der die aktuellen Daten angezeigt werden. Dabei haben wir uns an der physischen Komponente orientiert: auch digital sollen die Daten mit LEDs angezeigt werden, die je nach Wetterbedingungen leuchten oder blinken. Ansonsten ist die Seite sehr minimalistisch gestaltet. Wir haben uns für ein schlichtes Design entschieden, um die Aufmerksamkeit auf die Wetterdaten zu lenken. Die Seite ist responsiv und funktioniert auch auf mobilen Geräten. Das UX war etwas der letzten Dinge, die wir erledigte, da wir uns zuerst auf die Funktionalität konzentrierten.

## Known Bugs
haben wir das? könnte da evtl. rein, dass der Sensor teilweise 2sek braucht, bis er reagiert?

## Funfacts
??

## Aufgabenaufteilung
- [] gemeinsam: Datenbank aufsetzen, Kommunikation mit API, Datenstruktur
- [] Raphi: Hardware, Bewegungssensor, LED-Board, Kommunikation mit Datenbank
- [] Simea: Datenbank, Kommunikation mit Datenbank, Frontend, UX/UI

## Lizenz
Dieses Projekt ist unter der MIT-Lizenz lizenziert. Weitere Informationen finden Sie in der [LICENSE](LICENSE.txt) Datei.

## Mögliche Ergänzungen / To-Do's
- [] Video von live funktionen
- [] Codeabschnitte erläutern
