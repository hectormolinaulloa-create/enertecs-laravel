<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicioResource\Pages;
use App\Filament\Resources\ServicioResource\RelationManagers;
use App\Models\Servicio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServicioResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('descripcion')->required(),
            Forms\Components\TextInput::make('icono')->default('zap'),
            Forms\Components\FileUpload::make('imagen')->image()->directory('servicios'),
            Forms\Components\Toggle::make('activo')->default(true),
            Forms\Components\TextInput::make('orden')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('orden')->sortable(),
            Tables\Columns\TextColumn::make('nombre')->searchable(),
            Tables\Columns\TextColumn::make('slug'),
            Tables\Columns\IconColumn::make('activo')->boolean(),
        ])->defaultSort('orden')->reorderable('orden');
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
            'index' => Pages\ListServicios::route('/'),
            'create' => Pages\CreateServicio::route('/create'),
            'edit' => Pages\EditServicio::route('/{record}/edit'),
        ];
    }
}
