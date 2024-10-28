CREATE TABLE IF NOT EXISTS
    `Wettervorhersage`  (
        `unixtime` int (11) NOT NULL,
        `temperature` decimal(5, 2) NOT NULL,
        `tagesniederschlag_sum` decimal(6, 2) NOT NULL,
        `tagesniederschlag_max` decimal(6, 2) NOT NULL,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
    );

CREATE TABLE IF NOT EXISTS
    `Anfragen`  (
        `unixtime` int (11) NOT NULL,
        `temperature` decimal(5, 2) NOT NULL,
        `tagesniederschlag_sum` decimal(6, 2) NOT NULL,
        `tagesniederschlag_max` decimal(6, 2) NOT NULL,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
    );
