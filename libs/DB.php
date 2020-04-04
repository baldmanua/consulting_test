<?php
namespace libs;
use PDO;

class DB
{
    /**
     * @var DB|null $_instance DB connection instance
     */
    private static $_instance = null;
    /**
     * @var PDO
     */
    private $pdo;

    private function __construct()
    {
        require_once 'config.php';
        $this->pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASS
        );
    }

    private function __clone() {}
    private function __wakeup(){}

    /**
     * @return DB
     */
    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function query($query)
    {
        //@ToDo protect query
        return $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $query
     * @return bool|string
     */
    private function insertIgnoreAndGetId($query)
    {
        $sql = $this->pdo->prepare($query);
        if ( $sql->execute() && $sql->rowCount() > 0 ) {
            return $this->pdo->lastInsertId();
        } else {
            return false;
        }
    }

    public function insertOrGetButch($ref_num, $date, $merchant_id)
    {
        $id = $this->insertIgnoreAndGetId("
            INSERT IGNORE INTO batches
            SET ref_num = $ref_num,
                date = '$date',
                merchant_id = $merchant_id
        ");
        if($id === false) {
            $batch = $this->query("
                SELECT id
                FROM batches
                WHERE ref_num = $ref_num
                    AND date = '$date'
                LIMIT 1
            ");
            return $batch[0]['id'];
        } else {
            return $id;
        }
    }

    /**
     * @param $merchants_array
     * @return false|int
     */
    public function insertMerchants($merchants_array)
    {
        $query = 'INSERT IGNORE INTO merchants (id, name) VALUES ';
        $first = true;
        foreach ($merchants_array as $merchant) {
            if($first) {
                $first = false;
            } else {
                $query .= ', ';
            }
            $query .= "(".$merchant['id'].", '".$merchant['name']."')";
        }
        return $this->pdo->exec($query);
    }

    public function insertTransactions($transactions_array)
    {
        /**
         * @ToDo If too much rows, query can be out of max length.
         * array_chunk() fixes it, but then we will have too much queries.
         * needs performance testing
         */
        $query = 'INSERT INTO transactions (date, type, card_type, card_number, amount, batch_id) VALUES ';
        $first = true;
        foreach ($transactions_array as $transaction) {
            if($first) {
                $first = false;
            } else {
                $query .= ', ';
            }
            $query .= "(";
            $query .= "'".$transaction['date']."', ";
            $query .= "'".$transaction['type']."', ";
            $query .= "'".$transaction['card_type']."', ";
            $query .= "'".$transaction['card_number']."', ";
            $query .= $transaction['amount'].", ";
            $query .= $transaction['batch_id'];
            $query .= ")";
        }
        return $this->pdo->exec($query);
    }
}