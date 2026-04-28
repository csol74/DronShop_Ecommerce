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
        // Admin
        DB::table('users')->insert([
            'name'       => 'Admin DronShop',
            'email'      => 'admin@dronshop.com',
            'password'   => Hash::make('admin123'),
            'role'       => 'admin',
            'created_at' => now(),
        ]);

        // Cliente demo
        DB::table('users')->insert([
            'name'       => 'Cliente Demo',
            'email'      => 'cliente@dronshop.com',
            'password'   => Hash::make('cliente123'),
            'role'       => 'cliente',
            'created_at' => now(),
        ]);

        // Categorías
        $categorias = [
            ['nombre' => 'Electrónica', 'slug' => 'electronica', 'icono' => 'cpu'],
            ['nombre' => 'Deporte',     'slug' => 'deporte',     'icono' => 'activity'],
            ['nombre' => 'Ropa',        'slug' => 'ropa',        'icono' => 'shopping-bag'],
        ];
        foreach ($categorias as $cat) {
            DB::table('categorias')->insert(array_merge($cat, ['created_at' => now()]));
        }

        // Proveedores
        $proveedores = [
            [
                'nombre'      => 'Carlos Méndez',
                'empresa'     => 'TechWorld Colombia',
                'email'       => 'carlos@techworld.co',
                'telefono'    => '+57 300 123 4567',
                'pais'        => 'Colombia',
                'descripcion' => 'Importador líder de electrónica de consumo y gadgets premium en Latinoamérica.',
                'logo'        => 'https://api.dicebear.com/7.x/initials/svg?seed=TW&backgroundColor=1a1a2e',
            ],
            [
                'nombre'      => 'Sofía Restrepo',
                'empresa'     => 'SportPro Andina',
                'email'       => 'sofia@sportpro.co',
                'telefono'    => '+57 315 987 6543',
                'pais'        => 'Colombia',
                'descripcion' => 'Distribuidor oficial de marcas deportivas de alto rendimiento para atletas profesionales.',
                'logo'        => 'https://api.dicebear.com/7.x/initials/svg?seed=SP&backgroundColor=0f3460',
            ],
            [
                'nombre'      => 'Andrés Palomino',
                'empresa'     => 'Moda Élite SAS',
                'email'       => 'andres@modaelite.co',
                'telefono'    => '+57 321 456 7890',
                'pais'        => 'Colombia',
                'descripcion' => 'Marca colombiana de moda urbana de lujo con colecciones exclusivas y materiales premium.',
                'logo'        => 'https://api.dicebear.com/7.x/initials/svg?seed=ME&backgroundColor=16213e',
            ],
        ];
        foreach ($proveedores as $prov) {
            DB::table('proveedores')->insert(array_merge($prov, ['created_at' => now()]));
        }

        // Productos
        $productos = [
            // Electrónica (categoria_id=1, proveedor_id=1)
            [
                'nombre'          => 'Smartphone Galaxy Z Pro',
                'slug'            => 'smartphone-galaxy-z-pro',
                'descripcion'     => 'Smartphone flagship con pantalla AMOLED 6.8" plegable, cámara IA de 200MP y batería de 5000mAh con carga ultra rápida de 65W.',
                'precio'          => 3850000,
                'stock'           => 25,
                'imagen'          => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Pantalla'   => 'AMOLED 6.8" 120Hz plegable',
                    'Procesador' => 'Snapdragon 8 Gen 3',
                    'RAM'        => '12 GB LPDDR5',
                    'Almacenamiento' => '256 GB UFS 4.0',
                    'Cámara'     => '200MP + 50MP + 12MP IA',
                    'Batería'    => '5000mAh carga 65W',
                    'OS'         => 'Android 14',
                    'Garantía'   => '1 año oficial',
                ]),
                'categoria_id' => 1,
                'proveedor_id' => 1,
            ],
            [
                'nombre'          => 'Laptop UltraSlim X1 Carbon',
                'slug'            => 'laptop-ultraslim-x1-carbon',
                'descripcion'     => 'Laptop ultradelgada de fibra de carbono con display OLED 2.8K, rendimiento profesional y autonomía de 18 horas.',
                'precio'          => 6200000,
                'stock'           => 12,
                'imagen'          => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Pantalla'   => 'OLED 14" 2.8K 90Hz',
                    'Procesador' => 'Intel Core Ultra 7 165H',
                    'RAM'        => '32 GB DDR5',
                    'Almacenamiento' => '1 TB NVMe Gen 4',
                    'GPU'        => 'Intel Arc Xe integrada',
                    'Batería'    => '72Wh — 18h autonomía',
                    'Peso'       => '1.12 kg',
                    'Garantía'   => '2 años premium',
                ]),
                'categoria_id' => 1,
                'proveedor_id' => 1,
            ],
            [
                'nombre'          => 'Auriculares NoiseX Pro 360',
                'slug'            => 'auriculares-noisex-pro-360',
                'descripcion'     => 'Auriculares over-ear con cancelación activa de ruido de 42dB, audio espacial 360° y 30 horas de batería.',
                'precio'          => 890000,
                'stock'           => 40,
                'imagen'          => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Tipo'       => 'Over-ear inalámbrico',
                    'ANC'        => 'Cancelación activa 42dB',
                    'Audio'      => 'Espacial 360° Hi-Res',
                    'Bluetooth'  => '5.3 multipoint',
                    'Batería'    => '30h ANC / 40h sin ANC',
                    'Carga'      => 'USB-C — 10min = 3h',
                    'Peso'       => '254 g',
                    'Garantía'   => '1 año',
                ]),
                'categoria_id' => 1,
                'proveedor_id' => 1,
            ],
            [
                'nombre'          => 'Smart Watch Apex Series 5',
                'slug'            => 'smart-watch-apex-series-5',
                'descripcion'     => 'Reloj inteligente con AMOLED always-on, GPS dual, monitoreo cardíaco continuo y chasis de titanio premium.',
                'precio'          => 1250000,
                'stock'           => 30,
                'imagen'          => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Pantalla'   => 'AMOLED 1.4" Always-On',
                    'GPS'        => 'Dual-Band L1+L5',
                    'Salud'      => 'Frec. cardíaca + SpO2 + ECG',
                    'Chasis'     => 'Titanio grado 5',
                    'Batería'    => '7 días uso normal / 14h GPS',
                    'Resistencia'=> '10 ATM sumergible',
                    'Compatibilidad' => 'Android & iOS',
                    'Garantía'   => '1 año',
                ]),
                'categoria_id' => 1,
                'proveedor_id' => 1,
            ],
            // Deporte (categoria_id=2, proveedor_id=2)
            [
                'nombre'          => 'Bicicleta MTB Vortex Carbon 29"',
                'slug'            => 'bicicleta-mtb-vortex-carbon-29',
                'descripcion'     => 'Bicicleta de montaña de carbono con suspensión total Fox Factory, transmisión Shimano XT 12v y frenos hidráulicos.',
                'precio'          => 8500000,
                'stock'           => 8,
                'imagen'          => 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Marco'      => 'Carbono T800 Full-Suspension',
                    'Ruedas'     => '29" x 2.4" Maxxis Minion',
                    'Suspensión' => 'Fox Float 36 Factory 150mm',
                    'Transmisión'=> 'Shimano XT 12 velocidades',
                    'Frenos'     => 'Shimano XT hidráulicos 203mm',
                    'Manubrio'   => 'Carbono 35mm rise',
                    'Peso'       => '13.4 kg',
                    'Garantía'   => '2 años marco / 1 año partes',
                ]),
                'categoria_id' => 2,
                'proveedor_id' => 2,
            ],
            [
                'nombre'          => 'Tenis Running AirBlast V3',
                'slug'            => 'tenis-running-airblast-v3',
                'descripcion'     => 'Zapatilla de running con tecnología de amortiguación de aire reactiva, suela de carbono y upper Flyknit transpirable.',
                'precio'          => 520000,
                'stock'           => 55,
                'imagen'          => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Upper'      => 'Flyknit V2 transpirable',
                    'Amortiguación' => 'Air Zoom + placa carbono',
                    'Suela'      => 'Rubber outsole grip 360°',
                    'Drop'       => '8mm talón-punta',
                    'Peso'       => '245 g talla 42',
                    'Uso'        => 'Carretera — velocidad',
                    'Colores'    => '6 opciones disponibles',
                    'Garantía'   => '6 meses',
                ]),
                'categoria_id' => 2,
                'proveedor_id' => 2,
            ],
            [
                'nombre'          => 'Kit Yoga Pro Alignment',
                'slug'            => 'kit-yoga-pro-alignment',
                'descripcion'     => 'Kit completo de yoga profesional con mat antideslizante 6mm, bloques de corcho, correa y bolsa de transporte premium.',
                'precio'          => 285000,
                'stock'           => 60,
                'imagen'          => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Mat'        => 'TPE 6mm doble capa antideslizante',
                    'Bloques'    => '2x corcho natural 100%',
                    'Correa'     => 'Algodón orgánico 2.5m',
                    'Bolsa'      => 'Lona reciclada con asas',
                    'Medidas mat'=> '183cm x 61cm',
                    'Peso total' => '2.1 kg',
                    'Certificado'=> 'Eco-friendly REACH',
                    'Garantía'   => '1 año',
                ]),
                'categoria_id' => 2,
                'proveedor_id' => 2,
            ],
            [
                'nombre'          => 'Pesas Ajustables PowerSet 40kg',
                'slug'            => 'pesas-ajustables-powerset-40kg',
                'descripcion'     => 'Sistema de mancuernas ajustables de 4 a 40 kg con mecanismo de dial de precisión y base de carga incluida.',
                'precio'          => 1680000,
                'stock'           => 15,
                'imagen'          => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Rango'      => '4 kg a 40 kg por mancuerna',
                    'Ajuste'     => 'Dial de precisión 15 posiciones',
                    'Material'   => 'Acero cromado + nylon',
                    'Agarre'     => 'Texturizado antideslizante',
                    'Base'       => 'Incluida con bandeja de carga',
                    'Longitud'   => '38 cm compactas',
                    'Certificado'=> 'ISO 20957',
                    'Garantía'   => '2 años',
                ]),
                'categoria_id' => 2,
                'proveedor_id' => 2,
            ],
            // Ropa (categoria_id=3, proveedor_id=3)
            [
                'nombre'          => 'Chaqueta Urban Moto Premium',
                'slug'            => 'chaqueta-urban-moto-premium',
                'descripcion'     => 'Chaqueta de cuero sintético premium con forro térmico desmontable, cortes ergonómicos y detalles reflectivos para ciudad.',
                'precio'          => 420000,
                'stock'           => 35,
                'imagen'          => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Material'   => 'PU Premium + forro polar',
                    'Tallas'     => 'XS — 3XL',
                    'Forro'      => 'Desmontable térmico',
                    'Cierres'    => 'YKK impermeables',
                    'Detalles'   => 'Reflectivos 3M laterales',
                    'Lavado'     => 'Dry clean only',
                    'Colores'    => 'Negro, Café, Verde militar',
                    'Garantía'   => '6 meses costura',
                ]),
                'categoria_id' => 3,
                'proveedor_id' => 3,
            ],
            [
                'nombre'          => 'Sudadera Oversize Élite Collection',
                'slug'            => 'sudadera-oversize-elite-collection',
                'descripcion'     => 'Sudadera premium en fleece de 400GSM con bordado minimalista, fit oversize y puños acanalados de alta durabilidad.',
                'precio'          => 195000,
                'stock'           => 70,
                'imagen'          => 'https://m.media-amazon.com/images/I/41HotM2SuCL._AC_UY1000_.jpg',
                'caracteristicas' => json_encode([
                    'Material'   => '80% algodón / 20% poliéster 400GSM',
                    'Tallas'     => 'S — XXL',
                    'Fit'        => 'Oversize — largo extended',
                    'Bordado'    => 'Minimalista hilo premium',
                    'Capucha'    => 'Doble capa con cordón plano',
                    'Lavado'     => '30°C vuelta al revés',
                    'Colores'    => '8 colores temporada',
                    'Garantía'   => '3 meses costuras',
                ]),
                'categoria_id' => 3,
                'proveedor_id' => 3,
            ],
            [
                'nombre'          => 'Jean Slim Stretch DarkWash',
                'slug'            => 'jean-slim-stretch-darkwash',
                'descripcion'     => 'Jean slim fit de denim stretch 98/2 con lavado oscuro premium, refuerzos en puntos críticos y acabados artesanales.',
                'precio'          => 168000,
                'stock'           => 80,
                'imagen'          => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Material'   => '98% algodón / 2% elastano',
                    'Tallas'     => '28 — 38 / S — XL',
                    'Lavado'     => 'Dark wash artesanal',
                    'Fit'        => 'Slim — rise medio',
                    'Refuerzos'  => 'Remaches cobre en costuras',
                    'Bolsillos'  => '5 funcionales',
                    'Lavado'     => '30°C en frío',
                    'Garantía'   => '3 meses',
                ]),
                'categoria_id' => 3,
                'proveedor_id' => 3,
            ],
            [
                'nombre'          => 'Conjunto Activewear Elevate',
                'slug'            => 'conjunto-activewear-elevate',
                'descripcion'     => 'Conjunto deportivo de alto rendimiento con tela compresión 4-way stretch, control de humedad y protección UV50+.',
                'precio'          => 310000,
                'stock'           => 45,
                'imagen'          => 'https://images.unsplash.com/photo-1518459031867-a89b944bffe4?w=600&q=80',
                'caracteristicas' => json_encode([
                    'Material'   => 'Nylon 78% / Spandex 22% reciclado',
                    'Compresión' => '4-way stretch profesional',
                    'Humedad'    => 'Quick-dry DryFit technology',
                    'UV'         => 'Protección UPF 50+',
                    'Tallas'     => 'XS — XL',
                    'Incluye'    => 'Top + leggins cintura alta',
                    'Colores'    => 'Sand, Navy, Olive, Coral',
                    'Garantía'   => '6 meses',
                ]),
                'categoria_id' => 3,
                'proveedor_id' => 3,
            ],

        ];

        foreach ($productos as $prod) {
            DB::table('productos')->insert(array_merge($prod, [
                'activo'     => true,
                'created_at' => now(),
            ]));
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
