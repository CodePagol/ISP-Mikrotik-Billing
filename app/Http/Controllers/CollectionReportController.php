<?php

namespace App\Http\Controllers;

use App\Models\CollectionSummary;
use App\Models\CustomersAddress;
use App\Models\User;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;

class CollectionReportController extends Controller
{
    public function __construct()
    {
        if (! auth()->user()->can('payment-collection-report')) {
            abort(403, 'Unauthorized action.');
        }

        return true;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // if(auth()->user()->can('view collection report')){
        //     abort(403);
        // }
        if ($request->ajax()) {
            $from = $request->fromDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $to = $request->toDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');
            $customer = $request->collector;

            // Build query
            $query = CollectionSummary::with('customer', 'customer.pppUser')->whereBetween('collection_date', [$from, $to]);
            if ($customer) {
                $query->where('collected_by', $customer);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('customer_name', function ($row) {
                    return $row->customer->customer_name ?? 'N/A';
                })
                ->addColumn('customers_address', function ($row) {
                    $addresses = CustomersAddress::select('input_type_text', 'input_type_dropdown', 'input_type_textarea')
                        ->where('customer_address_unique_id', $row->customer_collection_unique_id)
                        ->get(); // Fetch all addresses
                    // Initialize an array to hold the formatted addresses
                    $formattedAddresses = [];

                    // Loop through each address record
                    foreach ($addresses as $address) {
                        $addressParts = [];
                        // Add parts of the address if they exist
                        if ($address->input_type_text) {
                            $addressParts[] = $address->input_type_text;
                        }
                        if ($address->input_type_dropdown) {
                            $addressParts[] = $address->input_type_dropdown;
                        }
                        if ($address->input_type_textarea) {
                            $addressParts[] = $address->input_type_textarea;
                        }
                        // Join the parts and add to the formatted addresses array
                        $formattedAddresses[] = implode(', ', $addressParts);
                    }

                    // Join all formatted addresses into a single string
                    return implode(', ', $formattedAddresses); // Use a semicolon or any separator you prefer
                })
                ->addColumn('ppp_secret', function ($row) {
                    $pppSecret = $row->customer->pppUser;

                    return $pppSecret ? $pppSecret->username : 'N/A';
                })
                ->rawColumns(['customers_address', 'ppp_secret'])
                ->make(true);
        }
        $collectors = User::select('name', 'email')->get();

        return view('reports.collections.index', compact('collectors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
