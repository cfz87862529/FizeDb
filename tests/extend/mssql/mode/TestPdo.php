<?php

namespace extend\mssql\mode;

use fize\database\extend\mssql\mode\Pdo;
use PHPUnit\Framework\TestCase;

class TestPdo extends TestCase
{

    public function test__construct()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');
        var_dump($db);
        self::assertIsObject($db);
    }

    public function test__destruct()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');
        var_dump($db);
        self::assertIsObject($db);
        unset($db);
        self::assertTrue(true);
    }

    public function testLastInsertId()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');
        $data = [
            'name'     => "!乱/七\八'糟\"的*字?符%串`一#大@堆(",
            'add_time' => time()
        ];
        $db->table('user')->insert($data);
        $sql = $db->getLastSql(true);
        var_dump($sql);
        $id = $db->lastInsertId();
        var_dump($id);
        self::assertIsNumeric($id);
    }

    public function testPrototype()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');
        $prototype = $db->prototype();
        var_dump($prototype);
        self::assertIsObject($prototype);
    }

    public function testQuery()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');

        //增
        $sql = 'INSERT INTO [user] ([name],[add_time]) VALUES (?,?)';
        $num = $db->query($sql, ["!乱/七\八'糟\"的*字?符%串`一#大@堆(", time()]);
        var_dump($num);
        self::assertIsInt($num);

        //删
        $sql = 'DELETE FROM [user] WHERE id <= 8';
        $num = $db->query($sql, ["!乱/七\八'糟\"的*字?符%串`一#大@堆(", time()]);
        var_dump($num);
        self::assertIsInt($num);

        //改
        $sql = 'UPDATE [user] SET [name] = ? WHERE id = 19';
        $num = $db->query($sql, ["陈峰展"]);
        var_dump($num);
        self::assertIsInt($num);

        //查
        $sql = 'SELECT * FROM [user]';
        $rows = $db->query($sql);
        var_dump($rows);
        self::assertIsArray($rows);
    }

    public function testStartTrans()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');
        $db->startTrans();
        $db->commit();
        self::assertTrue(true);
    }

    public function testCommit()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');

        $db->startTrans();

        $sql = 'UPDATE [user] SET [name] = ? WHERE id = 15';
        $num = $db->query($sql, ["陈峰展2329"]);
        var_dump($num);
        self::assertIsInt($num);

        $db->commit();

        $sql = 'SELECT * FROM [user] WHERE id = 15';
        $rows = $db->query($sql);
        var_dump($rows[0]);
        self::assertEquals($rows[0]['name'], '陈峰展2329');

    }

    public function testRollback()
    {
        $db = new Pdo('(local)', 'sa', '123456', 'gm_test');

        $db->startTrans();

        $sql = 'UPDATE [user] SET [name] = ? WHERE id = 15';
        $num = $db->query($sql, ["陈峰展23292"]);
        var_dump($num);
        self::assertIsInt($num);

        $db->rollback();

        $sql = 'SELECT * FROM [user] WHERE id = 15';
        $rows = $db->query($sql);
        var_dump($rows[0]);
        self::assertEquals($rows[0]['name'], '陈峰展2329');
    }
}
