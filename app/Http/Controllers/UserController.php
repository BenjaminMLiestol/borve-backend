<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->query('user_id');

        if ($user_id !== null) {
            $user = User::where('user_id', $user_id)->first();

            if ($user !== null && $user->deleted_at === null) {
                return response()->json($this->buildUserResponse($user), 200);
            } else {
                return response()->json(['error' => 'User not found'], 404);
            }
        } else {
            $userList = $userList = User::all(['user_id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at']);

            return response()->json($userList, 200);
        }
    }
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'password' => 'required|string',
        ]);

        // Check if the email already exists
        $existingUser = User::where('email', $validatedData['email'])->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => ['This email already exists'],
            ])->status(400);
        }

        // Hash the password
        $hashedPassword = Hash::make($validatedData['password']);

        // Create a new user
        $newUser = new User([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'user_id' => Str::slug(Str::random(15)),
            'password' => $hashedPassword,
        ]);

        try {
            $newUser->save();
            return response()->json(['message' => 'User created successfully', 'data' => $newUser], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $userId)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $user = User::where('user_id', $userId)->first();

        if ($user) {
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];

            try {
                $user->save();
                return response()->json($this->buildUserResponse($user), 200);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function destroy(Request $request)
    {
        $user_id = $request->query('user_id');

        $user = User::find($user_id);

        if ($user) {
            try {
                $user->delete();
                return response()->json(['message' => 'User deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    private function buildUserResponse(User $user)
    {
        return [
            'id' => $user->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
