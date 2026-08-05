<?php

namespace App\Http\Controllers;

use App\Models\Banned;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class BannedController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'fullname' => ['nullable', 'string', 'max:150'],
        ]);

        $records = Banned::query()
            ->with('creator:id,name,email,contact_number,avatar')
            ->when(
                $filters['fullname'] ?? null,
                fn ($query, string $fullname) => $query->where('fullname', 'like', '%'.addcslashes($fullname, '%_\\').'%'),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->latest('date_created')
            ->paginate(10)
            ->withQueryString();

        $pendingApprovalCount = $request->user()->is_admin
            ? User::query()->whereNull('approved_at')->count()
            : 0;

        return view('dashboard', compact('records', 'filters', 'pendingApprovalCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $licensePath = $request->hasFile('license')
            ? $request->file('license')->store('licenses', 'public')
            : null;

        try {
            Banned::query()->create([
                'fullname' => trim($validated['fullname']),
                'address' => trim($validated['address']),
                'source' => 'NEW',
                'license' => $licensePath,
                'description' => trim($validated['description']),
                'created_by' => $request->user()->id,
                'date_created' => now(),
            ]);
        } catch (Throwable $exception) {
            if ($licensePath) {
                Storage::disk('public')->delete($licensePath);
            }
            throw $exception;
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Banned renter record added successfully.');
    }

    public function destroy(Banned $banned): RedirectResponse
    {
        Gate::authorize('delete', $banned);

        $licensePath = $banned->license;
        $banned->delete();

        if ($licensePath) {
            Storage::disk('public')->delete($licensePath);
        }

        return back()->with('status', 'Banned renter record deleted successfully.');
    }

    public function update(Request $request, Banned $banned): RedirectResponse
    {
        Gate::authorize('update', $banned);

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $oldLicensePath = $banned->license;
        $newLicensePath = $request->hasFile('license')
            ? $request->file('license')->store('licenses', 'public')
            : null;

        try {
            $banned->update([
                'fullname' => trim($validated['fullname']),
                'address' => trim($validated['address']),
                'license' => $newLicensePath ?: $oldLicensePath,
                'description' => trim($validated['description']),
            ]);
        } catch (Throwable $exception) {
            if ($newLicensePath) {
                Storage::disk('public')->delete($newLicensePath);
            }

            throw $exception;
        }

        if ($newLicensePath && $oldLicensePath) {
            Storage::disk('public')->delete($oldLicensePath);
        }

        return back()->with('status', 'Banned renter record updated successfully.');
    }
}
