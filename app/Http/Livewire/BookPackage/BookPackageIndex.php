<?php

namespace App\Http\Livewire\BookPackage;

use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BookPackageIndex extends Component
{
    use WithPagination;
    //variables
    public $packages = [], $subjects = [];
    public $column, $recordes, $sortType;
    public $total, $sent, $received, $available = 0;
    public $status = [];

    // Search variables
    public $search = '';
    public $attributes;

    // Search function
    public function updatedSearch()
    {
        $this->column = 'order_organization_id';
        $this->sortType = 'asc';
        $this->resetPage();
    }
    public function mount()
    {
        $this->recordes = 5;
        $this->column = '';
        $this->loadPackageSummary();
    }

    protected function loadPackageSummary(): void
    {
        $actor = Auth::user();

        if (! $actor) {
            $this->packages = [];
            $this->subjects = [];
            $this->total = 0;
            $this->sent = 0;
            $this->received = 0;
            $this->available = 0;
            $this->status = $this->summarizeStatus(0, 0, 0, 0, collect());

            return;
        }

        $user = User::where('id', $actor->id)->with('organization')->first();

        if (! $user || ! $user->organization) {
            $this->packages = [];
            $this->subjects = [];
            $this->total = 0;
            $this->sent = 0;
            $this->received = 0;
            $this->available = 0;
            $this->status = $this->summarizeStatus(0, 0, 0, 0, collect());

            return;
        }

        $packageQuery = Package::query()->with([
            'subject:id,name',
            'grade:id,name',
        ]);

        $packageQuery->accessibleBy($actor);

        $packages = $packageQuery->get();

        $this->packages = $packages->toArray();
        $this->subjects = $packages
            ->groupBy('subject_id')
            ->map(function ($subjectPackages) {
                $subject = optional($subjectPackages->first()->subject);

                return [
                    'subject' => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                    ],
                    'grades' => $subjectPackages
                        ->groupBy('grade_id')
                        ->map(function ($gradePackages) {
                            $firstPackage = $gradePackages->first();
                            $grade = optional($firstPackage->grade);
                            $sent = (int) $gradePackages->sum('sent');
                            $received = (int) $gradePackages->sum('received');
                            $total = (int) $gradePackages->sum('no_of_books');
                            $available = max(0, $total + $received - $sent);

                            return [
                                'grade' => [
                                    'id' => $grade->id,
                                    'name' => $grade->name,
                                ],
                                'sent' => $sent,
                                'received' => $received,
                                'available' => $available,
                                'status' => $this->summarizeStatus($total, $sent, $received, $available, $gradePackages),
                            ];
                        })
                        ->sortBy(function ($gradePackage) {
                            return $gradePackage['grade']['name'] ?? '';
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->sortBy(function ($subjectPackage) {
                return $subjectPackage['subject']['name'] ?? '';
            })
            ->values()
            ->toArray();

        $this->total = (int) $packages->sum('no_of_books');
        $this->sent = (int) $packages->sum('sent');
        $this->received = (int) $packages->sum('received');
        $this->available = max(0, $this->total + $this->received - $this->sent);
        $this->status = $this->summarizeStatus($this->total, $this->sent, $this->received, $this->available, $packages);
    }

    protected function summarizeStatus(int $total, int $sent, int $received, int $available, $packages): array
    {
        $now = Carbon::now();
        $packages = collect($packages);
        $workflowStatus = (int) $packages->pluck('request_status')->filter(function ($status) {
            return $status !== null && $status !== '';
        })->max();
        $latestExpectedDelivery = $packages
            ->pluck('expected_delivery_school_time')
            ->filter()
            ->map(function ($time) {
                try {
                    return Carbon::parse($time);
                } catch (\Throwable $throwable) {
                    return null;
                }
            })
            ->filter()
            ->sortDesc()
            ->first();

        $latestActualDelivery = $packages
            ->pluck('actual_delivery_school_time')
            ->filter()
            ->map(function ($time) {
                try {
                    return Carbon::parse($time);
                } catch (\Throwable $throwable) {
                    return null;
                }
            })
            ->filter()
            ->sortDesc()
            ->first();

        if ($total === 0) {
            return [
                'label' => 'No Packages',
                'type' => 'secondary',
            ];
        }

        if ($available <= 0 && $sent > 0 && $received >= $sent) {
            return [
                'label' => 'Completed',
                'type' => 'success',
            ];
        }

        if ($latestActualDelivery || $workflowStatus >= 4) {
            return [
                'label' => 'Sent',
                'type' => 'success',
            ];
        }

        if ($latestExpectedDelivery && $now->greaterThan($latestExpectedDelivery) && $sent > 0 && $available > 0) {
            return [
                'label' => 'Sending',
                'type' => 'danger',
            ];
        }

        if ($workflowStatus >= 3 || ($sent > 0 && $received > 0)) {
            return [
                'label' => 'Sending',
                'type' => 'info',
            ];
        }

        if ($workflowStatus >= 2 || $received > 0) {
            return [
                'label' => 'Stored',
                'type' => 'success',
            ];
        }

        if ($workflowStatus >= 1) {
            return [
                'label' => 'Accepted',
                'type' => 'primary',
            ];
        }

        return [
            'label' => 'Requested',
            'type' => 'warning',
        ];
    }
    public function render()
    {
        $this->loadPackageSummary();

        return view('livewire.book-package.book-package-index')->extends('main.book-package.index');
    }
}
