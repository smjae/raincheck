-- Dieses Dokument haben wir zu Beginn verwendet, um die Tabellen in der Datenbank zu erstellen.


-- Tabelle "Wettervorhersage" löschen, falls sie existiert; dann erstellen
DROP TABLE IF EXISTS `Wettervorhersage`;
CREATE TABLE IF NOT EXISTS `Wettervorhersage` (
    `datum` DATE NOT NULL,
    `temperatur` FLOAT,
    `tagesniederschlag_sum` FLOAT,
    `schneefall_sum` FLOAT,
    `windgeschwindigkeit_max` FLOAT,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabelle "Anfragen" löschen, falls sie existiert; dann erstellen
DROP TABLE IF EXISTS `Anfragen`;
CREATE TABLE IF NOT EXISTS `Anfragen` (
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
