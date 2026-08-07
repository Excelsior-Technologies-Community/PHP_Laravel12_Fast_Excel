@extends('layouts.app')

@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

{{-- Header --}}
<div class="px-6 py-5 border-b border-gray-200">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <div>

            <h2 class="text-2xl font-bold text-gray-800">
                Import / Export History
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                View your product file import and export activity.
            </p>

        </div>

        <div class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium">

            {{ $histories->total() }} Activities

        </div>

    </div>

</div>


{{-- Table --}}
@if($histories->count() > 0)

    <div class="overflow-x-auto">

        <table class="min-w-full">

            <thead>

                <tr class="bg-gray-50 border-b border-gray-200">

                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        File
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Type
                    </th>

                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Total
                    </th>

                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Success
                    </th>

                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Failed
                    </th>

                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Date
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-gray-100">

                @foreach($histories as $history)

                    <tr class="hover:bg-gray-50 transition">

                        {{-- File --}}
                        <td class="px-6 py-4">

                            <div class="flex items-center gap-3">

                                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center">

                                    @if($history->type == 'import')

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor">

                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14" />

                                        </svg>

                                    @else

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-5 h-5 text-green-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor">

                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 20V8m0 0l-4 4m4-4l4 4M5 4h14" />

                                        </svg>

                                    @endif

                                </div>

                                <div>

                                    <p class="font-medium text-gray-800">
                                        {{ $history->file_name }}
                                    </p>

                                    <p class="text-xs text-gray-400">
                                        File operation
                                    </p>

                                </div>

                            </div>

                        </td>


                        {{-- Type --}}
                        <td class="px-6 py-4">

                            @if($history->type == 'import')

                                <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full text-xs font-semibold">

                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>

                                    Import

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-3 py-1 rounded-full text-xs font-semibold">

                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>

                                    Export

                                </span>

                            @endif

                        </td>


                        {{-- Total --}}
                        <td class="px-6 py-4 text-center">

                            <span class="font-semibold text-gray-700">

                                {{ $history->total_rows }}

                            </span>

                        </td>


                        {{-- Success --}}
                        <td class="px-6 py-4 text-center">

                            <span class="inline-block min-w-[36px] bg-green-50 text-green-700 px-2.5 py-1 rounded-md text-sm font-semibold">

                                {{ $history->success_rows }}

                            </span>

                        </td>


                        {{-- Failed --}}
                        <td class="px-6 py-4 text-center">

                            @if($history->failed_rows > 0)

                                <span class="inline-block min-w-[36px] bg-red-50 text-red-700 px-2.5 py-1 rounded-md text-sm font-semibold">

                                    {{ $history->failed_rows }}

                                </span>

                            @else

                                <span class="inline-block min-w-[36px] bg-gray-100 text-gray-500 px-2.5 py-1 rounded-md text-sm font-semibold">

                                    0

                                </span>

                            @endif

                        </td>


                        {{-- Date --}}
                        <td class="px-6 py-4 text-right">

                            <div class="text-sm font-medium text-gray-700">

                                {{ $history->created_at->format('d M Y') }}

                            </div>

                            <div class="text-xs text-gray-400 mt-1">

                                {{ $history->created_at->format('H:i') }}

                            </div>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>


    {{-- Pagination --}}
    <div class="px-6 py-4 border-t border-gray-200">

        {{ $histories->links() }}

    </div>


@else

    {{-- Empty State --}}
    <div class="py-14 text-center">

        <div class="w-14 h-14 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-7 h-7 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />

            </svg>

        </div>

        <h3 class="text-lg font-semibold text-gray-700">
            No file history found
        </h3>

        <p class="text-sm text-gray-500 mt-1">
            Import or export a file to see the activity here.
        </p>

    </div>

@endif

</div>

@endsection
