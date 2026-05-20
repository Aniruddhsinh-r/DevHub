<x-layout>
    <div class="max-w-3xl mx-auto my-10 p-12 bg-white border border-gray-200 rounded-2xl shadow-sm">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">My Profile</h1>
        <hr class="mb-6 border-gray-100">

        <form action="{{ route('profile.update',$user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="flex items-center gap-6 mb-6" x-data="{ imageUrl: '{{ $user->avtar ? asset('storage/' . $user->avtar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}' }">
                <div class="relative">
                    <!-- The src attribute is now bound to our reactive Alpine variable -->
                    <img :src="imageUrl" alt="Profile" class="w-24 h-24 rounded-full object-cover border-2 border-gray-100">
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <!-- @change listens for the file upload and updates our Alpine variable -->
                        <input type="file" name="avtar" id="avtar" class="hidden" accept="image/*"
                               @change="imageUrl = URL.createObjectURL($event.target.files[0])">

                        <label for="avtar" class="cursor-pointer bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>Change Image
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">We support PNGs, JPEGs and GIFs under 2MB</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 mb-10">
                <div>
                    <x-form.field name="name" type='text' label="Full name" placeholder="Enter name" :value="$user->name"></x-form.field>
                </div>

                <div>
                    <x-form.field name="bio" type="textarea" label="Bio" placeholder="Tell us a little about youself..." :value="$user->bio"></x-form.field>
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-900 mb-4">Account Security</h2>
            <hr class="mb-6 border-gray-100">

            <div class="space-y-6">
                <!-- Email Section -->
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-grow">
                        <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com" :value="$user->email"></x-form.field>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-form.field name="password" type="password" label="New Password" placeholder="••••••••"></x-form.field>
                    </div>
                    <div>
                        <x-form.field name="password_confirmation" type="password" label="Confirm Password" placeholder="••••••••"></x-form.field>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end gap-3">
                <button type="button" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="bg-black hover:bg-gray-900 text-white px-8 py-2.5 rounded-xl text-sm font-semibold transition shadow-lg shadow-gray-200">Save Changes</button>
            </div>
        </form>
    </div>
</x-layout>
