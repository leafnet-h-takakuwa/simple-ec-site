<?php

namespace App\Http\Controllers;

use App\Support\InputLimits;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'email' => ['required', 'email', 'max:' . InputLimits::SHORT_TEXT, 'unique:users,email,' . $request->user()->id],
            'postal_code' => ['nullable', 'string', 'max:' . InputLimits::POSTAL_CODE],
            'address' => ['nullable', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'phone' => ['nullable', 'string', 'max:' . InputLimits::PHONE],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('success', '会員情報を更新しました。');
    }
}
