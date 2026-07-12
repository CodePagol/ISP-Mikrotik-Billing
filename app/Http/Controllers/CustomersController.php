<?php

namespace App\Http\Controllers;

use App\Models\BillingInfo;
use App\Models\CustomersInfo;
use App\Models\PaymentSummary;
use App\Models\PPPSecrets;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function __construct()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $statusFilter = ['pending', 'disable', 'free', 'inactive'];

            $data = CustomersInfo::with(['billing', 'pppUser', 'customerAddress']);

            // Filter logic
            switch ($request->filter) {
                case 'without_collection':
                    $data->whereHas('billing', function ($q) {
                        $q->where('paid_amount', 0);
                    })->whereNotIn('status', $statusFilter);
                    break;

                case 'collection':
                    $data->whereHas('billing', function ($q) {
                        $q->where('paid_amount', '>', 0);
                    })->whereNotIn('status', $statusFilter);
                    break;

                case 'pending':
                case 'disable':
                case 'free':
                case 'inactive':
                    $data->where('status', $request->filter);
                    break;

                default:
                    $data->whereNotIn('status', $statusFilter);
            }

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('customers_address', function ($row) {
                    $formattedAddresses = [];

                    foreach ($row->customerAddress as $address) {
                        $parts = array_filter([
                            $address->input_type_text,
                            $address->input_type_dropdown,
                            $address->input_type_textarea,
                        ]);
                        $formattedAddresses[] = implode(', ', $parts);
                    }

                    return implode(', ', $formattedAddresses);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $enable_btn = '<button id="enable-customer" data-id="'.encrypt($row->customer_unique_id).'" class="btn btn-success btn-sm"><i class="bi bi-power"></i></button>';
                    $delete_btn = '<button id="delete-customer" data-id="'.encrypt($row->customer_unique_id).'" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>';
                    $customers_edit_btn = '<a href="'.route('customers.edit', encrypt($row->customer_unique_id)).'" id="edit-'.encrypt($row->customer_unique_id).'" class="edit btn btn-primary btn-sm" target="_blank"><i class="bi bi-pencil-square"></i></a>';
                    $bill_edit_btn = '<button id="edit-bill" data-id="'.encrypt($row->customer_unique_id).'" data-bs-toggle="modal" data-bs-target="#edit-bill-modal" class="bill btn btn-info btn-sm"><i class="bi bi-journal-arrow-up"></i></button>';

                    if ($row->status === 'pending') {
                        if (hasAccess(['Super Admin'], ['edit-customer', 'enable-pending-customer', 'delete-customer'])) {
                            return $btn.$customers_edit_btn.$enable_btn.$delete_btn;
                        } elseif (hasAccess(['Super Admin'], ['edit-customer'])) {
                            return $btn.$customers_edit_btn;
                        } elseif (hasAccess(['Super Admin'], ['enable-pending-customer'])) {
                            return $btn.$enable_btn;
                        } elseif (hasAccess(['Super Admin'], ['delete-customer'])) {
                            return $btn.$delete_btn;
                        }
                    } elseif ($row->status === 'disable') {
                        if (hasAccess(['Super Admin'], ['edit-customer', 'enable-pending-customer', 'delete-customer'])) {
                            return $btn.$customers_edit_btn.$enable_btn.$delete_btn;
                        } elseif (hasAccess(['Super Admin'], ['edit-customer'])) {
                            return $btn.$customers_edit_btn;
                        } elseif (hasAccess(['Super Admin'], ['enable-pending-customer'])) {
                            return $btn.$enable_btn;
                        } elseif (hasAccess(['Super Admin'], ['delete-customer'])) {
                            return $btn.$delete_btn;
                        }
                    } elseif ($row->status === 'inactive') {
                        if (hasAccess(['Super Admin'], ['edit-customer', 'delete-customer'])) {
                            return $btn.$customers_edit_btn.$delete_btn;
                        } elseif (hasAccess(['Super Admin'], ['edit-customer'])) {
                            return $btn.$customers_edit_btn;
                        } elseif (hasAccess(['Super Admin'], ['delete-customer'])) {
                            return $btn.$delete_btn;
                        }
                    } else {
                        if (hasAccess(['Super Admin'], ['edit-customer', 'update-bill'])) {
                            return $btn.$customers_edit_btn.$bill_edit_btn;
                        } elseif (hasAccess(['Super Admin'], ['edit-customer'])) {
                            return $btn.$customers_edit_btn;
                        } elseif (hasAccess(['Super Admin'], ['update-bill'])) {
                            return $btn.$bill_edit_btn;
                        }
                    }
                })
                ->addColumn('disable_details', function ($row) {
                    return 'Disable Time:'.$row->disable_count.'<br> Auto Disable:'.($row->billing->auto_disable ?? '').' <br> Extra Month:'.($row->billing->auto_disable_month ?? '');
                })
                ->rawColumns(['customers_address', 'action', 'disable_details'])
                ->make(true);
        }

        return view('customers');
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
        $unique_id = decrypt($id);
        $data = CustomersInfo::where('customer_unique_id', $unique_id)
            ->join('billing_infos', 'customers_infos.customer_unique_id', '=', 'billing_infos.customer_bill_unique_id')
            ->leftJoin('p_p_p_secrets', 'p_p_p_secrets.id', '=', 'customers_infos.ppp_user_id')
            ->select('customers_infos.customer_unique_id', 'customers_infos.customer_name', 'billing_infos.*', 'p_p_p_secrets.username as username')
            ->first();

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('edit-customer', [
            'customerId' => $id, // Send customerId to the Blade file
        ]);
    }

    public function customerEnable(string $id)
    {
        $unique_id = decrypt($id);
        $bill = BillingInfo::where('customer_bill_unique_id', $unique_id)->first();

        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => 'Billing Information not found',
            ]);
        }

        $customer = CustomersInfo::where('customer_unique_id', $unique_id)
            ->with('pppUser')
            ->first();

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ]);
        }

        try {
            \DB::beginTransaction();

            // 1. If Payment Summary does not exist for this month, create it
            $summaryExists = PaymentSummary::where('customer_payment_unique_id', $unique_id)
                ->where('summary_date', Carbon::now()->firstOfMonth()->format('Y-m-d'))
                ->exists();

            if (! $summaryExists) {
                PaymentSummary::create([
                    'customer_payment_unique_id' => $unique_id,
                    'summary_date' => Carbon::now()->firstOfMonth()->format('Y-m-d'),
                    'monthly_rent' => $bill->monthly_rent,
                    'additional_charge' => $bill->additional_charge,
                    'vat' => $bill->vat,
                    'previous_due' => $bill->previous_due,
                    'advance' => $bill->advance,
                    'discount' => $bill->discount,
                ]);
            }

            // 2. Set customer status to active and reset disable_count
            $customer->status = 'active';
            $customer->disable_count = 0;
            $customer->save();

            // 3. If auto_disable_date * auto_disable_month is equal to or less than today, increment by 1 month as needed
            if ($bill->auto_disable_date) {
                $autoDisableDate = Carbon::parse($bill->auto_disable_date)->startOfDay();
                $autoDisableMonth = $bill->auto_disable_month;
                $disableDate = $autoDisableDate->copy()->addMonths($autoDisableMonth);

                if ($disableDate->lte(today())) {
                    while ($disableDate->lte(today())) {
                        $disableDate->addMonth();
                    }

                    $bill->auto_disable_date = $disableDate->copy()->subMonths($autoDisableMonth)->toDateString();
                    $bill->save();
                }
            }

            if ($customer->pppUser) {
                if (! empty($customer->pppUser->router_name)) {
                    // 4. Call enablePPPSecret in Mikrotik (throws on failure)
                    app(MikrotikController::class)->enablePPPSecret(
                        $unique_id,
                        $customer->pppUser->router_name,
                        $customer->pppUser->username
                    );

                    // 5. Restore the profile on the router
                    app(MikrotikController::class)->updatePPPSecret(
                        $customer->pppUser->router_name,
                        $customer->pppUser->username,
                        'profile',
                        $customer->pppUser->profile
                    );

                    // 6. Kick any active session (non-critical)
                    try {
                        app(MikrotikController::class)->singleWrite(
                            $customer->pppUser->router_name,
                            '/ppp active remove [find name="'.$customer->pppUser->username.'"]'
                        );
                    } catch (\Exception $e) {
                        \Log::debug('customerEnable: active session removal skipped: '.$e->getMessage());
                    }
                }

                // 7. Sync ppp_secrets.status
                PPPSecrets::where('id', $customer->ppp_user_id)->update(['status' => 'active']);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer enabled successfully'.($customer->pppUser ? ' and PPP secret activated' : ''),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("customerEnable failed for {$unique_id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable customer on router: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // return response()->json(\decrypt($id));
        try {
            BillingInfo::where('customer_bill_unique_id', decrypt($id))->update([
                'monthly_rent' => $request->monthly_rent,
                'additional_charge' => $request->additional_charge,
                'discount' => $request->discount,
                'advance' => $request->advance,
                'vat' => $request->vat,
                'total_amount' => $request->total_amount,
                'due_amount' => $request->due_amount,
                'auto_disable' => $request->auto_disable === 'on' ? 1 : 0,
            ]);

            return response()->json(['success' => true, 'message' => 'Billing Information updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Decrypt the ID
            $decryptedId = decrypt($id);

            // Find the customer and eager-load the PPP user relationship
            $customerDelete = CustomersInfo::where('customer_unique_id', $decryptedId)->with('pppUser')->first();

            // If customer not found, return an error response
            if (! $customerDelete) {
                return response()->json(['error' => true, 'message' => 'Customer not found']);
            }

            \DB::beginTransaction();

            // Check if PPP User exists and delete it from Mikrotik (throws on failure)
            if ($customerDelete->pppUser && ! empty($customerDelete->pppUser->router_name)) {
                app(MikrotikController::class)->removePPPSecret(
                    $decryptedId,
                    $customerDelete->pppUser->router_name,
                    $customerDelete->pppUser->username
                );
            }

            // Delete the customer
            $customerDelete->delete();

            \DB::commit();

            return response()->json(['success' => true, 'message' => 'Customer deleted successfully']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Customer destroy failed: '.$e->getMessage());

            // Handle decryption errors or unexpected exceptions
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }
}
