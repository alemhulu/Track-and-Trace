<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Track and Trace Notes

### Database Seed Profiles

The application supports two seed profiles through the `SEED_PROFILE` environment variable:

- `demo` (default): Seeds reference data and full demo domain data.
- `minimal`: Seeds reference data and one minimal connected domain graph for faster local setup.

Common commands:

```bash
# Default profile (demo)
php artisan migrate:fresh --seed

# Minimal profile
SEED_PROFILE=minimal php artisan migrate:fresh --seed

# Explicit demo profile
SEED_PROFILE=demo php artisan migrate:fresh --seed
```

Notes:

- Reference seeders always run in both profiles (permissions, locations, organization types, ownership, grade and subject references).
- Profile switching only changes the domain-level data volume and shape.

### Factory Usage In Feature Tests

Core factories were added for the logistics flow to reduce manual setup in tests:

- `OrganizationFactory`
- `WareHouseFactory`
- `BookFactory`
- `PrintOrderFactory`
- `PackageFactory`
- `DistributionRouteFactory`
- `DistributionFactory`
- `DistributionStepFactory`

Example: create a package graph aligned to a warehouse and grade/subject filters.

```php
use App\Models\Book;
use App\Models\Grade;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\Subject;
use App\Models\WareHouse;

$grade = Grade::factory()->create();
$subject = Subject::factory()->create();
$warehouse = WareHouse::factory()->create();

$book = Book::factory()->create([
	'grade_id' => $grade->id,
	'subject_id' => $subject->id,
]);

$printOrder = PrintOrder::factory()->create([
	'book_id' => $book->id,
	'order_organization_id' => $warehouse->organization_id,
	'printer_organization_id' => $warehouse->organization_id,
	'no_of_books' => 120,
	'no_of_packages' => 12,
]);

$package = Package::factory()->create([
	'ware_house_id' => $warehouse->id,
	'print_order_id' => $printOrder->id,
	'sender_organization_id' => $warehouse->organization_id,
	'receiver_organization_id' => $warehouse->organization_id,
	'grade_id' => $grade->id,
	'subject_id' => $subject->id,
	'no_of_books' => 120,
	'books_per_package' => 10,
	'balance' => 120,
]);
```

Example: create route and distribution step wiring.

```php
use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\DistributionStep;
use App\Models\WareHouse;

$fromWarehouse = WareHouse::factory()->create();
$toWarehouse = WareHouse::factory()->create();

$route = DistributionRoute::factory()->create([
	'from_ware_house_id' => $fromWarehouse->id,
	'to_ware_house_id' => $toWarehouse->id,
]);

$distribution = Distribution::factory()->create();

$step = DistributionStep::factory()->create([
	'distribution_id' => $distribution->id,
	'route_id' => $route->id,
	'step_order' => 1,
]);
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
