<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asset Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('asset_id')
                            ->label('Asset ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'vehicle' => 'Vehicle',
                                'machinery' => 'Machinery',
                                'equipment' => 'Equipment',
                                'tool' => 'Tool',
                                'building' => 'Building',
                            ]),

                        Forms\Components\TextInput::make('make')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dates & Maintenance')
                    ->schema([
                        Forms\Components\DatePicker::make('registration_expiry')
                            ->helperText('When the registration expires'),

                        Forms\Components\DatePicker::make('next_service_due')
                            ->helperText('When the next service is due'),

                        Forms\Components\DatePicker::make('insurance_renewal')
                            ->helperText('When insurance needs renewal'),

                        Forms\Components\DatePicker::make('last_inspection_date')
                            ->helperText('Date of last inspection'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Assignment & Status')
                    ->schema([
                        Forms\Components\Select::make('assignedUsers')
                            ->relationship('assignedUsers', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Forms\Components\Toggle::make('active')
                            ->default(true),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Documents')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('assets/documents')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Custom Fields')
                    ->schema([
                        Forms\Components\KeyValue::make('custom_fields')
                            ->helperText('Additional custom fields for this asset')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_id')
                    ->label('Asset ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vehicle' => 'success',
                        'machinery' => 'warning',
                        'equipment' => 'info',
                        'tool' => 'gray',
                        'building' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('make')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_service_due')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('assignedUsers.name')
                    ->label('Assigned To')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'vehicle' => 'Vehicle',
                        'machinery' => 'Machinery',
                        'equipment' => 'Equipment',
                        'tool' => 'Tool',
                        'building' => 'Building',
                    ]),
                Tables\Filters\TernaryFilter::make('active'),
                Tables\Filters\Filter::make('overdue_service')
                    ->query(fn (Builder $query): Builder => $query->whereDate('next_service_due', '<', now())),
            ])
            ->actions([
                Tables\Actions\Action::make('qr_code')
                    ->icon('heroicon-o-qr-code')
                    ->modalContent(fn (Asset $record) => view('filament.modals.qr-code', [
                        'qrCode' => self::generateQrCode($record)
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function generateQrCode(Asset $asset): string
    {
        $qrCode = QrCode::create(route('api.assets.show', $asset->qr_code))
            ->setSize(200)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
