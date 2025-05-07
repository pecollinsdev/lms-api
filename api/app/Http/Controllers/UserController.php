<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * List all users (paginated).
     * Only admins can view all users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::paginate(15);

        return $this->respond($users);
    }

    /**
     * Create a new user.
     * Only admins can create users.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $this->validated($request, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:student,instructor,admin',
            'bio'             => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|max:2048', // jpeg/png, max 2 MB
        ]);

        $data['password'] = Hash::make($data['password']);

        // Handle profile picture upload if present
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request
            ->file('profile_picture')
            ->store('profiles', 'public');
        }

        $user = User::create($data);

        return $this->respondCreated($user);
    }

    /**
     * Show a single user.
     * Users can view their own profile; admins can view any.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return $this->respond($user);
    }

    /**
     * Update a user's profile (or role if admin).
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'bio'             => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|max:2048', // jpeg/png, max 2 MB
        ];

        // Only admins may change roles
        if ($request->user()->isAdmin()) {
            $rules['role'] = 'in:student,instructor,admin';
        }

        $data = $this->validated($request, $rules);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Optionally delete old picture here...
            $data['profile_picture'] = $request
            ->file('profile_picture')
            ->store('profiles', 'public');
        } else {
        unset($data['profile_picture']);
        }

        $user->update($data);

        return $this->respond($user, 'User updated');
    }

    /**
     * Delete a user.
     * Admins can delete any user except themselves.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return $this->respond(null, 'User deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * Get the authenticated user's information.
     */
    public function me(Request $request)
    {
        return $this->respond($request->user());
    }
}
