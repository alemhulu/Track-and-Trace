@props(['warehouse'=>'Warehouse 1', 'company'=>'MoE', 'stock'=>'10', 'email'=>'foo@bar.com', 'phone'=>'123-456-1234'])
<div class="grid grid-cols-2 items-center">
    <div class="flex flex-col text-sm font-medium text-gray-700">
        <div class="">
            <x-organization.info name="{{ $company }}" email="{{ $email }}" phone="{{ $phone }}" />
        </div>
        <div class="mt-1 ml-1">
            <span class="text-xs text-gray-500 uppercase">Warehouse:</span> {{ $warehouse }}
        </div>
        <div class="ml-1">
            <span class="text-xs text-gray-500 uppercase">Stock-IN:</span> {{ $stock }} <span
                class="text-xs text-gray-500">Packages</span>
        </div>
    </div>
</div>