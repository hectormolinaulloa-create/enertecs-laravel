<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BillExtractor
{
    private const PROMPT = <<<'PROMPT'
Eres un extractor de datos de boletas eléctricas chilenas.
Extrae los siguientes campos del documento y devuelve SOLO un JSON válido, sin explicación.
Si un campo no se encuentra, usa null.

Campos requeridos:
{
  "nombre_cliente": string | null,
  "rut": string | null,
  "numero_cliente": string | null,
  "direccion": string | null,
  "comuna": string | null,
  "consumo_kwh": number | null,
  "consumo_historico_kwh": number[] | null,
  "monto_total_clp": number | null,
  "tipo_tarifa": string | null,
  "distribuidora": string | null,
  "tipo_medidor": "monofasico" | "trifasico" | null
}

Notas:
- tipo_medidor: busca "monofásico", "trifásico", "1F", "3F", o el número de fases del medidor
- consumo_kwh: el consumo del mes actual, solo el número sin unidades
- consumo_historico_kwh: array con los valores de consumo mensual en kWh de los últimos meses, de más antiguo a más reciente. Si no hay historial, usa null.
- monto_total_clp: solo el número total a pagar, sin puntos ni símbolos
- tipo_tarifa: BT1, BT2, BT3, AT, etc.
PROMPT;

    public function extract(string $pdfPath): array
    {
        if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
            throw new \InvalidArgumentException("PDF no encontrado o no legible: {$pdfPath}");
        }

        $base64 = base64_encode(file_get_contents($pdfPath));

        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 1024,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'   => 'document',
                        'source' => [
                            'type'       => 'base64',
                            'media_type' => 'application/pdf',
                            'data'       => $base64,
                        ],
                    ],
                    ['type' => 'text', 'text' => self::PROMPT],
                ],
            ]],
        ]);

        $response->throw();
        $body = $response->json();

        if (($body['stop_reason'] ?? '') === 'max_tokens') {
            throw new \RuntimeException('Respuesta truncada — boleta demasiado extensa.');
        }

        $raw = $body['content'][0]['text'] ?? '';
        preg_match('/\{[\s\S]*\}/', $raw, $matches);
        if (empty($matches[0])) {
            throw new \RuntimeException('Respuesta de Claude no contiene JSON válido.');
        }

        $data = json_decode($matches[0], true);
        if (!is_array($data)) {
            throw new \RuntimeException('JSON de Claude no es válido o está malformado.');
        }

        return $data;
    }
}
