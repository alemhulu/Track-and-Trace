{{-- Sidebar content shared by the desktop rail and the mobile drawer --}}
<div class="flex flex-col h-full bg-gradient-to-b from-brand-900 via-brand-900 to-brand-950 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 dark:border-r dark:border-slate-800">
    <a href="/dashboard" class="flex items-center gap-3 px-5 pt-5 pb-4">
        <span class="flex items-center justify-center w-11 h-11 bg-white rounded-full shadow-brand ring-2 ring-white/20 shrink-0">
            <img src="/logom.png" alt="Ministry of Education logo" class="w-10 h-10">
        </span>
        <span class="flex flex-col min-w-0 leading-tight">
            <span class="text-[11px] font-medium tracking-wide uppercase text-brand-200/80">Ministry of Education</span>
            <span class="text-base font-bold text-white">Track <span class="text-gold-400">&amp;</span> Trace</span>
        </span>
    </a>

    <div class="mx-5 brand-stripe rounded-full"></div>

    <div class="flex-1 overflow-y-auto sidebar-scroll">
        <x-side-navigation.nav />
    </div>

    <x-side-navigation.footer />
</div>
