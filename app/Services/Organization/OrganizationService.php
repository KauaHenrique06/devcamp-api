
<?php

use App\Http\Resources\Organization\OrganizationCollection;
use App\Http\Resources\Organization\OrganizationResource;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class OrganizationService
{

    public function index(array $data): OrganizationCollection
    {
        $perPage = (int) ($data['data[''] ?? 10);
        $page = (int) ($data['page'] ?? 1);
        $search = $data['search'] ?? null;

        $query = Organization::with('organization')
         ->when($search, function($query, $search){
             $query->where(function($q) use($search){
                 $q->where('name', 'ILike', "%{$search}")
                    ->orWhere('description', 'ILike', "%{$search}" )
             })
         })
        return new OrganizationCollection($paginate)
    }
    public function store(array $data): OrganizationResource
    {
        return DB::transaction(function() use ($data): OrganizationResource{
            $organization = Organization::create([
                'name' => $data['name'],
                'slug'=> $data['slug'],
                'description'=> $data['description']
            ]);
            return new OrganizationResource($organization)

        })
    }
}
