<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use Concerns\DetectsImpersonation;

    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = $this->getEffectiveUser();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information (and optionally password)
     */
    public function update(Request $request)
    {
        $user = $this->getEffectiveUser();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:' . $user->getTable() . ',email,' . $user->id,
            'wa' => 'nullable|string|max:20',
            'hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
            'current_password' => 'nullable|required_with:new_password,new_password_confirmation',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
        ];

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                if (Storage::disk('uploads')->exists($user->foto)) {
                    Storage::disk('uploads')->delete($user->foto);
                }
            }

            $folder = match ($user->getTable()) {
                'dosen' => 'foto-dosen',
                'mahasiswa' => 'foto-mahasiswa',
                'admins' => 'foto-admin',
                default => 'profile-photos',
            };

            $data['foto'] = $request->file('foto')->store($folder, 'uploads');
        }

        // If user filled new password fields, verify and update password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }

            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the change password form
     */
    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $user = $this->getEffectiveUser();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        // Update the password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Delete the user's profile photo
     */
    public function destroyFoto()
    {
        $user = $this->getEffectiveUser();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->foto) {
            if (\Illuminate\Support\Facades\Storage::disk('uploads')->exists($user->foto)) {
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($user->foto);
            }
            $user->update(['foto' => null]);
        }

        return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
    }

}
