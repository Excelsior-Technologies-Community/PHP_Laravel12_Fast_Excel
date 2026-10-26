@extends('layouts.app', ['title' => 'Step 2: Dynamic Column Mapping — FastExcel Enterprise'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">Step 2 of 2</span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Smart Column Mapping</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Map your spreadsheet headers to the system product fields.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                <i class="fa-solid fa-file-excel text-emerald-600 mr-1.5"></i> {{ $fileName }}
            </span>
            <a href="{{ route('products.import.form') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                Change File
            </a>
        </div>
    </div>

    <!-- Spreadsheet Sample Preview -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 overflow-hidden">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                <i class="fa-solid fa-table mr-1.5"></i> Sample Preview from Your File (First 5 Rows)
            </h3>
            <span class="text-xs text-slate-400">Total detected headers: {{ count($excelHeaders) }}</span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold">
                        @foreach($excelHeaders as $h)
                            <th class="py-2.5 px-3 whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-slate-600">
                    @foreach($previewRows as $row)
                        <tr class="hover:bg-slate-50">
                            @foreach($excelHeaders as $h)
                                <td class="py-2 px-3 whitespace-nowrap truncate max-w-xs">{{ $row[$h] ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mapping Configuration Form -->
    <form action="{{ route('products.import.process') }}" method="POST" id="importProcessForm" class="space-y-6">
        @csrf
        <input type="hidden" name="stored_path" value="{{ $storedPath }}">
        <input type="hidden" name="file_name" value="{{ $fileName }}">

        <!-- Mapping Matrix -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Map Columns</h3>
                <p class="text-xs text-slate-500">Select which header in your spreadsheet corresponds to each product field.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product Name (Required) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Product Name <span class="text-rose-500 font-bold">*Required</span>
                    </label>
                    <select name="mapping[name]" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Choose Excel Header --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['name'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }} {{ ($autoMappings['name'] ?? '') === $h ? '✓ (Auto Matched)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category (Required) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Category <span class="text-rose-500 font-bold">*Required</span>
                    </label>
                    <select name="mapping[category]" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Choose Excel Header --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['category'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }} {{ ($autoMappings['category'] ?? '') === $h ? '✓ (Auto Matched)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price (Required) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Price (Numeric) <span class="text-rose-500 font-bold">*Required</span>
                    </label>
                    <select name="mapping[price]" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Choose Excel Header --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['price'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }} {{ ($autoMappings['price'] ?? '') === $h ? '✓ (Auto Matched)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Quantity (Required) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Stock Quantity (Integer) <span class="text-rose-500 font-bold">*Required</span>
                    </label>
                    <select name="mapping[stock]" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Choose Excel Header --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['stock'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }} {{ ($autoMappings['stock'] ?? '') === $h ? '✓ (Auto Matched)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- SKU (Optional) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        SKU Code <span class="text-slate-400 font-normal">(Optional - auto generated if empty)</span>
                    </label>
                    <select name="mapping[sku]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Leave empty to auto-generate --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['sku'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Barcode (Optional) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Barcode (EAN-13 / UPC) <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <select name="mapping[barcode]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Leave empty to auto-generate --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['barcode'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Description (Optional) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Description <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <select name="mapping[description]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Not in file --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['description'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Image URL (Optional) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Image URL / File Path <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <select name="mapping[image]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white">
                        <option value="">-- Not in file --</option>
                        @foreach($excelHeaders as $h)
                            <option value="{{ $h }}" {{ ($autoMappings['image'] ?? '') === $h ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Upsert & Duplicate Handling Mode -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Duplicate Handling Strategy</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <label class="cursor-pointer border border-slate-200 rounded-xl p-4 hover:border-indigo-500 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 transition flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-slate-800">Insert New Only</span>
                        <input type="radio" name="upsert_mode" value="insert_new" checked class="text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-slate-500">Only creates new records. If SKU/Name already exists, row is flagged as failed.</p>
                </label>

                <label class="cursor-pointer border border-slate-200 rounded-xl p-4 hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40 transition flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-slate-800">Update Existing (Upsert)</span>
                        <input type="radio" name="upsert_mode" value="update_existing" class="text-emerald-600 focus:ring-emerald-500">
                    </div>
                    <p class="text-xs text-slate-500">If product already exists (by SKU or Name), updates its price &amp; stock. Inserts if new.</p>
                </label>

                <label class="cursor-pointer border border-slate-200 rounded-xl p-4 hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/40 transition flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-slate-800">Skip Duplicates</span>
                        <input type="radio" name="upsert_mode" value="skip_duplicates" class="text-purple-600 focus:ring-purple-500">
                    </div>
                    <p class="text-xs text-slate-500">Silently skips existing records without throwing errors or failing the row.</p>
                </label>
            </div>
        </div>

        <!-- Execution & Processing Mode -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="processing_mode" value="queue" checked class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-bold text-slate-800">
                        <i class="fa-solid fa-bolt text-indigo-600 mr-1"></i> Background Queue Processing <span class="text-xs text-slate-400 font-normal">(With Live Progress Bar)</span>
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer ml-4">
                    <input type="radio" name="processing_mode" value="sync" class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-600">Immediate Sync</span>
                </label>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('products.import.form') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" id="btnStartImport" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-500/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-play"></i> Start FastExcel Import
                </button>
            </div>
        </div>
    </form>

</div>

<!-- Live Real-Time Progress Bar Modal -->
<div id="progressModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-slate-200 text-center">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto text-2xl animate-pulse">
            <i class="fa-solid fa-bolt"></i>
        </div>

        <div>
            <h3 class="text-xl font-black text-slate-900" id="progressTitle">Importing Products...</h3>
            <p class="text-xs text-slate-500 mt-1" id="progressSubtitle">Processing rows in background queue with FastExcel stream.</p>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-2">
            <div class="flex justify-between text-xs font-bold text-slate-600">
                <span id="progressPercentText">0%</span>
                <span id="progressRowsText">0 / 0 Rows</span>
            </div>
            <div class="w-full h-3 rounded-full bg-slate-100 overflow-hidden">
                <div id="progressBarFill" class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400 transition-all duration-300" style="width: 0%;"></div>
            </div>
        </div>

        <!-- Stats Counter -->
        <div class="grid grid-cols-2 gap-3 text-xs pt-2">
            <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                <div class="text-lg font-black" id="progressSuccessCount">0</div>
                <span>Successfully Imported</span>
            </div>
            <div class="p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 font-bold">
                <div class="text-lg font-black" id="progressFailedCount">0</div>
                <span>Failed Rows</span>
            </div>
        </div>

        <!-- Action when Completed -->
        <div id="progressCompletedActions" class="hidden pt-3 space-y-2">
            <div id="failedFileDownloadContainer" class="hidden">
                <a id="btnDownloadFailed" href="#" class="inline-flex items-center px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                    <i class="fa-solid fa-file-excel mr-1.5"></i> Download Failed Rows Excel (With Error Reasons)
                </a>
            </div>
            <div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow transition">
                    Go to Products Dashboard <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('importProcessForm').addEventListener('submit', function (e) {
        const mode = document.querySelector('input[name="processing_mode"]:checked').value;
        if (mode === 'queue') {
            e.preventDefault();
            const formData = new FormData(this);

            document.getElementById('progressModal').classList.remove('hidden');

            fetch("{{ route('products.import.process') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.file_history_id) {
                    startPollingProgress(data.file_history_id);
                } else {
                    window.location.href = "{{ route('products.index') }}";
                }
            })
            .catch(err => {
                console.error(err);
                alert('Import initialization error. Redirecting to File History.');
                window.location.href = "{{ route('files.history') }}";
            });
        }
    });

    function startPollingProgress(historyId) {
        const pollInterval = setInterval(() => {
            fetch(`/products/import/progress/${historyId}`)
                .then(res => res.json())
                .then(info => {
                    const pct = info.percentage || 0;
                    document.getElementById('progressBarFill').style.width = pct + '%';
                    document.getElementById('progressPercentText').textContent = pct + '%';
                    document.getElementById('progressRowsText').textContent = `${info.current_row || 0} / ${info.total_rows || 0} Rows`;
                    document.getElementById('progressSuccessCount').textContent = info.success_rows || 0;
                    document.getElementById('progressFailedCount').textContent = info.failed_rows || 0;

                    if (info.status === 'completed' || info.status === 'failed') {
                        clearInterval(pollInterval);
                        document.getElementById('progressTitle').textContent = info.status === 'completed' ? 'Import Complete!' : 'Import Halted';
                        document.getElementById('progressSubtitle').textContent = `Processed ${info.total_rows} total rows.`;
                        document.getElementById('progressCompletedActions').classList.remove('hidden');

                        if (info.failed_file_url) {
                            document.getElementById('btnDownloadFailed').href = info.failed_file_url;
                            document.getElementById('failedFileDownloadContainer').classList.remove('hidden');
                        }
                    }
                })
                .catch(err => console.error('Polling error:', err));
        }, 1000);
    }
</script>
@endpush
@endsection
