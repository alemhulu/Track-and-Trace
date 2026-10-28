<?php

namespace App\Http\Controllers\ManualTracking;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\Organization;
use App\Models\School\Student;
use App\Support\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Paper forms for places without a reliable connection. Each form mirrors a screen, so
 * what is filled in on paper can be typed in (or entered in the field app) later.
 */
class ManualPrintController extends Controller
{
    // Delivery note that travels with the truck and comes back signed as the receipt
    public function shipment(Request $request, int $shipment): View
    {
        $shipment = ManualDistribution::query()
            ->accessibleBy($request->user())
            ->with(['lines.book', 'organization', 'destinationOrganization', 'dispatcher:id,name'])
            ->findOrFail($shipment);

        return view('print.shipment-note', ['shipment' => $shipment]);
    }

    // Class list to tick as books are handed out; blank rows cover students not yet registered
    public function classSheet(Request $request, int $school): View
    {
        $school = Organization::query()->accessibleBy($request->user())->findOrFail($school);
        $year = (int) $request->query('year', AcademicYear::current());
        $grade = Grade::query()->findOrFail((int) $request->query('grade'));
        $section = $request->query('section') ?: null;

        $students = Student::query()->active()
            ->where('organization_id', $school->id)
            ->where('academic_year', $year)
            ->where('grade_id', $grade->id)
            ->when($section, fn ($query) => $query->where('section', $section))
            ->with('heldBooks:id,student_id,manual_book_id')
            ->orderBy('section')->orderBy('name')
            ->get();

        return view('print.class-sheet', [
            'school' => $school,
            'yearLabel' => AcademicYear::label($year),
            'grade' => $grade,
            'section' => $section,
            'students' => $students,
            'titles' => ManualBook::query()
                ->where('grade_id', $grade->id)
                ->where(fn ($query) => $query->whereNull('region_id')->orWhere('region_id', $school->region_id))
                ->orderBy('title')
                ->get(['id', 'title']),
        ]);
    }
}
