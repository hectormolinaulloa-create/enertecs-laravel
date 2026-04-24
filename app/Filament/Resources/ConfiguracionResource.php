<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfiguracionResource\Pages;
use App\Filament\Resources\ConfiguracionResource\RelationManagers;
use App\Models\Configuracion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConfiguracionResource extends Resource
{
    protected static ?string $model = Configuracion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('clave')->required()->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('valor')->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('clave')->searchable(),
            Tables\Columns\TextColumn::make('valor')->limit(60),
            Tables\Columns\TextColumn::make('updated_at')->since(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfiguracions::route('/'),
            'create' => Pages\CreateConfiguracion::route('/create'),
            'edit' => Pages\EditConfiguracion::route('/{record}/edit'),
        ];
    }
}
