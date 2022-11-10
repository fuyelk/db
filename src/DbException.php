<?php

namespace fuyelk\db;

use Exception;

class DbException extends Exception
{

    /**
     * @var string PDO异常代码
     */
    private $pdo_error_code = '';

    /**
     * @var string SQL异常代码
     */
    private $sql_error_code = 0;

    /**
     * @var string SQL异常信息
     */
    private $sql_error_message = '';

    /**
     * DbException constructor.
     * @param string $message 异常消息
     * @param int $code 异常代码
     * @param array $errorInfo 异常信息
     */
    public function __construct($message = "", $code = 0, array $errorInfo = [])
    {
        if (!empty($errorInfo)) {
            $this->pdo_error_code = intval($errorInfo[0] ?? 0);
            $this->sql_error_code = intval($errorInfo[1] ?? 0);
            $this->sql_error_message = intval($errorInfo[2] ?? 0);
        }
        parent::__construct($message, $this->sql_error_code);
    }

    /**
     * 获取PDO异常代码
     * @return string
     */
    public function getPdoErrorCode(): string
    {
        return $this->pdo_error_code;
    }

    /**
     * 获取SQL异常代码
     * @return string
     */
    public function getSqlErrorCode(): string
    {
        return $this->sql_error_code;
    }

    /**
     * 获取SQL异常信息
     * @return string
     */
    public function getSqlErrorMessage(): string
    {
        return $this->sql_error_message;
    }
}