<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index() {
        return UserResource::collection(User::all());
    }

    public function update(Request $request, User $user){
        Gate::authorize('update-user', $user);
        $user->update($request->all());
        return new UserResource($user->fresh());
    }
}
