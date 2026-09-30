<?php

namespace App\Http\Livewire\Trace;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class TraceBookInfo extends Component
{
    public $gradeId = null;
    public $subjectId = null;

    public function mount($gradeId = null, $subjectId = null)
    {
        $this->gradeId = $gradeId ?: null;
        $this->subjectId = $subjectId ?: null;
    }

    public function render()
    {
        $actor = Auth::user();

        $gradeName = 'All Grades';
        if ($this->gradeId) {
            $gradeName = optional(Grade::find($this->gradeId))->name ?? 'All Grades';
        }

        $subjectName = 'All Subjects';
        if ($this->subjectId) {
            $subjectName = optional(Subject::find($this->subjectId))->name ?? 'All Subjects';
        }

        $bookPreview = Book::query()
            ->when($this->gradeId, function ($query) {
                $query->where('grade_id', $this->gradeId);
            })
            ->when($this->subjectId, function ($query) {
                $query->where('subject_id', $this->subjectId);
            })
            ->first();

        $image = '/biology-grade-10.jpg';
        if ($bookPreview && $bookPreview->front_cover_location) {
            $coverPath = $bookPreview->front_cover_location;
            $image = str_starts_with($coverPath, '/') ? $coverPath : '/' . $coverPath;
        }

        $type = 'Student Text Book';
        if ($bookPreview && ! is_null($bookPreview->book_type)) {
            $type = $bookPreview->book_type ? 'Teacher Text Book' : 'Student Text Book';
        }

        $edition = 'N/A';
        if ($bookPreview && $bookPreview->edition) {
            $edition = $bookPreview->edition . ' Edition';
        }

        $isbn = $bookPreview && $bookPreview->isbn ? $bookPreview->isbn : 'N/A';

        $hasPackagesTable = Schema::hasTable('packages');
        $packageQuery = $hasPackagesTable ? Package::query() : null;

        if ($packageQuery && $actor) {
            $packageQuery->accessibleBy($actor);
        }

        $canFilterByPackage = $hasPackagesTable
            && Schema::hasColumn('packages', 'grade_id')
            && Schema::hasColumn('packages', 'subject_id');

        if ($packageQuery && $canFilterByPackage) {
            $packageQuery
                ->when($this->gradeId, function ($query) {
                    $query->where('grade_id', $this->gradeId);
                })
                ->when($this->subjectId, function ($query) {
                    $query->where('subject_id', $this->subjectId);
                });
        }

        $hasPrintOrdersTable = Schema::hasTable('print_orders');
        $printOrderQuery = $hasPrintOrdersTable ? PrintOrder::query() : null;

        if ($printOrderQuery && $actor instanceof User && ! $actor->hasNationalAccess()) {
            $printOrderQuery->where(function ($query) use ($actor) {
                $query
                    ->whereHas('orderOrganization', function ($organizationQuery) use ($actor) {
                        $organizationQuery->accessibleBy($actor);
                    })
                    ->orWhereHas('printOrganization', function ($organizationQuery) use ($actor) {
                        $organizationQuery->accessibleBy($actor);
                    });
            });
        }

        $canFilterPrintOrderByBook = $hasPrintOrdersTable
            && Schema::hasColumn('print_orders', 'book_id')
            && Schema::hasTable('books')
            && Schema::hasColumn('books', 'grade_id')
            && Schema::hasColumn('books', 'subject_id');

        if ($printOrderQuery && $canFilterPrintOrderByBook) {
            $printOrderQuery->whereHas('book', function ($query) {
                $query
                    ->when($this->gradeId, function ($bookQuery) {
                        $bookQuery->where('grade_id', $this->gradeId);
                    })
                    ->when($this->subjectId, function ($bookQuery) {
                        $bookQuery->where('subject_id', $this->subjectId);
                    });
            });
        }

        $totalPrinted = 0;
        if ($printOrderQuery && Schema::hasColumn('print_orders', 'no_of_books')) {
            $totalPrinted = (int) (clone $printOrderQuery)->sum('no_of_books');
        }

        $totalDistributed = 0;
        if ($packageQuery && Schema::hasColumn('packages', 'sent')) {
            $totalDistributed = (int) (clone $packageQuery)->sum('sent');
        }

        $totalInStock = 0;
        if ($packageQuery && Schema::hasColumn('packages', 'balance')) {
            $totalInStock = (int) (clone $packageQuery)->sum('balance');
        }

        $totalOnStudentHand = 0;
        if ($packageQuery && Schema::hasColumn('packages', 'received')) {
            $totalOnStudentHand = (int) (clone $packageQuery)->sum('received');
        }

        return view('livewire.trace.trace-book-info', compact(
            'gradeName',
            'subjectName',
            'image',
            'type',
            'edition',
            'isbn',
            'totalPrinted',
            'totalDistributed',
            'totalInStock',
            'totalOnStudentHand'
        ));
    }
}
