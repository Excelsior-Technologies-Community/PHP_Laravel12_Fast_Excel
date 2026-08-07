<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laravel FastExcel Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen flex flex-col bg-gray-50">

    <!-- Navbar -->

    <nav class="bg-indigo-700 shadow-lg">

        <div class="max-w-7xl mx-auto px-6">

            <div class="flex justify-between items-center h-16">

                <div>

                    <a href="{{ route('products.index') }}"
                        class="text-white text-2xl font-bold">

                        Laravel FastExcel

                    </a>

                </div>

                <div class="flex space-x-3">

                    <a href="{{ route('products.index') }}"
                        class="text-white hover:bg-indigo-600 px-4 py-2 rounded">

                        Dashboard

                    </a>

                    <a href="{{ route('products.import.form') }}"
                        class="text-white hover:bg-indigo-600 px-4 py-2 rounded">

                        Import

                    </a>

                    <a href="{{ route('products.export') }}"
                        class="text-white hover:bg-indigo-600 px-4 py-2 rounded">

                        Export

                    </a>

                    <a href="{{ route('files.history') }}"
                        class="text-white hover:bg-indigo-600 px-4 py-2 rounded">

                        File History

                    </a>

                </div>

            </div>

        </div>

    </nav>


    <!-- Main Container -->

    <main class="flex-1">

        <div class="max-w-7xl mx-auto py-8 px-4">

            <!-- Success Message -->

            @if(session('success'))

                <div class="bg-green-100 border border-green-400 text-green-700 px-5 py-3 rounded-lg mb-5">

                    {{ session('success') }}

                </div>

            @endif


            <!-- Error Message -->

            @if(session('error'))

                <div class="bg-red-100 border border-red-400 text-red-700 px-5 py-3 rounded-lg mb-5">

                    {{ session('error') }}

                </div>

            @endif


            <!-- Import Errors -->

            @if(session('errors'))

                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-5 py-3 rounded-lg mb-5">

                    <h3 class="font-bold mb-2">
                        Import Errors
                    </h3>

                    <ul class="list-disc list-inside">

                        @foreach(session('errors') as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- Validation Errors -->

            @if($errors->any())

                <div class="bg-red-100 border border-red-400 text-red-700 px-5 py-3 rounded-lg mb-5">

                    <ul class="list-disc list-inside">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            @yield('content')

        </div>

    </main>


    <!-- Footer -->

    <footer class="bg-white border-t">

        <div class="max-w-7xl mx-auto py-5 text-center text-gray-500">

            Laravel 12 FastExcel Demo • Developed with ❤️ using FastExcel

        </div>

    </footer>

</body>

</html>