<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

class FeedbackController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeedbackRequest $request): RedirectResponse
    {
        // 1. Get validated data (Type and Description)
        $validated = $request->validated();

        // 2. Auto-fill hidden fields
        $validated['user_id'] = $request->user()->id;
        $validated['priority'] = Feedback::PRIORITY_MEDIUM; // Default
        
        // Auto-detect technical info
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($request->userAgent());

        $validated['ip_address'] = $request->ip();
        
        // Truncate User-Agent to 100 characters (database column limit)
        $validated['browser'] = \Illuminate\Support\Str::limit($request->userAgent(), 97, '...');
        
        // Detect OS and Device
        $validated['os'] = $agent->platform();
        $validated['device'] = $agent->device();
        
        // Determine source
        if ($agent->isMobile()) {
            $validated['source'] = 'web_mobile';
        } elseif ($agent->isTablet()) {
            $validated['source'] = 'web_tablet';
        } else {
            $validated['source'] = 'web_desktop';
        }
        
        // 3. Create Feedback
        Feedback::create($validated);

        // 4. Redirect with flash message
        return back()->with('status', __('feedback.messages.submit_success'));
    }
}
