<?php

namespace App\Http\Controllers;

use App\Imports\CollectionImport;
use App\Imports\MonthlyBillingImport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\PPPSecrets;
use App\Models\CustomersInfo;
use App\Models\BillingInfo;
use App\Models\OfficialInfo;
use Carbon\Carbon;
use Redirect;

class ImportController extends Controller
{
    public function importForm(Request $request)
    {
        $data = null;
        if ($request->isMethod('post') && $request->file('file')) {
            $file = $request->file('file');

            try {
                // Read data from the Excel file using SimpleExcelReader
                $importData = SimpleExcelReader::create($file->getRealPath())->getRows()->toArray();
                $data = $importData;
            } catch (\Exception $e) {
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

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $uploadedRows = 0;
        $skippedRows = 0;
        $skippedUsernames = [];

        try {
            $reader = SimpleExcelReader::create($filePath);
            $rows = $reader->getRows();

            // Fetch customer ID settings
            $prefix = siteUrlSettings('customer_id_prefix') ?: 'FCNET';
            $lastCustomerUniqueId = CustomersInfo::orderBy('id', 'desc')->value('customer_unique_id');
            if ($lastCustomerUniqueId) {
                if (str_starts_with($lastCustomerUniqueId, $prefix)) {
                    $lastIdCount = (int) substr($lastCustomerUniqueId, strlen($prefix));
                } else {
                    if (preg_match('/(\d+)$/', $lastCustomerUniqueId, $matches)) {
                        $lastIdCount = (int) $matches[1];
                    } else {
                        $lastIdCount = 99;
                    }
                }
            } else {
                $lastIdCount = 99;
            }

            foreach ($rows as $row) {
                // Normalize keys to lowercase and trim spaces/underscores for flexible column matching
                $normalizedRow = [];
                foreach ($row as $key => $val) {
                    $normKey = strtolower(str_replace([' ', '_', '-'], '', $key));
                    $normalizedRow[$normKey] = $val;
                }

                // Match username
                $pppoeUsername = null;
                foreach (['pppoeusername', 'pppoeuser', 'username', 'user'] as $key) {
                    if (isset($normalizedRow[$key]) && !empty($normalizedRow[$key])) {
                        $pppoeUsername = trim($normalizedRow[$key]);
                        break;
                    }
                }

                if (!$pppoeUsername) {
                    $skippedRows++;
                    continue;
                }

                // Match unique ID
                $importedUniqueId = null;
                foreach (['customeruniqueid', 'uniqueid', 'customerid', 'clientid', 'cid', 'id'] as $key) {
                    if (isset($normalizedRow[$key]) && !empty($normalizedRow[$key])) {
                        $importedUniqueId = trim($normalizedRow[$key]);
                        break;
                    }
                }

                // Find corresponding PPPoE secret
                $pppSecret = PPPSecrets::where('username', $pppoeUsername)->first();

                if (!$pppSecret) {
                    $skippedRows++;
                    $skippedUsernames[] = $pppoeUsername;
                    continue;
                }

                // Extract fields
                $name = $normalizedRow['customername'] ?? $normalizedRow['name'] ?? $pppoeUsername;
                $address = $normalizedRow['address'] ?? $normalizedRow['customeraddress'] ?? $normalizedRow['location'] ?? null;
                $mobile = $normalizedRow['mobile'] ?? $normalizedRow['phone'] ?? $normalizedRow['mobileno'] ?? $normalizedRow['phoneno'] ?? $normalizedRow['contact'] ?? $normalizedRow['contactno'] ?? null;
                $altMobile = $normalizedRow['alternativemobile'] ?? $normalizedRow['altmobile'] ?? $normalizedRow['alternativephone'] ?? $normalizedRow['altphone'] ?? null;

                // Find or create customer info
                $customer = CustomersInfo::where('ppp_user_id', $pppSecret->id)->first();

                if ($customer) {
                    // Update
                    $customer->customer_name = $name;
                    if ($address) $customer->address = $address;
                    if ($mobile) $customer->mobile = $mobile;
                    if ($altMobile) $customer->alternative_mobile = $altMobile;
                    
                    if ($customer->status === 'pending') {
                        $customer->status = 'active';
                    }

                    // Check if unique ID needs updating to match old ID in Excel
                    if ($importedUniqueId && $customer->customer_unique_id !== $importedUniqueId) {
                        $oldUniqueId = $customer->customer_unique_id;
                        
                        $customer->customer_unique_id = $importedUniqueId;
                        $customer->save();

                        // Cascade updates to related tables
                        BillingInfo::where('customer_bill_unique_id', $oldUniqueId)
                            ->update(['customer_bill_unique_id' => $importedUniqueId]);

                        OfficialInfo::where('customer_office_unique_id', $oldUniqueId)
                            ->update(['customer_office_unique_id' => $importedUniqueId]);

                        \App\Models\CustomersAddress::where('customer_address_unique_id', $oldUniqueId)
                            ->update(['customer_address_unique_id' => $importedUniqueId]);

                        \App\Models\PaymentSummary::where('customer_payment_unique_id', $oldUniqueId)
                            ->update(['customer_payment_unique_id' => $importedUniqueId]);

                        \App\Models\CollectionSummary::where('customer_collection_unique_id', $oldUniqueId)
                            ->update(['customer_collection_unique_id' => $importedUniqueId]);
                    } else {
                        $customer->save();
                    }

                    $uploadedRows++;
                } else {
                    // Create
                    $newId = $importedUniqueId;
                    if (!$newId) {
                        $lastIdCount++;
                        $newId = $prefix.$lastIdCount;
                    }

                    CustomersInfo::create([
                        'customer_unique_id' => $newId,
                        'ppp_user_id' => $pppSecret->id,
                        'customer_name' => $name,
                        'address' => $address,
                        'mobile' => $mobile,
                        'alternative_mobile' => $altMobile,
                        'status' => 'active',
                        'connection_date' => Carbon::now(),
                    ]);

                    BillingInfo::create([
                        'customer_bill_unique_id' => $newId,
                        'billing_type' => 'prepaid',
                        'auto_disable_date' => Carbon::now()
                    ]);

                    OfficialInfo::create([
                        'customer_office_unique_id' => $newId
                    ]);

                    $uploadedRows++;
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: '.$e->getMessage());
        }

        $message = "File processed successfully. {$uploadedRows} customers updated/added.";
        if ($skippedRows > 0) {
            $message .= " {$skippedRows} rows skipped (usernames not found in Mikrotik PPPoE Secrets: " . implode(', ', array_slice($skippedUsernames, 0, 10)) . ")";
        }

        return redirect(route('import.form'))->with('success', $message);
    }

    private function findCustomer($normalizedRow)
    {
        // 1. Try to find by unique id
        $uniqueId = null;
        foreach (['customeruniqueid', 'uniqueid', 'customerid', 'clientid', 'cid', 'id'] as $key) {
            if (isset($normalizedRow[$key]) && $normalizedRow[$key] !== '') {
                $uniqueId = trim($normalizedRow[$key]);
                break;
            }
        }
        
        if ($uniqueId) {
            $customer = CustomersInfo::where('customer_unique_id', $uniqueId)->first();
            if ($customer) {
                return $customer;
            }
        }
        
        // 2. Try to find by username
        $username = null;
        foreach (['pppoeusername', 'pppoeuser', 'username', 'user'] as $key) {
            if (isset($normalizedRow[$key]) && $normalizedRow[$key] !== '') {
                $username = trim($normalizedRow[$key]);
                break;
            }
        }
        
        if ($username) {
            $pppSecret = PPPSecrets::where('username', $username)->first();
            if ($pppSecret) {
                $customer = CustomersInfo::where('ppp_user_id', $pppSecret->id)->first();
                if ($customer) {
                    return $customer;
                }
            }
        }
        
        return null;
    }

    public function collectionForm(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $uploadedRows = 0;
        $skippedRows = 0;
        $skippedUsernames = [];

        try {
            $reader = SimpleExcelReader::create($filePath);
            $rows = $reader->getRows();
            $collectedBy = strtok(auth()->user()->email ?? 'admin@example.com', '@');

            foreach ($rows as $row) {
                $normalizedRow = [];
                foreach ($row as $key => $val) {
                    $normKey = strtolower(str_replace([' ', '_', '-'], '', $key));
                    $normalizedRow[$normKey] = $val;
                }

                $customer = $this->findCustomer($normalizedRow);

                if (!$customer) {
                    $skippedRows++;
                    $skippedUsernames[] = $normalizedRow['customerid'] ?? $normalizedRow['username'] ?? 'Unknown';
                    continue;
                }

                // Parse amount
                $amount = 0;
                foreach (['collectionamount', 'amount', 'collectedamount', 'payment', 'paid'] as $key) {
                    if (isset($normalizedRow[$key]) && $normalizedRow[$key] !== '') {
                        $amount = (float) $normalizedRow[$key];
                        break;
                    }
                }

                if ($amount <= 0) {
                    $skippedRows++;
                    $skippedUsernames[] = ($customer->customer_unique_id . " (Amount: 0)");
                    continue;
                }

                // Date
                $dateStr = $normalizedRow['collectiondate'] ?? $normalizedRow['date'] ?? null;
                $date = Carbon::now();
                if ($dateStr) {
                    try {
                        $date = Carbon::parse($dateStr);
                    } catch (\Exception $e) {
                        $date = Carbon::now();
                    }
                }

                \App\Models\CollectionSummary::create([
                    'customer_collection_unique_id' => $customer->customer_unique_id,
                    'collection_date' => $date,
                    'collection_amount' => $amount,
                    'collected_by' => $normalizedRow['collectedby'] ?? $collectedBy,
                    'payment_type' => $normalizedRow['paymenttype'] ?? 'prepaid',
                    'payment_method' => $normalizedRow['paymentmethod'] ?? 'cash',
                    'transaction_id' => $normalizedRow['transactionid'] ?? null,
                    'payment_status' => $normalizedRow['paymentstatus'] ?? 'paid',
                    'bill_month' => $normalizedRow['billmonth'] ?? $date->format('Y-m'),
                    'invoice_no' => $normalizedRow['invoiceno'] ?? null,
                ]);

                $uploadedRows++;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: '.$e->getMessage());
        }

        $message = "File processed successfully. {$uploadedRows} collections imported.";
        if ($skippedRows > 0) {
            $message .= " {$skippedRows} rows skipped (details not found or invalid: " . implode(', ', array_slice($skippedUsernames, 0, 10)) . ")";
        }

        return redirect(route('import.form'))->with('success', $message);
    }

    public function monthlyBillForm(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $uploadedRows = 0;
        $skippedRows = 0;
        $skippedUsernames = [];

        try {
            $reader = SimpleExcelReader::create($filePath);
            $rows = $reader->getRows();

            foreach ($rows as $row) {
                $normalizedRow = [];
                foreach ($row as $key => $val) {
                    $normKey = strtolower(str_replace([' ', '_', '-'], '', $key));
                    $normalizedRow[$normKey] = $val;
                }

                $customer = $this->findCustomer($normalizedRow);

                if (!$customer) {
                    $skippedRows++;
                    $skippedUsernames[] = $normalizedRow['customerid'] ?? $normalizedRow['username'] ?? 'Unknown';
                    continue;
                }

                // Extract parameters
                $monthlyRent = null;
                foreach (['monthlyrent', 'rent', 'amount', 'bill', 'billamount'] as $key) {
                    if (isset($normalizedRow[$key]) && $normalizedRow[$key] !== '') {
                        $monthlyRent = (float) $normalizedRow[$key];
                        break;
                    }
                }

                // If no rent in Excel, fall back to customer's package price if available
                if ($monthlyRent === null) {
                    $monthlyRent = $customer->package ? (float) $customer->package->price : 0;
                }

                // Date
                $dateStr = $normalizedRow['summarydate'] ?? $normalizedRow['date'] ?? null;
                $date = Carbon::now();
                if ($dateStr) {
                    try {
                        $date = Carbon::parse($dateStr);
                    } catch (\Exception $e) {
                        $date = Carbon::now();
                    }
                }

                \App\Models\PaymentSummary::create([
                    'customer_payment_unique_id' => $customer->customer_unique_id,
                    'summary_date' => $date,
                    'monthly_rent' => $monthlyRent,
                    'additional_charge' => isset($normalizedRow['additionalcharge']) ? (float)$normalizedRow['additionalcharge'] : 0,
                    'discount' => isset($normalizedRow['discount']) ? (float)$normalizedRow['discount'] : 0,
                    'advance' => isset($normalizedRow['advance']) ? (float)$normalizedRow['advance'] : 0,
                    'vat' => isset($normalizedRow['vat']) ? (float)$normalizedRow['vat'] : 0,
                    'previous_due' => isset($normalizedRow['previousdue']) ? (float)$normalizedRow['previousdue'] : 0,
                ]);

                $uploadedRows++;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: '.$e->getMessage());
        }

        $message = "File processed successfully. {$uploadedRows} monthly bills imported.";
        if ($skippedRows > 0) {
            $message .= " {$skippedRows} rows skipped (details not found: " . implode(', ', array_slice($skippedUsernames, 0, 10)) . ")";
        }

        return redirect(route('import.form'))->with('success', $message);
    }
}
