<?php

namespace Port\Tests\Writer;

use Port\Exception\WriterException;
use Port\Pdo\PdoWriter;
use PHPUnit\Framework\TestCase;

class PdoWriterTest extends TestCase
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('DROP TABLE IF EXISTS example');
        $this->pdo->exec('CREATE TABLE example (a TEXT, b TEXT)');
    }

    public function testValidWriteItem()
    {
        $writer = new PdoWriter($this->pdo, 'example');
        $writer->prepare();
        $writer->writeItem(['a' => 'foo', 'b' => 'bar']);
        $writer->finish();

        $stmnt = $this->pdo->query('SELECT * FROM example');
        $this->assertEquals(
            [['a' => 'foo', 'b' => 'bar']],
            $stmnt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function testValidWriteMultiple()
    {
        $writer = new PdoWriter($this->pdo, 'example');
        $writer->prepare();
        $writer->writeItem(['a' => 'foo', 'b' => 'bar']);
        $writer->writeItem(['a' => 'cat', 'b' => 'dog']);
        $writer->writeItem(['a' => 'ac', 'b' => 'dc']);
        $writer->finish();

        $stmnt = $this->pdo->query('SELECT * FROM example');
        $this->assertEquals(
            [
                ['a' => 'foo', 'b' => 'bar'],
                ['a' => 'cat', 'b' => 'dog'],
                ['a' => 'ac', 'b' => 'dc'],
            ],
            $stmnt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function testWriteTooManyValues()
    {
        $this->expectException(WriterException::class);

        $writer = new PdoWriter($this->pdo, 'example');
        $writer->prepare();
        $writer->writeItem(['a' => 'foo', 'b' => 'bar', 'c' => 'baz']);
        $writer->finish();
    }

    public function testWriteToNonexistentTable()
    {
        $this->expectException(WriterException::class);

        $writer = new PdoWriter($this->pdo, 'foobar');
        $writer->prepare();
        $writer->writeItem(['a' => 'foo', 'b' => 'bar']);
        $writer->finish();
    }

    public function testRequiresExceptionErrorMode()
    {
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage('Please set the pdo error mode to PDO::ERRMODE_EXCEPTION');

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        new PdoWriter($this->pdo, 'example');
    }
}
