<?php

class ReportFields
{
    const MERCHANT_ID             = 0; // digits only, up to 18 digits
    const MERCHANT_NAME           = 1; // string, max length - 100
    const BATCH_DATE              = 2; // YYYY-MM-DD
    const BATCH_REF_NUM           = 3; // digits only, up to 24 digits
    const TRANSACTION_DATE        = 4; // YYYY-MM-DD
    const TRANSACTION_TYPE        = 5; // string, max length - 20
    const TRANSACTION_CARD_TYPE   = 6; // string, max length - 2, possible values - VI/MC/AX and so on
    const TRANSACTION_CARD_NUMBER = 7; // string, max length - 20
    const TRANSACTOIN_AMOUNT      = 8; // amount, negative values are possible
}

$mapping = [
    ReportFields::MERCHANT_ID             => 'Merchant ID',
    ReportFields::MERCHANT_NAME           => 'Merchant Name',
    ReportFields::BATCH_DATE              => 'Batch Date',
    ReportFields::BATCH_REF_NUM           => 'Batch Reference Number',
    ReportFields::TRANSACTION_DATE        => 'Transaction Date',
    ReportFields::TRANSACTION_TYPE        => 'Transaction Type',
    ReportFields::TRANSACTION_CARD_TYPE   => 'Transaction Card Type',
    ReportFields::TRANSACTION_CARD_NUMBER => 'Transaction Card Number',
    ReportFields::TRANSACTOIN_AMOUNT      => 'Transaction Amount'
];

/**
 * CSV report:
 * - the first line contains headers always
 * - you should ensure that all required fields are present
 * - columns order is unknown
 * - file contains a list of transactions
 * - batch's transactions are always stored together
 *
 * Merchant1 (key - MERCHANT_ID)
 *      Batch1 (key - BATCH_DATE & BATCH_REF_NUM)
 *          Transaction1
 *          Transaction2
 *      Batch2
 *          Transaction3
 *          Transaction4
 * Merchant2 (key - MERCHANT_ID)
 *      Batch3 (key - BATCH_DATE & BATCH_REF_NUM)
 *          Transaction5
 *          Transaction6
 *
 * Your class:
 * - will receive a file name (with full path) and mappings (like $mapping)
 * - should be able to import a given file (if all required headers are present)
 * - suggest a db structure and write SQL commands to create it
 * - be able to process big files with low enough memory usage
 *
 * Use cases (just prepare SQL queries for these cases):
 * - display all transactions for a batch (merchant + date + ref num)
 *      date, type, card_type, card_number, amount
 * - display stats for a batch
 *   per card type (VI - 2 transactions with 100 total, MC - 10 transaction with 200 total, Total - 12 transaction with 300 total)
 * - display stats for a merchant and a given date range
 * - display top 10 merchants (by total amount) for a given date range
 *      merchant id, merchant name, total amount, number of transactions
 */
