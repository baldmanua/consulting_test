CREATE TABLE IF NOT EXISTS transactions
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date        DATE        NOT NULL,
    type        VARCHAR(10) NOT NULL,
    card_type   VARCHAR(2)  NOT NULL,
    card_number VARCHAR(16) NOT NULL,
    amount      float       NOT NULL,
    batch_id    INT UNSIGNED,
    FOREIGN KEY (batch_id) REFERENCES batches (id)
) ENGINE = MYISAM;