<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all()->map(function ($user) {
            $registrationMethod = 'email';
            if ($user->google_id) {
                $registrationMethod = 'Google';
            } elseif ($user->apple_id) {
                $registrationMethod = 'Apple';
            }
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'provider' => $user->provider,
                'registration_method' => $registrationMethod,
                'created_at' => $user->created_at,
            ];
        });

        return view('admin.users.index', compact('users'));
    }
}
