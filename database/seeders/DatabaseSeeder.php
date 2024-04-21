<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\phanloai_sanpham::factory(3)->create(); 
        // \App\Models\taikhoan::factory(1)->create();   
        // \App\Models\sanpham::factory(7)->create();
        \App\Models\mausac::factory(10)->create();    
        // \App\Models\size::factory(3)->create();  
        // \App\Models\sanpham_mausac_size::factory(10)->create();   
        // \App\Models\thongtingiaohang::factory(3)->create();   
        // \App\Models\voucher::factory(3)->create();   

        // insert into sizes values('S');
        // insert into sizes values('M');
        // insert into sizes values('L');
        // insert into sizes values('XL');
        // insert into sizes values('XXL');
        // insert into sizes values('3XL');
        //MAPL1 = 1, 2, 3 tương ứng là nam, nữ, trẻ em
        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(1, 1, 'Áo thun');
        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(2, 1, 'Áo POLO');
        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(3, 1, 'Quần short');

        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(1, 2, 'Áo thun');
        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(2, 2, 'Váy');
        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(3, 2, 'Đầm');

        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(1, 3, 'Áo');
        // insert into phanloai_sanpham2s( MAPL2 ,APL1, TENPL2) values(2, 3, 'Quần');
        // insert into phanloai_sanpham2s( MAPL2 ,MAPL1, TENPL2) values(3, 3, 'Set đồ');
    }
}                                                                                                                   
