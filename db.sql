    CREATE TABLE
    IF NOT EXISTS Wettervorhersage (
        unixtime DATETIME NOT NULL,
        temperature DECIMAL(5,2) NOT NULL,
        tagesniederschlag_sum DECIMAL(6,2) NOT NULL,
        tagesniederschlag_max DECIMAL(6,2) NOT NULL
    );