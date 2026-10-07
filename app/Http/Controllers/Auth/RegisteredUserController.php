<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppNotificationService;
use App\Services\WebhookService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $mode = Setting::get('registration.mode', 'open');

        abort_if($mode === 'closed', 403, __('Registration is closed. Please contact the administrator.'));

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $mode = Setting::get('registration.mode', 'open');

        abort_if($mode === 'closed', 403, __('Registration is closed. Please contact the administrator.'));

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $requiresApproval = $mode === 'approval';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => ! $requiresApproval,
        ]);

        if ($customerRole = Role::where('name', 'customer')->first()) {
            $user->assignRole($customerRole);
        }

        $user->forceFill(['role' => 'customer'])->saveQuietly();

        app(WebhookService::class)->dispatchGeneric('customer.created', [
            'customer' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ], 'customer-'.$user->id);

        event(new Registered($user));

        if ($requiresApproval) {
            $admins = User::role(['admin', 'super-admin'])->get();
            app(AppNotificationService::class)->notifyMany(
                $admins,
                'registration_approval',
                __('New registration pending approval'),
                $user->name.' ('.$user->email.')',
                route('admin.users.index', absolute: false)
            );

            return redirect()->route('login')->with('status', __('Your account is pending approval. Please wait for an administrator.'));
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
