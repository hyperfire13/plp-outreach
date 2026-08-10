<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()
            ->where('email', 'calo.admin@plp.edu.ph')
            ->firstOrFail();

        $communities = [
            ['name' => 'Barangay San Miguel', 'barangay_code' => 'PSG-SM-001', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'estimated_population' => 40199, 'estimated_households' => 9180, 'community_type' => 'urban residential', 'predominant_livelihood' => 'Retail, transport, and service work', 'address' => 'Barangay San Miguel, Pasig City', 'remarks' => 'Partner community for digital literacy and livelihood initiatives.'],
            ['name' => 'Barangay Pinagbuhatan', 'barangay_code' => 'PSG-PB-002', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'estimated_population' => 163598, 'estimated_households' => 36500, 'community_type' => 'high-density urban', 'predominant_livelihood' => 'Manufacturing, construction, and informal enterprise', 'address' => 'Barangay Pinagbuhatan, Pasig City', 'remarks' => 'Priority area for health, sanitation, and youth-development programs.'],
            ['name' => 'Barangay Manggahan', 'barangay_code' => 'PSG-MG-003', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'estimated_population' => 93271, 'estimated_households' => 20700, 'community_type' => 'urban riverside', 'predominant_livelihood' => 'Small business, food services, and wage employment', 'address' => 'Barangay Manggahan, Pasig City', 'remarks' => 'Flood-prone areas require disaster preparedness and environmental programs.'],
            ['name' => 'Barangay Santolan', 'barangay_code' => 'PSG-ST-004', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'estimated_population' => 57525, 'estimated_households' => 12800, 'community_type' => 'urban residential', 'predominant_livelihood' => 'Commerce, education, and transport services', 'address' => 'Barangay Santolan, Pasig City', 'remarks' => 'Active youth council and senior-citizen associations.'],
            ['name' => 'Barangay Kalawaan', 'barangay_code' => 'PSG-KL-005', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'estimated_population' => 32812, 'estimated_households' => 7350, 'community_type' => 'mixed residential-industrial', 'predominant_livelihood' => 'Factory work, logistics, and microenterprise', 'address' => 'Barangay Kalawaan, Pasig City', 'remarks' => 'Community partners requested livelihood and waste-management support.'],
        ];

        foreach ($communities as $community) {
            Community::query()->updateOrCreate(
                ['barangay_code' => $community['barangay_code']],
                [...$community, 'is_active' => true, 'created_by' => $creator->id]
            );
        }
    }
}
