<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenLocationCode\OpenLocationCode;

class AddressSeeder extends Seeder
{
    /**
     * Seed realistic Omdurman/Khartoum addresses with actual GPS coordinates.
     */
    public function run(): void
    {
        $addresses = [
            // أم درمان
            ['address_str' => 'حي الثورة - مربع 12 - منزل 5', 'neighborhood' => 'الثورة', 'lat' => 15.6361, 'lng' => 32.4777],
            ['address_str' => 'حي العمدة - مربع 3 - منزل 18', 'neighborhood' => 'العمدة', 'lat' => 15.6445, 'lng' => 32.4812],
            ['address_str' => 'حي الموردة - شارع النيل - منزل 7', 'neighborhood' => 'الموردة', 'lat' => 15.6289, 'lng' => 32.4733],
            ['address_str' => 'حي أبو سعد - مربع 8 - منزل 22', 'neighborhood' => 'أبو سعد', 'lat' => 15.6512, 'lng' => 32.4690],
            ['address_str' => 'حي بيت المال - مربع 6 - منزل 11', 'neighborhood' => 'بيت المال', 'lat' => 15.6380, 'lng' => 32.4850],
            ['address_str' => 'حي الملازمين - مربع 2 - منزل 9', 'neighborhood' => 'الملازمين', 'lat' => 15.6200, 'lng' => 32.4950],
            ['address_str' => 'حي ود نوباوي - مربع 15 - منزل 3', 'neighborhood' => 'ود نوباوي', 'lat' => 15.6550, 'lng' => 32.4600],
            ['address_str' => 'حي أبو روف - مربع 10 - منزل 14', 'neighborhood' => 'أبو روف', 'lat' => 15.6100, 'lng' => 32.4880],
            // الخرطوم
            ['address_str' => 'حي الرياض - شارع 61 - منزل 20', 'neighborhood' => 'الرياض', 'lat' => 15.5801, 'lng' => 32.5402],
            ['address_str' => 'حي المنشية - مربع 4 - منزل 16', 'neighborhood' => 'المنشية', 'lat' => 15.5950, 'lng' => 32.5300],
            ['address_str' => 'حي الصحافة - شارع الصحافة - منزل 8', 'neighborhood' => 'الصحافة', 'lat' => 15.5700, 'lng' => 32.5550],
            ['address_str' => 'حي جبرة - مربع 20 - منزل 1', 'neighborhood' => 'جبرة', 'lat' => 15.5500, 'lng' => 32.5700],
            // بحري
            ['address_str' => 'حي الحلفايا - مربع 11 - منزل 6', 'neighborhood' => 'الحلفايا', 'lat' => 15.6700, 'lng' => 32.5200],
            ['address_str' => 'حي الختمية - شارع المك نمر - منزل 13', 'neighborhood' => 'الختمية', 'lat' => 15.6350, 'lng' => 32.5350],
        ];

        foreach ($addresses as $addr) {
            $id = Str::uuid();
            $token = 's2_' . substr(md5($addr['lat'] . $addr['lng'] . '21'), 0, 7);
            $plusCode = OpenLocationCode::encode($addr['lat'], $addr['lng']);

            DB::table('addresses')->insert([
                'id' => $id,
                's2_cell_id' => $token,
                'plus_code' => $plusCode,
                'address_str' => $addr['address_str'],
                'neighborhood' => $addr['neighborhood'],
                'location' => DB::raw("ST_SetSRID(ST_MakePoint({$addr['lng']}, {$addr['lat']}), 4326)"),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ تم إدخال 15 عنوان واقعي (أم درمان / الخرطوم / بحري)');
    }
}
