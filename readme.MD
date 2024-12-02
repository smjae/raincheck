# RainCheck

RainCheck ist ein Wetterindikator, der idealerweise bei der Haustür platziert wird. Das Gerät kombiniert ein minimalistisches Design mit praktischer Funktionalität: Sobald der integrierte Bewegungssensor eine Person erkennt, aktiviert sich die LED-Anzeige und visualisiert die aktuellen Wetterdaten in Echtzeit. Die Daten werden für unseren Studienort Chur abgerufen.

Begleitend zum physischen Indikator bietet die Webseite www.raincheck.ch eine digitale Ergänzung. Hier finden Nutzer dieselbe Wettervisualisierung und können zusätzlich auf erweiterte Informationen zugreifen. Die Webseite dient zugleich als Schnittstelle zu unserer Datenbank, die in Kombination mit der API von open-meteo.com die Wetterdaten sammelt und aufbereitet.

RainCheck – für einen schnellen, klaren Wetterüberblick direkt vor der Tür oder online.

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
Die Idee hatten wir schon relativ früh. Nach den ersten Tagen in Physical Computing (Teil des Moduls «Interaktive Medien V» der FHGR), bei denen wir uns mit dem ESP32 und dem Steckbrett vertraut gemacht haben, bestellten wir zeitnah den Bewegungssensor für unser Projekt. Dann ging es ans Erarbeiten des Konzepts: wann sollte welches LED leuchten? Wie bauen wir die Datenstruktur auf? Was muss in die Datenbank geschrieben werden? Wie sieht unsere Webseite aus? Ergänzen wir mit Extras, wie z.B. nebst Digital zu Physisch auch von Physisch zu Digital?
Nachdem wir die Grundstruktur gebaut hatten und erste php-Abfragen zum Funktionieren brachten, waren wir schon gut unterwegs. In der zweiten Blockwoche erarbeiteten wir unsere genaue Datenarchitektur, die uns erheblich weiterbrachte. Beim ESP32 war es viel Trial & Error und wir merkten, dass wir noch viel zu wenig darüber wussten. Viele Stunden später haben wir es jedoch geschafft, und man kann wirklich sagen, dass wir etwas Tolles auf die Beine gestellt haben!

## Challenges und Lernerfolge
Challenges hatten wir definitiv genug. Da wir relativ früh ins Projekt gestartet sind und noch kaum Erfahrung mit Hardware wie dem ESP32 hatten, gerieten wir oftmals in Sackgassen und mussten nochmals einige Schritte zurück machen. Mittlerweile würden wir das ganze Projekt wohl in der Hälfte der Zeit abschliessen, aber genau da liegt ja der Lernerfolg. Eine weitere Challenge war die Arbeitsteilung. Aufgrund der physischen Komponente des Microcontrollers war es schwierig, dass wir beide zeitgleich daran arbeiten konnten. So teilten wir uns dann auf, was jedoch dazu führte, dass wir uns oft absprechen mussten bezüglich Kommunikationswegen der Programme und bezüglich der Logik. Dies verlangsamte den Prozess, und ausserdem brauchte es etwas mehr Zeit, um uns gegenseitig das eigene Werken beizubringen.
Bei der Software war eine Challenge, dass php & co. doch schon einige Monate nicht mehr genutzt wurde, weshalb ein erstaunlich grosser Teil der Zeit fürs Wiedererarbeiten der Syntax draufging. Auch Debugging war viel nötig, da teilweise falsche Variablennamen oder Funktionen eingebaut wurden.
Beim ESP32 und der Arduino Software gab es auch so einige Hürden. So war es eine Challenge, bis der Code zum Abrufen des aktuellen Datums und Uhrzeit am passenden Ort in den Schlaufen platziert war. A pro pos Schlaufen. Der Microcontroller arbeitet Schlaufen jeweils nacheinander ab. Somit mussten gewisse Prozesse, die einen Moment dauern sollten so umgeschrieben werden, dass alles aufgeht, auch wenn sich was blockiert etc. z.B. beim Blinken der individuellen LEDs. Doch es war machbar und der Code ist nun auch verständlich kommentiert. :)

## Technik
Dieses Projekt wurde mit folgenden Technologien umgesetzt:
physisch:
- ESP32 Dev Modul
- Bewegungssensor
- LED-Board
digital:
- PHP
- MySQL
- HTML
- CSS
- JavaScript
- Webserver????????

Unsere Datenstruktur:

![Datenstruktur](assets/img/datenstruktur.png)

Unser Steckbrett:

![Steckbrett](assets/img/steckbrett.png)

### Kommunikation zwischen den Medienkomponenten
- Dokumentation der Kommunikation zwischen den Medienkomponenten 
??

## UX Bla Bla
- Dokumentation der kreativen Konzeption, z. B. Inspirationen, Designentscheidungen, Vorgehensweise bei der Planung, Prozess, Ablaufdiagramm
Zusätzlich zu unserem LED-Board haben wir uns dazu entschieden, eine Webseite zu erstellen, auf der die aktuellen Daten angezeigt werden. Dabei haben wir uns an der physischen Komponente orientiert: auch digital sollen die Daten mit LEDs angezeigt werden, die je nach Wetterbedingungen leuchten oder blinken. Ansonsten ist die Seite sehr minimalistisch gestaltet. Wir haben uns für ein schlichtes Design entschieden, um die Aufmerksamkeit auf die Wetterdaten zu lenken. Die Seite ist responsiv und funktioniert auch auf mobilen Geräten. Das UX war etwas der letzten Dinge, die wir erledigte, da wir uns zuerst auf die Funktionalität konzentrierten.

## Known Bugs
- ESP32 kann nur bespielt werden, wenn der PIR Sensor nicht angeschlossen ist.

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
