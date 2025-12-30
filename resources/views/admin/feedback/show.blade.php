<x-admin-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <a href="{{ route('admin.feedback.index', ['locale' => app()->getLocale()]) }}" class="text-sm text-blue-600 hover:underline mb-2 inline-block">
                                ← Back to list
                            </a>
                            <h2 class="text-xl font-semibold">Feedback Detail</h2>
                        </div>
                        <div class="flex space-x-2">
                            <!-- Status Badge -->
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $feedback->status_badge_color }}-100 text-{{ $feedback->status_badge_color }}-800">
                                {{ $feedback->status_label }}
                            </span>
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-sm">
                        <div>
                            <span class="text-gray-500">Type:</span>
                            <div class="font-medium">{{ $feedback->type_label }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Priority:</span>
                            <div class="font-medium">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $feedback->priority_badge_color }}-100 text-{{ $feedback->priority_badge_color }}-800">
                                    {{ $feedback->priority_label }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500">User:</span>
                            <div class="font-medium">{{ $feedback->display_name }}</div>
                            @if($feedback->user_id)
                                <div class="text-xs text-gray-400">{{ $feedback->user->email }}</div>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-500">Date:</span>
                            <div class="font-medium">{{ $feedback->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                        <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap text-gray-800">{{ $feedback->description }}</div>
                    </div>

                    <!-- Technical Info -->
                    <div class="mb-6 text-sm">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Technical Info</h3>
                        <div class="bg-gray-50 p-4 rounded-lg space-y-1 text-xs text-gray-600">
                            <div><strong>Source:</strong> 
                                @if(Str::contains($feedback->source, 'mobile'))
                                    📱 Mobile Web
                                @elseif(Str::contains($feedback->source, 'app'))
                                    📱 App
                                @elseif(Str::contains($feedback->source, 'web'))
                                    💻 Web
                                @else
                                    {{ $feedback->source }}
                                @endif
                            </div>
                            <div><strong>OS:</strong> {{ $feedback->os ?? 'N/A' }}</div>
                            <div><strong>Device:</strong> {{ $feedback->device ?? 'N/A' }}</div>
                            <div><strong>IP:</strong> {{ $feedback->ip_address ?? 'N/A' }}</div>
                            <div><strong>Browser:</strong> {{ $feedback->browser ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    @if($feedback->admin_notes)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Admin Notes</h3>
                        <div class="bg-yellow-50 p-4 rounded-lg text-gray-800">{{ $feedback->admin_notes }}</div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="border-t pt-6 flex space-x-4">
                        @if($feedback->status !== 'resolved')
                            <form action="{{ route('admin.feedback.update', ['feedback' => $feedback->id, 'locale' => app()->getLocale()]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm" onclick="return confirm('Mark as resolved?')">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark as Resolved
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded-md text-sm">
                                <svg class="h-4 w-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Resolved {{ $feedback->resolved_at ? $feedback->resolved_at->diffForHumans() : '' }}
                            </span>
                        @endif

                        <form action="{{ route('admin.feedback.destroy', ['feedback' => $feedback->id, 'locale' => app()->getLocale()]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm" onclick="return confirm('Are you sure you want to delete this feedback?')">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
