<?php
require_once 'libs\DB.php';
require_once 'libs\Importer.php';

use libs\Importer as Importer;

$mapping = [
    'MERCHANT_ID' => 'Merchant ID',
    'MERCHANT_NAME' => 'Merchant Name',
    'BATCH_DATE' => 'Batch Date',
    'BATCH_REF_NUM' => 'Batch Reference Number',
    'TRANSACTION_DATE' => 'Transaction Date',
    'TRANSACTION_TYPE' => 'Transaction Type',
    'TRANSACTION_CARD_TYPE' => 'Transaction Card Type',
    'TRANSACTION_CARD_NUMBER' => 'Transaction Card Number',
    'TRANSACTOIN_AMOUNT' => 'Transaction Amount'
];

$csv_file_path = 'CSV/report.csv';
try {
    $importer = new Importer($mapping);
    $importer->setFilePath($csv_file_path);
    $result = $importer->importCSV();
    var_dump($result);
} catch (Exception $e) {
    echo $e->getMessage();
}