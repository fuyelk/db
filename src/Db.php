<?php

namespace fuyelk\db;

use fuyelk\db\Database;

/**
 * Class Db
 * @package fuyelk\db
 * @method Database name(string $name) static 设置数据库表, 自动加前缀
 * @method Database table(string $name) static 设置数据库表
 * @method Database field(string $field) static 查询字段
 * @method Database limit($start, $length = null) static 数量
 * @method Database where($where) static 查询条件
 * @method Database order(string $field, string $order = null) static 排序规则
 * @method Database buildSql() static 构建Sql语句
 * @method Database select() static 查询多条数据
 * @method Database find() static 查询一条数据
 * @method Database value($field = '') static 获取一个值
 * @method Database column(string $field, $key = null) static 获取指定字段的数组
 * @method Database paginate(int $pagesize, int $page) static 查询分页数据
 * @method Database insert(array $data) static 插入数据
 * @method Database insertGetID(array $data) 插入数据并返回最后一条数据ID, static 要求该表必须有int类型主键
 * @method Database update(array $data) static 更新数据
 * @method Database delete() static 删除数据
 * @method Database query(string $query) static 查询
 * @method Database startTrans() static 启动事务
 * @method Database commit() static 提交事务
 * @method Database rollback() static 事务回滚
 * @method Database writeLog($log, string $name = '') static 写日志
 * @method Database getLog(string $logid) static 获取日志
 * @author fuyelk <fuyelk@fuyelk.com>
 */
class Db
{

    /**
     * @var Database 数据库连接实例
     */
    private static $instance = null;

    /**
     * @var array 数据库配置
     */
    private static $config = [];

    /**
     * 设置数据库信息
     * @param array $config ['type','host','database','username','password','port','prefix']
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
    }

    /**
     * 获取数据库配置
     * @param string $name 配置名
     * @return array|mixed|null
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public static function getConfig(string $name = '')
    {
        if (!empty(self::$config)) {
            if ($name) {
                if (!array_key_exists($name, self::$config)) {
                    return null;
                }
                return self::$config[$name];
            }
            return self::$config;
        }

        $configPath = __DIR__ . '/Config.php';
        if (!is_file($configPath)) {
            $configContent = "<?php

namespace fuyelk\db;

return [
    // 数据库类型
    'type' => 'mysql',
    // 服务器地址
    'host' => '127.0.0.1',
    // 数据库名
    'database' => 'test',
    // 用户名
    'username' => 'root',
    // 密码
    'password' => 'root',
    // 端口
    'port' => '3306',
    // 数据库编码默认采用utf8
    'charset' => 'utf8',
    // 数据库表前缀
    'prefix' => '',
];";
            file_put_contents($configPath, $configContent);
            throw new DbException('数据库未配置');
        }

        if ($name) {
            if (!array_key_exists($name, self::$config)) {
                return null;
            }
            return self::$config[$name];
        }
        return require_once $configPath;
    }

    /**
     * 初始化连接
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    private static function init()
    {
        if (is_null(self::$instance)) {
            try {
                self::$instance = new Database(self::getConfig());
            } catch (DbException $e) {
                throw new DbException($e->getMessage());
            }
            self::$instance->connection();
        }
        return self::$instance;
    }

    /**
     * 静态调用数据库类
     * @param $name
     * @param $arguments
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::init(), $name], $arguments);
    }

}