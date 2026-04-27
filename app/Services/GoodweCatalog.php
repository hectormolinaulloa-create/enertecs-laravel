<?php
namespace App\Services;

class GoodweCatalog
{
    public static function inversores(): array
    {
        return [
            // ── Monofásico: DNS G4 (3–6 kW, 2 MPPT) ──────────────────────
            [
                'id' => 'GW3K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW3K-DNS-G40',
                'potencia_kw' => 3.0, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 6.0,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW3.6K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW3.6K-DNS-G40',
                'potencia_kw' => 3.6, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 7.2,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW4.2K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW4.2K-DNS-G40',
                'potencia_kw' => 4.2, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 8.4,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW5K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW5K-DNS-G40',
                'potencia_kw' => 5.0, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 10.0,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW6K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW6K-DNS-G40',
                'potencia_kw' => 6.0, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 12.0,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],

            // ── Trifásico: SDT G2 PLUS+ (4–20 kW, 2 MPPT) ────────────────
            [
                'id' => 'GW4K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW4K-SDT-20',
                'potencia_kw' => 4.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 12.5, 'potencia_max_dc_kw' => 6.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW5K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW5K-SDT-20',
                'potencia_kw' => 5.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 12.5, 'potencia_max_dc_kw' => 7.5,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW6K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW6K-SDT-20',
                'potencia_kw' => 6.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 12.5, 'potencia_max_dc_kw' => 9.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW8K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW8K-SDT-20',
                'potencia_kw' => 8.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 12.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW10K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW10K-SDT-20',
                'potencia_kw' => 10.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 15.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW12K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW12K-SDT-20',
                'potencia_kw' => 12.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 18.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],
            [
                'id' => 'GW15K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW15K-SDT-20',
                'potencia_kw' => 15.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 22.5,
                'eficiencia_max_pct' => 98.4, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],
            [
                'id' => 'GW17K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW17K-SDT-20',
                'potencia_kw' => 17.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 25.5,
                'eficiencia_max_pct' => 98.4, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],
            [
                'id' => 'GW20K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW20K-SDT-20',
                'potencia_kw' => 20.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 30.0,
                'eficiencia_max_pct' => 98.4, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],

            // ── Trifásico: SDT G3 (10–20 kW, 2 MPPT) — con SEC Chile ─────
            [
                'id' => 'GW10K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW10K-SDT-30',
                'potencia_kw' => 10.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 15.0,
                'eficiencia_max_pct' => 98.6, 'activo' => true,
                'dimensiones' => ['alto_mm' => 418, 'ancho_mm' => 400, 'fondo_mm' => 174, 'peso_kg' => 20],
            ],
            [
                'id' => 'GW12K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW12K-SDT-30',
                'potencia_kw' => 12.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 18.0,
                'eficiencia_max_pct' => 98.6, 'activo' => true,
                'dimensiones' => ['alto_mm' => 418, 'ancho_mm' => 400, 'fondo_mm' => 174, 'peso_kg' => 20],
            ],
            [
                'id' => 'GW15K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW15K-SDT-30',
                'potencia_kw' => 15.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 22.5,
                'eficiencia_max_pct' => 98.6, 'activo' => true,
                'dimensiones' => ['alto_mm' => 500, 'ancho_mm' => 480, 'fondo_mm' => 195, 'peso_kg' => 25],
            ],
            [
                'id' => 'GW17K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW17K-SDT-30',
                'potencia_kw' => 17.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 25.5,
                'eficiencia_max_pct' => 98.7, 'activo' => true,
                'dimensiones' => ['alto_mm' => 500, 'ancho_mm' => 480, 'fondo_mm' => 195, 'peso_kg' => 25],
            ],
            [
                'id' => 'GW20K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW20K-SDT-30',
                'potencia_kw' => 20.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 30.0,
                'eficiencia_max_pct' => 98.7, 'activo' => true,
                'dimensiones' => ['alto_mm' => 500, 'ancho_mm' => 480, 'fondo_mm' => 195, 'peso_kg' => 25],
            ],
        ];
    }
}
