<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function transactions(Request $request): StreamedResponse
    {
        $plan = $request->user()->profile?->plan ?? 'free';

        if ($plan !== 'premium') {
            abort(403, 'CSV export is a premium feature.');
        }

        $transactions = $request->user()->transactions()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $filename = 'transactions-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Category', 'Amount', 'Description']);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->type,
                    $transaction->category,
                    $transaction->amount,
                    $transaction->description ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
