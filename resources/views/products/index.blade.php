@extends('layouts.app')

@section('content')

<div class="bg-white rounded-lg shadow-lg p-6">

    <!-- Header -->

    <div class="flex flex-col lg:flex-row justify-between items-center mb-6">

        <div>
            <h2 class="text-3xl font-bold text-gray-800">
                Product Dashboard
            </h2>

            <p class="text-gray-500 mt-1">
                FastExcel Product Management System
            </p>
        </div>

        <!-- Export Buttons -->

        <div class="mt-4 lg:mt-0 flex flex-wrap gap-2">

            <a href="{{ route('products.export', request()->query()) }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">

                Export Excel
            </a>

            <a href="{{ route('products.export.simple', request()->query()) }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">

                Simple Export
            </a>

            <a href="{{ route('products.export.csv', request()->query()) }}"
                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow">

                Export CSV
            </a>

            <a href="{{ route('products.import.form') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow">

                Import Products
            </a>

        </div>

    </div>

    <!-- Analytics Cards -->

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        <div class="bg-blue-500 text-white rounded-xl shadow-lg p-6">

            <h4 class="text-lg font-semibold">
                Total Products
            </h4>

            <h2 class="text-4xl font-bold mt-2">
                {{ $totalProducts }}
            </h2>

        </div>

        <div class="bg-green-500 text-white rounded-xl shadow-lg p-6">

            <h4 class="text-lg font-semibold">
                Categories
            </h4>

            <h2 class="text-4xl font-bold mt-2">
                {{ $totalCategories }}
            </h2>

        </div>

        <div class="bg-yellow-500 text-white rounded-xl shadow-lg p-6">

            <h4 class="text-lg font-semibold">
                Total Stock
            </h4>

            <h2 class="text-4xl font-bold mt-2">
                {{ $totalStock }}
            </h2>

        </div>

        <div class="bg-red-500 text-white rounded-xl shadow-lg p-6">

            <h4 class="text-lg font-semibold">
                Inventory Value
            </h4>

            <h2 class="text-3xl font-bold mt-2">
                ${{ number_format($totalInventoryValue,2) }}
            </h2>

        </div>

    </div>

    <!-- Search & Filter -->

    <div class="bg-gray-50 border rounded-xl p-6 mb-8">

        <form action="{{ route('products.index') }}" method="GET">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                <!-- Search -->

                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, Category, Description..."
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">

                </div>

                <!-- Category -->

                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Category
                    </label>

                    <select
                        name="category"
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">

                        <option value="">All Categories</option>

                        @foreach($categories as $category)

                        <option
                            value="{{ $category }}"
                            {{ request('category') == $category ? 'selected' : '' }}>

                            {{ $category }}

                        </option>

                        @endforeach

                    </select>

                </div>

                <!-- Min Price -->

                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Min Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="min_price"
                        value="{{ request('min_price') }}"
                        placeholder="0"
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">

                </div>

                <!-- Max Price -->

                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Max Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="max_price"
                        value="{{ request('max_price') }}"
                        placeholder="1000"
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">

                </div>

                <!-- Buttons -->

                <div class="flex items-end gap-2">

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">

                        Search

                    </button>

                    <a
                        href="{{ route('products.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">

                        Reset

                    </a>

                </div>

            </div>

        </form>

    </div>

    <!-- Statistics Section -->

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        <!-- Highest Price -->

        <div class="bg-white border-l-4 border-green-500 rounded-lg shadow p-5">

            <p class="text-sm text-gray-500">
                Highest Price Product
            </p>

            @if($highestPrice)

            <h3 class="text-xl font-bold mt-2">
                {{ $highestPrice->name }}
            </h3>

            <p class="text-green-600 font-semibold text-lg mt-1">
                ${{ number_format($highestPrice->price,2) }}
            </p>

            @else

            <p class="text-gray-400 mt-2">
                No Products
            </p>

            @endif

        </div>

        <!-- Lowest Price -->

        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow p-5">

            <p class="text-sm text-gray-500">
                Lowest Price Product
            </p>

            @if($lowestPrice)

            <h3 class="text-xl font-bold mt-2">
                {{ $lowestPrice->name }}
            </h3>

            <p class="text-blue-600 font-semibold text-lg mt-1">
                ${{ number_format($lowestPrice->price,2) }}
            </p>

            @else

            <p class="text-gray-400 mt-2">
                No Products
            </p>

            @endif

        </div>

        <!-- Average Price -->

        <div class="bg-white border-l-4 border-yellow-500 rounded-lg shadow p-5">

            <p class="text-sm text-gray-500">
                Average Product Price
            </p>

            <h3 class="text-3xl font-bold mt-2 text-yellow-600">
                ${{ number_format($averagePrice,2) }}
            </h3>

        </div>

        <!-- Out of Stock -->

        <div class="bg-white border-l-4 border-red-500 rounded-lg shadow p-5">

            <p class="text-sm text-gray-500">
                Out of Stock Products
            </p>

            <h3 class="text-3xl font-bold mt-2 text-red-600">
                {{ $outOfStock }}
            </h3>

        </div>

    </div>

    <!-- Products Table -->

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">

        <div class="px-6 py-4 border-b">

            <h3 class="text-xl font-semibold text-gray-700">
                Product List
            </h3>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">
                            ID
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">
                            Product
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">
                            Category
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">
                            Price
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-bold uppercase text-gray-600">
                            Stock
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-bold uppercase text-gray-600">
                            Status
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-bold uppercase text-gray-600">
                            Created
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($products as $product)

                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-6 py-4">

                            {{ $product->id }}

                        </td>

                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-800">

                                {{ $product->name }}

                            </div>

                            <div class="text-sm text-gray-500">

                                {{ $product->description ?: 'No Description' }}

                            </div>

                        </td>

                        <td class="px-6 py-4">

                            {{ $product->category }}

                        </td>

                        <td class="px-6 py-4 text-right font-semibold text-green-600">

                            ${{ number_format($product->price,2) }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            {{ $product->stock }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            @if($product->stock == 0)

                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                Out of Stock
                            </span>

                            @elseif($product->stock <= 10)

                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                                Low Stock
                                </span>

                                @else

                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    In Stock
                                </span>

                                @endif

                        </td>

                        <td class="px-6 py-4 text-center text-gray-500">

                            {{ $product->created_at->format('d M Y') }}

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="7" class="text-center py-12">

                            <div class="text-gray-500">

                                <div class="text-5xl mb-3">
                                    📦
                                </div>

                                <h3 class="text-xl font-semibold">

                                    No Products Found

                                </h3>

                                <p class="mt-2">

                                    Try changing your search or filter.

                                </p>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <!-- Footer -->

    <div class="flex flex-col md:flex-row justify-between items-center mt-6">

        <!-- Result Count -->

        <div class="text-sm text-gray-600 mb-3 md:mb-0">

            Showing

            <span class="font-semibold">
                {{ $products->firstItem() ?? 0 }}
            </span>

            to

            <span class="font-semibold">
                {{ $products->lastItem() ?? 0 }}
            </span>

            of

            <span class="font-semibold">
                {{ $products->total() }}
            </span>

            products

        </div>

        <!-- Pagination -->

        <div>

            {{ $products->onEachSide(1)->links() }}

        </div>

    </div>

</div>

@endsection