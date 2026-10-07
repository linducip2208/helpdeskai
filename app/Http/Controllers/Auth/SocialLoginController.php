<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class SocialLoginController extends Controller
{
    public static function enabled(): bool
    {
        return (bool) config('services.google.client_id')
            && (bool) config('services.google.client_secret');
    }

    public function redirect(): RedirectResponse
    {
        abort_unless(self::enabled(), 404, 'Social login is not configured.');

        /** @var \Laravel\Socialite\Two\AbstractProvider $google */
        $google = Socialite::driver('google');

        return $google
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        abort_unless(self::enabled(), 404, 'Social login is not configured.');

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $google */
            $google = Socialite::driver('google');
            $social = $google->stateless()->user();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        $email = strtolower((string) $social->getEmail());

        if ($email === '') {
            return redirect()->route('login')->withErrors(['email' => 'Google did not share an email address.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            if (Setting::get('registration.mode', 'open') !== 'open') {
                return redirect()->route('login')->withErrors(['email' => 'Registration is closed. Please contact the administrator.']);
            }

            $user = User::create([
                'name' => $social->getName() ?: explode('@', $email)[0],
                'email' => $email,
                'password' => bcrypt(str()->random(32)),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            if (Role::where('name', 'customer')->exists()) {
                $user->assignRole('customer');
            }
            $user->forceFill(['role' => 'customer'])->saveQuietly();
            $user = $user->fresh();
        }

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'Your account is pending approval or has been disabled.']);
        }

        Auth::login($user, true);
        $user->forceFill(['avatar' => $social->getAvatar()])->saveQuietly();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
