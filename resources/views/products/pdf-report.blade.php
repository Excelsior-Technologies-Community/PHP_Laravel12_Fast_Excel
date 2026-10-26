<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Valuation & Catalog Report — FastExcel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; }
            table { font-size: 10px !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-800">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-6">

        <!-- Top Action Bar (hidden on print) -->
        <div class="no-print flex items-center justify-between pb-6 border-b border-slate-200">
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                &larr; Back to Dashboard
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    Print / Save as PDF
                </button>
            </div>
        </div>

        <!-- Report Header -->
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Inventory Valuation &amp; Stock Report</h1>
                <p class="text-xs text-slate-500 mt-1">Generated dynamically via Laravel 12 FastExcel Enterprise Engine</p>
                <div class="text-xs text-slate-400 mt-0.5">Date: {{ now()->format('F d, Y - h:i A') }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Portfolio Valuation</div>
                <div class="text-2xl font-black text-indigo-600 mt-1">${{ number_format($totalValuation, 2) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ number_format($products->count()) }} total catalog items</div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">SKU</th>
                        <th class="py-2.5 px-3">Product Name</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3 text-right">Unit Price</th>
                        <th class="py-2.5 px-3 text-center">Stock</th>
                        <th class="py-2.5 px-3 text-right">Total Valuation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $idx => $p)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 px-3 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 font-mono font-semibold text-slate-700">{{ $p->sku ?? 'NO-SKU' }}</td>
                            <td class="py-2 px-3 font-bold text-slate-900">{{ $p->name }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $p->category }}</td>
                            <td class="py-2 px-3 text-right font-semibold">${{ number_format($p->price, 2) }}</td>
                            <td class="py-2 px-3 text-center">
                                <span class="font-bold {{ $p->stock <= 0 ? 'text-rose-600' : ($p->stock <= 5 ? 'text-amber-600' : 'text-slate-700') }}">
                                    {{ $p->stock }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-right font-black text-slate-900">
                                ${{ number_format($p->price * $p->stock, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Report Footer -->
        <div class="pt-4 border-t border-slate-200 flex justify-between items-center text-xs text-slate-400">
            <span>FastExcel Autonomous Data Warehouse</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

</body>
</html>
