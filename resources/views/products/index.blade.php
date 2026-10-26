@extends('layouts.app', ['title' => 'Product Inventory & FastExcel Dashboard'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Center -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Product Dashboard</h1>
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-indigo-200">Laravel 12</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">High-performance FastExcel engine with Queue, Upsert, and Smart Mapping.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <!-- Custom Export Modal Button -->
            <button type="button" onclick="openExportModal()" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm shadow-emerald-500/20 transition">
                <i class="fa-solid fa-file-export mr-2"></i> Custom Export
            </button>

            <!-- Multi-Sheet Excel -->
            <a href="{{ route('products.export.multisheet') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm shadow-sm shadow-teal-500/20 transition" title="Export 3 Sheets: Products, Category Summary, Low Stock">
                <i class="fa-solid fa-layer-group mr-2"></i> Multi-Sheet Excel
            </a>

            <!-- Printable PDF Report -->
            <a href="{{ route('products.export.pdf') }}" target="_blank" class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-semibold text-sm shadow-sm transition">
                <i class="fa-solid fa-print mr-2"></i> PDF / Print
            </a>

            <!-- Import Hub -->
            <a href="{{ route('products.import.form') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-500/20 transition">
                <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Import Hub
            </a>
        </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- Total Products -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs font-bold uppercase tracking-wider">Total Products</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-boxes-stacked"></i></div>
            </div>
            <div class="text-2xl font-black text-slate-800 mt-2">{{ number_format($totalProducts) }}</div>
            <div class="text-xs text-slate-400 mt-1">Catalog items</div>
        </div>

        <!-- Categories -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs font-bold uppercase tracking-wider">Categories</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm"><i class="fa-solid fa-tags"></i></div>
            </div>
            <div class="text-2xl font-black text-slate-800 mt-2">{{ number_format($totalCategories) }}</div>
            <div class="text-xs text-slate-400 mt-1">Distinct product lines</div>
        </div>

        <!-- Total Stock -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs font-bold uppercase tracking-wider">Total Units</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-warehouse"></i></div>
            </div>
            <div class="text-2xl font-black text-slate-800 mt-2">{{ number_format($totalStock) }}</div>
            <div class="text-xs text-slate-400 mt-1">Physical inventory units</div>
        </div>

        <!-- Inventory Valuation -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs font-bold uppercase tracking-wider">Total Valuation</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm"><i class="fa-solid fa-dollar-sign"></i></div>
            </div>
            <div class="text-2xl font-black text-slate-800 mt-2">${{ number_format($totalInventoryValue, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Avg ${{ number_format($averagePrice, 2) }} / unit</div>
        </div>

        <!-- Low Stock Alert (<= 5) -->
        <div class="bg-amber-50/70 rounded-2xl p-5 shadow-sm border border-amber-200 flex flex-col justify-between">
            <div class="flex items-center justify-between text-amber-700">
                <span class="text-xs font-bold uppercase tracking-wider">Low Stock (&le; 5)</span>
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
            <div class="text-2xl font-black text-amber-900 mt-2">{{ number_format($lowStockCount) }}</div>
            <div class="flex items-center justify-between mt-2">
                <a href="{{ route('products.index', ['stock_status' => 'low_stock']) }}" class="text-xs font-bold text-amber-800 hover:underline">View Items</a>
                <form action="{{ route('products.lowstock.alert') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-200/80 hover:bg-amber-300 text-amber-900 transition" title="Send Low Stock Email to Admin">
                        <i class="fa-regular fa-envelope"></i> Alert
                    </button>
                </form>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-rose-50/70 rounded-2xl p-5 shadow-sm border border-rose-200 flex flex-col justify-between">
            <div class="flex items-center justify-between text-rose-700">
                <span class="text-xs font-bold uppercase tracking-wider">Out of Stock</span>
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm"><i class="fa-solid fa-ban"></i></div>
            </div>
            <div class="text-2xl font-black text-rose-900 mt-2">{{ number_format($outOfStock) }}</div>
            <a href="{{ route('products.index', ['stock_status' => 'out_of_stock']) }}" class="text-xs font-bold text-rose-800 hover:underline mt-2">Filter Critical</a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Search Catalog</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Name, SKU, Barcode, Category..."
                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50">
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Category</label>
                <select name="category" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Stock Status</label>
                <select name="stock_status" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50/50">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (> 5)</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (&le; 5)</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm transition shadow-sm">
                    <i class="fa-solid fa-filter mr-1.5"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'category', 'stock_status', 'min_price', 'max_price']))
                    <a href="{{ route('products.index') }}" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Floating Action Bar for Selected Rows -->
    <div id="selectionActionBar" class="hidden bg-slate-900 text-white rounded-2xl p-4 shadow-xl flex items-center justify-between transition-all animate-bounce-short">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs" id="selectedCountBadge">0</span>
            <span class="text-sm font-semibold"><span id="selectedCountText">0 items</span> selected in table</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="exportSelectedOnly('xlsx')" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition">
                <i class="fa-solid fa-file-excel mr-1"></i> Export Selected (Excel)
            </button>
            <button type="button" onclick="exportSelectedOnly('csv')" class="px-3.5 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-xs font-bold transition">
                <i class="fa-solid fa-file-csv mr-1"></i> Export Selected (CSV)
            </button>
            <button type="button" onclick="clearAllSelections()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-400 hover:text-white transition">
                Deselect All
            </button>
        </div>
    </div>

    <!-- Products Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-4 w-12 text-center">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                        </th>
                        <th class="py-4 px-3 w-16 text-center">Image</th>
                        <th class="py-4 px-4">Product &amp; Category</th>
                        <th class="py-4 px-4">SKU / Barcode</th>
                        <th class="py-4 px-4 text-right">Price</th>
                        <th class="py-4 px-4 text-center">Stock</th>
                        <th class="py-4 px-4">Created Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60 transition group" id="row-{{ $product->id }}">
                            <!-- Checkbox -->
                            <td class="py-3.5 px-4 text-center">
                                <input type="checkbox" value="{{ $product->id }}" onchange="handleRowCheckboxChange()" class="row-checkbox w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                            </td>

                            <!-- Image Thumbnail -->
                            <td class="py-3.5 px-3 text-center">
                                @if($product->image)
                                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 mx-auto" onerror="this.onerror=null; this.src='https://via.placeholder.com/40x40.png?text=Item';">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mx-auto">
                                        <i class="fa-regular fa-image text-sm"></i>
                                    </div>
                                @endif
                            </td>

                            <!-- Product Name & Category -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition">{{ $product->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200">{{ $product->category }}</span>
                                    @if($product->description)
                                        <span class="text-xs text-slate-400 truncate max-w-xs" title="{{ $product->description }}">{{ $product->description }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- SKU & Barcode -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 inline-block mb-1">
                                    {{ $product->sku ?? 'NO-SKU' }}
                                </div>
                                @if($product->barcode)
                                    <div class="text-xs text-slate-400 flex items-center gap-1 font-mono">
                                        <i class="fa-solid fa-barcode text-slate-400"></i> {{ $product->barcode }}
                                    </div>
                                @endif
                            </td>

                            <!-- Price -->
                            <td class="py-3.5 px-4 text-right font-black text-slate-900">
                                ${{ number_format($product->price, 2) }}
                            </td>

                            <!-- Stock -->
                            <td class="py-3.5 px-4 text-center">
                                @if($product->stock <= 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Out of Stock (0)
                                    </span>
                                @elseif($product->stock <= 5)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Low: {{ $product->stock }} left
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ $product->stock }} in stock
                                    </span>
                                @endif
                            </td>

                            <!-- Created Date -->
                            <td class="py-3.5 px-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $product->created_at?->format('M d, Y') }}
                                <div class="text-slate-400 text-[11px]">{{ $product->created_at?->format('h:i A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-3">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <h3 class="text-base font-bold text-slate-800">No products found</h3>
                                <p class="text-sm text-slate-500 mt-1">Try adjusting your filters or import your first Excel file.</p>
                                <div class="mt-4">
                                    <a href="{{ route('products.import.form') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 transition">
                                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Import Products
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Custom Column Export Modal -->
<div id="exportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 transition-all">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-file-excel"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Custom Column Export</h3>
                    <p class="text-xs text-slate-500">Choose columns and format to export.</p>
                </div>
            </div>
            <button type="button" onclick="closeExportModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Export Form -->
        <form method="GET" action="{{ route('products.export') }}" id="customExportForm" class="p-6 space-y-5">
            <!-- Hidden inputs carrying current search/filter state -->
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
            <input type="hidden" name="max_price" value="{{ request('max_price') }}">
            <input type="hidden" name="selected_ids" id="modalSelectedIds" value="">

            <!-- File Format -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Export Format</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-2 hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:text-emerald-800 transition">
                        <input type="radio" name="format" value="xlsx" checked class="hidden">
                        <i class="fa-solid fa-file-excel text-emerald-600"></i>
                        <span class="text-xs font-bold">Excel (.xlsx)</span>
                    </label>
                    <label class="cursor-pointer border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-2 hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/50 has-[:checked]:text-purple-800 transition">
                        <input type="radio" name="format" value="csv" class="hidden">
                        <i class="fa-solid fa-file-csv text-purple-600"></i>
                        <span class="text-xs font-bold">CSV (.csv)</span>
                    </label>
                    <label class="cursor-pointer border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-2 hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-800 transition">
                        <input type="radio" name="format" value="json" class="hidden">
                        <i class="fa-solid fa-code text-blue-600"></i>
                        <span class="text-xs font-bold">JSON (.json)</span>
                    </label>
                </div>
            </div>

            <!-- Column Checkbox Matrix -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Select Columns</label>
                    <button type="button" onclick="selectAllExportCols(true)" class="text-xs text-indigo-600 hover:underline font-semibold">Select All</button>
                </div>

                <div class="grid grid-cols-2 gap-2.5 bg-slate-50 p-4 rounded-xl border border-slate-200/80 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="id" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Database ID</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="sku" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>SKU Code</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="name" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Product Name</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="category" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Category</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="price" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Price</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="stock" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Stock Quantity</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="barcode" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Barcode</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="description" class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Description</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="image" class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Image URL</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="columns[]" value="created_at" checked class="col-export-check w-4 h-4 rounded text-emerald-600">
                        <span>Created Date</span>
                    </label>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeExportModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" onclick="setTimeout(closeExportModal, 800)" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition">
                    <i class="fa-solid fa-download mr-1.5"></i> Download File
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Selected rows state
    let selectedIds = new Set();

    function handleRowCheckboxChange() {
        selectedIds.clear();
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
            selectedIds.add(cb.value);
        });
        updateActionBar();
    }

    function toggleSelectAll(masterCb) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = masterCb.checked;
            if (masterCb.checked) {
                selectedIds.add(cb.value);
            } else {
                selectedIds.delete(cb.value);
            }
        });
        updateActionBar();
    }

    function clearAllSelections() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        const master = document.getElementById('selectAllCheckbox');
        if (master) master.checked = false;
        selectedIds.clear();
        updateActionBar();
    }

    function updateActionBar() {
        const bar = document.getElementById('selectionActionBar');
        const badge = document.getElementById('selectedCountBadge');
        const text = document.getElementById('selectedCountText');
        const count = selectedIds.size;

        if (count > 0) {
            badge.textContent = count;
            text.textContent = count + (count === 1 ? ' item' : ' items');
            bar.classList.remove('hidden');
        } else {
            bar.classList.add('hidden');
        }
    }

    function exportSelectedOnly(format = 'xlsx') {
        if (selectedIds.size === 0) return;
        const ids = Array.from(selectedIds).join(',');
        const url = `{{ route('products.export') }}?format=${format}&selected_ids=${ids}`;
        window.location.href = url;
    }

    // Modal controls
    function openExportModal() {
        const modal = document.getElementById('exportModal');
        const hiddenIdsInput = document.getElementById('modalSelectedIds');
        if (hiddenIdsInput) {
            hiddenIdsInput.value = Array.from(selectedIds).join(',');
        }
        modal.classList.remove('hidden');
    }

    function closeExportModal() {
        document.getElementById('exportModal').classList.add('hidden');
    }

    function selectAllExportCols(state) {
        document.querySelectorAll('.col-export-check').forEach(cb => cb.checked = state);
    }
</script>
@endpush
@endsection
