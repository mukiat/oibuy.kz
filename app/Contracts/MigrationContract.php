<?php

namespace App\Contracts;

/**
 * Interface MigrationContract
 * @package App\Contracts
 */
interface MigrationContract
{
    /**
     * 执行入口
     * @return mixed
     */
    public function run();

    /**
     * 数据迁移
     * @return mixed
     */
    public function migration();

    /**
     * 数据填充
     * @return mixed
     */
    public function seed();

    /**
     * 更新版本
     * @return mixed
     */
    public function clean();
}
