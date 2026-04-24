<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalculadoraSolicitudResource\Pages;
use App\Filament\Resources\CalculadoraSolicitudResource\RelationManagers;
use App\Models\CalculadoraSolicitud;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CalculadoraSolicitudResource extends Resource
{
    protected static ?string $model = CalculadoraSolicitud::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('telefono')->disabled(),
            Forms\Components\TextInput::make('estado')->disabled(),
            Forms\Components\KeyValue::make('datos_boleta')->disabled(),
            Forms\Components\KeyValue::make('resultado')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('nombre')->searchable(),
            Tables\Columns\TextColumn::make('telefono'),
            Tables\Columns\TextColumn::make('estado')->badge()
                ->color(fn($state) => match($state) {
                    'completado' => 'success', 'error' => 'danger',
                    'procesando' => 'warning', default => 'gray',
                }),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalculadoraSolicituds::route('/'),
            'create' => Pages\CreateCalculadoraSolicitud::route('/create'),
            'edit' => Pages\EditCalculadoraSolicitud::route('/{record}/edit'),
        ];
    }
}
