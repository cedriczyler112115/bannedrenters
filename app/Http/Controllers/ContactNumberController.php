<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactNumberController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if (filled($request->user()->contact_number)) {
            return redirect()->route($request->user()->isApproved() ? 'dashboard' : 'approval.pending');
        }

        return view('auth.contact-number');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_number' => ['required', 'string', 'min:7', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
        ], [
            'contact_number.regex' => 'Enter a valid contact number using digits, spaces, parentheses, +, or -.',
        ]);

        $request->user()->forceFill([
            'contact_number' => trim($validated['contact_number']),
        ])->save();

        return redirect()
            ->route($request->user()->isApproved() ? 'dashboard' : 'approval.pending')
            ->with('status', 'Your contact number has been saved.');
    }
}
