<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <div class="hidden sm:ms-6 sm:flex sm:space-x-8">
                    <!-- Current: "border-indigo-500 text-gray-900", Default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700" -->
                    <x-nav-link href="{{ url('/mikrotik') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Sync Mikrotik') }}
                    </x-nav-link>
                    <x-nav-link href="{{ url('/address') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Address') }}
                    </x-nav-link>
                    <x-nav-link href="{{ url('/packages') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Packages') }}
                    </x-nav-link>
                    <x-nav-link href="{{ url('/create-customer') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Create Customer') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('customers.index') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Customers') }}
                    </x-nav-link>
                    <x-nav-link href="{{ url('/payment-collection') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Payment Collection') }}
                    </x-nav-link>
                </div>
            </div>
        </div>
</nav>
