<?php

namespace App\Services;

use App\Models\Community;

class CommunityService
{
    public function paginate(int $perPage = 10)
    {
        return Community::select([
                'id',
                'name',
                'region',
                'population',
                'poverty_rate',
                'unemployment_rate',
                'literacy_rate',
                'urban_rural',
                'disaster_risk_level'
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data): Community
    {
        return Community::create($data);
    }

    public function update(Community $community, array $data): Community
    {
        $community->update($data);
        return $community;
    }

    public function delete(Community $community): void
    {
        $community->delete();
    }
}