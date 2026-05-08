@props(['label','name','placeholder','type','value'=>null])

<div class="space-y-0.5">
    <label for="{{ $name }}" class="block text-xs font-bold uppercase tracking-wider text-gray-700">{{ $label }}</label>
    @if ($type==='textarea')
        {{-- <textarea type="textarea" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" class="bg-neutral-secondary-medium rounded-lg border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow placeholder:text-body" placeholder="{{ $placeholder }}"/> --}}
        <textarea id="{{ $name }}" rows="4" name="{{ $name }}" value="{{ old($name,$value) }}" class="border border-gray-400 w-full p-2 font-semibold text-sm text-gray-800 rounded-md shadow-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Write your thoughts here...">{{ old($name, $value) }}</textarea>
    @else
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" required
        class="border border-gray-400 w-full p-2 font-semibold text-sm text-gray-800 rounded-md shadow-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="{{ $placeholder }}" value="{{ old($name,$value) }}"/>

    @error($name)
        <div class="red text-sm text-red-600">{{ $message }}</div>
    @enderror
    @endif
</div>
