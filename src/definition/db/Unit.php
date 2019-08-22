<?php

namespace fize\db\definition\db;


use fize\db\definition\Query;

/**
 * 数据库语句构造基本功能
 */
trait Unit
{

    /**
     * 是否指明为DISTINCT
     * @var bool
     */
    protected $_distinct = false;

    /**
     * SQL指定要返回的字段语句
     * @var string
     */
    protected $_field = "";

    /**
     * 当前数据库前缀
     * @var string
     */
    protected $_tablePrefix = "";

    /**
     * 当前数据表名，不含前缀
     * @var string
     */
    protected $_tableName = null;

    /**
     * ALIAS语句
     * @var string
     */
    protected $_alias = "";

    /**
     * WHERE语句
     * @var string
     */
    protected $_where = "";

    /**
     * WHERE语句使用的绑定参数数组
     * @var array
     */
    protected $_whereParams = [];

    /**
     * GROUP语句
     * @var string
     */
    protected $_group = "";

    /**
     * HAVING语句
     * @var string
     */
    protected $_having = "";

    /**
     * HAVING语句使用的绑定参数数组
     * @var array
     */
    protected $_havingParams = [];

    /**
     * ORDER语句
     * @var string
     */
    protected $_order = "";

    /**
     * 指定distinct查询
     * @param bool $distinct 为true时表示distinct
     * @return $this
     */
    public function distinct($distinct = true)
    {
        $this->_distinct = $distinct;
        return $this;
    }

    /**
     * 指定要查询的字段，支持链式调用
     * @param mixed $fields 要查询的字段组成的数组或者字符串,如果需要指定别名，则使用：别名=>实际名称
     * @return $this
     */
    public function field($fields)
    {
        if (is_array($fields)) {
            $parts = [];
            foreach ($fields as $alias => $field) {
                if (is_int($alias)) {
                    $parts[] = $this->_field_($field);
                } else {
                    $parts[] = "{$this->_field_($field)} AS {$this->_field_($alias)}";
                }
            }
            $this->_field = join(',', $parts);
        } else {
            $this->_field = $this->_field_($fields);
        }
        return $this;
    }

    /**
     * 指定当前要操作的表,支持链式调用
     * @param string $name 表名
     * @param string $prefix 表前缀，默认为null表示使用当前前缀
     * @return $this
     */
    public function table($name, $prefix = null)
    {
        $this->_tableName = $name;
        if (!is_null($prefix)) {
            $this->_tablePrefix = $prefix;
        }
        return $this;
    }

    /**
     * 对当前表设置别名,支持链式调用
     * @param string $alias 别名
     * @return $this
     */
    public function alias($alias)
    {
        $this->_alias = $alias;
        return $this;
    }

    /**
     * GROUP语句,支持链式调用
     * @param mixed $fields 要GROUP的字段字符串或则数组
     * @return $this
     */
    public function group($fields)
    {
        if (is_array($fields)) {
            $fields = array_map([$this, '_field_'], $fields);
            $fields = implode(',', $fields);
        }
        if (empty($this->_group)) {
            $this->_group = "{$fields}";
        } else {
            $this->_group .= ",{$fields}";
        }
        return $this;
    }

    /**
     * 设置排序条件,支持链式调用
     * @param mixed $field_order 字符串原样，如果是数组(推荐)，则形如字段=>排序
     * @return $this
     */
    public function order($field_order)
    {
        if (is_array($field_order)) {
            foreach ($field_order as $field => $order) {
                $order = strtoupper($order);
                if(!empty($this->_order)){
                    $this->_order .= ", ";
                }
                $this->_order .= " {$this->_field_($field)} $order";
            }
        } else {
            if(!empty($this->_order)){
                $this->_order .= ", ";
            }
            $this->_order .= " {$field_order}";
        }
        $this->_order = trim($this->_order);
        return $this;
    }

    /**
     * 设置WHERE语句,支持链式调用
     * @param mixed $statements “Query对象”或者“查询数组”或者“WHERE子语句”，其中“WHERE子语句”支持原生的PDO问号预处理占位符;
     * @param array $parse 如果$statements是SQL预处理语句，则可以传递本参数用于预处理替换参数数组
     * @return $this
     */
    public function where($statements, array $parse = [])
    {
        if (is_array($statements)) {  // 通常情况下，我们使用简洁方式来更简便地定义条件，对于复杂条件无法满足的，可以使用查询器或者直接使用预处理语句
            $query = new Query();
            $query->analyze($statements);
            $this->_where = $query->sql();
            $this->_whereParams = $query->params();
        } elseif ($statements instanceof Query) {  // $statements是查询器的情况
            $this->_where = $statements->sql();
            $this->_whereParams = $statements->params();
        } else {  //直接传入SQL预处理语句的情况
            $this->_where = $statements;
            $this->_whereParams = $parse;
        }
        return $this;
    }

    /**
     * HAVING语句，支持链式调用
     * @param mixed $statements “QueryMysql对象”或者“查询数组”或者“WHERE子语句”，其中“WHERE子语句”支持原生的PDO问号预处理占位符;
     * @param array $parse 如果$statements是SQL预处理语句，则可以传递本参数用于预处理替换参数数组
     * @return $this
     */
    public function having($statements, array $parse = [])
    {
        if ($statements instanceof Query) {  // $statements是查询器的情况
            $this->_having = $statements->sql();
            $this->_havingParams = $statements->params();
        } else {  //直接传入SQL预处理语句的情况
            $this->_having = $statements;
            $this->_havingParams = $parse;
        }
        return $this;
    }
}