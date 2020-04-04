CREATE TABLE IF NOT EXISTS transactions
(
    id          INT UNSIGNED AUTO_INCREMENT,
    date        DATE        NOT NULL,
    type        VARCHAR(20) NOT NULL,
    card_type   VARCHAR(2)  NOT NULL,
    card_number VARCHAR(20) NOT NULL,
    amount      float       NOT NULL,
    batch_id    INT UNSIGNED,
    PRIMARY KEY ('id'),
    FOREIGN KEY (batch_id) REFERENCES batches (id)
) ENGINE = MYISAM
  DEFAULT CHARSET = utf8;