<?php


namespace fize\database\extend\mssql;


use fize\database\core\ModeFactoryInterface;
use fize\database\exception\Exception;

/**
 * 模式工厂
 */
class ModeFactory implements ModeFactoryInterface
{

    /**
     * 创建实例
     * @param string $mode   连接模式
     * @param array  $config 参数选项
     * @return Db
     * @throws Exception
     */
    public static function create($mode, array $config)
    {
        $mode = $mode ? $mode : 'pdo';
        $default_config = [
            'port'        => '',
            'prefix'      => '',
            'new_feature' => true,
            'driver'      => null,
            'charset'     => 'GBK',
            'opts'        => []
        ];
        $config = array_merge($default_config, $config);
        switch ($mode) {
            case 'adodb':
                $db = Mode::adodb($config['host'], $config['user'], $config['password'], $config['dbname'], $config['port'], $config['driver']);
                break;
            case 'odbc':
                $db = Mode::odbc($config['host'], $config['user'], $config['password'], $config['dbname'], $config['port'], $config['driver']);
                break;
            case 'pdo':
                $db = Mode::pdo(
                    $config['host'],
                    $config['user'],
                    $config['password'],
                    $config['dbname'],
                    $config['port'],
                    $config['charset'],
                    $config['opts']
                );
                break;
            case 'sqlsrv':
                $db = Mode::sqlsrv($config['host'], $config['user'], $config['password'], $config['dbname'], $config['port'], $config['charset']);
                break;
            default:
                throw new Exception("error db mode: {$mode}");
        }
        $db->prefix($config['prefix']);
        $db->newFeature($config['new_feature']);
        return $db;
    }
}
