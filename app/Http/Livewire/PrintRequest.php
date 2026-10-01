<?php

namespace App\Http\Livewire;

use App\Models\Book;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class PrintRequest extends Component
{
    use WithPagination;

    public $order, $clearid;
    public $packagesPerPage = 5;
    public function mount($id)
    {
        $actor = auth()->user();
        $query = PrintOrder::query();

        if ($actor) {
            $query->accessibleBy($actor);
        }

        $this->order = $query->findOrFail($id);
        if ($actor && Gate::denies('view', $this->order)) {
            abort(403, 'You are not authorized to view this print request.');
        }
    }
    public function render()
    {
        $packageCodes = collect($this->order->Book_codes ?? []);
        $currentPage = LengthAwarePaginator::resolveCurrentPage('packagesPage');
        $paginatedPackages = new LengthAwarePaginator(
            $packageCodes->forPage($currentPage, $this->packagesPerPage),
            $packageCodes->count(),
            $this->packagesPerPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'packagesPage',
            ]
        );

        return view('livewire.print-request', [
            'packages' => $paginatedPackages,
        ]);
    }
    public function status($status)
    {
        $actor = auth()->user();
        if ($actor && ! PrintOrder::query()->accessibleBy($actor)->whereKey($this->order->id)->exists()) {
            abort(403, 'You are not authorized to modify this print request.');
        }
        if ($actor && Gate::denies('update', $this->order)) {
            abort(403, 'You are not authorized to modify this print request.');
        }

        $this->order->request_status = $status;
        $this->order->save();
        if ($status == 2) {
            $warehouse = WareHouse::where('organization_id', $this->order->printer_organization_id)->first();
            if (!$warehouse) {
                throw new \Exception("Warehouse not found for organization {$this->order->printer_organization_id}");
            }
            $book = Book::findOrFail($this->order->book_id);

            // Validate book relationships
            if (!$book->subject) {
                throw new \Exception("Book ID {$book->id} does not have a valid subject");
            }
            if (!$book->grade) {
                throw new \Exception("Book ID {$book->id} does not have a valid grade");
            }

            $data = [
                'ware_house_id' => $warehouse->id,
                'print_order_id' => $this->order->id,
                'sender_organization_id' => $this->order->printer_organization_id,
                'receiver_organization_id' => $this->order->printer_organization_id,
                'step' => 0,
                'Book_codes' => $this->order->Book_codes,
                'balance' => $this->order->no_of_books,
                'no_of_books' => $this->order->no_of_books,
                'sent' => 0,
                'received' => $this->order->no_of_books,
                'books_per_package' => $this->order->no_of_packages,
                'qrcode_start' => $this->order->qrcode_start,
                'qrcode_end' => $this->order->qrcode_end,
                'barcode_start' => $this->order->barcode_start,
                'barcode_end' => $this->order->barcode_end,
                'request_status' => 2,
                'subject_id' => $book->subject->id,
                'grade_id' => $book->grade->id,
            ];

            Package::create($data);
        }
    }

    // Clear input variables
    public function clearid() {}
}
