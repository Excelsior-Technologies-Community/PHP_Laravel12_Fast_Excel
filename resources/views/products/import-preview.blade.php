@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Import Preview
        </h2>

        <p class="text-gray-500 mt-1">
            Review the first 10 rows before importing.
        </p>
    </div>

    <a href="{{ route('products.import.form') }}"
       class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">

        Cancel

    </a>

</div>


<div class="bg-white rounded-lg shadow overflow-hidden">

    <div class="overflow-x-auto">

        <table class="min-w-full">

            <thead>

                <tr class="bg-gray-100 border-b">

                    <th class="px-4 py-3 text-left">
                        Name
                    </th>

                    <th class="px-4 py-3 text-left">
                        Description
                    </th>

                    <th class="px-4 py-3 text-left">
                        Category
                    </th>

                    <th class="px-4 py-3 text-left">
                        Price
                    </th>

                    <th class="px-4 py-3 text-left">
                        Stock
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach($products as $product)

                <tr class="border-b hover:bg-gray-50">

                    <td class="px-4 py-3">
                        {{ $product['Name'] ?? '' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $product['Description'] ?? '' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $product['Category'] ?? '' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $product['Price'] ?? '' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $product['Stock'] ?? '' }}
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>


<div class="mt-6 flex justify-end">

    <form method="POST"
          action="{{ route('products.import') }}">

        @csrf

        <input type="hidden"
               name="confirm"
               value="1">

        <button
            type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">

            Confirm Import

        </button>

    </form>

</div>

@endsection