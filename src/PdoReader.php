<?php

namespace Port\Pdo;

use Port\Reader\CountableReader;

/**
 * Reads data through PDO
 *
 * @author Robbie Mackay
 */
class PdoReader implements CountableReader
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * @var array
     */
    private $data;

    /**
     * @param \PDO   $pdo
     * @param string $sql
     * @param array  $params
     */
    public function __construct(\PDO $pdo, $sql, array $params = [])
    {
        $this->pdo = $pdo;
        $this->statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $this->statement->bindValue($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $key = key($this->data);

        return ($key !== null && $key !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->loadData();

        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->loadData();

        return count($this->data);
    }

    /**
     * Load data if it hasn't been loaded yet
     */
    protected function loadData()
    {
        if (null === $this->data) {
            $this->statement->execute();
            $this->data = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        }
    }
}
