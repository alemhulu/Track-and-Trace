<?php

namespace App\Http\Livewire\ManualTracking\Package;

use App\Models\ManualTracking\ManualBookPackage;
use Livewire\Component;
use Livewire\WithPagination;

class ListManualPackage extends Component
{
    use WithPagination;

    public $recordes = 10;

    public function render()
    {
        $packages = ManualBookPackage::query()
            ->with('book:id,title,subject_name,grade_name,total_copies')
            ->orderByDesc('id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.package.list-manual-package', [
            'packages' => $packages,
        ])->extends('main.manual-tracking.index');
    }

    public function editPackage($id)
    {
        return redirect()->route('manual-tracking.packages.add', ['edit' => $id]);
    }

    public function deletePackage($id)
    {
        $package = ManualBookPackage::query()->withCount('distributionLines')->find($id);
        if (! $package) {
            return $this->alertError('Manual package not found.');
        }

        if ($package->distribution_lines_count > 0) {
            return $this->alertError('Package has distribution history and cannot be deleted.');
        }

        $package->delete();
        $this->alertSuccess('Manual package deleted successfully.');
    }

    public function deleteId($id)
    {
        return $this->deletePackage($id);
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
