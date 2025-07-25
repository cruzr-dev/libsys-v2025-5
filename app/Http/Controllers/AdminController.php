<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admins.index', ['admins' => $admins]);
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(Request $request) : RedirectResponse
    {

        $adminRole = Role::where('name', 'admin')->firstOrFail();

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'first-name' => 'required|string|max:50',
            'last-name' => 'required|string|max:50',
            'middle-name' => 'required|string|max:50',
            'birth-date' => 'required|date|before:today',
            'username' => 'required|string|max:20|unique:users,username',
            'email' => 'required|string|email|max:50|unique:users,email',
            'contact-number' => 'required|unique:users,contact_number',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create the admin
            $admin = User::create([
                'first_name' => $request->input('first-name'),
                'last_name' => $request->input('last-name'),
                'middle_name' => $request->input('middle-name'),
                'birth_date' => $request->input('birth-date'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'contact_number' => $request->input('contact-number'),
                'role_id' => $adminRole->id,
                'password' => Hash::make($request->input('password')),
            ]);

            // Redirect with success message
            return redirect()->route('admins.index')
                ->with('success', 'Admin created successfully!');

        } catch (\Exception $e) {
            // Handle any errors during creation
            return redirect()->back()
                ->with('error', 'Failed to create admin. Please try again.')
                ->withInput();
        }
    }

    public function show(User $admin)
    {
        return view('admins.show', ['admin' => $admin]);
    }

    public function edit(User $admin)
    {
        return view('admins.edit', ['admin' => $admin]);
    }

    public function update(Request $request, User $admin) : RedirectResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'first-name' => 'required|string|max:50',
            'last-name' => 'required|string|max:50',
            'middle-name' => 'required|string|max:50',
            'birth-date' => 'required|date|before:today',
            'username' => 'required|string|max:255|unique:users,username,' . $admin->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Prepare update data
            $updateData = [
                'first_name' => $request->input('first-name'),
                'last_name' => $request->input('last-name'),
                'middle_name' => $request->input('middle-name'),
                'birth_date' => $request->input('birth-date'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
            ];


            // Update the admin
            $admin->update($updateData);

            // Redirect with success message
            return redirect()->route('admins.index')
                ->with('success', 'Admin updated successfully!');

        } catch (\Exception $e) {
            // Handle any errors during update
            return redirect()->back()
                ->with('error', 'Failed to update admin. Please try again.')
                ->withInput();
        }
    }

    public function destroy(User $admin): RedirectResponse
    {
        try {
            // If using soft deletes, this will soft delete
            $admin->delete();

            return redirect()->route('admins.index')
                ->with('success', 'Admin deactivated successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admins.index')
                ->with('error', 'Failed to deactivate admin.');
        }
    }
}
