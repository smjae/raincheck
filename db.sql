-- Drop the Wettervorhersage table if it exists and create it
DROP TABLE IF EXISTS `Wettervorhersage`;
CREATE TABLE IF NOT EXISTS `Wettervorhersage` (
    `unixtime` INT NOT NULL,
    `temperatur` FLOAT,
    `tagesniederschlag_sum` FLOAT,
    `schneefall_sum` FLOAT,
    `windgeschwindigkeit_max` FLOAT,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Drop the Anfragen table if it exists and create it
DROP TABLE IF EXISTS `Anfragen`;
CREATE TABLE IF NOT EXISTS `Anfragen` (
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
