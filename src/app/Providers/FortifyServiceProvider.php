<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\Laravel\Fortify\Http\Requests\LoginRequest::class, LoginRequest::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->app->singleton(VerifyEmailViewResponse::class, fn() => new SimpleViewResponse(view('auth.verify-email')));
        Fortify::verifyEmailView(fn() => view('auth.verify-email'));

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません。'],
                ]);
            }

            if ($request->is('admin/*') && $user->role !== 1) {
                throw ValidationException::withMessages([
                    'email' => ['管理者権限がありません。'],
                ]);
            }

            return $user;
        });

        Fortify::redirects('login', function () {
            return Auth::user()->role === 1
                ? route('admin.attendance.index')
                : route('attendance.index');
        });

        Fortify::redirects('logout', function () {
            return Auth::user()->role === 1
                ? route('admin.login')
                : route('login');
        });

        RateLimiter::for('login', fn(Request $request) => Limit::perMinute(5)->by(
            Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip())
        ));

        RateLimiter::for('two-factor', fn(Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
