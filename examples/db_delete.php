<?php
require_once "../vendor/autoload.php";

use fize\db\Db;

$config = [
    'type'   => 'mysql',
    'mode'   => 'pdo',
    'config' => [
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => '123456',
        'dbname'   => 'gm_test'
    ]
];

new Db($config);

$result = Db::table('user')->where(['id' => 73])->delete();
var_dump($result);
var_dump(Db::getLastSql(true));