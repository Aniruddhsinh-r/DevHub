<x-layout>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold tracking-tight text-gray-900">
                Create an account
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <form method="POST" action="/register" class="space-y-5" enctype="multipart/form-data">
                    @csrf

                    <x-form.field name="name" type="string" label="Full name" placeholder="Enter Name"></x-form.field>

                    <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>

                    <x-form.field name="password" type="password" label="Password" placeholder="••••••••"></x-form.field>

                    <x-form.field name="bio" type="textarea" label="Bio" placeholder="Tell us a little about youself..."></x-form.field>

                    <div class='space-y-0.5'>
                        <label for="avtar" class="block font-medium">Profile Pic</label>
                        <input type="file" id="avtar" name="avtar" class="border border-gray-400 w-full font-medium text-sm text-gray-700 rounded-md shadow-xs cursor-pointer file:bg-gray-700 file:text-white file:px-4 file:py-2 file:rounded-l-md file:border-0" placeholder="Profile Pic" />
                    </div>

                    <div class="items-center space-y-0.5">
                        <div class="flex items-center gap-4 w-full max-w-md">
                            <label class="font-medium">Role :</label>

                            <label for="admin" class="ml-3 font-medium cursor-pointer">
                                <input
                                    id='admin'
                                    type="radio"
                                    name="role"
                                    value="admin"
                                    {{ old('role') == 'admin' ? 'checked' : '' }}
                                    class="mr-1.5 accent-indigo-600"
                                />
                                Admin
                            </label>

                            <label for="author" class="ml-3 font-medium cursor-pointer">
                                <input
                                    id='author'
                                    type="radio"
                                    name="role"
                                    value="author"
                                    {{ old('role') == 'author' ? 'checked' : '' }}
                                    class="mr-1.5 accent-indigo-600"
                                />
                                Author
                            </label>
                        </div>
                        @error('role')
                            <div class="red text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
