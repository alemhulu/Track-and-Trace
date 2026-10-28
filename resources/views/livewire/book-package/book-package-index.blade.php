@extends('main.book-package.index')

@section('content')
<div class="space-y-6" wire:poll.60s>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-kpi-card label="Total Packages" :value="$total ?? '0'" icon="fi-rr-box" />
        <x-kpi-card label="Packages Sent" :value="$sent ?? '0'" icon="fi-rr-paper-plane" tone="sky" />
        <x-kpi-card label="Packages Received" :value="$received ?? '0'" icon="fi-rr-inbox" tone="emerald" />
        <x-kpi-card label="Packages Available" :value="$available ?? '0'" icon="fi-rr-home-location-alt" tone="gold" />
        <x-kpi-card label="Current Status" :value="$status['label'] ?? 'Pending'" icon="fi-rr-info" tone="slate" compact />
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Available Packages Per Book</h2>
                <p class="card-subtitle">Grouped by subject, with every grade shown once per subject.</p>
            </div>
        </div>

        <div class="p-5 space-y-8">
            @forelse ($subjects as $subject)
            <x-stat.section name="{{ $subject['subject']['name'] ?? 'Unknown Subject' }}">
                @foreach ($subject['grades'] as $package)
                <x-stat.grade-list grade="{{ $package['grade']['name'] ?? 'Unknown Grade' }}" sent="{{ $package['sent']}}"
                    received="{{ $package['received']}}" available="{{ $package['available']}}"
                    status="{{ $package['status']['label'] ?? 'Pending' }}"
                    statusType="{{ $package['status']['type'] ?? 'secondary' }}" />
                @endforeach
            </x-stat.section>
            @empty
            <div class="py-10 text-center">
                <span class="inline-flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-brand-50 text-brand-500 dark:bg-brand-900/40 dark:text-brand-300">
                    <i class="flex text-xl fi fi-rr-box"></i>
                </span>
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">No packages yet</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Packages appear here once print orders are packed and received.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
