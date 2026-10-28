<?php

use App\Http\Controllers\DistributionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Livewire\Book\AddBook;
use App\Http\Livewire\Book\ListBook;
use App\Http\Livewire\BookPackage\AvialableBookPackage;
use App\Http\Livewire\BookPackage\BookPackageIndex;
use App\Http\Livewire\BookPackage\BookPackageRequest;
use App\Http\Livewire\BookPackage\PackageAvailableInfo;
use App\Http\Livewire\BookPackage\PackagesRequestInfo;
use App\Http\Livewire\BookPackage\SendBookPackage;
use App\Http\Livewire\Distribution\AddDistribution;
use App\Http\Livewire\Distribution\ListDistribution;
use App\Http\Livewire\Location\Country;
use App\Http\Livewire\Location\Kebele;
use App\Http\Livewire\Location\Region;
use App\Http\Livewire\Location\Woreda;
use App\Http\Livewire\Location\Zone;
use App\Http\Livewire\ManualTracking\Book\AddManualBook;
use App\Http\Livewire\ManualTracking\Book\ListManualBook;
use App\Http\Livewire\ManualTracking\Discrepancy\ManualDiscrepancyIndex;
use App\Http\Controllers\ManualTracking\FieldAppController;
use App\Http\Controllers\ManualTracking\ManualPrintController;
use App\Http\Controllers\ManualTracking\ManualExportController;
use App\Http\Livewire\ManualTracking\Oversight\RecordVisit;
use App\Http\Livewire\ManualTracking\Oversight\ShowVisit;
use App\Http\Livewire\ManualTracking\Oversight\VisitIndex;
use App\Http\Livewire\ManualTracking\Scan\ScanAlertIndex;
use App\Http\Livewire\ManualTracking\Scan\SortPackages;
use App\Http\Livewire\ManualTracking\Distribution\ListManualDistribution;
use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Http\Livewire\ManualTracking\Shipment\ShowShipment;
use App\Http\Livewire\ManualTracking\Stock\ManualStockIndex;
use App\Http\Livewire\ManualTracking\Report\ManualTrackingReports;
use App\Http\Livewire\Oganization\AddOrganization;
use App\Http\Livewire\Oganization\AddStore;
use App\Http\Livewire\Oganization\ListOrganization;
use App\Http\Livewire\Oganization\OrganizationType;
use App\Http\Livewire\Print\AddPrintOrder;
use App\Http\Livewire\Print\ListPrintOrder;
use App\Http\Livewire\PrintRequest;
use App\Http\Livewire\School\SchoolIndex;
use App\Http\Livewire\School\SchoolShow;
use App\Http\Livewire\Route\AddRoute;
use App\Http\Livewire\Route\ListRoute;
use App\Http\Livewire\Route\ViewRoute;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/inventory', \App\Http\Controllers\InventoryDashboardController::class)->name('dashboard.inventory');
    Route::get('/dashboard/distribution', \App\Http\Controllers\DistributionDashboardController::class)->name('dashboard.distribution');


    // Location routes
    Route::get('/location', function () {
        return view('main.location.index');
    })->name('location');
    Route::get('/location/country', Country::class)->name('location.country');
    Route::get('/location/region', Region::class)->name('location.region');
    Route::get('/location/zone', Zone::class)->name('location.zone');
    Route::get('/location/woreda', Woreda::class)->name('location.woreda');
    Route::get('/location/kebele', Kebele::class)->name('location.kebele');

    // Organization routes
    Route::get('/organization', function () {
        return view('main.organization.index');
    })->name('organization');
    Route::get('/organization/list', ListOrganization::class)->name('organization.list');
    Route::get('/organization/add', AddOrganization::class)->name('organization.add');
    Route::get('/organization/store-add', AddStore::class)->name('organization.store-add');
    Route::get('/organization/type', OrganizationType::class)->name('organization.type');

    // Print-order routes
    Route::get('/print-order', function () {
        return view('main.print-order.index');
    })->name('print-order');
    Route::get('/print-order/order', AddPrintOrder::class)->name('print-order.order');
    Route::get('/print-order/list', ListPrintOrder::class)->name('print-order.list');
    Route::get('/print-order/view/{id}', PrintRequest::class)->name('print-order.view');

    // Book Packages routes
    Route::get('/book-packages', BookPackageIndex::class)->name('book-packages');
    Route::get('/book-packages/request', BookPackageRequest::class)->name('packages.request');
    Route::get('/book-packages/send', SendBookPackage::class)->name('packages.send');
    Route::get('/book-packages/available', AvialableBookPackage::class)->name('packages.available');
    Route::get('/book-packages/request/info', PackagesRequestInfo::class)->name('packages.request.info');
    Route::get('/book-packages/available/info/{id}', PackageAvailableInfo::class)->name('packages.available.info');

    Route::get('/warehouse', function () {
        return view('main.warehouse.index');
    })->name('warehouse');

    // Book routes
    Route::get('/book', function () {
        return view('main.book.index');
    })->name('book');
    Route::get('/book/list', ListBook::class)->name('book.list');
    Route::get('/book/add', AddBook::class)->name('book.add');
    Route::get('/book/settings', function () {
        return view('main.book.setting');
    })->name('book.setting');

    // Route routes
    Route::get('/route', function () {
        return view('main.route.index');
    })->name('route');
    Route::get('/route/list', ListRoute::class)->name('route.list');
    Route::get('/route/add', AddRoute::class)->name('route.add');
    Route::get('/route/view/{id}', ViewRoute::class)->name('route.view');

    // Distribution routes
    Route::get('/distribution', function () {
        return view('main.distribution.index');
    })->name('distribution');
    Route::get('/distribution/list', ListDistribution::class)->name('distribution.list');
    Route::get('/distribution/add', AddDistribution::class)->name('distribution.add');

    Route::get('/trace', function () {
        return view('main.trace.index');
    })->name('trace');

    // Manual Tracking routes
    Route::get('/manual-tracking', function () {
        return view('main.manual-tracking.index');
    })->name('manual-tracking');

    Route::get('/manual-tracking/books/list', ListManualBook::class)->name('manual-tracking.books.list');
    Route::get('/manual-tracking/books/add', AddManualBook::class)->name('manual-tracking.books.add');

    Route::get('/manual-tracking/stock', ManualStockIndex::class)->name('manual-tracking.stock.index');

    Route::get('/manual-tracking/distribution/list', ListManualDistribution::class)->name('manual-tracking.distribution.list');
    Route::get('/manual-tracking/distribution/add', CreateShipment::class)->name('manual-tracking.distribution.add');
    Route::get('/manual-tracking/shipments/{shipment}', ShowShipment::class)->whereNumber('shipment')->name('manual-tracking.shipments.show');

    Route::get('/manual-tracking/discrepancies', ManualDiscrepancyIndex::class)->name('manual-tracking.discrepancies.index');

    Route::get('/field', [FieldAppController::class, 'show'])->name('field.app');
    Route::get('/field/pack', [FieldAppController::class, 'pack'])->name('field.pack');
    Route::post('/field/sync', [FieldAppController::class, 'sync'])->name('field.sync');

    Route::get('/manual-tracking/shipments/{shipment}/print', [ManualPrintController::class, 'shipment'])->whereNumber('shipment')->name('manual-tracking.shipments.print');
    Route::get('/manual-tracking/schools/{school}/print', [ManualPrintController::class, 'classSheet'])->whereNumber('school')->name('manual-tracking.schools.print');

    Route::get('/manual-tracking/visits', VisitIndex::class)->name('manual-tracking.visits.index');
    Route::get('/manual-tracking/visits/new/{school}', RecordVisit::class)->whereNumber('school')->name('manual-tracking.visits.create');
    Route::get('/manual-tracking/visits/{visit}', ShowVisit::class)->whereNumber('visit')->name('manual-tracking.visits.show');
    Route::get('/manual-tracking/exports/{type}', ManualExportController::class)->name('manual-tracking.exports');

    Route::get('/manual-tracking/scan/sort', SortPackages::class)->name('manual-tracking.scan.sort');
    Route::get('/manual-tracking/scan/alerts', ScanAlertIndex::class)->name('manual-tracking.scan.alerts');

    Route::get('/manual-tracking/schools', SchoolIndex::class)->name('manual-tracking.schools.index');
    Route::get('/manual-tracking/schools/{school}', SchoolShow::class)->whereNumber('school')->name('manual-tracking.schools.show');

    Route::get('/manual-tracking/reports', ManualTrackingReports::class)->name('manual-tracking.reports.index');

    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('distribution-details', DistributionController::class)
        ->parameters(['distribution-details' => 'distribution']);
});
