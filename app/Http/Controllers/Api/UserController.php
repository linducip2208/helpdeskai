<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole(['admin', 'manager']), 403, 'You are not authorized to list users.');

        $users = User::with('roles')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->paginate(min((int) ($request->per_page ?? 25), 100));

        $users->getCollection()->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']);

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved.',
        ]);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless(
            $request->user()->hasRole(['admin', 'manager']) || $request->user()->id === $user->id,
            403,
            'You are not authorized to view this user.'
        );

        return response()->json([
            'success' => true,
            'data' => $user->load('roles')->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']),
            'message' => 'User retrieved.',
        ]);
    }
}
