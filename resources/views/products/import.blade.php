@extends('layouts.app', ['title' => 'Smart Import Hub — FastExcel Enterprise'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Smart Import Hub</h1>
            <p class="text-sm text-slate-500 mt-1">Upload Excel, CSV, or Product Image ZIP archives with smart column mapping.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.template') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-semibold text-xs transition">
                <i class="fa-solid fa-download mr-1.5"></i> Download Template (.xlsx)
            </a>
            <a href="{{ route('files.history') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-xs transition">
                <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> History &amp; Undo
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 bg-white rounded-t-2xl px-6 pt-3 space-x-6">
        <button type="button" onclick="switchImportTab('excel')" id="tabBtnExcel" class="pb-3 text-sm font-bold border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i> Excel &amp; CSV Smart Import
        </button>
        <button type="button" onclick="switchImportTab('zip')" id="tabBtnZip" class="pb-3 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-2">
            <i class="fa-solid fa-file-zipper"></i> Bulk Product Images (ZIP)
        </button>
    </div>

    <!-- TAB 1: Excel & CSV Smart Import -->
    <div id="tabContentExcel" class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-slate-200/80 p-8 space-y-6">
        <div>
            <h3 class="text-base font-bold text-slate-800">Step 1: Upload Spreadsheet</h3>
            <p class="text-xs text-slate-500 mt-0.5">We will inspect your column headers and allow you to map them to database fields in Step 2.</p>
        </div>

        <form action="{{ route('products.import.map') }}" method="POST" enctype="multipart/form-data" id="excelUploadForm" class="space-y-6">
            @csrf

            <!-- Drag & Drop Zone -->
            <div id="dropzoneExcel" class="border-2 border-dashed border-indigo-200 rounded-2xl p-10 text-center hover:border-indigo-500 bg-indigo-50/20 transition cursor-pointer" onclick="document.getElementById('excelFileInput').click()">
                <div class="w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto text-2xl mb-4">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h4 class="text-base font-bold text-slate-800" id="excelUploadPrompt">Click to upload or drag &amp; drop</h4>
                <p class="text-xs text-slate-400 mt-1">Supports Microsoft Excel (.xlsx, .xls) and CSV (.csv) up to 20MB</p>
                <div id="selectedExcelName" class="mt-3 text-sm font-semibold text-indigo-700 hidden"></div>
                <input type="file" name="file" id="excelFileInput" accept=".xlsx,.xls,.csv" class="hidden" onchange="handleExcelFileSelected(this)">
            </div>

            <!-- Features Summary Badges -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2.5 text-slate-600">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-600 text-sm"></i>
                    <span><strong>Auto Column Detection:</strong> Matches Price, Qty, SKU headers automatically.</span>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2.5 text-slate-600">
                    <i class="fa-solid fa-bolt text-emerald-600 text-sm"></i>
                    <span><strong>FastExcel Engine:</strong> Streamlines 100k+ rows with zero memory spikes.</span>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2.5 text-slate-600">
                    <i class="fa-solid fa-rotate-left text-amber-600 text-sm"></i>
                    <span><strong>Failed Rows Export:</strong> Fix errors in Excel and re-upload failed items.</span>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" id="btnNextMapping" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Proceed to Column Mapping <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: Bulk Image ZIP Import -->
    <div id="tabContentZip" class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-slate-200/80 p-8 space-y-6 hidden">
        <div>
            <h3 class="text-base font-bold text-slate-800">Bulk Product Photos (.ZIP)</h3>
            <p class="text-xs text-slate-500 mt-0.5">Upload a ZIP archive containing images named by SKU or Product ID (e.g. <code>APL-IPH15-256.jpg</code> or <code>105.png</code>).</p>
        </div>

        <form action="{{ route('products.import.images') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- ZIP Dropzone -->
            <div id="dropzoneZip" class="border-2 border-dashed border-teal-200 rounded-2xl p-10 text-center hover:border-teal-500 bg-teal-50/20 transition cursor-pointer" onclick="document.getElementById('zipFileInput').click()">
                <div class="w-16 h-16 rounded-2xl bg-teal-100 text-teal-600 flex items-center justify-center mx-auto text-2xl mb-4">
                    <i class="fa-solid fa-file-zipper"></i>
                </div>
                <h4 class="text-base font-bold text-slate-800" id="zipUploadPrompt">Click to upload product image ZIP</h4>
                <p class="text-xs text-slate-400 mt-1">Supports .zip archives containing JPG, PNG, WebP up to 50MB</p>
                <div id="selectedZipName" class="mt-3 text-sm font-semibold text-teal-700 hidden"></div>
                <input type="file" name="images_zip" id="zipFileInput" accept=".zip" class="hidden" onchange="handleZipFileSelected(this)">
            </div>

            <!-- Help Notice -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-indigo-600"></i> How Image Matching Works:
                </div>
                <div>1. The system extracts each photo and looks for a product with matching <strong>SKU code</strong> (e.g., <code>SNY-WHXM5-BLK.jpg</code> matches product SKU <code>SNY-WHXM5-BLK</code>).</div>
                <div>2. If no SKU matches, it checks if the filename is a numeric <strong>Product Database ID</strong> (e.g. <code>12.png</code> matches product ID 12).</div>
                <div>3. Matched photos are stored in <code>storage/products</code> and linked instantly to your catalog.</div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-sm transition">
                    <i class="fa-solid fa-upload mr-2"></i> Extract &amp; Match Photos
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function switchImportTab(tab) {
        const tabExcel = document.getElementById('tabContentExcel');
        const tabZip = document.getElementById('tabContentZip');
        const btnExcel = document.getElementById('tabBtnExcel');
        const btnZip = document.getElementById('tabBtnZip');

        if (tab === 'excel') {
            tabExcel.classList.remove('hidden');
            tabZip.classList.add('hidden');
            btnExcel.classList.add('border-indigo-600', 'text-indigo-600');
            btnExcel.classList.remove('border-transparent', 'text-slate-500');
            btnZip.classList.remove('border-indigo-600', 'text-indigo-600');
            btnZip.classList.add('border-transparent', 'text-slate-500');
        } else {
            tabZip.classList.remove('hidden');
            tabExcel.classList.add('hidden');
            btnZip.classList.add('border-indigo-600', 'text-indigo-600');
            btnZip.classList.remove('border-transparent', 'text-slate-500');
            btnExcel.classList.remove('border-indigo-600', 'text-indigo-600');
            btnExcel.classList.add('border-transparent', 'text-slate-500');
        }
    }

    function handleExcelFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('excelUploadPrompt').textContent = 'File selected: ' + file.name;
            const badge = document.getElementById('selectedExcelName');
            badge.textContent = `File Size: ${(file.size / 1024 / 1024).toFixed(2)} MB • Ready for mapping`;
            badge.classList.remove('hidden');
        }
    }

    function handleZipFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('zipUploadPrompt').textContent = 'ZIP selected: ' + file.name;
            const badge = document.getElementById('selectedZipName');
            badge.textContent = `ZIP Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            badge.classList.remove('hidden');
        }
    }
</script>
@endpush
@endsection
