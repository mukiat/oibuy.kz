<?php

namespace App\Extensions;

use Illuminate\Http\Request;
use App\Repositories\Common\StrRepository;

trait AdvancedThrottlesLogins
{
    /**
     * The rate limiter instance.
     *
     * @var \App\Extensions\AdvancedRateLimiter
     */
    protected $limiter;

    /**
     * Determine if the user has too many failed login attempts.
     * 确定用户是否有太多失败的登录尝试
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request)
    {
        if (!config('auth.login_attempts.enable')) {
            return false;
        }

        $result = $this->limiter()->tooManyAttempts(
            $this->throttleKey($request),
            $this->maxAttempts(),
            $this->decayMinutes() * 60
        );

        return $result;
    }

    /**
     * Increment the login attempts for the user.
     * 如果登录尝试不成功，我们将增加尝试登录的次数，并将用户重定向回登录表单。当然，当这个用户超过他们的最大尝试次数时，他们将被锁定。
     *
     * @param \Illuminate\Http\Request $request
     * @return bool/string
     */
    protected function incrementLoginAttempts(Request $request)
    {
        if (!config('auth.login_attempts.enable')) {
            return false;
        }

        $this->limiter()->hit(
            $this->throttleKey($request),
            $this->decayMinutes() * 60,
            $this->errorLogData($request)
        );

//        if (config('app.debug')) {
//            $retriesLeft = $this->limiter()->retriesLeft($this->throttleKey($request), $this->maxAttempts());
//            Log::info('listener 剩余次数', ['retriesLeft' => $retriesLeft]);
//        }

        // 计算剩余次数
        $remaining = $this->calculateRemainingAttempts($request);

        // 格式化时间 显示多少分钟
        $formattedDecayMinutes = $this->decayMinutes() . trans('common.minute');

        return trans('user.login_attempts_increment', ['remaining' => $remaining + 1, 'formatted_decay_minutes' => $formattedDecayMinutes]);
    }

    /**
     * Redirect the user after determining they are locked out.
     * 在确定用户被锁定后，重定向用户。
     *
     * @param \Illuminate\Http\Request $request
     * @return bool/int
     */
    protected function sendLockoutResponse(Request $request)
    {
        if (!config('auth.login_attempts.enable')) {
            return false;
        }

        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

//        if (config('app.debug')) {
//            Log::info('5. 丢出访问次数限制异常，结束请求。剩余时间 lockout', ['seconds' => $seconds]);
//        }

        // 格式化友好时间 多少分钟多少秒后
        $formattedAddMinutes = $this->limiter()->formatForHumansTime($seconds, 'after');

        return trans('user.login_attempts_lockout', ['formatted_add_minutes' => $formattedAddMinutes]);
    }

    /**
     * Clear the login locks for the given user credentials.
     * 清除给定密钥的命中和锁定计时器
     *
     * @param \Illuminate\Http\Request $request
     * @return bool/void
     */
    protected function clearLoginAttempts(Request $request)
    {
        if (!config('auth.login_attempts.enable')) {
            return false;
        }

        $this->limiter()->clear($this->throttleKey($request));
    }

    /**
     * Get the number of retries left for the given key.
     * 计算剩余次数
     *
     * @param \Illuminate\Http\Request $request
     * @return array|bool|\Illuminate\Contracts\Translation\Translator|null|string
     */
    protected function calculateRemainingAttempts(Request $request)
    {
        if (!config('auth.login_attempts.enable')) {
            return false;
        }

        return $this->limiter()->retriesLeft($this->throttleKey($request), $this->maxAttempts(), $this->decayMinutes() * 60);
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return StrRepository::lower($request->input($this->request_username()) . '|' . $request->ip());
    }

    /**
     * log data
     * @param Request $request
     * @return array
     */
    protected function errorLogData(Request $request)
    {
        // 当前第几次登录失败
        $attempts = $this->limiter()->attempts($this->throttleKey($request), $this->decayMinutes() * 60) + 1;

        return [
            'user_name' => $request->input($this->request_username()),
            'user_id' => $request->input('user_id', 0),
            'admin_id' => $request->input('admin_id', 0),
            'store_user_id' => $request->input('store_user_id', 0),
            'ip_address' => $request->ip(),
            'create_time' => $this->limiter()->noTime(),
            'operation_note' => trans('user.login_fail') . '+' . $attempts,
            'user_agent' => $request->userAgent()
        ];
    }

    /**
     * Get the rate limiter instance.
     *
     * @return \App\Extensions\AdvancedRateLimiter
     */
    public function limiter()
    {
        return app(AdvancedRateLimiter::class);
    }

    /**
     * Get the login request username to be used by the controller.
     *
     * @return string
     */
    public function request_username()
    {
        return 'username';
    }

    /**
     * Get the maximum number of attempts to allow.
     *
     * @return int
     */
    public function maxAttempts()
    {
        $error_num = config('auth.login_attempts.error_num');
        return !empty($error_num) ? (int)$error_num : 5;
    }

    /**
     * Get the number of minutes to throttle for.
     *
     * @return int
     */
    public function decayMinutes()
    {
        $error_lock_minutes = config('auth.login_attempts.error_lock_minutes');
        return !empty($error_lock_minutes) ? (int)$error_lock_minutes : 1;
    }
}
