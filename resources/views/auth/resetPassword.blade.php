<x-layout>
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Set new password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Ensure your account is secure with a strong password.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100">
            <form action="{{ route('password.reset.post') }}" method="POST" class="space-y-6">
                @csrf
                <!-- New Password Field -->
                <div>
                    <x-form.field
                        name="password"
                        type="password"
                        label="New Password"
                        placeholder="••••••••">
                    </x-form.field>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <x-form.field
                        name="password_confirmation"
                        type="password"
                        label="Confirm Password"
                        placeholder="••••••••">
                    </x-form.field>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        Update Password
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
</x-layout>
