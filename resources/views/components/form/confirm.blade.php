@props([
'name' => 'confirmModal',
'type'=> 'wire',
'message' => 'delete',
'subtitle' => 'fordelete',
'second' => '',
'deleteId' => '',
'id'=> ''])

@php
if ( $message == "reject" ){
$subtitle = "forreject";
}
$subtitle = [
'fordelete' => 'This process cannot be undone',
'forreject' => 'If you reject the request you have to give reason.',
][$subtitle ?? 'fordelete'];


$message = [
'delete' => 'Are you sure you want to Delete?',
'reject' => 'Are you sure you want to Reject this request?',
][$message ?? 'delete'];

// $function = preg_replace('/[0-9]+/', '', $name);
// $html = $browser->element('.takein')->getAttribute('innerHTML');
@endphp

<x-data-table.modal name="{{ $name }}" maxWidth="md" buttons="">
    <x-slot name="title">
        Confirmation
    </x-slot>

    <x-slot name="body">
        <div class="text-center">
            <i class="text-4xl fi fi-rr-shield-exclamation text-red-600 font-bold"></i>
        </div>

        <p class="flex flex-col text-center pb-3">
            <span class="text-gray-600 font-bold">{{ $message }}</span>
            <span class="text-gray-500 text-sm">{{ $subtitle }} </span>
        </p>

        <div class="flex justify-end space-x-2 mt-8 sm:mt-0 -mx-3">
            @if ($type == "wire")
            <a href="#" wire:click='clearid'>
                <x-button type="button" btnType="secondary">CANCEL</x-button>
            </a>

            <a href="#" wire:click="{{ $name }}({{ $id }})">
                <x-button type="button" btnType="danger">
                    Yes Delete It ! {{ $this->deleteId }}
                </x-button>
            </a>
            @endif

            @if ($type == 'frist')
            <a href="#">
                <x-button type="button" btnType="secondary">CANCEL</x-button>
            </a>
            <a href="#{{ $second }}">
                <x-button type="button" btnType="danger">Yes Reject It !</x-button>
            </a>
            @endif
        </div>
    </x-slot>
</x-data-table.modal>