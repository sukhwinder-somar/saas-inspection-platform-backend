<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Information')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->label('Organization')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('stripe_subscription_id')
                            ->label('Stripe Subscription ID')
                            ->maxLength(255),

                        Forms\Components\Select::make('plan')
                            ->options([
                                'basic' => 'Basic',
                                'pro' => 'Pro',
                                'enterprise' => 'Enterprise',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'canceled' => 'Canceled',
                                'incomplete' => 'Incomplete',
                                'incomplete_expired' => 'Incomplete Expired',
                                'past_due' => 'Past Due',
                                'trialing' => 'Trialing',
                                'unpaid' => 'Unpaid',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Trial Ends At'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Ends At'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Subscription Limits')
                    ->schema([
                        Forms\Components\TextInput::make('limits->users')
                            ->label('Max Users')
                            ->numeric()
                            ->default(10),

                        Forms\Components\TextInput::make('limits->assets')
                            ->label('Max Assets')
                            ->numeric()
                            ->default(50),

                        Forms\Components\TextInput::make('limits->inspections_per_month')
                            ->label('Max Inspections per Month')
                            ->numeric()
                            ->default(100),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organization')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->colors([
                        'gray' => 'basic',
                        'primary' => 'pro',
                        'success' => 'enterprise',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'trialing',
                        'danger' => ['canceled', 'past_due', 'unpaid'],
                        'gray' => ['incomplete', 'incomplete_expired'],
                    ]),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->options([
                        'basic' => 'Basic',
                        'pro' => 'Pro',
                        'enterprise' => 'Enterprise',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'canceled' => 'Canceled',
                        'trialing' => 'Trialing',
                        'past_due' => 'Past Due',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
