<?php

namespace fize\db\realization\oracle\mode;


use fize\db\realization\oracle\Db;
use fize\db\middleware\Odbc as Middleware;

/**
 * ODBC方式Oracle数据库模型类
 * @todo 未测试通过，暂无法使用
 * 注意ODBC返回的类型都为字符串格式(null除外)
 */
class Odbc extends Db
{
    use Middleware {
        Middleware::query as protected queryOdbc;
    }

    /**
     * 构造
     * @param string $user 用户名，必填
     * @param string $pwd 用户密码，必填
     * @param string $sid 连接串，必填
     * @param mixed $port 端口号，选填，Oracle默认是1521
     * @param string $charset 指定编码，选填，默认utf8
     * @param string $driver 指定ODBC驱动名称。
     */
    public function __construct($user, $pwd, $sid, $port = "", $charset = "utf8", $driver = null)
    {
        if (is_null($driver)) {  //默认驱动名
            //$driver = "{Oracle in OraClient11g_home1}";
            $driver = "{Oracle in OraDB12Home1}";
        }
        $dsn = "DRIVER={$driver};SERVER='{$sid}';UID='{$user}';PWD='{$pwd}';CHARSET={$charset}";
        $dsn = "DRIVER={$driver};SERVER={$sid};UID={$user};PWD={$pwd};";
        //$dsn = "DRIVER={$driver};SERVER={$sid};";
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
     * 安全化值
     * @todo 待优化
     * 由于本身存在SQL注入风险，不在业务逻辑时使用，仅供日志输出参考
     * ODBC为驱动层，安全化值应由各数据库自行实现
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value)
    {
        if (is_string($value)) {
            $value = "'" . addcslashes($value, "'") . "'";
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }

    /**
     * 执行一个SQL语句并返回相应结果
     * @param string $sql SQL语句，支持原生的ODBC问号预处理
     * @param array $params 可选的绑定参数
     * @param callable $callback 如果定义该记录集回调函数则不返回数组而直接进行循环回调
     * @return array|int|null SELECT语句返回数组或不返回，INSERT/REPLACE返回自增ID，其余返回受影响行数
     */
    public function query($sql, array $params = [], callable $callback = null)
    {
        $result = $this->queryOdbc($sql, $params, $callback);
        if (stripos($sql, "INSERT") === 0 || stripos($sql, "REPLACE") === 0) {
            return 0;  //返回自增ID
        } elseif (stripos($sql, "SELECT") === 0) {
            return $result;
        } else {
            $this->driver->exec("SELECT sql%rowcount");
            $rows = $this->driver->result(1);
            return (int)$rows; //返回受影响条数
        }
    }
}
