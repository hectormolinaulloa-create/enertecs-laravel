<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProyectoResource\Pages;
use App\Filament\Resources\ProyectoResource\RelationManagers;
use App\Models\Proyecto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProyectoResource extends Resource
{
    protected static ?string $model = Proyecto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->required(),
            Forms\Components\TextInput::make('cliente')->required(),
            Forms\Components\TextInput::make('categoria')->required(),
            Forms\Components\TextInput::make('año')->numeric()->required(),
            Forms\Components\TextInput::make('lat')->numeric()->nullable(),
            Forms\Components\TextInput::make('lng')->numeric()->nullable(),
            Forms\Components\Textarea::make('descripcion')->nullable(),
            Forms\Components\Toggle::make('activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nombre')->searchable(),
            Tables\Columns\TextColumn::make('cliente')->searchable(),
            Tables\Columns\TextColumn::make('categoria')->badge(),
            Tables\Columns\TextColumn::make('año')->sortable(),
            Tables\Columns\IconColumn::make('activo')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('categoria')
                ->options(fn() => Proyecto::distinct()->orderBy('categoria')->pluck('categoria', 'categoria')->toArray()),
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
            'index' => Pages\ListProyectos::route('/'),
            'create' => Pages\CreateProyecto::route('/create'),
            'edit' => Pages\EditProyecto::route('/{record}/edit'),
        ];
    }
}
