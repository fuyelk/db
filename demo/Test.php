<?php

namespace fuyelk\demo;
require __DIR__ . '/../src/Db.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/DbException.php';

use fuyelk\db\DbException;
use fuyelk\db\Db;

Db::setConfig([
    'database' => 'test',
    'username' => 'root',
    'password' => 'root',
    'prefix' => 'tb_'
]);

// +---------------------------
// | 查询数据
// +---------------------------
$where = [
    'level' => ['>', 8],
    'mobile' => null,
    'gender' => '男',
    'wechat' => ['is', null],
];

try {
    $buildSql = Db::name('box')
        ->field('id,box_name name')
        ->field('create_time')
        ->where($where)
        ->where('level', '>', 8)
        ->where('delete_time', null)
        ->where('gender', '男')
        ->where('wechat', 'is', null)
        ->order('id desc')
        ->order('create_time desc')
        ->buildSql(); // SELECT id,box_name name,create_time FROM tb_box WHERE  `level` > 8 AND `mobile` IS null AND `gender` = '男' AND `wechat` is null AND `delete_time` IS null AND `wechat` IS null ORDER BY id desc,create_time desc
//        ->paginate(2,2);
//        ->select();
//        ->find();
//        ->value('update_time');
//        ->column('update_time,create_time','');
}catch (DbException $e) {
    var_dump($e->getMessage());
    exit();
}

var_dump(['buildSql' => $buildSql]);


// +---------------------------
// | 创建数据
// +---------------------------
$data = [
    [
        'name' => 'zs',
        'gender' => '男',
        'age' => 20,
        'mobile' => '666666'
    ],
    [
        'name' => 'ls',
        'gender' => '男',
        'age' => 21,
        'mobile' => '111'
    ],
];
$create = Db::name('test')->insert($data);
var_dump(['create' => $create]);


// +---------------------------
// | 事务
// +---------------------------
Db::startTrans();
try {
    Db::name('test')->insert($data);
    Db::name('test')->insert(['id' => 1, 'name' => date('Y-m-d H:i:s')]);
} catch (DbException $e) {
    Db::rollback();
    var_dump($e->getMessage());
    exit();
}
Db::commit();
echo "提交成功";


// +---------------------------
// | 修改数据
// +---------------------------
$res = Db::name('test')->where('id', '<>', 1)->update($data[1]);
$res = Db::name('test')->where('id', 4)->delete();


// +---------------------------
// | 手动查询
// +---------------------------
$res = Db::query('select * from tb_test');
foreach ($res as $re) {
    var_dump($re);
}

