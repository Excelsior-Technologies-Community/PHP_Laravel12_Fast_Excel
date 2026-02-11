@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Import Products</h2>
        <a href="{{ route('products.template') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Download Template
        </a>
    </div>

    <div class="mb-6">
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
            <h3 class="font-bold mb-2">Instructions:</h3>
            <ul class="list-disc list-inside">
                <li>Download the template file</li>
                <li>Fill in your product data</li>
                <li>Upload the Excel/CSV file</li>
                <li>Required columns: Name, Price, Stock, Category</li>
                <li>Optional column: Description</li>
            </ul>
        </div>
    </div>

    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="file">
                Choose Excel/CSV File
            </label>
            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
            @error('file')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-gray-500 text-xs mt-1">Maximum file size: 2MB</p>
        </div>

        <div class="flex items-center space-x-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                Import Products
            </button>
            <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-800">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection