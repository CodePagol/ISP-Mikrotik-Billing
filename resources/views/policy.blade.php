<x-guest-layout>
    <div class="terms-policy-container">
        <div class="box">
            <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                <div class="h-25">
                    <x-authentication-auth-logo />
                </div>

                <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg prose dark:prose-invert">
                    {!! $policy !!}
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
