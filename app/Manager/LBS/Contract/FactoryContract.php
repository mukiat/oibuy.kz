<?php

namespace App\Manager\LBS\Contract;

/**
 * Interface FactoryContract
 * @package App\Manager\LBS\Contract
 */
interface FactoryContract
{
    /**
     * Get a lbs driver instance by name.
     *
     * @param string|null $name
     * @return RepositoryContract
     */
    public function driver($name = null): RepositoryContract;
}
