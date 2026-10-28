<div>
    <section>
        <div class="pt-3 mt-6">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                Book
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-300">
                Book Information and Status
            </p>
        </div>
        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="p-4 card sm:col-span-2 xl:col-span-1">
                <x-book.book-info :image="$image" :grade="$gradeName" :subject="$subjectName" :type="$type"
                    :edition="$edition" :ISBN="$isbn" />
            </div>
            <x-kpi-card label="Total Printed" :value="number_format($totalPrinted)" icon="fi-rr-print" />
            <x-kpi-card label="Total Distributed" :value="number_format($totalDistributed)" icon="fi-rr-truck-side" tone="sky" />
            <x-kpi-card label="Total In Stock" :value="number_format($totalInStock)" icon="fi-rr-box" tone="gold" />
            <x-kpi-card label="On Students' Hands" :value="number_format($totalOnStudentHand)" icon="fi-rr-graduation-cap" tone="emerald" />
        </div>
    </section>
</div>
