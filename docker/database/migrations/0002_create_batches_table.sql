CREATE TABLE IF NOT EXISTS batches
(
    id          INT UNSIGNED AUTO_INCREMENT,
    ref_num     DECIMAL(24, 0) UNSIGNED NOT NULL,
    date        DATE         NOT NULL,
    merchant_id BIGINT UNSIGNED,
    PRIMARY KEY (id),
    UNIQUE KEY ref_num_date_key (ref_num, date),
    FOREIGN KEY (merchant_id) REFERENCES merchants (id)
) ENGINE = MYISAM
  DEFAULT CHARSET = utf8;

CREATE INDEX ref_num_date_index ON batches (ref_num, date);