<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function pending(Request $request): View|RedirectResponse
    {
        if (blank($request->user()->contact_number)) {
            return redirect()->route('contact.edit');
        }

        if ($request->user()->isApproved()) {
            return redirect()->route('dashboard');
        }

        return view('auth.pending-approval');
    }

    public function index(): View
    {
        $users = User::query()
            ->whereNull('approved_at')
            ->oldest()
            ->paginate(15);

        return view('admin.approvals', compact('users'));
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        if (! $user->isApproved()) {
            $user->forceFill([
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ])->save();
        }

        return back()->with('status', "{$user->name}'s account has been approved.");
    }
}
