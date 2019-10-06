<?php

use PHPUnit\Framework\TestCase;
use fize\db\Db;
use fize\db\Query;


class AccessTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $options = [
            'type'   => 'access',
            'mode'   => 'adodb',
            'config' => [
                'file'     => dirname(__FILE__) . '/data/test_with_password.mdb',
                'password' => '123456'
            ]
        ];

        Db::init($options);
    }

    public function testAdd()
    {
        $data = [
            'name'     => "!乱/七\八'糟\"的*字?符%串`一#大@堆(",
            'add_time' => time()
        ];
        $rst = Db::table('user')->insert($data);
        var_dump($rst);
        echo "<br/>";
        $sql = Db::getLastSql(true);
        print_r($sql);
        self::assertIsNumeric($rst);
    }

    public function testCommit()
    {
        Db::startTrans();
        echo 'OK';
        Db::commit();

        self::assertTrue(true);
    }

    public function testDelete()
    {
        $user = Db::table('user');
        $result = $user->where(['id' => ['IN', [2, 3, 4, 5, 9, 20, 21]]])->delete();
        var_dump($result);
        var_dump(Db::getLastSql(true));
    }

    public function testFetch()
    {
        $user = Db::table('user');

        $user->where(['sex' => ['NEQ', 1]])->fetch(
            function ($row) {
                var_dump($row);
            }
        );

        var_dump(Db::getLastSql(true));
    }

    public function testRollback()
    {
        Db::startTrans();
        echo 'OK';
        Db::rollback();
    }

    public function testSelect()
    {
        $user = Db::table('tt_user_role');
        $map2 = [
            'name' => ['LIKE', '%测试%']
        ];
        $list = $user->where($map2)->limit(2)->select();
        echo $user->getLastSql();
        echo "<br/>";
        var_dump($list);
    }

    public function testSelectMulti()
    {
        $user = Db::table('user');

//示例1，以数组连接
        $map1 = [
            'name'     => '35NEW,哈哈哈',
            'add_time' => ['<>', 1493712345, "OR"],  //EQ、OR不区分大小写
            "[name] IS NOT NULL"  //测试非标
        ];
        $map2 = [
            'add_time' => ['IN', "1493716872, 1493717205, 1493717205"]  //值为字符串格式，不含左右括号
        ];
        $map3 = [
            'name'     => '35NEW,哈哈哈',
            'add_time' => ['BETWEEN', 1493712345, 1493716872, "OR"]  //BETWEEN、OR不区分大小写
        ];
        $query1 = Query::qOr(Query::qAnd($map1, $map2), $map3);
        $list1 = $user->where($query1)->select();
        var_dump($list1);
        echo "<br/>";
        echo $user->getLastSql();
        echo "<br/>";

//示例2，以QueryMysql对象连接
        $map1 = Query::field('name')
            ->eq('35NEW,哈哈哈')
            ->logic('OR')
            ->field('add_time')
            ->neq(1493712345)
            ->logic('AND')
            ->object(null)
            ->exp('[name] IS NOT NULL');
        $map2 = Query::field('add_time')
            ->isIn("1493716872, 1493717205, 1493717205");
        $map3 = Query::field('name')
            ->eq('35NEW,哈哈哈')
            ->logic('OR')
            ->field('add_time')
            ->between(1493712345, 1493716872);
        $query2 = Query::qOr(Query::qAnd($map1, $map2), $map3);
        $list2 = $user->where($query2)->select();
        var_dump($list2);
        echo "<br/>";
        echo $user->getLastSql();
        echo "<br/>";
    }

    public function testSelectOr()
    {
        $user = Db::table('user');
        $map1 = [
            'name'     => "陈峰展'",
            'add_time' => ['BETWEEN', [1422720001, 1461226895]]
        ];

        $list1 = $user->where($map1)->select();
        var_dump($list1);
        echo "<br/>";
        echo $user->getLastSql(true);
        echo "<br/>";

        $map2 = [
            'name' => '35NEW,哈哈哈',
            'sex'  => ['=', 4, "OR"]
        ];
        $list2 = $user->where($map2)->select();
        var_dump($list2);
        echo "<br/>";
        echo $user->getLastSql();
    }

    public function testStartTrans()
    {
        Db::startTrans();
        echo 'OK';
        Db::commit();
    }
}