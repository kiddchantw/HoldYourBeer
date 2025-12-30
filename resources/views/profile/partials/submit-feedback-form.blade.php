<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Give Feedback
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Your feedback helps us improve. Please let us know your thoughts or any issues you encountered.
        </p>
    </header>

    <form method="post" action="{{ route('feedback.store', ['locale' => app()->getLocale()]) }}" class="mt-6 space-y-6">
        @csrf

        <!-- Type -->
        <div>
            <x-input-label for="feedback_type" value="Type" />
            <select id="feedback_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="feedback" {{ old('type') == 'feedback' ? 'selected' : '' }}>General Feedback</option>
                <option value="bug_report" {{ old('type') == 'bug_report' ? 'selected' : '' }}>Bug Report</option>
                <option value="feature_request" {{ old('type') == 'feature_request' ? 'selected' : '' }}>Feature Request</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('type')" />
        </div>

        <!-- Description -->
        <div>
            <x-input-label for="feedback_description" value="Description" />
            <textarea id="feedback_description" name="description" rows="4" 
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                placeholder="Please describe your issue or suggestion in detail...">{{ old('description') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Submit Feedback</x-primary-button>

            @if (session('status'))
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600"
                >{{ session('status') }}</p>
            @endif
        </div>
    </form>
</section>
