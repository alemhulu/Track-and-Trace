<?php

namespace App\Http\Livewire\ManualTracking\Package;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AddManualPackage extends Component
{
    public $books = [];
    public $manual_book_id;
    public $package_code;
    public $no_of_packages = 1;
    public $books_per_package = 0;
    public $status = 'available';
    public $notes;
    public $editingPackageId;
    public $selectedBookTotalCopies = 0;
    public $selectedBookTitle = '';
    public $packageTotalBooks = 0;

    protected function rules()
    {
        $packageId = $this->editingPackageId;

        return [
            'manual_book_id' => 'required|exists:manual_books,id',
            'package_code' => 'required|string|max:255|unique:manual_book_packages,package_code,' . $packageId,
            'no_of_packages' => 'required|integer|min:1',
            'books_per_package' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function mount()
    {
        $this->books = ManualBook::query()->orderBy('title')->get();

        $editId = request()->query('edit');
        if ($editId) {
            $this->loadForEdit((int) $editId);
        }

        $this->recalculatePackageSummary();
    }

    public function updatedManualBookId($bookId): void
    {
        $book = ManualBook::query()->find($bookId);
        $this->selectedBookTotalCopies = $book ? (int) $book->total_copies : 0;
        $this->selectedBookTitle = $book?->title ?? '';
        $this->recalculatePackageSummary();
    }

    public function updatedNoOfPackages($value): void
    {
        $this->recalculatePackageSummary();
    }

    public function updatedBooksPerPackage($value): void
    {
        $this->recalculatePackageSummary();
    }

    public function render()
    {
        return view('livewire.manual-tracking.package.add-manual-package')->extends('main.manual-tracking.index');
    }

    public function savePackage()
    {
        try {
            $data = $this->validate();
            $this->validatePackageStock(
                manualBookId: (int) $data['manual_book_id'],
                noOfPackages: (int) $data['no_of_packages'],
                booksPerPackage: (int) $data['books_per_package'],
            );
            $totalBooks = (int) $data['no_of_packages'] * (int) $data['books_per_package'];

            DB::transaction(function () use ($data, $totalBooks): void {
                if ($this->editingPackageId) {
                    $package = ManualBookPackage::query()->find($this->editingPackageId);
                    if (! $package) {
                        throw new \RuntimeException('Manual package not found.');
                    }

                    $package->update(array_merge($data, [
                        'total_books' => $totalBooks,
                        'current_balance' => $package->distributionLines()->exists() ? $package->current_balance : $totalBooks,
                    ]));

                    $this->recalculateBookCopies((int) $package->manual_book_id);
                    $this->recalculateBookCopies((int) $data['manual_book_id']);
                    return;
                }

                $package = ManualBookPackage::query()->create(array_merge($data, [
                    'total_books' => $totalBooks,
                    'current_balance' => $totalBooks,
                ]));

                $this->recalculateBookCopies((int) $package->manual_book_id);
            });
        } catch (\Throwable $exception) {
            return $this->alertError($exception->getMessage());
        }

        if ($this->editingPackageId) {
            $this->alertSuccess('Manual package updated successfully.');
            return redirect()->route('manual-tracking.packages.list');
        }

        $this->resetForm();
        $this->alertSuccess('Manual package created successfully.');
    }

    public function validatePackageStock(?int $manualBookId = null, ?int $noOfPackages = null, ?int $booksPerPackage = null): void
    {
        $bookId = $manualBookId ?? (int) ($this->manual_book_id ?? 0);
        $totalPackages = (int) ($noOfPackages ?? (int) ($this->no_of_packages ?? 0));
        $copiesPerPackage = (int) ($booksPerPackage ?? (int) ($this->books_per_package ?? 0));

        if ($bookId <= 0) {
            return;
        }

        $book = ManualBook::query()->find($bookId);
        if (! $book) {
            throw new \RuntimeException('Selected book not found.');
        }

        $this->selectedBookTotalCopies = (int) $book->total_copies;
        $this->selectedBookTitle = $book->title;

        $totalBooks = $totalPackages * $copiesPerPackage;
        if ($totalBooks > (int) $book->total_copies) {
            throw new \RuntimeException('Package total ' . $totalBooks . ' exceeds the selected book total copies (' . $book->total_copies . ').');
        }
    }

    private function loadForEdit(int $id): void
    {
        $package = ManualBookPackage::query()->find($id);
        if (! $package) {
            $this->alertError('Manual package not found.');
            return;
        }

        $this->editingPackageId = $package->id;
        $this->manual_book_id = $package->manual_book_id;
        $this->package_code = $package->package_code;
        $this->no_of_packages = $package->no_of_packages;
        $this->books_per_package = $package->books_per_package;
        $this->status = $package->status;
        $this->notes = $package->notes;
        $this->updatedManualBookId($package->manual_book_id);
    }

    private function recalculatePackageSummary(): void
    {
        $this->packageTotalBooks = ((int) ($this->no_of_packages ?? 0)) * ((int) ($this->books_per_package ?? 0));

        if (! empty($this->manual_book_id)) {
            $book = ManualBook::query()->find($this->manual_book_id);
            $this->selectedBookTotalCopies = $book ? (int) $book->total_copies : 0;
            $this->selectedBookTitle = $book?->title ?? '';
        }
    }

    private function recalculateBookCopies(int $bookId): void
    {
        $book = ManualBook::query()->find($bookId);
        if (! $book) {
            return;
        }

        $total = ManualBookPackage::query()
            ->where('manual_book_id', $bookId)
            ->sum('current_balance');

        $book->update(['total_copies' => $total]);
    }

    private function resetForm(): void
    {
        $this->manual_book_id = null;
        $this->package_code = null;
        $this->no_of_packages = 1;
        $this->books_per_package = 0;
        $this->status = 'available';
        $this->notes = null;
        $this->editingPackageId = null;
    }

    private function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    private function alertSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
