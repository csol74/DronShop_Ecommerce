<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DronShopSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // 👑 ADMIN
        // =========================
        DB::table('users')->insert([
            'name' => 'Admin DronShop',
            'email' => 'admin@dronshop.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'created_at' => now(),
        ]);

        // =========================
        // 👤 CLIENTE
        // =========================
        DB::table('users')->insert([
            'name' => 'Cliente Demo',
            'email' => 'cliente@dronshop.com',
            'password' => Hash::make('cliente123'),
            'role' => 'cliente',
            'created_at' => now(),
        ]);

        // =========================
        // 📦 CATEGORÍAS
        // =========================
        $catElectronica = DB::table('categorias')->insertGetId([
            'nombre' => 'Electrónica',
            'slug' => 'electronica',
            'icono' => 'cpu',
            'created_at' => now(),
        ]);

        $catDeporte = DB::table('categorias')->insertGetId([
            'nombre' => 'Deporte',
            'slug' => 'deporte',
            'icono' => 'activity',
            'created_at' => now(),
        ]);

        $catRopa = DB::table('categorias')->insertGetId([
            'nombre' => 'Ropa',
            'slug' => 'ropa',
            'icono' => 'shopping-bag',
            'created_at' => now(),
        ]);

        // =========================
        // 🏢 PROVEEDORES + USERS
        // =========================

        // 🔹 PROVEEDOR 1
        $user1 = DB::table('users')->insertGetId([
            'name' => 'Carlos Méndez',
            'email' => 'proveedor1@dronshop.com',
            'password' => Hash::make('proveedor123'),
            'role' => 'proveedor',
            'created_at' => now(),
        ]);

        $prov1 = DB::table('proveedores')->insertGetId([
            'nombre' => 'Carlos Méndez',
            'empresa' => 'TechWorld Colombia',
            'email' => 'carlos@techworld.co',
            'telefono' => '3001234567',
            'pais' => 'Colombia',
            'descripcion' => 'Electrónica premium',
            'logo' => 'https://api.dicebear.com/7.x/initials/svg?seed=TW',
            'user_id' => $user1,
            'created_at' => now(),
        ]);

        // 🔹 PROVEEDOR 2
        $user2 = DB::table('users')->insertGetId([
            'name' => 'Sofía Restrepo',
            'email' => 'proveedor2@dronshop.com',
            'password' => Hash::make('proveedor123'),
            'role' => 'proveedor',
            'created_at' => now(),
        ]);

        $prov2 = DB::table('proveedores')->insertGetId([
            'nombre' => 'Sofía Restrepo',
            'empresa' => 'SportPro Andina',
            'email' => 'sofia@sportpro.co',
            'telefono' => '3159876543',
            'pais' => 'Colombia',
            'descripcion' => 'Artículos deportivos',
            'logo' => 'https://api.dicebear.com/7.x/initials/svg?seed=SP',
            'user_id' => $user2,
            'created_at' => now(),
        ]);

        // 🔹 PROVEEDOR 3
        $user3 = DB::table('users')->insertGetId([
            'name' => 'Andrés Palomino',
            'email' => 'proveedor3@dronshop.com',
            'password' => Hash::make('proveedor123'),
            'role' => 'proveedor',
            'created_at' => now(),
        ]);

        $prov3 = DB::table('proveedores')->insertGetId([
            'nombre' => 'Andrés Palomino',
            'empresa' => 'Moda Élite SAS',
            'email' => 'andres@modaelite.co',
            'telefono' => '3214567890',
            'pais' => 'Colombia',
            'descripcion' => 'Moda urbana premium',
            'logo' => 'https://api.dicebear.com/7.x/initials/svg?seed=ME',
            'user_id' => $user3,
            'created_at' => now(),
        ]);


        // =========================
        // 🛒 PRODUCTOS
        // =========================

        $productos = [
            // ELECTRÓNICA
            ['nombre'=>'iPhone Ultra X','precio'=>5200000,'categoria_id'=>$catElectronica,'proveedor_id'=>$prov1, 'img' => 'https://picsum.photos/id/160/600/400'],
            ['nombre'=>'Tablet Pro 12','precio'=>2100000,'categoria_id'=>$catElectronica,'proveedor_id'=>$prov1, 'img' => 'https://picsum.photos/id/0/600/400'],
            ['nombre'=>'Monitor 4K 144Hz','precio'=>1800000,'categoria_id'=>$catElectronica,'proveedor_id'=>$prov1, 'img' => 'https://picsum.photos/id/201/600/400'],
            ['nombre'=>'Teclado Mecánico RGB','precio'=>450000,'categoria_id'=>$catElectronica,'proveedor_id'=>$prov1, 'img' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=600'],
            ['nombre'=>'Mouse Gamer Pro','precio'=>320000,'categoria_id'=>$catElectronica,'proveedor_id'=>$prov1, 'img' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600'],

            // DEPORTE
            ['nombre'=>'Cinta de correr Pro','precio'=>3200000,'categoria_id'=>$catDeporte,'proveedor_id'=>$prov2, 'img' => 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?w=600'],
            ['nombre'=>'Guantes Gym Elite','precio'=>120000,'categoria_id'=>$catDeporte,'proveedor_id'=>$prov2, 'img' => 'https://images.unsplash.com/photo-1583473848882-f9a5bc7fd2ee?w=600'],
            ['nombre'=>'Balón Profesional','precio'=>95000,'categoria_id'=>$catDeporte,'proveedor_id'=>$prov2, 'img' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=600'],
            ['nombre'=>'Mancuernas 20kg','precio'=>280000,'categoria_id'=>$catDeporte,'proveedor_id'=>$prov2, 'img' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=600'],
            ['nombre'=>'Banda resistencia kit','precio'=>75000,'categoria_id'=>$catDeporte,'proveedor_id'=>$prov2, 'img' => 'https://images.unsplash.com/photo-1517130038641-a774d04afb3c?q=80&w=600'],

            // ROPA
            ['nombre'=>'Camiseta Oversize','precio'=>90000,'categoria_id'=>$catRopa,'proveedor_id'=>$prov3, 'img' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600'],
            ['nombre'=>'Pantalón Jogger','precio'=>140000,'categoria_id'=>$catRopa,'proveedor_id'=>$prov3, 'img' => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600'],
            ['nombre'=>'Gorra Streetwear','precio'=>60000,'categoria_id'=>$catRopa,'proveedor_id'=>$prov3, 'img' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600'],
            ['nombre'=>'Chaqueta Windbreaker','precio'=>220000,'categoria_id'=>$catRopa,'proveedor_id'=>$prov3, 'img' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600'],
            ['nombre'=>'Hoodie Premium','precio'=>180000,'categoria_id'=>$catRopa,'proveedor_id'=>$prov3, 'img' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600'],
        ];

        foreach ($productos as $p) {
            DB::table('productos')->insert([
                'nombre' => $p['nombre'],
                'slug' => Str::slug($p['nombre']),
                'descripcion' => 'Producto de alta calidad disponible en nuestra tienda.',
                'precio' => $p['precio'],
                'stock' => rand(10, 80),
                'stock_minimo' => 5,
                'imagen' => $p['img'],
                'caracteristicas' => json_encode(['Calidad'=>'Premium', 'Garantía' => '1 año']),
                'categoria_id' => $p['categoria_id'],
                'proveedor_id' => $p['proveedor_id'],
                'activo' => true,
                'created_at' => now(),
            ]);
        }
        // Dron
        DB::table('drones')->insert([
            'nombre'              => 'DronShop Alpha-1',
            'modelo'              => 'DJI Matrice 300 RTK',
            'numero_serie'        => 'DS-UAV-2024-001',
            'fabricante'          => 'DJI Enterprise',
            'fecha_adquisicion'   => '2024-01-15',
            'autonomia_min'       => 55,
            'velocidad_max_kmh'   => 82.8,
            'alcance_max_km'      => 15.0,
            'carga_max_kg'        => 2.7,
            'bateria_minima_pct'  => 20,
            'bateria_actual_pct'  => 87,
            'zonas_permitidas'    => json_encode([
                ['nombre' => 'Zona Norte Bucaramanga', 'radio_km' => 8, 'lat' => 7.1198, 'lng' => -73.1227],
                ['nombre' => 'Zona Centro',            'radio_km' => 5, 'lat' => 7.1254, 'lng' => -73.1198],
            ]),
            'condiciones_climaticas' => json_encode([
                'viento_max_kmh' => 40,
                'lluvia'         => false,
                'niebla'         => false,
                'temp_min_c'     => 5,
                'temp_max_c'     => 40,
            ]),
            'estado'      => 'disponible',
            'lat_actual'  => 7.1254,
            'lng_actual'  => -73.1198,
            'created_at'  => now(),
        ]);

        // Mantenimiento 1
        DB::table('mantenimientos')->insert([
            'dron_id'          => 1,
            'tipo'             => 'preventivo',
            'descripcion'      => 'Revisión general de motores y hélices',
            'fecha_programada' => now()->addDays(7)->toDateString(),
            'fecha_realizada'  => null,
            'costo'            => null,
            'tecnico'          => 'Ing. Carlos Ruiz',
            'estado'           => 'pendiente',
            'observaciones'    => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Mantenimiento 2
        DB::table('mantenimientos')->insert([
            'dron_id'          => 1,
            'tipo'             => 'correctivo',
            'descripcion'      => 'Reemplazo de batería principal',
            'fecha_programada' => now()->subDays(10)->toDateString(),
            'fecha_realizada'  => now()->subDays(9)->toDateString(),
            'costo'            => 450000,
            'tecnico'          => 'Ing. Carlos Ruiz',
            'estado'           => 'completado',
            'observaciones'    => 'Batería reemplazada. Autonomía restaurada a 55 min.',
            'created_at'       => now()->subDays(10),
            'updated_at'       => now()->subDays(9),
        ]);
    }

}
