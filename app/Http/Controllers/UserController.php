<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);

        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $actor = Auth::user();

        $data = User::query()
            ->with(['organization:id,name', 'region:id,name', 'zone:id,name', 'woreda:id,name'])
            ->accessibleBy($actor)
            ->orderByDesc('id')
            ->paginate(10);

        return view('users.index', compact('data'))->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $actor = Auth::user();
        $roles = Role::query()->orderBy('name')->pluck('name', 'name')->all();

        return view('users.create', array_merge(
            compact('roles'),
            $this->formContext($actor)
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $actor = Auth::user();
        $input = $request->validate($this->rules());

        $this->validateAccessPayload($request, $actor);
        $input = $this->normalizeLocationPayload($input);
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')
            ->with('success', 'user has been created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::query()
            ->accessibleBy(Auth::user())
            ->with(['organization', 'country', 'region', 'zone', 'woreda'])
            ->findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $actor = Auth::user();
        $user = User::query()->accessibleBy($actor)->findOrFail($id);
        $roles = Role::query()->orderBy('name')->get();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', array_merge(
            compact('user', 'roles', 'userRole'),
            $this->formContext($actor)
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $actor = Auth::user();
        $user = User::query()->accessibleBy($actor)->findOrFail($id);

        $input = $request->validate($this->rules($id));
        $this->validateAccessPayload($request, $actor, $user);

        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, ['password']);
        }

        $input = $this->normalizeLocationPayload($input);

        if ($actor->can('role-edit')) {
            $user->update($input);
            $user->syncRoles($request->input('roles', []));
        } else {
            return back()->with('error', 'You are not allowed to edit this role.');
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::query()->accessibleBy(Auth::user())->findOrFail($id);
        $user->delete();

        return back()->with('success', 'User has been deleted successfully');
    }

    // Alert Success Notification
    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Country Added Successfuly']
        );
    }

    private function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => [$id ? 'nullable' : 'required', 'same:confirm-password'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', Rule::exists('roles', 'name')],
            'access_level' => ['required', Rule::in(config('access_hierarchy.levels', []))],
            'organization_id' => ['nullable', Rule::exists('organizations', 'id')],
            'country_id' => ['nullable', Rule::exists('countries', 'id')],
            'region_id' => ['nullable', Rule::exists('regions', 'id')],
            'zone_id' => ['nullable', Rule::exists('zones', 'id')],
            'woreda_id' => ['nullable', Rule::exists('woredas', 'id')],
        ];
    }

    private function normalizeLocationPayload(array $input): array
    {
        foreach (['organization_id', 'country_id', 'region_id', 'zone_id', 'woreda_id'] as $field) {
            if (($input[$field] ?? null) === '') {
                $input[$field] = null;
            }
        }

        $level = $input['access_level'] ?? User::ACCESS_LEVEL_ORGANIZATION;

        if ($level === User::ACCESS_LEVEL_NATIONAL) {
            $input['organization_id'] = null;
            $input['country_id'] = null;
            $input['region_id'] = null;
            $input['zone_id'] = null;
            $input['woreda_id'] = null;
        }

        if ($level === User::ACCESS_LEVEL_REGION) {
            $input['organization_id'] = null;
            $input['zone_id'] = null;
            $input['woreda_id'] = null;
        }

        if ($level === User::ACCESS_LEVEL_ZONE) {
            $input['organization_id'] = null;
            $input['woreda_id'] = null;
        }

        if ($level === User::ACCESS_LEVEL_WOREDA) {
            $input['organization_id'] = null;
        }

        if ($level === User::ACCESS_LEVEL_ORGANIZATION && ! empty($input['organization_id'])) {
            $organization = Organization::query()->find($input['organization_id']);
            if ($organization) {
                $input['country_id'] = $organization->country_id;
                $input['region_id'] = $organization->region_id;
                $input['zone_id'] = $organization->zone_id;
                $input['woreda_id'] = $organization->woreda_id;
            }
        }

        return $input;
    }

    private function validateAccessPayload(Request $request, User $actor, ?User $target = null): void
    {
        $level = $request->string('access_level')->toString();
        $roles = $request->input('roles', []);

        if (!$actor->canManageAccessLevel($level)) {
            $this->failValidation('access_level', 'You cannot assign this access level.');
        }

        $this->validateRoleScopeCompatibility($roles, $actor, $level);
        $this->validateRequiredLocationByLevel($request, $level);
        $this->validateHierarchyConsistency($request);
        $this->validateRequestInsideActorScope($request, $actor, $level);

        if ($target && $target->id === $actor->id && !$actor->canManageAccessLevel($level)) {
            $this->failValidation('access_level', 'You cannot reduce your own access to a conflicting scope.');
        }
    }

    private function validateRoleScopeCompatibility(array $roles, User $actor, string $targetLevel): void
    {
        foreach ($roles as $roleName) {
            $roleScope = config('access_hierarchy.role_scope.' . $roleName, config('access_hierarchy.default_role_scope'));

            if (!$actor->canManageAccessLevel($roleScope)) {
                $this->failValidation('roles', 'You cannot assign the selected role.');
            }

            if (User::accessLevelRank($roleScope) > User::accessLevelRank($targetLevel)) {
                $this->failValidation('roles', 'Selected role requires broader access than selected access level.');
            }
        }
    }

    private function validateRequiredLocationByLevel(Request $request, string $level): void
    {
        if ($level === User::ACCESS_LEVEL_NATIONAL) {
            return;
        }

        if (empty($request->input('country_id'))) {
            $this->failValidation('country_id', 'Country is required for non-national users.');
        }

        if (in_array($level, [User::ACCESS_LEVEL_REGION, User::ACCESS_LEVEL_ZONE, User::ACCESS_LEVEL_WOREDA], true) && empty($request->input('region_id'))) {
            $this->failValidation('region_id', 'Region is required for this access level.');
        }

        if (in_array($level, [User::ACCESS_LEVEL_ZONE, User::ACCESS_LEVEL_WOREDA], true) && empty($request->input('zone_id'))) {
            $this->failValidation('zone_id', 'Zone is required for this access level.');
        }

        if ($level === User::ACCESS_LEVEL_WOREDA && empty($request->input('woreda_id'))) {
            $this->failValidation('woreda_id', 'Woreda is required for this access level.');
        }

        if ($level === User::ACCESS_LEVEL_ORGANIZATION && empty($request->input('organization_id'))) {
            $this->failValidation('organization_id', 'Organization is required for organization-level users.');
        }
    }

    private function validateHierarchyConsistency(Request $request): void
    {
        $regionId = $request->input('region_id');
        $zoneId = $request->input('zone_id');
        $woredaId = $request->input('woreda_id');

        if ($zoneId) {
            $zone = Zone::query()->find($zoneId);
            if (!$zone || (int) $zone->region_id !== (int) $regionId) {
                $this->failValidation('zone_id', 'Selected zone does not belong to selected region.');
            }
        }

        if ($woredaId) {
            $woreda = Woreda::query()->find($woredaId);
            if (!$woreda || (int) $woreda->zone_id !== (int) $zoneId) {
                $this->failValidation('woreda_id', 'Selected woreda does not belong to selected zone.');
            }
        }
    }

    private function validateRequestInsideActorScope(Request $request, User $actor, string $targetLevel): void
    {
        if ($actor->hasNationalAccess()) {
            return;
        }

        $organizationId = $request->input('organization_id');
        $regionId = $request->input('region_id');
        $zoneId = $request->input('zone_id');
        $woredaId = $request->input('woreda_id');

        $actorLevel = $actor->effectiveAccessLevel();

        if ($actorLevel === User::ACCESS_LEVEL_REGION && (int) $regionId !== (int) $actor->region_id) {
            $this->failValidation('region_id', 'You can only manage users inside your region.');
        }

        if ($actorLevel === User::ACCESS_LEVEL_ZONE) {
            if ((int) $regionId !== (int) $actor->region_id || (int) $zoneId !== (int) $actor->zone_id) {
                $this->failValidation('zone_id', 'You can only manage users inside your zone.');
            }
        }

        if ($actorLevel === User::ACCESS_LEVEL_WOREDA) {
            if ((int) $regionId !== (int) $actor->region_id || (int) $zoneId !== (int) $actor->zone_id || (int) $woredaId !== (int) $actor->woreda_id) {
                $this->failValidation('woreda_id', 'You can only manage users inside your woreda.');
            }
        }

        if ($actorLevel === User::ACCESS_LEVEL_ORGANIZATION) {
            if ((int) $organizationId !== (int) $actor->organization_id || $targetLevel !== User::ACCESS_LEVEL_ORGANIZATION) {
                $this->failValidation('organization_id', 'Organization-level users can only manage users inside their organization.');
            }
        }

        if (!empty($organizationId)) {
            $organization = Organization::query()->find($organizationId);
            if (!$organization) {
                $this->failValidation('organization_id', 'Selected organization was not found.');
            }

            if ($actorLevel === User::ACCESS_LEVEL_REGION && (int) $organization->region_id !== (int) $actor->region_id) {
                $this->failValidation('organization_id', 'Selected organization is outside your region.');
            }

            if ($actorLevel === User::ACCESS_LEVEL_ZONE && ((int) $organization->region_id !== (int) $actor->region_id || (int) $organization->zone_id !== (int) $actor->zone_id)) {
                $this->failValidation('organization_id', 'Selected organization is outside your zone.');
            }

            if ($actorLevel === User::ACCESS_LEVEL_WOREDA && ((int) $organization->region_id !== (int) $actor->region_id || (int) $organization->zone_id !== (int) $actor->zone_id || (int) $organization->woreda_id !== (int) $actor->woreda_id)) {
                $this->failValidation('organization_id', 'Selected organization is outside your woreda.');
            }
        }
    }

    private function failValidation(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    private function formContext(User $actor): array
    {
        $countries = Country::query();
        $regions = Region::query();
        $zones = Zone::query();
        $woredas = Woreda::query();
        $organizations = Organization::query()->select('id', 'name', 'country_id', 'region_id', 'zone_id', 'woreda_id');

        if (!$actor->hasNationalAccess()) {
            if (!empty($actor->country_id)) {
                $countries->where('id', $actor->country_id);
                $regions->where('country_id', $actor->country_id);
                $zones->where('country_id', $actor->country_id);
                $woredas->where('country_id', $actor->country_id);
                $organizations->where('country_id', $actor->country_id);
            }

            if (!empty($actor->region_id)) {
                $regions->where('id', $actor->region_id);
                $zones->where('region_id', $actor->region_id);
                $woredas->where('region_id', $actor->region_id);
                $organizations->where('region_id', $actor->region_id);
            }

            if (!empty($actor->zone_id)) {
                $zones->where('id', $actor->zone_id);
                $woredas->where('zone_id', $actor->zone_id);
                $organizations->where('zone_id', $actor->zone_id);
            }

            if (!empty($actor->woreda_id)) {
                $woredas->where('id', $actor->woreda_id);
                $organizations->where('woreda_id', $actor->woreda_id);
            }

            if (!empty($actor->organization_id)) {
                $organizations->where('id', $actor->organization_id);
            }
        }

        return [
            'accessLevels' => config('access_hierarchy.levels', []),
            'countries' => $countries->orderBy('name')->get(),
            'regions' => $regions->orderBy('name')->get(),
            'zones' => $zones->orderBy('name')->get(),
            'woredas' => $woredas->orderBy('name')->get(),
            'organizations' => $organizations->orderBy('name')->get(),
        ];
    }
}
