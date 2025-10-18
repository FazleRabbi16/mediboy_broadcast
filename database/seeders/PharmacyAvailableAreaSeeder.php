<?php

namespace Database\Seeders;

use App\Models\PharmacyArea;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PharmacyAvailableAreaSeeder extends Seeder
{
   //30 min cover area seeder
    public function run()
    {
        $divsion = ['divison1','divison2','divison3','divison4','divison5'];
        $disctrict = ['disctrict1','disctrict2','disctrict3','disctrict4','disctrict5'];
        $sub_disctrict = ['sub_disctrict1','sub_disctrict2','sub_disctrict3','sub_disctrict4','sub_disctrict5'];
        $place = ['place1','place2','palce3','palce4','palce5'];

        $limit=20;
        for ($i=0; $i <$limit ; $i++) {
            shuffle($divsion);
            shuffle($disctrict);
            shuffle($sub_disctrict);
            shuffle($place);
            PharmacyArea::create([
                'division'=>$divsion[0],
                'district'=>$disctrict[0],
                'subDistrict'=>$sub_disctrict[0],
                'area'=>$place[0]
            ]);
        }
    }
}
