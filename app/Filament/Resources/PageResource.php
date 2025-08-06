<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Page Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9-]+$/')
                            ->helperText('URL slug for the page. Only lowercase letters, numbers, and hyphens.'),

                        Forms\Components\Select::make('template')
                            ->options(\App\Models\Page::getAvailableTemplates())
                            ->default('default')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->default(now())
                            ->required()
                            ->helperText('Page will be visible after this date/time'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO & Meta')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(60)
                            ->helperText('Leave empty to use page title'),

                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Page description for search engines'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Builder::make('content')
                            ->blocks([
                                Forms\Components\Builder\Block::make('hero')
                                    ->label('Hero Section')
                                    ->icon('heroicon-o-star')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\Textarea::make('subtitle')
                                            ->rows(2),
                                        Forms\Components\TextInput::make('button_text')
                                            ->placeholder('Get Started'),
                                        Forms\Components\TextInput::make('button_url')
                                            ->url(),
                                        Forms\Components\FileUpload::make('background_image')
                                            ->label('Background Image')
                                            ->image()
                                            ->directory('pages/hero-images')
                                            ->disk('public')
                                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif'])
                                            ->maxSize(5120)
                                            ->helperText('Upload a background image (PNG, JPG, WebP, GIF). Max 5MB.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Builder\Block::make('text')
                                    ->label('Rich Text')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('content')
                                            ->required()
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),
                                    ]),

                                Forms\Components\Builder\Block::make('features')
                                    ->label('Features Grid')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->placeholder('Our Features'),
                                        Forms\Components\Textarea::make('subtitle'),
                                        Forms\Components\Repeater::make('features')
                                            ->schema([
                                                Forms\Components\TextInput::make('title')->required(),
                                                Forms\Components\Textarea::make('description'),
                                                Forms\Components\TextInput::make('icon'),
                                            ])
                                            ->columns(3),
                                    ]),

                                Forms\Components\Builder\Block::make('cta')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->required(),
                                        Forms\Components\Textarea::make('subtitle'),
                                        Forms\Components\TextInput::make('button_text'),
                                        Forms\Components\TextInput::make('button_url'),
                                    ]),
                            ])
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('template')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'home' => 'success',
                        'pricing' => 'warning',
                        'about' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                    }),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('template')
                    ->options(\App\Models\Page::getAvailableTemplates()),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (\App\Models\Page $record): string => url($record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (\App\Models\Page $record): bool => $record->status === 'published'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
