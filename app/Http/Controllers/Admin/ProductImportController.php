<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImport;
use App\Services\Import\ProductImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductImportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Import/Index', [
            'imports' => ProductImport::latest()->take(15)->get()->map(fn (ProductImport $im) => [
                'id' => $im->id,
                'filename' => $im->filename,
                'status' => $im->status,
                'total_rows' => $im->total_rows,
                'created_count' => $im->created_count,
                'updated_count' => $im->updated_count,
                'skipped_count' => $im->skipped_count,
                'failed_count' => $im->failed_count,
                'created_at' => $im->created_at->format('Y/m/d H:i'),
            ]),
        ]);
    }

    public function store(Request $request, ProductImporter $importer): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:8192'],
        ], [], ['file' => 'فایل']);

        $path = $request->file('file')->store('imports', 'local');

        $import = ProductImport::create([
            'user_id' => $request->user()->id,
            'filename' => $request->file('file')->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
        ]);

        $importer->run($import);

        return redirect()->route('admin.import.show', $import)->with('success', 'درون‌ریزی انجام شد.');
    }

    public function show(ProductImport $productImport): Response
    {
        return Inertia::render('Admin/Import/Show', [
            'record' => [
                ...$productImport->only('id', 'filename', 'status', 'total_rows', 'created_count', 'updated_count', 'skipped_count', 'failed_count', 'report'),
                'created_at' => $productImport->created_at->format('Y/m/d H:i'),
            ],
        ]);
    }

    public function template(): StreamedResponse
    {
        $headers = ['کد کالا', 'نام', 'قیمت', 'دسته', 'موجودی', 'تصویر', 'ترتیب', 'فعال'];
        $sample = ['1001', 'رب گوجه ۹۰۰ گرم', '2680000', 'رب، آبلیمو، آبغوره و سرکه', '10', '', '0', 'بله'];

        return response()->streamDownload(function () use ($headers, $sample) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, $sample);
            fclose($out);
        }, 'felfeli-products-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
