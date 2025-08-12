<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add a new beer') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('beers.store') }}" id="beerForm">
                        @csrf
                        
                        <!-- Brand Name -->
                        <div class="mb-6">
                            <label for="brand_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Brand Name *
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="brand_name" 
                                    name="brand_name" 
                                    value="{{ old('brand_name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Start typing brand name..."
                                    autocomplete="off"
                                    required
                                >
                                <div id="brand-suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-48 overflow-y-auto">
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Type at least 2 characters to see suggestions</p>
                        </div>

                        <!-- Beer Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Beer Name / Series *
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter beer name or series..."
                                    autocomplete="off"
                                    required
                                >
                                <div id="beer-suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-48 overflow-y-auto">
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Select a brand first to see beer suggestions</p>
                        </div>

                        <!-- Beer Style -->
                        <div class="mb-6">
                            <label for="style" class="block text-sm font-medium text-gray-700 mb-2">
                                Beer Style (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="style" 
                                name="style" 
                                value="{{ old('style') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., IPA, Stout, Lager..."
                            >
                            <p class="mt-1 text-sm text-gray-500">Examples: IPA, Stout, Lager, Wheat Beer, etc.</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <button 
                                type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline"
                            >
                                Add Beer to Collection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Autocomplete -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const brandInput = document.getElementById('brand_name');
            const brandSuggestions = document.getElementById('brand-suggestions');
            const beerInput = document.getElementById('name');
            const beerSuggestions = document.getElementById('beer-suggestions');
            
            let selectedBrandId = null;
            let brandTimeout = null;
            let beerTimeout = null;

            // Brand autocomplete
            brandInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(brandTimeout);
                
                if (query.length < 2) {
                    brandSuggestions.classList.add('hidden');
                    selectedBrandId = null;
                    return;
                }

                brandTimeout = setTimeout(() => {
                    fetch(`{{ route('brands.suggestions') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            brandSuggestions.innerHTML = '';
                            
                            if (data.length > 0) {
                                data.forEach(brand => {
                                    const item = document.createElement('div');
                                    item.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
                                    item.textContent = brand.name;
                                    item.addEventListener('click', function() {
                                        brandInput.value = brand.name;
                                        selectedBrandId = brand.id;
                                        brandSuggestions.classList.add('hidden');
                                    });
                                    brandSuggestions.appendChild(item);
                                });
                                brandSuggestions.classList.remove('hidden');
                            } else {
                                brandSuggestions.classList.add('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching brand suggestions:', error);
                            brandSuggestions.classList.add('hidden');
                        });
                }, 300);
            });

            // Beer name autocomplete
            beerInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(beerTimeout);
                
                if (!selectedBrandId || query.length < 2) {
                    beerSuggestions.classList.add('hidden');
                    return;
                }

                beerTimeout = setTimeout(() => {
                    fetch(`{{ route('beers.suggestions') }}?brand_id=${selectedBrandId}&q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            beerSuggestions.innerHTML = '';
                            
                            if (data.length > 0) {
                                data.forEach(beer => {
                                    const item = document.createElement('div');
                                    item.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
                                    item.textContent = beer.name;
                                    item.addEventListener('click', function() {
                                        beerInput.value = beer.name;
                                        beerSuggestions.classList.add('hidden');
                                    });
                                    beerSuggestions.appendChild(item);
                                });
                                beerSuggestions.classList.remove('hidden');
                            } else {
                                beerSuggestions.classList.add('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching beer suggestions:', error);
                            beerSuggestions.classList.add('hidden');
                        });
                }, 300);
            });

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(event) {
                if (!brandInput.contains(event.target) && !brandSuggestions.contains(event.target)) {
                    brandSuggestions.classList.add('hidden');
                }
                if (!beerInput.contains(event.target) && !beerSuggestions.contains(event.target)) {
                    beerSuggestions.classList.add('hidden');
                }
            });

            // Reset selected brand when brand input changes
            brandInput.addEventListener('input', function() {
                // Only reset if the current value doesn't match any existing brand
                if (selectedBrandId && this.value !== this.dataset.selectedBrandName) {
                    selectedBrandId = null;
                    beerSuggestions.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>