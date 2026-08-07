@extends('layouts.app')

@section('content')

<div class="bg-white rounded-lg shadow-md p-6">

<div class="flex justify-between items-center mb-6">

    <h2 class="text-2xl font-bold text-gray-800">
        Import Products
    </h2>

    <a href="{{ route('products.template') }}"
        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
        Download Template
    </a>

</div>


{{-- Instructions --}}

<div class="mb-6">

    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">

        <h3 class="font-bold mb-2">
            Instructions:
        </h3>

        <ul class="list-disc list-inside space-y-1">

            <li>
                Download the template file
            </li>

            <li>
                Fill in your product data
            </li>

            <li>
                Upload the Excel/CSV file
            </li>

            <li>
                Required columns: Name, Price, Stock, Category
            </li>

            <li>
                Optional column: Description
            </li>

            <li>
                Click <strong>Preview Import</strong> to check your data before importing
            </li>

        </ul>

    </div>

</div>


{{-- Validation Errors --}}

@if($errors->any())

    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-5">

        <ul class="list-disc list-inside">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


{{-- Error Message --}}

@if(session('error'))

    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-5">

        {{ session('error') }}

    </div>

@endif


{{-- Preview Upload Form --}}

<form
    action="{{ route('products.import.preview') }}"
    method="POST"
    enctype="multipart/form-data"
>

    @csrf


    <div class="mb-5">

        <label
            for="file"
            class="block text-gray-700 text-sm font-bold mb-2"
        >
            Choose Excel/CSV File
        </label>


        <input
            type="file"
            name="file"
            id="file"
            accept=".xlsx,.xls,.csv"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        >


        <p class="text-gray-500 text-xs mt-2">
            Maximum file size: 2MB
        </p>

    </div>


    <div class="flex items-center space-x-4">

        <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded"
        >
            Preview Import
        </button>


        <a
            href="{{ route('products.index') }}"
            class="text-gray-600 hover:text-gray-800"
        >
            Cancel
        </a>

    </div>

</form>

</div>

@endsection
