<?php

namespace App\Http\Controllers;

use App\Models\FileHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileHistoryController extends Controller
{
    public function index()
    {
        $histories = FileHistory::latest()->paginate(15);

        return view('files.history', compact('histories'));
    }

    /**
     * One-Click Rollback / Undo an entire import batch
     */
    public function rollback($id)
    {
        $history = FileHistory::findOrFail($id);

        if ($history->type !== 'import') {
            return redirect()->back()->with('error', 'Only import records can be rolled back.');
        }

        if ($history->status === 'rolled_back') {
            return redirect()->back()->with('error', 'This import batch has already been rolled back.');
        }

        if (empty($history->batch_id)) {
            return redirect()->back()->with('error', 'No batch ID associated with this legacy import record.');
        }

        DB::beginTransaction();
        try {
            $deletedCount = Product::where('import_batch_id', $history->batch_id)->delete();

            $history->update([
                'status' => 'rolled_back',
            ]);

            DB::commit();

            return redirect()->back()->with(
                'success',
                "Successfully rolled back import '{$history->file_name}' (Batch: {$history->batch_id}). {$deletedCount} products were safely removed."
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to rollback import: ' . $e->getMessage());
        }
    }
}
