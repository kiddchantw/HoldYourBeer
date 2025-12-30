<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $feedbacks = Feedback::with('user')
            ->orderByPriority() // Custom scope
            ->latest()
            ->paginate(20);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request): View
    {
        $feedback = Feedback::with('user')->findOrFail($request->route('feedback'));
        return view('admin.feedback.show', compact('feedback'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $feedback = Feedback::findOrFail($request->route('feedback'));
        
        // Simple update logic for status
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Feedback::getStatuses()),
            'admin_notes' => 'nullable|string',
            'priority' => 'nullable|in:' . implode(',', Feedback::getPriorities()),
        ]);

        // Specific logic if resolving
        if ($validated['status'] === Feedback::STATUS_RESOLVED && $feedback->status !== Feedback::STATUS_RESOLVED) {
            $feedback->markAsResolved($validated['admin_notes'] ?? null);
        } else {
            $feedback->update($validated);
        }

        return back()->with('success', __('feedback.messages.update_success'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $feedback = Feedback::findOrFail($request->route('feedback'));
        $feedback->delete();
        return redirect()->route('admin.feedback.index', ['locale' => app()->getLocale()])
            ->with('success', __('feedback.messages.delete_success'));
    }
}
