<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private TotpService $totp)
    {
    }

    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.manage');
        }

        if (! $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => Crypt::encryptString($this->totp->generateSecret()),
            ])->save();
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        $otpauth = $this->totp->otpAuthUrl($secret, $user->email, config('app.name', 'HelpDesk AI'));

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'qrUrl' => $this->totp->qrUrl($otpauth),
            'otpauth' => $otpauth,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);
        $user = $request->user();

        if (! $user->two_factor_secret) {
            return back()->withErrors(['code' => 'No 2FA secret found. Restart setup.']);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        if (! $this->totp->verify($secret, $request->code)) {
            throw ValidationException::withMessages(['code' => 'The code is invalid or expired.']);
        }

        $codes = $this->totp->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->put('2fa_passed', true);
        $request->session()->flash('two_factor_recovery_codes', $codes);

        return redirect()->route('two-factor.manage')->with('status', '2FA enabled successfully.');
    }

    public function manage(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->two_factor_confirmed_at, 404);

        return view('auth.two-factor.manage', [
            'recoveryCodes' => $request->session()->get('two_factor_recovery_codes'),
        ]);
    }

    public function regenerateRecovery(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->two_factor_confirmed_at, 404);

        $codes = $this->totp->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes)),
        ])->save();

        return back()->with('two_factor_recovery_codes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget('2fa_passed');

        return redirect()->route('profile.edit')->with('status', '2FA disabled.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (! Auth::check() || ! $request->user()->two_factor_confirmed_at) {
            return redirect()->route('login');
        }

        if ($request->session()->get('2fa_passed')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.two-factor.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);
        $user = $request->user();
        abort_unless($user && $user->two_factor_confirmed_at, 403);

        $code = preg_replace('/\s+/', '', $request->code);

        if (strlen($code) === 6 && ctype_digit($code)) {
            $secret = Crypt::decryptString($user->two_factor_secret);
            if ($this->totp->verify($secret, $code)) {
                $request->session()->put('2fa_passed', true);
                return redirect()->intended(route('dashboard', absolute: false));
            }
        } else {
            $stored = json_decode(Crypt::decryptString($user->two_factor_recovery_codes ?? Crypt::encryptString('[]')), true) ?: [];
            $normalized = strtolower($code);

            foreach ($stored as $i => $candidate) {
                if (hash_equals(strtolower($candidate), $normalized)) {
                    unset($stored[$i]);
                    $user->forceFill([
                        'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($stored))),
                    ])->save();

                    $request->session()->put('2fa_passed', true);
                    return redirect()->intended(route('dashboard', absolute: false));
                }
            }
        }

        throw ValidationException::withMessages(['code' => 'Invalid code. Try again.']);
    }
}
