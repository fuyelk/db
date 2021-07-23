<?php

namespace fuyelk\db;

use PDO;
use PDOException;

/**
 * Class Db
 * @package fuyelk\db
 * @author fuyelk <fuyelk@fuyelk.com>
 */
class Database
{

    /**
     * @var PDO
     */
    private $pdo = null;

    /**
     * @var array 数据库配置
     */
    protected $config = [
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
    ];

    /**
     * @var string 表名
     */
    private $table = '';

    /**
     * @var string 查询字段
     */
    private $field = '';

    /**
     * @var string 查询条件
     */
    private $where = "";

    /**
     * @var int|string 查询数量
     */
    private $limit = null;

    /**
     * @var string 排序条件
     */
    private $order = '';

    /**
     * 构造函数
     * @param array $config
     * @throws DbException
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 连接数据库
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function connection()
    {
        try {
            $this->pdo = new PDO("{$this->config['type']}:host={$this->config['host']};dbname={$this->config['database']};port={$this->config['port']};charset={$this->config['charset']}", $this->config['username'], $this->config['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new DbException('Database connection failure');
        }
    }

    /**
     * 设置数据库表
     * @param string $name
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function table(string $name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * 设置数据库表
     * @param string $name
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function name(string $name)
    {
        $this->table = $this->config['prefix'] . $name;
        return $this;
    }

    /**
     * 查询字段
     * @param string $field 字段
     * @return $this
     */
    public function field(string $field)
    {
        $this->field = $this->strMerge($this->field, $field);
        return $this;
    }

    /**
     * 数量
     * @param string|int $start
     * @param null $length
     * @return $this
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function limit($start, $length = null)
    {
        if (is_null($length)) {
            $this->limit = $start;
        } else {
            $this->limit = $start . ' , ' . $length;
        }
        return $this;
    }

    /**
     * 查询条件
     * @param array|mixed $where
     * @return $this
     */
    public function where($field, $option = '=', $condition = null)
    {
        $sql = [];
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $condOption = '=';
                $condValue = $value;

                if (is_null($condValue)) {
                    $condOption = 'IS';
                }
                if (is_array($condValue)) {
                    if (!array_key_exists(1, $value)) {
                        throw new DbException('where格式不正确');
                    }
                    $condOption = $value[0];
                    $condValue = $value[1];
                }

                if (is_string($condValue)) {
                    $condValue = "'" . $condValue . "'";
                }
                if (is_null($condValue)) {
                    $condValue = 'null';
                }
                $sql[] = "`{$key}` {$condOption} {$condValue}";
            }
        } else {
            $condOption = $option;
            $condValue = $condition;
            if (is_string($condition)) {
                $condValue = "'" . $condition . "'";
            }

            if (is_null($condition)) {
                $condOption = 'IS';
                $condValue = 'null';
                if ('IS' != strtoupper($option)) {
                    $condOption = '=';
                    $condValue = $option;
                    if (is_string($option)) {
                        $condValue = "'" . $option . "'";
                    }
                    if (is_null($option)) {
                        $condValue = 'null';
                    }
                }
            }

            if (is_null($option)) {
                $condOption = 'IS';
                $condValue = 'null';
            }
            $sql[] = "`{$field}` {$condOption} {$condValue}";

        }
        $this->where = $this->strMerge($this->where, implode(' AND ', $sql), ' AND ');
        return $this;
    }

    /**
     * 排序规则
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function order(string $field, string $order = null)
    {
        if (!is_null($order)) {
            $field .= ' ' . $order;
        }
        $this->order = $this->strMerge($this->order, $field);
        return $this;
    }

    /**
     * 构建Sql语句
     * @return string
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function buildSql()
    {
        if (empty($this->table)) {
            throw new DbException('No tables used');
        }

        $field = $this->field ? trim($this->field, ',') : '*';

        $sql = "SELECT {$field} FROM {$this->table}";

        if ($this->where) {
            $sql .= " WHERE  {$this->where}";
        }

        if ($this->order) {
            $sql .= " ORDER BY {$this->order}";
        }
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        return $sql;
    }

    /**
     * 查询多条数据
     * @return array
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function select()
    {
        if (empty($this->table)) {
            throw new DbException('No tables used');
        }

        $field = $this->field ? trim($this->field, ',') : '*';

        $sql = "SELECT {$field} FROM {$this->table}";

        if ($this->where) {
            $sql .= " WHERE  {$this->where}";
        }
        if ($this->order) {
            $sql .= " ORDER BY {$this->order}";
        }
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new DbException($e->getMessage());
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 查询一条数据
     * @return array
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function find()
    {
        if (empty($this->table)) {
            throw new DbException('No tables used');
        }

        $field = $this->field ? trim($this->field, ',') : '*';

        $sql = "SELECT {$field} FROM {$this->table}";

        if ($this->where) {
            $sql .= " WHERE  {$this->where}";
        }
        if ($this->order) {
            $sql .= " ORDER BY {$this->order}";
        }

        $sql .= " LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new DbException($e->getMessage());
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 获取一个值
     * @param string $field
     * @return mixed|null
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function value($field = '')
    {
        if (empty($this->field)) {
            $this->field = trim($field);
        }

        try {
            $value = $this->find();
        } catch (DbException $e) {
            throw new DbException($e->getMessage());
        }

        if (empty($value)) return null;

        if (array_key_exists($field, $value)) {
            return $value[$field];
        }

        return null;
    }

    /**
     * 获取指定字段的数组
     * @param string $field
     * @param null $key
     * @return array|false|null
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function column(string $field, $key = null)
    {
        $field = trim($field, ',');
        if (empty($this->field)) {
            $this->field = $field;
        }
        if (!is_null($key)) {
            $this->field .= ',' . $key;
        }

        try {
            $value = $this->select();
        } catch (DbException $e) {
            throw new DbException($e->getMessage());
        }

        if (empty($value)) return null;

        // 自定义键名
        if (!empty($key)) {

            // 一个键对应一个值
            if (false === strpos($field, ',')) {
                return array_combine(array_column($value, $key), array_column($value, $field));
            }

            // 一个键对应多个值

            $arrValues = []; // 记录每一组需要的值
            $arrKey = explode(',', $field);
            foreach ($value as $item) {
                $valueItem = [];
                foreach ($item as $k => $v) {
                    if (in_array($k, $arrKey)) {
                        $valueItem[$k] = $v;
                    }
                }
                $arrValues[] = $valueItem;
            }
            return array_combine(array_column($value, $key), $arrValues);
        }

        // 只取一个字段
        if (false === strpos($field, ',')) {
            return array_column($value, $field);
        }

        return $value;
    }

    /**
     * 查询分页数据
     * @param int $pagesize
     * @param int $page
     * @return array
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function paginate(int $pagesize, int $page)
    {
        if (empty($this->table)) {
            throw new DbException('No tables used');
        }

        $field = $this->field ? trim($this->field, ',') : '*';

        $sql = "SELECT {$field} FROM {$this->table}";

        if ($this->where) {
            $sql .= " WHERE  {$this->where}";
        }
        if ($this->order) {
            $sql .= " ORDER BY {$this->order}";
        }

        $limit = $pagesize * $page - $pagesize . ',' . $pagesize;
        $sql .= " LIMIT {$limit}";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new DbException($e->getMessage());
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 插入数据
     * @param array $data
     * @return bool
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function insert(array $data)
    {
        if (!isset($data[0])) {
            $data = [$data];
        }
        $sql = [];
        foreach ($data as $datum) {
            $fields = [];
            $values = [];

            foreach ($datum as $field => $value) {
                $fields[] = '`' . $field . '`';

                if (is_string($value)) {
                    $value = "'" . $value . "'";
                }
                if (is_null($value)) {
                    $value = 'null';
                }

                $values[] = $value;
            }
            $fields = implode(',', $fields);
            $values = implode(',', $values);
            $sql[] = "insert into {$this->table} ({$fields}) value ({$values})";
        }

        $count = 0;
        // 执行数据插入
        try {
            foreach ($sql as $item) {
                $this->pdo->exec($item);
                $count++;
            }
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
        return $count;
    }

    /**
     * 插入数据并返回最后一条数据ID,要求该表必须有int类型主键
     * @param array $data
     * @return int
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     * @date 2021/07/23 19:17
     */
    public function insertGetID(array $data)
    {
        if (!isset($data[0])) {
            $data = [$data];
        }
        $sql = [];
        foreach ($data as $datum) {
            $fields = [];
            $values = [];

            foreach ($datum as $field => $value) {
                $fields[] = '`' . $field . '`';

                if (is_string($value)) {
                    $value = "'" . $value . "'";
                }
                if (is_null($value)) {
                    $value = 'null';
                }

                $values[] = $value;
            }
            $fields = implode(',', $fields);
            $values = implode(',', $values);
            $sql[] = "insert into {$this->table} ({$fields}) value ({$values})";
        }

        // 执行数据插入
        try {
            foreach ($sql as $item) {
                $this->pdo->exec($item);
                $res = $this->pdo->lastInsertId();
            }
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
        return intval($res);
    }

    /**
     * 更新数据
     * @param array $data
     * @return bool
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     * @date 2021/07/23 19:30
     */
    public function update(array $data)
    {
        if (empty($this->where)) {
            throw new DbException('更新条件不能为空');
        }
        $updateSql = [];

        foreach ($data as $field => $value) {
            $fields = '`' . $field . '`';

            if (is_string($value)) {
                $value = "'" . $value . "'";
            }
            if (is_null($value)) {
                $value = 'null';
            }

            $updateSql[] = $fields . ' = ' . $value;
        }

        $updateSql = implode(',', $updateSql);

        try {
            $sql = "update {$this->table} set {$updateSql} where {$this->where}";
            $res = $this->pdo->exec($sql);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
        return $res;
    }

    /**
     * 删除数据
     * @return bool
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function delete()
    {
        if (empty($this->where)) {
            throw new DbException('删除条件不能为空');
        }

        try {
            $sql = "delete from {$this->table}  where {$this->where}";
            $res = $this->pdo->exec($sql);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
        return $res;
    }

    /**
     * 查询
     * @param string $query
     * @return false|\PDOStatement
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function query(string $query)
    {
        try {
            $res = $this->pdo->query($query);
        } catch (PDOException $e) {
            throw new DbException($e->getMessage());
        }
        return $res;
    }

    /**
     * 启动事务
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function startTrans()
    {
        try {
            $this->pdo->beginTransaction();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * 提交事务
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function commit()
    {
        try {
            $this->pdo->commit();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * 事务回滚
     * @throws DbException
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function rollback()
    {
        try {
            $this->pdo->rollBack();
        } catch (PDOException $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * 无重复合并字符串,如向'1,2,5,'中追加'3,4'
     * @param string $str1 字符串1
     * @param string $str2 字符串2
     * @param string $delimiter 分隔符
     * @return string
     * @author fuyelk <fuyelk@fuyelk.com>
     * @date 2021/3/18 13:23
     */
    private function strMerge($str1, $str2, $delimiter = ',')
    {
        $arrStr = $str1 ? explode($delimiter, trim($str1, $delimiter)) : [];
        $arrStr = array_unique($arrStr);
        if (!empty($str2)) {
            $arrStr2 = explode($delimiter, trim($str2, $delimiter));
            $arrStr2 = array_unique($arrStr2);
            $arrStr = array_unique(array_merge($arrStr, $arrStr2));
        }
        return trim(implode($delimiter, $arrStr), $delimiter);
    }
}