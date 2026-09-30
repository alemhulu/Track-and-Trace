<?php

namespace App\Http\Livewire;

use App\Models\Book;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\WareHouse;
use Livewire\Component;

class PrintRequest extends Component
{
    public $order, $clearid;
    public function mount($id)
    {
        $this->order = PrintOrder::findOrFail($id);
    }
    public function render()
    {
        return view('livewire.print-request');
    }
    public function status($status)
    {
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
