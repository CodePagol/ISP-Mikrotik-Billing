<?php

namespace App\Livewire\Reseller;

use App\Models\CustomersInfo;
use App\Models\RouterList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Livewire\Attributes\On;
use Livewire\Component;
use Yajra\DataTables\Facades\DataTables;

class ResellerCustomerList extends Component
{
    public $editingCustomerId = null;

    public $routers = [];

    public function render()
    {
        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            abort(403);
        }

        $this->routers = RouterList::all();

        return view('livewire.reseller.customer-list')->layout('layouts.app');
    }

    public function getData(Request $request)
    {
        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            abort(403, 'Unauthorized action.');
        }

        $statusFilter = ['pending', 'disable', 'free', 'inactive'];

        $data = CustomersInfo::query()
            ->where('reseller_id', $reseller->id)
            ->with(['billing', 'pppUser', 'customerAddress', 'official', 'package'])
            ->select('customers_infos.*');

        // Router Filter
        if ($request->router_name) {
            $data->whereHas('pppUser', function ($q) use ($request) {
                $q->where('p_p_p_secrets.router_name', $request->router_name);
            });
        }

        // Filter logic
        switch ($request->filter) {
            case 'all':
                break;

            case 'all_active':
                $data->whereNotIn('status', $statusFilter);
                break;

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
            ->addColumn('customer_identity', function ($row) {
                // Round avatar: photo if exists, else coloured initials
                $initials = mb_strtoupper(mb_substr($row->customer_name ?? '', 0, 1, 'UTF-8'), 'UTF-8');
                $colors = ['#4f46e5', '#0ea5e9', '#16a34a', '#ea580c', '#dc2626', '#7c3aed', '#0891b2', '#ca8a04'];
                $bgColor = $colors[abs(crc32($row->customer_unique_id)) % count($colors)];

                if (! empty($row->photo_url)) {
                    $avatar = '<img src="'.asset('storage/'.$row->photo_url).'" '
                        .'alt="'.e($row->customer_name).'" '
                        .'class="avatar avatar-2xl rounded-circle">';
                } else {
                    $avatar = '<div class="avatar avatar-2xl rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="background:'.$bgColor.';font-size:15px;">'
                        .$initials.'</div>';
                }

                return '<div class="d-flex align-items-center gap-2">'.
                    $avatar.
                    '<div>'.
                    '<div class="fw-bold text-dark">'.$row->customer_name.
                    (! empty($row->contact_person && $row->contact_person != '-') ? '<small class="text-muted"> ('.$row->contact_person.')</small>' : '').'</div>'.

                    '<div class="small">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary pe-2">'.$row->customer_unique_id.'</span> '.

                        (! empty($row->mobile)
                            ? '<span class="text-muted"><i class="bi bi-telephone text-success"></i> '.$row->mobile.'</span> '
                            : '').

                        (! empty($row->contact_email)
                            ? '<span class="text-muted"><i class="bi bi-envelope text-success"></i> '.$row->contact_email.'</span>'
                            : '').

                    '</div>'.
                    '</div></div>';
            })
            ->addColumn('customers_address', function ($row) {
                $formattedAddresses = [];
                foreach ($row->customerAddress as $address) {
                    $parts = array_filter([$address->input_type_text, $address->input_type_dropdown, $address->input_type_textarea]);
                    $formattedAddresses[] = implode(', ', $parts);
                }

                return implode(', ', $formattedAddresses);
            })
            ->addColumn('billing_breakdown', function ($row) {
                $rent = number_format($row->billing?->monthly_rent ?? 0, 2);
                $p_due = number_format($row->billing?->previous_due ?? 0, 2);
                $a_charge = number_format($row->billing?->additional_charge ?? 0, 2);
                $vat = $row->billing?->vat ?? 0;
                $disc = number_format($row->billing?->discount ?? 0, 2);
                $adv = number_format($row->billing?->advance ?? 0, 2);

                return '<div class="small text-muted" style="font-size: 0.7rem; line-height: 1.4;">'.
                       '<div><i class="bi bi-calendar3 me-1"></i>Rent: <span class="text-dark fw-bold">'.$rent.',</span></div>'.
                       '<div><i class="bi bi-exclamation-triangle me-1"></i>P.Due: <span class="text-dark fw-bold">'.$p_due.',</span></div>'.
                       '<div><i class="bi bi-plus-circle me-1"></i>Add: <span class="text-dark fw-bold">'.$a_charge.'</span> | <i class="bi bi-percent me-1"></i>Vat: <span class="text-dark fw-bold">'.$vat.',</span></div>'.
                       '<div><i class="bi bi-tag me-1"></i>Disc: <span class="text-danger fw-bold">'.$disc.'</span> | <i class="bi bi-wallet-fill me-1"></i>Adv: <span class="text-success fw-bold">'.$adv.'</span></div>'.
                       '</div>';
            })
            ->addColumn('connection_details', function ($row) {
                return '<div class="mb-1 fw-bold text-info"><i class="bi bi-person-fill"></i> '.($row->pppUser->username ?? 'N/A').'</div>'.
                       '<span class="badge bg-info bg-opacity-10 text-white border border-info border-opacity-25">'.
                       '<i class="bi bi-router-fill me-1"></i>'.($row->pppUser->router_name ?? 'N/A').'</span>';
            })
            ->addColumn('billing_summary', function ($row) {
                $bill = number_format($row->billing?->total_amount ?? 0, 2);
                $paid = number_format($row->billing?->paid_amount ?? 0, 2);
                $due = number_format($row->billing?->due_amount ?? 0, 2);

                return '<div class="billing-card small">'.
                       '<div class="d-flex justify-content-between"><span>Bill:</span> <span class="fw-bold text-primary">'.$bill.'</span></div>'.
                       '<div class="d-flex justify-content-between"><span>Paid:</span> <span class="fw-bold text-success">'.$paid.'</span></div>'.
                       '<hr class="my-1">'.
                       '<div class="d-flex justify-content-between"><span>Due:</span> <span class="fw-bold text-danger">'.$due.'</span></div>'.
                       '</div>';
            })
            ->addColumn('disable_details', function ($row) {
                $statusClass = $row->billing?->auto_disable == 1 ? 'bg-danger text-white' : 'bg-light text-muted';
                $disableDate = $row->billing?->auto_disable_date ? Carbon::parse($row->billing->auto_disable_date)->format('d-M-Y') : 'N/A';

                return '<div class="small fw-bold mb-1">Count: '.$row->disable_count.'</div>'.
                       '<span class="badge badge-soft '.$statusClass.' mb-1">Auto: '.($row->billing?->auto_disable == 1 ? 'Yes' : 'No').'</span>'.
                       '<div class="text-primary fw-bold" style="font-size: 0.75rem"><i class="bi bi-calendar-x me-1"></i>'.$disableDate.'</div>'.
                       '<div class="text-muted" style="font-size: 0.7rem">Ext: '.($row->billing?->auto_disable_month ?? 0).' Mon</div>';
            })
            ->addColumn('action', function ($row) {
                $delete_btn = '<button onclick="confirmDeleteCustomer(\''.encrypt($row->customer_unique_id).'\')" class="btn btn-danger"><i class="bi bi-trash"></i></button>';
                $customers_edit_btn = '<button onclick="Livewire.dispatch(\'open-edit-customer\', { id: \''.encrypt($row->customer_unique_id).'\' })" class="edit btn btn-primary"><i class="bi bi-pencil-square"></i></button>';

                return '<div class="action-btns d-flex justify-content-center">'.$customers_edit_btn.$delete_btn.'</div>';
            })
            ->rawColumns(['customer_identity', 'customers_address', 'billing_breakdown', 'connection_details', 'billing_summary', 'action', 'disable_details'])
            ->make(true);
    }

    #[On('delete-customer')]
    public function deleteCustomer($id): void
    {
        $id = is_array($id) ? $id['id'] ?? $id : $id;

        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            flash()->addError('Unauthorized action.');
            $this->dispatch('customer-action-done');

            return;
        }

        try {
            $decryptedId = decrypt($id);
            $customerDelete = CustomersInfo::where('reseller_id', $reseller->id)
                ->where('customer_unique_id', $decryptedId)
                ->first();

            if (! $customerDelete) {
                flash()->addError('Customer not found.');
                $this->dispatch('customer-action-done');

                return;
            }

            \DB::beginTransaction();
            $pppUser = $customerDelete->pppUser;
            if ($pppUser) {
                if (! empty($pppUser->router_name)) {
                    app(\App\Http\Controllers\MikrotikController::class)->removePPPSecret(
                        $decryptedId,
                        $pppUser->router_name,
                        $pppUser->username
                    );
                }
                $pppUser->delete();
            }
            $customerDelete->delete();
            \DB::commit();

            flash()->addSuccess('Customer deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            flash()->addError('Failed to delete customer: '.$e->getMessage());
        }

        $this->dispatch('customer-action-done');
    }

    #[On('open-edit-customer')]
    public function openEditCustomerModal($id)
    {
        $this->editingCustomerId = is_array($id) ? $id['id'] ?? $id : $id;
    }

    public function closeEditCustomerModal()
    {
        $this->editingCustomerId = null;
        $this->dispatch('customer-action-done');
    }
}
