<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div x-data="{ open: false, id: null, title: '', type: '' }"
     x-on:open-delete.window="
        open = true;
        id = $event.detail.id;
        title = $event.detail.title;
        type = $event.detail.type;
     "
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">

    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative bg-white p-6 rounded-xl shadow-2xl max-w-md w-full z-10">
        <h3 class="text-lg font-bold text-gray-900">Are you absolutely sure?</h3>
        <p class="text-sm text-gray-500 mt-2">You are about to delete <span class="font-bold text-gray-800" x-text="title"></span>. This action is permanent.</p>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="open = false" class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</button>

            <button type="button"
                    @click="$dispatch('trigger-delete', { id: id, type: type }); open = false"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-500">
                Yes, Delete
            </button>
        </div>
    </div>
</div>
