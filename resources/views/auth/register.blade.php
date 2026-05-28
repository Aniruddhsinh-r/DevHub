<x-layout>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold tracking-tight text-gray-900">
                Create an account
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <form method="POST" action="{{ route('register.store') }}" class="space-y-5" enctype="multipart/form-data">
                    @csrf

                    <x-form.field name="name" type="string" label="Full name" placeholder="Enter Name"></x-form.field>

                    <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>

                    <x-form.field name="password" type="password" label="Password" placeholder="••••••••"></x-form.field>

                    <x-form.field name="bio" type="textarea" label="Bio" placeholder="Tell us a little about youself..."></x-form.field>

                    <div class='space-y-0.5'>
                        <label for="avtar" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Profile Pic</label>
                        <input type="file" id="avtar" name="avtar" class="border border-gray-400 w-full font-medium text-sm text-gray-700 rounded-md shadow-xs cursor-pointer file:bg-gray-700 file:text-white file:px-4 file:py-2 file:rounded-l-md file:border-0" placeholder="Profile Pic" />
                    </div>

                    <div class="mt-8 flex items-center justify-between gap-4">
                        <a href="{{ route('login.create') }}" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                            <span>Already have an account?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:translate-x-1 transition-transform duration-150">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>

                        <div>
                            <button type="submit" class="inline-flex justify-center py-2.5 px-6 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                Register
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
