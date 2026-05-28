<x-layout>
<div class="my-8 bg-[#f6f7fb] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black tracking-tight text-gray-900">
                OTP Verification
            </h1>

            <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                Enter the 4-digit verification code sent to your email address.
            </p>
        </div>

        {{-- CARD --}}
        <div class="bg-white border border-gray-100 rounded-[2rem] shadow-xl shadow-gray-100 overflow-hidden">
            <form action="{{ route('password.forgot.otp.post',$email) }}" method="POST" class="p-8 space-y-6">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium rounded-2xl px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ERROR --}}
                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 text-sm font-medium rounded-2xl px-4 py-3">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="space-y-3">

                    <label class="block text-sm font-black tracking-tight text-gray-800">
                        Enter OTP
                    </label>
                    <input type="text" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="one-time-code" placeholder="0000" value="{{ old('otp') }}" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full h-16 rounded-2xl border border-gray-200 bg-gray-50 px-5 text-center text-3xl tracking-[0.6em] font-black text-gray-900 outline-none transition-all duration-200 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 placeholder:text-gray-300 shadow-sm">

                    @error('otp')
                        <p class="text-sm font-semibold text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- BUTTON --}}
                <button
                    type="submit"
                    class="w-full h-14 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black tracking-wide transition-all duration-200 shadow-lg shadow-indigo-200 hover:shadow-indigo-300"
                >
                    VERIFY OTP
                </button>

                {{-- FOOTER --}}
                <div class="text-center pt-2">
                    <p class="text-xs text-gray-400 font-medium">
                        OTP expires in 10 minutes
                    </p>
                </div>

            </form>

        </div>

    </div>

</div>
</x-layout>
