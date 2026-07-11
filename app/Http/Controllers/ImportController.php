<?php

namespace App\Http\Controllers;

use App\Imports\CollectionImport;
use App\Imports\MonthlyBillingImport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;

class ImportController extends Controller
{
    public function importForm(Request $request)
    {
        $data = null;
        if ($request->isMethod('post') && $request->file('file')) {
            $file = $request->file('file');

            try {
                // Read data from the Excel file
                $importData = Excel::toArray(new ProductsImport, $file);
                // Store the first sheet's data
                $data = $importData[0] ?? [];
                // dd($data);
            } catch (\Exception $e) {
                // Handle exceptions or invalid file format
                return redirect()->back()->withErrors('Error processing file: '.$e->getMessage());
            }
        }

        return view('mikrotik.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        // Excel::import(new ProductsImport, $request->file('file'));
        // return redirect(route('import.form'))->with('success', 'Products imported successfully.');

        $file = $request->file('file');
        $import = new ProductsImport;
        Excel::import($import, $file);

        // Provide feedback on the import process
        if ($import->skippedRows > 0) {
            return redirect(route('import.form'))->withErrors(['duplicates' => 'Some records were duplicates and were not imported.'])
                ->with([
                    'uploadedRows' => $import->uploadedRows,
                    'skippedRows' => $import->skippedRows,
                    'duplicates' => $import->duplicates,
                ]);
        }

        return redirect(route('import.form'))->with('success', 'File uploaded successfully. '.$import->uploadedRows.' rows were added.');
    }

    public function collectionForm(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        // Excel::import(new ProductsImport, $request->file('file'));
        // return redirect(route('import.form'))->with('success', 'Products imported successfully.');

        $file = $request->file('file');
        $import = new CollectionImport;
        Excel::import($import, $file);

        // Provide feedback on the import process
        if ($import->skippedRows > 0) {
            return redirect(route('import.form'))->withErrors(['duplicates' => 'Some records were duplicates and were not imported.'])
                ->with([
                    'uploadedRows' => $import->uploadedRows,
                    'skippedRows' => $import->skippedRows,
                    'duplicates' => $import->duplicates,
                ]);
        }

        return redirect(route('import.form'))->with('success', 'File uploaded successfully. '.$import->uploadedRows.' rows were added.');
    }

    public function monthlyBillForm(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        // Excel::import(new ProductsImport, $request->file('file'));
        // return redirect(route('import.form'))->with('success', 'Products imported successfully.');

        $file = $request->file('file');
        $import = new MonthlyBillingImport;
        Excel::import($import, $file);

        // Provide feedback on the import process
        if ($import->skippedRows > 0) {
            return redirect(route('import.form'))->withErrors(['duplicates' => 'Some records were duplicates and were not imported.'])
                ->with([
                    'uploadedRows' => $import->uploadedRows,
                    'skippedRows' => $import->skippedRows,
                    'duplicates' => $import->duplicates,
                ]);
        }

        return redirect(route('import.form'))->with('success', 'File uploaded successfully. '.$import->uploadedRows.' rows were added.');
    }
}
