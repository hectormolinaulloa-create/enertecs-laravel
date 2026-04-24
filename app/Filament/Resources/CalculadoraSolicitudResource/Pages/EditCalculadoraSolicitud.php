<?php

namespace App\Filament\Resources\CalculadoraSolicitudResource\Pages;

use App\Filament\Resources\CalculadoraSolicitudResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCalculadoraSolicitud extends EditRecord
{
    protected static string $resource = CalculadoraSolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
