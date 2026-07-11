<?php

namespace App\Livewire\Report;

use App\Models\CustomersInfo;
use Illuminate\Http\Request;
use Livewire\Component;
use Yajra\DataTables\DataTables;

class DisReport extends Component
{
    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        return true;
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $data = CustomersInfo::with(['billing', 'package', 'customerAddress', 'official', 'pppUser'])->where('status', 'active')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('ppp_remote_ip', function ($row) {
                    // return optional($row->pppUser)->ppp_remote_ip ?? optional($row->pppUser)->username;
                    return optional($row->pppUser)->username;
                })
                ->addColumn('division', function ($row) {
                    $divisionData = '';

                    foreach ($row->customerAddress as $address) {
                        if (stripos($address->label_name, 'Division') === 0) {
                            $divisionData = implode(', ', array_filter([
                                $address->input_type_text,
                                $address->input_type_dropdown,
                                $address->input_type_textarea,
                            ]));
                            break;
                        }
                    }

                    return $divisionData;
                })
                ->addColumn('district', function ($row) {
                    $districtData = '';

                    foreach ($row->customerAddress as $address) {
                        if (stripos($address->label_name, 'district') === 0) {
                            $districtData = implode(', ', array_filter([
                                $address->input_type_text,
                                $address->input_type_dropdown,
                                $address->input_type_textarea,
                            ]));
                            break;
                        }
                    }

                    return $districtData;
                })
                ->addColumn('thana', function ($row) {
                    $thanaData = '';

                    foreach ($row->customerAddress as $address) {
                        if (stripos($address->label_name, 'thana') === 0) {
                            $thanaData = implode(', ', array_filter([
                                $address->input_type_text,
                                $address->input_type_dropdown,
                                $address->input_type_textarea,
                            ]));
                            break;
                        }
                    }

                    return $thanaData;
                })
                ->addColumn('area', function ($row) {
                    $areaData = '';

                    foreach ($row->customerAddress as $address) {
                        if (stripos($address->label_name, 'area') === 0) {
                            $areaData = implode(', ', array_filter([
                                $address->input_type_text,
                                $address->input_type_dropdown,
                                $address->input_type_textarea,
                            ]));
                            break;
                        }
                    }

                    return $areaData;
                })
                ->make(true);
        }
    }

    public function render()
    {
        return view('livewire.report.dis-report')->layout('layouts.app');
    }
}
