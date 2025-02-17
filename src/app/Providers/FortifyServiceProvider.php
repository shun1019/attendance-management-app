<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->app->singleton(VerifyEmailViewResponse::class, fn() => new SimpleViewResponse(view('auth.verify-email')));
        Fortify::verifyEmailView(fn() => view('auth.verify-email'));

        Fortify::authenticateUsing(function (LoginRequest $request) {
            $validated = $request->validated();
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['ログイン情報が登録されていません。'],
                ]);
            }

            return $user;
        });

        RateLimiter::for('login', fn(Request $request) => Limit::perMinute(5)->by(
            Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip())
        ));

        RateLimiter::for('two-factor', fn(Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
