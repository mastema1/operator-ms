<nav x-data="{ open: false }" class="bg-gray-900 border-b border-gray-800 text-white">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('Logo.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-8 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:flex">
                    <a href="{{ route('operators.index') }}" class="{{ request()->routeIs('operators.index') ? 'text-white font-semibold' : 'text-gray-300 hover:text-white' }}">Operator Management</a>
                    <a href="{{ route('postes.index') }}" class="{{ request()->routeIs('postes.*') ? 'text-white font-semibold' : 'text-gray-300 hover:text-white' }}">Postes Management</a>
                    <a href="{{ route('absences.index') }}" class="{{ request()->routeIs('absences.index') ? 'text-white font-semibold' : 'text-gray-300 hover:text-white' }}">Absence Management</a>
                    <a href="{{ route('post-status.index') }}" class="{{ request()->routeIs('post-status.index') ? 'text-white font-semibold' : 'text-gray-300 hover:text-white' }}">State of Post</a>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-white font-semibold' : 'text-gray-300 hover:text-white' }}">Dashboard</a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm leading-4 rounded-md text-white bg-gray-800 hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150 border border-gray-700">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-gray-900 text-white">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('operators.index')" :active="request()->routeIs('operators.index')">
                Operator Management
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('postes.index')" :active="request()->routeIs('postes.*')">
                Postes Management
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('absences.index')" :active="request()->routeIs('absences.index')">
                Absence Management
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('post-status.index')" :active="request()->routeIs('post-status.index')">
                State of Post
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-800">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-300">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
