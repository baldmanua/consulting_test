<?php


namespace libs;

use libs\DB as DB;

class UseCases
{
    /**
     * Get all transactions for a batch
     * @param $ref_num
     * @param $date
     * @return array
     */
    public static function getBatchTransactions($ref_num, $date)
    {
        $query = "
        SELECT t.date, 
               t.type,
               t.card_type,
               t.card_number,
               t.amount
        FROM batches b
        RIGHT JOIN transactions t 
            ON b.id = t.batch_id
        WHERE b.ref_num = $ref_num
            AND b.date = $date
        ";
        return DB::getInstance()->query($query);
    }

    /**
     * Get stats for a batch
     * @param $ref_num
     * @param $date
     * @return array
     */
    public static function getBatchStats($ref_num, $date)
    {
        $query = "
        SELECT t.card_type AS card_type,
               COUNT(t.id) AS number,
               SUM(t.amount) AS total
        FROM batches b
        RIGHT JOIN transactions t 
            ON b.id = t.batch_id
        WHERE b.ref_num = $ref_num
            AND b.date = $date
        GROUP BY t.card_type
        ";
        return DB::getInstance()->query($query);
    }

    /**
     * Get stats for a merchant and a given date range
     * @param $merchant_id
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public static function getMerchantStatsBetween($merchant_id, $date_from, $date_to)
    {
        $query = "
        SELECT t.card_type AS card_type,
               COUNT(t.id) AS number,
               SUM(t.amount) AS total
        FROM batches b
        RIGHT JOIN transactions t 
            ON b.id = t.batch_id
        LEFT JOIN merchants m 
            ON b.merchant_id = m.id
        WHERE m.id = $merchant_id
            AND t.date BETWEEN $date_from AND $date_to
        GROUP BY t.card_type
        ";
        return DB::getInstance()->query($query);
    }

    /**
     * Get top 10 merchants (by total amount) for a given date range
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public static function getTopTenMerchantsBetween($date_from, $date_to)
    {
        $query = "
        SELECT name, total FROM (        
            SELECT m.name AS name,
                           SUM(t.amount) AS total
                    FROM batches b
                    RIGHT JOIN transactions t 
                        ON b.id = t.batch_id
                    LEFT JOIN merchants m 
                        ON b.merchant_id = m.id
                    WHERE t.date BETWEEN $date_from AND $date_to
            ) as t
        ORDER BY total
        LIMIT 10
        ";
        return DB::getInstance()->query($query);
    }
}