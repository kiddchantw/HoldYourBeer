<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center py-3 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 border-0 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
