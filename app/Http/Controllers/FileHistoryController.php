<?php

namespace App\Http\Controllers;

use App\Models\FileHistory;

class FileHistoryController extends Controller
{
    public function index()
    {
        $histories = FileHistory::latest()->paginate(10);

        return view(
            'files.history',
            compact('histories')
        );
    }
}