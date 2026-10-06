<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class WidgetController extends Controller
{
    public function ticket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'website' => 'nullable|string|max:0',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt(Str::random(32)),
            ]);

            if (Role::where('name', 'customer')->exists()) {
                $user->assignRole('customer');
            }
            $user->forceFill(['role' => 'customer'])->saveQuietly();
        }

        $ticket = app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => substr($validated['subject'], 0, 255),
            'body' => $validated['message'],
            'department_id' => Department::where('is_active', true)->orderBy('id')->value('id')
                ?? abort(response()->json(['success' => false, 'message' => 'Support is not configured yet.', 'errors' => []], 422)),
            'priority' => 'medium',
            'source' => 'chat',
        ]);

        return response()->json([
            'success' => true,
            'data' => ['uid' => $ticket->uid],
            'message' => 'Message received. We will reply by email.',
        ], 201);
    }
}
