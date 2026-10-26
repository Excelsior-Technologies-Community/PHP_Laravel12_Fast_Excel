@extends('layouts.app', ['title' => 'File History & One-Click Rollback'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Import &amp; Export Audit Log</h1>
            <p class="text-sm text-slate-500 mt-1">Track background file batches, download failed rows, and rollback accidental imports.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('products.import.form') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
                <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> New Import
            </a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- History Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        @if($histories->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-4 px-5">File &amp; Batch ID</th>
                            <th class="py-4 px-4 text-center">Type</th>
                            <th class="py-4 px-4 text-center">Status</th>
                            <th class="py-4 px-4 text-center">Total Rows</th>
                            <th class="py-4 px-4 text-center">Success / Failed</th>
                            <th class="py-4 px-4">Date &amp; Strategy</th>
                            <th class="py-4 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($histories as $history)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- File & Batch ID -->
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fa-solid {{ $history->type === 'import' ? 'fa-file-import text-indigo-600' : 'fa-file-export text-emerald-600' }}"></i>
                                        <span>{{ $history->file_name }}</span>
                                    </div>
                                    @if($history->batch_id)
                                        <div class="mt-1">
                                            <span class="inline-block font-mono text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $history->batch_id }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Type -->
                                <td class="py-4 px-4 text-center">
                                    @if($history->type === 'import')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Import
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            Export
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4 text-center">
                                    @if($history->status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="fa-solid fa-check text-[10px]"></i> Completed
                                        </span>
                                    @elseif($history->status === 'processing' || $history->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 animate-pulse">
                                            <i class="fa-solid fa-spinner fa-spin text-[10px]"></i> {{ ucfirst($history->status) }}
                                        </span>
                                    @elseif($history->status === 'rolled_back')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-300">
                                            <i class="fa-solid fa-rotate-left text-[10px]"></i> Rolled Back
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> Failed
                                        </span>
                                    @endif
                                </td>

                                <!-- Total Rows -->
                                <td class="py-4 px-4 text-center font-bold text-slate-800">
                                    {{ number_format($history->total_rows) }}
                                </td>

                                <!-- Success / Failed -->
                                <td class="py-4 px-4 text-center">
                                    <span class="text-emerald-600 font-bold">{{ number_format($history->success_rows) }}</span>
                                    <span class="text-slate-300 mx-1">/</span>
                                    <span class="font-bold {{ $history->failed_rows > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                        {{ number_format($history->failed_rows) }}
                                    </span>
                                </td>

                                <!-- Date & Strategy -->
                                <td class="py-4 px-4 text-xs text-slate-500">
                                    <div>{{ $history->created_at?->format('M d, Y h:i A') }}</div>
                                    @if($history->upsert_mode)
                                        <div class="text-[11px] text-slate-400 mt-0.5">Mode: {{ str_replace('_', ' ', $history->upsert_mode) }}</div>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Download Failed Rows Excel -->
                                        @if($history->failed_file_path && file_exists(public_path($history->failed_file_path)))
                                            <a href="{{ asset($history->failed_file_path) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition" title="Download spreadsheet of failed rows with exact validation error reasons">
                                                <i class="fa-solid fa-file-excel mr-1"></i> Failed Rows
                                            </a>
                                        @endif

                                        <!-- One-Click Rollback / Undo -->
                                        @if($history->type === 'import' && $history->status === 'completed' && $history->batch_id)
                                            <form action="{{ route('files.history.rollback', $history->id) }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to ROLLBACK import batch #{{ $history->batch_id }}?\n\nThis will permanently remove all products imported in this batch.');" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition" title="Delete all products created by this import batch">
                                                    <i class="fa-solid fa-rotate-left mr-1"></i> Undo Import
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($histories->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $histories->links() }}
                </div>
            @endif
        @else
            <div class="py-12 text-center">
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-3">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No import/export activity recorded yet</h3>
                <p class="text-xs text-slate-500 mt-1">Activity logs will appear here once you perform imports or exports.</p>
            </div>
        @endif
    </div>

</div>
@endsection
