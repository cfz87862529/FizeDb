<?php

namespace fize\db\realization\mysql\mode;


use fize\db\realization\mysql\Db;
use fize\db\middleware\Odbc as Middleware;

/**
 * ODBC方式MySQL数据库模型类
 *
 * 注意ODBC返回的类型都为字符串格式(null除外)
 * @package fize\db\realization\mysql\mode
 */
class Odbc extends Db
{
    use Middleware;

    /**
     * 构造
     * @param string $host 服务器地址
     * @param string $user 用户名
     * @param string $pwd 用户密码
     * @param string $dbname 数据库名
     * @param mixed $port 端口号，选填，MySQL默认是3306
     * @param string $charset 指定编码，选填，默认utf8
     * @param string $driver 指定ODBC驱动名称。
     */
    public function __construct($host, $user, $pwd, $dbname, $port = "", $charset = "utf8", $driver = null)
    {
        if (is_null($driver)) {
            $driver = "{MySQL ODBC 8.0 ANSI Driver}";
        }
        $dsn = "DRIVER={$driver};SERVER={$host};DATABASE={$dbname};CHARSET={$charset}";
        if (!empty($port)) {
            $dsn .= ";PORT={$port}";
        }
        $this->odbcConstruct($dsn, $user, $pwd);
    }

    /**
     * 析构时关闭ODBC
     */
    public function __destruct()
    {
        $this->odbcDestruct();
        parent::__destruct();
    }

    /**
     * 返回最后插入行的ID或序列值
     * @param string $name 应该返回ID的那个序列对象的名称,该参数在mysql中无效
     * @return int|string
     */
    public function lastInsertId($name = null)
    {
        $result = $this->driver->exec("SELECT @@IDENTITY");
        return $result->result(1);
    }
}