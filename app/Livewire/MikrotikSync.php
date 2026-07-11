<?php

namespace App\Livewire;

use App\Http\Controllers\MikrotikController;
use App\Models\BillingInfo;
use App\Models\CustomersInfo;
use App\Models\OfficialInfo;
use App\Models\PPPSecrets;
use App\Models\RouterList;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class MikrotikSync extends Component
{
    use WithPagination;

    public $RouterListId;

    public $router_name;

    public $ip_address;

    public $username;

    public $password;

    public $ssh_port;

    public $api_port;

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['mikrotik-setup'])) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function render()
    {
        // Pagination of routers
        $routers = RouterList::paginate(10);
        $routers->map(function ($router) {
            $router->user_list_count = PPPSecrets::where('router_name', $router->router_name)->where('status', '!=', 'removed')->count();

            return $router;
        });

        return view('livewire.mikrotik-sync', ['routers' => $routers])->layout('layouts.app');
    }

    public function rules()
    {
        return [
            'router_name' => ['required', 'string', 'max:255', 'unique:router_lists,router_name,'.$this->RouterListId],
            'ip_address' => ['required', 'ip',
                function ($attribute, $value, $fail) {
                    $exists = RouterList::all()->filter(function ($router) use ($value) {
                        if ($this->RouterListId && $router->id == $this->RouterListId) {
                            return false;
                        }
                        
                        if ($router->ip_address !== $value) {
                            return false;
                        }

                        $sshMatch = ($router->ssh_port !== null && $this->ssh_port !== null && (int)$router->ssh_port === (int)$this->ssh_port);
                        $apiMatch = ($router->api_port !== null && $this->api_port !== null && (int)$router->api_port === (int)$this->api_port);

                        return $sshMatch || $apiMatch;
                    })->isNotEmpty();
                    if ($exists) {
                        $fail('This IP address is already used with the same SSH or API port.');

                        return;
                    }
                },
            ],
            'username' => 'required|string|max:255',
            'password' => 'required_if:RouterListId,null|string|max:255',
            'ssh_port' => 'nullable|required_without:api_port|integer|min:1|max:65535',
            'api_port' => 'nullable|required_without:ssh_port|integer|min:1|max:65535',
        ];
    }

    public function submit()
    {
        $this->validate($this->rules());

        // Data preparation for creating or updating a router
        $data = [
            'router_name' => $this->router_name,
            'ip_address' => $this->ip_address,
            'username' => $this->username,
            'ssh_port' => $this->ssh_port ?? null,
            'api_port' => $this->api_port ?? null,
        ];

        // Include password only if provided
        if (! empty($this->password)) {
            $data['password'] = $this->password;
        }

        RouterList::updateOrCreate(
            ['id' => $this->RouterListId],
            $data
        );

        $this->reset();
        flash()->success('Router added successfully!');
    }

    public function connect_toggle($routerId)
    {
        $router = RouterList::find($routerId);
        if ($router) {  // Check if router exists
            $router->action = $router->action === 'connected' ? 'disconnected' : 'connected';
            $router->save();
            flash()->success('Router '.$router->router_name.' is '.$router->action.' successfully!');
            // $this->dispatch('showToast', 'Router ' . $router->router_name . ' is ' . $router->action . ' successfully!', 'success');
            if ($router->action === 'connected') {
                $this->dataSync($routerId);
            }
        } else {
            flash()->error('Router not found!');
            // $this->dispatch('showToast', 'Router not found!', 'error');
        }
    }

    public function userSync($pppSecrets)
    {
        foreach ($pppSecrets as $routerName => $result) {
            if (! is_array($result)) {
                flash()->error("Invalid response for router {$routerName}");

                continue;
            }

            if (empty($result['status'])) {
                $msg = $result['message'] ?? 'Connection failed';
                flash()->error("Skipped synchronizing {$routerName}: {$msg}");

                continue;
            }

            $users = $result['data'] ?? [];
            if (! is_array($users)) {
                $users = [];
            }

            $createdCount = 0;
            $updatedCount = 0;
            $unchangedCount = 0;

            DB::beginTransaction();
            try {
                // 1. Mark existing users for this router as removed temporarily
                PPPSecrets::where('router_name', $routerName)
                    ->where('status', '!=', 'removed')
                    ->update(['status' => 'removed']);

                // 2. Pre-load all existing secrets for this router
                $existingSecrets = PPPSecrets::where('router_name', $routerName)
                    ->get()
                    ->keyBy(fn ($item) => strtolower($item->username));

                // 3. Pre-fetch latest customer unique ID count
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

                $statusGroups = []; // For bulk status updates

                foreach ($users as $user) {
                    $username = $user['name'];
                    $rawPassword = $user['password'] ?? '';

                    $lowerUsername = strtolower($username);
                    $existingSecret = $existingSecrets->get($lowerUsername);

                    // --- REVERSIBLE ENCRYPTION LOGIC ---
                    // If no existing record: Encrypt new password
                    // If existing record:
                    //    - If the decrypted password matches the raw Mikrotik password, keep the raw original encrypted string
                    //      (to avoid updating the database since encrypting yields a new string each time).
                    //    - If the password changed on Mikrotik (or it wasn't encrypted/hashed yet), store the plaintext raw password
                    //      (the model attribute setter will automatically encrypt it).
                    $passwordToStore = $rawPassword;

                    if ($existingSecret) {
                        if ($existingSecret->password === $rawPassword) {
                            // Password unchanged, keep the existing database (encrypted) value to avoid isDirty() triggering
                            $passwordToStore = $existingSecret->getRawOriginal('password');
                        } else {
                            // Password changed, store the new plaintext value (which model setter will encrypt)
                            $passwordToStore = $rawPassword;
                        }
                    } else {
                        // New user, store plaintext (which model setter will encrypt)
                        $passwordToStore = $rawPassword;
                    }


                    try {
                        $lastLoggedOut = null;
                        if (! empty($user['last-logged-out'])) {
                            $dt = Carbon::createFromFormat('M/d/Y H:i:s', $user['last-logged-out']);
                            if ($dt->year >= 2000) {
                                $lastLoggedOut = $dt->format('Y-m-d H:i:s');
                            }
                        }
                    } catch (\Exception $e) {
                        $lastLoggedOut = null;
                    }

                    $expiredProfile = siteUrlSettings('expired_profile_name') ?? 'Expired';
                    $profileFromMikrotik = $user['profile'] ?? '-';
                    $profileToStore = ($profileFromMikrotik === $expiredProfile && $existingSecret)
                        ? $existingSecret->profile
                        : $profileFromMikrotik;

                    // Normalize status from both API (disabled = true/false) and SSH (status = active/disable)
                    $status = 'active';
                    if (isset($user['status'])) {
                        $status = $user['status'];
                    } elseif (isset($user['disabled'])) {
                        $status = ($user['disabled'] === 'true' || $user['disabled'] === true) ? 'disable' : 'active';
                    }

                    $secretData = [
                        'router_name' => $routerName,
                        'username' => $username,
                        'password' => $passwordToStore,
                        'service' => $user['service'] ?? '-',
                        'profile' => $profileToStore,
                        'caller_id' => $user['caller-id'] ?? '',
                        'comment' => $user['comment'] ?? '',
                        'ppp_remote_ip' => $user['ppp_remote_ip'] ?? '',
                        'bandwidth' => trim(($user['limit-bytes-in'] ?? '').'/'.($user['limit-bytes-out'] ?? ''), '/'),
                        'last_logged_out' => $lastLoggedOut,
                        'last_caller_id' => $user['last-caller-id'] ?? '',
                        'last_disconnect_reason' => $user['last-disconnect-reason'] ?? '',
                        'routes' => $user['routes'] ?? '',
                        'ipv6_routes' => $user['ipv6-routes'] ?? '',
                        'status' => $status,
                    ];

                    if ($existingSecret) {
                        $existingSecret->fill($secretData);
                        if ($existingSecret->isDirty()) {
                            $statusChanged = $existingSecret->isDirty('status');
                            $newStatus = $existingSecret->status;

                            $existingSecret->save();
                            $updatedCount++;

                            if ($statusChanged) {
                                $statusGroups[$newStatus][] = $existingSecret->id;
                            }
                        } else {
                            $unchangedCount++;
                            // Even if unchanged, we consider them 'active/not removed' now since they were in Mikrotik.
                            // But since we did `->update(['status' => 'removed'])` earlier on all users,
                            // we need to set the status back if it was unchanged in fill() but is now 'removed' in DB!
                            // Wait, fill() populated 'status' from Mikrotik, and if it wasn't dirty compared to PRE-fetched data,
                            // we didn't save. But the actual DB row is now 'removed'!
                            // Luckily, the $existingSecret instance hasn't re-fetched. It will think it's not dirty.
                            // However, we SHOULD save to revert the 'removed' status.
                            // To fix this cleanly: only the 'dirty' check needs to be mindful of the status change.
                            // Actually, fill() overrides whatever is currently loaded.
                            // If it matches exactly what we loaded at the start, isDirty is false.
                            // But the DB row status was changed to 'removed'. We MUST unconditionally save the status back!
                            PPPSecrets::where('id', $existingSecret->id)->update(['status' => $existingSecret->status]);
                        }
                    } else {
                        $newSecret = PPPSecrets::create($secretData);
                        $createdCount++;
                        $lastIdCount++;
                        $newId = $prefix.$lastIdCount;

                        CustomersInfo::create([
                            'customer_unique_id' => $newId,
                            'ppp_user_id' => $newSecret->id,
                            'customer_name' => $username,
                            'status' => 'pending',
                            'connection_date' => Carbon::now(),
                        ]);
                        BillingInfo::create(['customer_bill_unique_id' => $newId, 'billing_type' => 'prepaid', 'auto_disable_date' => Carbon::now()]);
                        OfficialInfo::create(['customer_office_unique_id' => $newId]);
                    }
                }

                // 4. Bulk update customer statuses
                foreach ($statusGroups as $status => $ids) {
                    CustomersInfo::whereIn('ppp_user_id', $ids)
                        ->whereNotIn('status', ['free', 'pending', 'deleted'])
                        ->update(['status' => $status]);
                }

                // 5. Cleanup
                PPPSecrets::where('router_name', $routerName)
                    ->where('status', 'removed')
                    ->where('updated_at', '<', Carbon::now()->subDays(15))
                    ->delete();

                DB::commit();
                flash()->success("Router {$routerName} synchronized! Created: {$createdCount}, Updated: {$updatedCount}, Unchanged: {$unchangedCount}");
            } catch (\Exception $e) {
                DB::rollBack();
                flash()->error('Error syncing router '.$routerName.': '.$e->getMessage());
            }
        }
    }

    public function dataSync($id)
    {
        $routerList = RouterList::find($id);
        if ($routerList && $routerList->action === 'connected') {
            $pppSecrets = app(MikrotikController::class)->routerList($routerList->router_name, '/ppp/secret/print', '/ppp secret print without-paging terse');
            if (is_array($pppSecrets)) {
                $this->userSync($pppSecrets);
            } else {
                flash()->error($pppSecrets);
            }
        } else {
            flash()->error('Router is not connected or not found!');
        }
    }

    public function allSync()
    {
        $pppSecrets = app(MikrotikController::class)->routerList(null, '/ppp/secret/print', '/ppp secret print without-paging terse');
        if (is_array($pppSecrets)) {
            $this->userSync($pppSecrets);
        } else {
            flash()->error($pppSecrets);
        }
    }

    public function edit($id)
    {
        $router = RouterList::find($id);  // Use meaningful variable name

        if ($router) {
            $this->RouterListId = $id;
            $this->router_name = $router->router_name;
            $this->ip_address = $router->ip_address;
            $this->username = $router->username;
            $this->password = '';  // Reset password field
            $this->ssh_port = $router->ssh_port;
            $this->api_port = $router->api_port;
        }
    }

    public function delete($id)
    {
        $router = RouterList::find($id);  // Check if router exists
        try {
            if ($router) {
                $router->delete();
                flash()->success('Router deleted successfully!');
            } else {
                flash()->error('Router not found!');
            }
        } catch (\Exception $e) {
            flash()->error('Error deleting router: '.$e->getMessage());
        }
    }
}
