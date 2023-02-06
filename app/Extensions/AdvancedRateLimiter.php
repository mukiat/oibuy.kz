<?php

namespace App\Extensions;

use App\Repositories\User\UsersErrorLogRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\InteractsWithTime;

class AdvancedRateLimiter
{
    use InteractsWithTime;

    protected $driver = 'database'; // 数据库缓存驱动

    /**
     * @var UsersErrorLogRepository
     */
    protected $usersErrorLogRepository;

    public function __construct(
        UsersErrorLogRepository $usersErrorLogRepository
    )
    {
        $this->usersErrorLogRepository = $usersErrorLogRepository;

        $this->driver = config('cache.default', 'database');
        $this->driver = $this->driver == 'file' ? 'database' : $this->driver;
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return bool
     */
    public function tooManyAttempts($key, $maxAttempts, $decaySeconds = 60)
    {
        // 获取该请求已登录失败次数
        $attempts = (int)$this->attempts($key, $decaySeconds);

//        if (config('app.debug')) {
//            Log::info('1. 获取唯一请求来源，进行唯一标识', ['key' => $key]);
//            Log::info('2. 获取该请求已登录失败次数', ['attempts' => $attempts]);
//            Log::info('3. 判断是否超过最大限制', ['maxAttempts' => $maxAttempts]);
//        }

        if ($attempts >= $maxAttempts) {

//            Log::info('4. 若达到上限，进入5');

            if (Cache::store($this->driver)->has($key . ':timer')) {
                $locked = Cache::store($this->driver)->add($key . ':lock', 1, $decaySeconds);

                // 判断是否锁定
                if ($locked && Cache::store($this->driver)->has($key . ':lock')) {
//                    Log::info('5. 丢出访问次数限制异常，结束请求，并增加锁定时间');
                    $this->updecaySeconds($key, $decaySeconds);

                    // 更新过期的锁定时间请求
                    $expired_time = Carbon::now()->subSeconds($decaySeconds)->getTimestamp();
                    $this->usersErrorLogRepository->updateExpired($this->user_name($key), $expired_time);
                }

                return true;
            }

            Log::info('已过锁定时间, 重置已请求次数，可再次请求', ['current' => $this->currentTime()]);

            Cache::store($this->driver)->forget($key . ':lock');

            $this->resetAttempts($key);
        }

        // 锁定时间内 返回剩余时间
        if (Cache::store($this->driver)->has($key . ':lock')) {
            return true;
        }

//        Log::info('4. 未达到上限，则进入6');

        return false;
    }

    /**
     * Update the counter for a given key of the decay time
     * 增加锁定时间
     * @param string $key
     * @param int $decaySeconds
     */
    public function updecaySeconds($key, $decaySeconds = 60)
    {
        $availableAt = $this->availableAt($decaySeconds);

//        if (config('app.debug')) {
//            $string = Carbon::createFromTimestamp($availableAt)->toDateTimeString();
//            Log::info('5. 增加锁定时间 updecaySeconds', ['availableAtString' => $string, 'availableAt' => $availableAt, 'current' => $this->currentTime()]);
//        }

        Cache::store($this->driver)->put($key . ':timer', $availableAt, $decaySeconds);
    }

    /**
     * Increment the counter for a given key for a given decay time.
     *
     * @param string $key
     * @param int $decaySeconds
     * @param array $errorLogData
     * @return int
     */
    public function hit($key, $decaySeconds = 60, $errorLogData = [])
    {
//        Log::info('6. 开始');

        $availableAt = $this->availableAt($decaySeconds);

        Cache::store($this->driver)->add(
            $key . ':timer',
            $availableAt,
            $decaySeconds
        );

        // 增加登录失败次数统计
        if (!empty($errorLogData)) {
            $log_id = $this->usersErrorLogRepository->insertGetId($errorLogData);

            // 过期时间内
            $expired_time = Carbon::now()->subSeconds($decaySeconds)->getTimestamp();
            $hits = $this->usersErrorLogRepository->count($this->user_name($key), $expired_time);

            //Log::info('7. hits 进行计数 + 1，更新。 ', ['log_id' => $log_id, 'hits' => $hits, 'availableAt' => $availableAt, 'current' => $this->currentTime()]);

            return $hits;
        }

        $added = Cache::store($this->driver)->add($key, 0, $decaySeconds);

        $hits = (int)Cache::store($this->driver)->increment($key);

//        if (config('app.debug')) {
//            $string = Carbon::createFromTimestamp($availableAt)->toDateTimeString();
//            Log::info('7. hits 进行计数 + 1，更新。 ', ['added' => $added, 'hits' => $hits, 'availableAtString' => $string, 'availableAt' => $availableAt, 'current' => $this->currentTime()]);
//        }

        if (!$added && $hits == 1) {
//            Log::info('7. 若是第一次，则需要 hits = 1（次数）, 并添加访问标识 key 到缓存中，以标记请求周期。', ['decaySeconds' => $decaySeconds]);
            Cache::store($this->driver)->put($key, 1, $decaySeconds);
        }

        return $hits;
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @param string $key
     * @param int $decaySeconds
     * @return mixed
     */
    public function attempts($key, $decaySeconds = 60)
    {
        $user_name = $this->user_name($key);

        if (!empty($user_name)) {
            // 过期时间内
            $expired_time = Carbon::now()->subSeconds($decaySeconds)->getTimestamp();
            return (int)$this->usersErrorLogRepository->count($user_name, $expired_time);
        }

        return (int)Cache::store($this->driver)->get($key, 0);
    }

    /**
     * Reset the number of attempts for the given key.
     *
     * @param string $key
     * @return mixed
     */
    public function resetAttempts($key)
    {
        // 修改登录日志 expired 时间过期
        $user_name = $this->user_name($key);

        if (!empty($user_name)) {
            return $this->usersErrorLogRepository->updateExpired($user_name, $this->currentTime());
        }

        return Cache::store($this->driver)->forget($key);
    }

    /**
     * Get the number of retries left for the given key.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return int
     */
    public function retriesLeft($key, $maxAttempts, $decaySeconds = 60)
    {
        $attempts = (int)$this->attempts($key, $decaySeconds);

        return $maxAttempts >= $attempts ? $maxAttempts - $attempts : 0;
    }

    /**
     * Clear the hits and lockout timer for the given key.
     *
     * @param string $key
     * @return void
     */
    public function clear($key)
    {
        Cache::store($this->driver)->forget($key . ':lock');

        $this->resetAttempts($key);

        Cache::store($this->driver)->forget($key . ':timer');
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
     *
     * @param string $key
     * @return int
     */
    public function availableIn($key)
    {
        $timer = (int)Cache::store($this->driver)->get($key . ':timer');

//        if (config('app.debug')) {
//            $timerstring = Carbon::createFromTimestamp($timer)->toDateTimeString();
//
//            Log::info('返回剩余时间 availableIn', ['timer' => $timer, 'timerstring' => $timerstring, 'current' => $this->currentTime()]);
//        }

        return $timer >= $this->currentTime() ? $timer - $this->currentTime() : 0;
    }

    /**
     * user_name
     * @param $key
     * @return string
     */
    public function user_name($key)
    {
        list($user_name, $ip) = explode('|', $key);

        return $user_name ?? '';
    }

    /**
     * 当前时间戳
     * @return int
     */
    public function noTime()
    {
        return $this->currentTime();
    }

    /**
     * 传入秒数 显示为几分钟
     * @param int $seconds
     * @return int
     */
    public function formatTimeMinutes($seconds = 60)
    {
        return Carbon::now()->addSeconds($seconds)->diffInMinutes() . trans('common.minute');
    }

    /**
     * Shows the time difference of human's easy to read
     * 显示人类容易阅读的时间差 例如：3分钟后、3分钟
     * @link https://carbon.nesbot.com/docs/
     * @param int $seconds
     * @param string $after
     * @return string
     */
    public function formatForHumansTime($seconds = 60, $after = '')
    {
        if ($after == 'after') {
            return Carbon::now()->addSeconds($seconds)->diffForHumans(Carbon::now(), null, true, 2);
        }

        return Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);
    }
}
