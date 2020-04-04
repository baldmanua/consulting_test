<?php

namespace libs;

use Exception;
use libs\DB as DB;

class Importer
{

    /**
     * @var array $mapping_assoc associative fields mapping
     * Setting in constructor (optionaly)
     * Can be changed with setMapping function
     * Keys are presented below, values are CSV columns in 1st row
     * Example:
     * [
     *   'MERCHANT_ID'             => 'Merchant ID',
     *   'MERCHANT_NAME'           => 'Merchant Name',
     *   'BATCH_DATE'              => 'Batch Date',
     *   'BATCH_REF_NUM'           => 'Batch Reference Number',
     *   'TRANSACTION_DATE'        => 'Transaction Date',
     *   'TRANSACTION_TYPE'        => 'Transaction Type',
     *   'TRANSACTION_CARD_TYPE'   => 'Transaction Card Type',
     *   'TRANSACTION_CARD_NUMBER' => 'Transaction Card Number',
     *   'TRANSACTOIN_AMOUNT'      => 'Transaction Amount'
     */
    private $mapping_assoc = [];

    /**
     * @var array $mapping_num column numbers mapping
     * Keys are presented below, values are column numbers in CSV starting from 0
     * Example:
     * [
     *   'MERCHANT_ID'             => 0,
     *   'MERCHANT_NAME'           => 1,
     *   'BATCH_DATE'              => 2,
     *   'BATCH_REF_NUM'           => 3,
     *   'TRANSACTION_DATE'        => 4,
     *   'TRANSACTION_TYPE'        => 5,
     *   'TRANSACTION_CARD_TYPE'   => 6,
     *   'TRANSACTION_CARD_NUMBER' => 7,
     *   'TRANSACTOIN_AMOUNT'      => 8
     */
    private $mapping_num = [];

    /**
     * @var string $file_path path to CSV file
     */
    private $file_path = '';

    /**
     * Importer constructor.
     * @var array $mapping mapping array
     */
    public function __construct($mapping_assoc = [])
    {
        if(!empty($mapping_assoc) && $this->validateMapping($mapping_assoc)) {
            $this->mapping_assoc = $mapping_assoc;
        }
    }

    /**
     * $mapping_assoc setter.
     * If error in mapping, returns false
     * @var array $mapping mapping array
     * @return self|false
     */
    public function setMapping($mapping_assoc)
    {
        if($this->validateMapping($mapping_assoc)) {
            $this->mapping_assoc = $mapping_assoc;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * populates number mapping for current CSV file
     * To make it work, populate assoc mapping first
     * @param array $head The first column from CSV file
     * @throws Exception
     */
    private function setMappingNum($head)
    {
        if(empty($this->mapping_assoc)) {
            throw new Exception('CSV file mapping was not set. Use setMapping() function');
        }
        if(count($head) < count($this->mapping_assoc)) {
            throw new Exception('CSV file not valid - not enough fields');
        }
        if(count($head) > 100) {
            throw new Exception('CSV file not valid - too much columns (> 100)');
        }
        $mapping_num = [];
        foreach ($head as $col_num => $col_name) {
            foreach ($this->mapping_assoc as $key => $map_name) {
                if($col_name == $map_name) {
                    $mapping_num[$key] = $col_num;
                }
            }
        }
        if(count($mapping_num) != count($this->mapping_assoc)) {
            throw new Exception('CSV file not valid - not all fields presented');
        } else {
            $this->mapping_num = $mapping_num;
        }
    }

    /**
     * set CSV file to work with
     * Returns falce if file not exist
     * @param string $path path to CSV file
     * @return self|false
     * @throws Exception
     */
    public function setFilePath($path)
    {
        if(!file_exists($path)) {
            throw new Exception('CSV file path not valid. File not found.');
        }
        $this->file_path = $path;
        return $this;
    }

    /**
     * @throws Exception
     * @return array
     */
    public function importCSV() {
        if($this->file_path == '') {
            throw new Exception('CSV file path was not set. Use setFilePath() function.');
        }
        if(empty($this->mapping_assoc)) {
            throw new Exception('CSV file mapping was not set. Use setMapping() function');
        }

        $csv = array_map('str_getcsv', file($this->file_path));

        $csv_head = $csv[0];
        unset($csv[0]);

        if(!$this->validateHead($csv_head)) {
            throw new Exception('CSV file head not valid. Check CSV file');
        }

        $this->setMappingNum($csv_head);

        $merchants = [];
        $transactions = [];
        $invalid_values = [
            0 => $csv_head
        ];

        $prev_batch_id = null;
        $prev_batch_ref_num = null;
        $prev_batch_date = null;

        $batches_num = 0;
        foreach ($csv as $num => $row) {
            if(!$this->validateRow($row)) {
                $invalid_values[] = $row;
                continue;
            }
            $cur_batch_ref_num = $row[$this->mapping_num['BATCH_REF_NUM']];
            $cur_batch_date    = $row[$this->mapping_num['BATCH_DATE']];
            $cur_merchant_id   = $row[$this->mapping_num['MERCHANT_ID']];

            if($prev_batch_ref_num != $cur_batch_ref_num || $cur_batch_date != $prev_batch_date) {
                $cur_batch_id = DB::getInstance()->insertOrGetButch($cur_batch_ref_num, $cur_batch_date, $cur_merchant_id);

                $prev_batch_id      = $cur_batch_id;
                $prev_batch_ref_num = $cur_batch_ref_num;
                $prev_batch_date    = $cur_batch_date;
                $batches_num++;
            } else {
                $cur_batch_id = $prev_batch_id;
            }
            if(!isset($merchants[$cur_merchant_id])) {
                $merchants[$cur_merchant_id] = [
                    'id'   => $cur_merchant_id,
                    'name' => $row[$this->mapping_num['MERCHANT_NAME']]
                ];
            }
            $transactions[] = [
                'date'        => $row[$this->mapping_num['TRANSACTION_DATE']],
                'type'        => $row[$this->mapping_num['TRANSACTION_TYPE']],
                'card_type'   => $row[$this->mapping_num['TRANSACTION_CARD_TYPE']],
                'card_number' => $row[$this->mapping_num['TRANSACTION_CARD_NUMBER']],
                'amount'      => $row[$this->mapping_num['TRANSACTOIN_AMOUNT']],
                'batch_id'    => $cur_batch_id,
            ];
        }
        $merchants_num    = DB::getInstance()->insertMerchants($merchants);
        $transactions_num = DB::getInstance()->insertTransactions($transactions);
        $errors_num = count($invalid_values) - 1;
        //@ToDO add saving invalid values from $invalid_values array to some error csv log file

        return [
          'imported' => [
              'merchants' => $merchants_num,
              'batches'   => $batches_num,
              'transactions' => $transactions_num
            ],
            'errors' => $errors_num
        ];
    }

    /**
     * Validates the CSV header (imported variables)
     * @param array $head
     * @return bool
     */
    private function validateHead($head)
    {
        //@ToDo write validation for CSV head row
        return true;
    }

    /**
     * Validates the CSV row (imported values)
     * @param array $row
     * @return bool
     */
    private function validateRow($row)
    {
        //@ToDo write validation for CSV regular row
        if(empty($row) || is_null($row[0])) {
            return false;
        }
        return true;
    }

    /**
     * Validates CSV mapping
     * @param array $mapping
     * @return bool
     */
    private function validateMapping($mapping) {
        //@ToDo write validation for mapping
        return true;
    }
}