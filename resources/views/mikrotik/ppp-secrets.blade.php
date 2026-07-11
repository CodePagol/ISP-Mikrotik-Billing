<!-- resources/views/mikrotik/ppp-secrets.blade.php -->

<x-app-layout>
    {{-- <div class="mt-10 sm:mt-0">
        <x-form-section submit="addTeamMember">
            <x-slot name="title">
                {{ __('Add Team Member') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Add a new team member to your team, allowing them to collaborate with you.') }}
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6">
                    <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Please provide the email address of the person you would like to add to this team.') }}
                    </div>
                </div>

                <!-- Member Email -->
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" type="email" class="mt-1 block w-full" wire:model="addTeamMemberForm.email" />
                    <x-input-error for="email" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="actions">
                <x-button-success>
                    {{ __('Add') }}
                </x-button-success>
            </x-slot>
        </x-form-section>
    </div> --}}
    {{-- <h1>PPP Secrets</h1> --}}
    {{-- <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Comment</th>
                <th>Name</th>
                <th>Service</th>
                <th>Caller ID</th>
                <th>Password</th>
                <th>Profile</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($pppSecrets))
            @foreach ($DBpppSecrets as $key => $secret)
                <tr>
                    <td>{{ $secret['id'] }}</td>
                    <td>{{ $secret['comment'] ?? 'N/A' }}</td>
                    <td>{{ $secret['username'] }}</td>
                    <td>{{ $secret['service'] }}</td>
                    <td>{{ $secret['caller_id'] }}</td>
                    <td>{{ $secret['password'] }}</td>
                    <td>{{ $secret['profile'] }}</td>
                    <td>{{ $secret['status'] }}</td>
                </tr>
            @endforeach
            @else
                <tr>
                    <td colspan="6">No data available</td>
                </tr>
            @endif
        </tbody>
    </table> --}}

</x-app-layout>
