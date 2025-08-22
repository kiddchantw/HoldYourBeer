<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center py-3 px-4 !bg-gradient-to-r !from-red-500 !to-red-600 hover:!from-red-600 hover:!to-red-700 border-0 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
