<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FastExcel Pro — Enterprise Data Import & Export' }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 font-sans antialiased text-slate-800">

    <!-- Top Navigation Bar -->
    <nav class="bg-indigo-900 border-b border-indigo-800 shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand Mark -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('products.index') }}" class="flex items-center space-x-2 text-white font-black text-xl tracking-tight">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-indigo-500 flex items-center justify-center shadow-md">
                            <i class="fa-solid fa-file-excel text-white text-lg"></i>
                        </div>
                        <span>FastExcel <span class="text-emerald-400 font-medium text-sm px-2 py-0.5 rounded-full bg-emerald-950/60 border border-emerald-500/30">Enterprise</span></span>
                    </a>
                </div>

                <!-- Nav Links -->
                <div class="flex items-center space-x-1 sm:space-x-3">
                    <a href="{{ route('products.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('products.index') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-200 hover:bg-indigo-800/60 hover:text-white' }}">
                        <i class="fa-solid fa-table-cells mr-1.5"></i> Dashboard
                    </a>

                    <a href="{{ route('products.import.form') }}"
                        class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('products.import*') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-200 hover:bg-indigo-800/60 hover:text-white' }}">
                        <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Smart Import
                    </a>

                    <a href="{{ route('files.history') }}"
                        class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('files.history*') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-200 hover:bg-indigo-800/60 hover:text-white' }}">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> History &amp; Rollback
                    </a>

                    <a href="{{ route('products.template') }}"
                        class="hidden md:inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-emerald-300 hover:bg-indigo-800/60 hover:text-emerald-200 transition">
                        <i class="fa-solid fa-download mr-1.5"></i> Template
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mb-6 flex items-start p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm transition">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-lg mr-3 flex-shrink-0"></i>
                <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 ml-3">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 flex items-start p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm transition">
                <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 text-lg mr-3 flex-shrink-0"></i>
                <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 ml-3">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="flex items-center font-bold text-sm mb-1.5 text-rose-900">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Please correct the following errors:
                </div>
                <ul class="list-disc list-inside text-sm space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-3">
            <div class="flex items-center space-x-2">
                <span class="font-semibold text-slate-700">Laravel 12 FastExcel Suite</span>
                <span>•</span>
                <span>High-Speed Queue Processing &amp; Smart Mapping</span>
            </div>
            <div>
                Built for 100k+ Large Datasets &amp; Instant Rollback
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
