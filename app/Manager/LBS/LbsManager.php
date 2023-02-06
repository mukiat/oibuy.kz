<?php

namespace App\Manager\LBS;

use App\Manager\LBS\Contract\FactoryContract;
use App\Manager\LBS\Contract\LbsContract;
use App\Manager\LBS\Contract\RepositoryContract;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class LbsManager
 * @package App\Manager\LBS
 */
class LbsManager implements FactoryContract
{
    /**
     * The array of resolved cache drivers.
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Get a lbs driver instance.
     *
     * @param string|null $name
     * @return RepositoryContract
     */
    public function driver($name = null): RepositoryContract
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] = $this->get($name);
    }

    /**
     * Attempt to get the driver from the local cache.
     *
     * @param string $name
     * @return RepositoryContract
     */
    protected function get(string $name): RepositoryContract
    {
        return $this->drivers[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given driver.
     *
     * @param string $name
     * @return RepositoryContract
     *
     * @throws InvalidArgumentException
     */
    protected function resolve(string $name): RepositoryContract
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("lbs driver [{$name}] is not defined.");
        }

        $driverClass = __NAMESPACE__ . '\\Driver\\' . Str::studly($config['driver']);
        if (class_exists($driverClass)) {
            return $this->repository(new $driverClass($config));
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Create a new lbs repository with the given implementation.
     *
     * @param LbsContract $driver
     * @return Repository
     */
    public function repository(LbsContract $driver): Repository
    {
        return new Repository($driver);
    }

    /**
     * Get the lbs configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig(string $name): array
    {
        return config("services.lbs.{$name}");
    }

    /**
     * Get the default lbs driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('services.lbs.default');
    }

    /**
     * Set the default lbs driver name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultDriver(string $name)
    {
        config(['services.lbs.default' => $name]);
    }

    /**
     * Unset the given driver instances.
     *
     * @param array|string|null $name
     * @return $this
     */
    public function forgetDriver($name = null): LbsManager
    {
        $name = $name ?? $this->getDefaultDriver();

        foreach ((array)$name as $cacheName) {
            if (isset($this->drivers[$cacheName])) {
                unset($this->drivers[$cacheName]);
            }
        }

        return $this;
    }

    /**
     * Disconnect the given driver and remove from local.
     *
     * @param string|null $name
     * @return void
     */
    public function purge($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();

        unset($this->drivers[$name]);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
