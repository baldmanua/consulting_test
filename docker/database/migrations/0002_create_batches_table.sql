CREATE TABLE IF NOT EXISTS batches
(
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_number FLOAT NOT NULL,
    date             DATE  NOT NULL,
    merchant_id      INT UNSIGNED,
    FOREIGN KEY (merchant_id) REFERENCES merchants (id)
) ENGINE = MYISAM;
CREATE INDEX ref_num_date ON batches(reference_number, date);