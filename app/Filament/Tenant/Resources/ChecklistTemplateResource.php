<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ChecklistTemplateResource\Pages;
use App\Models\ChecklistTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChecklistTemplateResource extends Resource
{
    protected static ?string $model = ChecklistTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inspection Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('asset_types')
                            ->multiple()
                            ->options([
                                'vehicle' => 'Vehicle',
                                'machinery' => 'Machinery',
                                'equipment' => 'Equipment',
                                'tool' => 'Tool',
                                'building' => 'Building',
                            ])
                            ->required()
                            ->helperText('Which asset types can use this checklist template'),

                        Forms\Components\Toggle::make('active')
                            ->default(true),

                        Forms\Components\TextInput::make('version')
                            ->numeric()
                            ->default(1)
                            ->disabled()
                            ->helperText('Version is automatically managed'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Checklist Sections')
                    ->schema([
                        Forms\Components\Repeater::make('sections')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('Section name (e.g., Engine Check)'),

                                Forms\Components\Repeater::make('questions')
                                    ->schema([
                                        Forms\Components\TextInput::make('question')
                                            ->required()
                                            ->placeholder('Enter your question'),

                                        Forms\Components\Select::make('type')
                                            ->required()
                                            ->options([
                                                'radio' => 'Pass/Fail',
                                                'checkbox' => 'Checkbox',
                                                'text' => 'Text Input',
                                                'number' => 'Number',
                                                'date' => 'Date',
                                                'photo' => 'Photo Upload',
                                                'signature' => 'Signature',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, $state) {
                                                // Set default options for radio type
                                                if ($state === 'radio') {
                                                    $set('options', ['Pass', 'Fail']);
                                                } elseif ($state === 'checkbox') {
                                                    $set('options', ['Yes', 'No', 'N/A']);
                                                } else {
                                                    $set('options', null);
                                                }
                                            }),

                                        Forms\Components\TagsInput::make('options')
                                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['radio', 'checkbox']))
                                            ->helperText('Enter the available options'),

                                        Forms\Components\Toggle::make('required')
                                            ->default(true),

                                        Forms\Components\Textarea::make('notification_message')
                                            ->placeholder('Custom notification message for this question')
                                            ->rows(2),

                                        Forms\Components\KeyValue::make('conditional_logic')
                                            ->helperText('Advanced: Define conditional logic')
                                            ->collapsed(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->addActionLabel('Add Question')
                                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel('Add Section')
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('asset_types')
                    ->badge()
                    ->separator(', ')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('version')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sections')
                    ->formatStateUsing(fn ($state) => count($state ?? []) . ' sections')
                    ->label('Sections'),

                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_types')
                    ->options([
                        'vehicle' => 'Vehicle',
                        'machinery' => 'Machinery',
                        'equipment' => 'Equipment',
                        'tool' => 'Tool',
                        'building' => 'Building',
                    ]),
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (ChecklistTemplate $record) {
                        $newTemplate = $record->replicate();
                        $newTemplate->name = $record->name . ' (Copy)';
                        $newTemplate->version = 1;
                        $newTemplate->created_by = auth()->id();
                        $newTemplate->save();

                        // Copy questions
                        foreach ($record->questions as $question) {
                            $newQuestion = $question->replicate();
                            $newQuestion->template_id = $newTemplate->id;
                            $newQuestion->save();
                        }

                        return redirect(static::getUrl('edit', ['record' => $newTemplate]));
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListChecklistTemplates::route('/'),
            'create' => Pages\CreateChecklistTemplate::route('/create'),
            'view' => Pages\ViewChecklistTemplate::route('/{record}'),
            'edit' => Pages\EditChecklistTemplate::route('/{record}/edit'),
        ];
    }
}
