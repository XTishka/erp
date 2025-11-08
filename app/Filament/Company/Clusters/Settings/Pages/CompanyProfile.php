<?php

namespace App\Filament\Company\Clusters\Settings\Pages;

use App\Enums\Common\AddressType;
use App\Enums\Setting\EntityType;
use App\Filament\Company\Clusters\Settings;
use App\Filament\Forms\Components\AddressFields;
use App\Filament\Forms\Components\Banner;
use App\Models\Setting\CompanyProfile as CompanyProfileModel;
use App\Utilities\Localization\Timezone;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Exceptions\Halt;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use function Filament\authorize;

/**
 * @property Form $form
 */
class CompanyProfile extends Page
{
    use InteractsWithFormActions;

    protected static ?string $title = 'Company Profile';

    protected static string $view = 'filament.company.pages.setting.company-profile';

    protected static ?string $cluster = Settings::class;

    public ?array $data = [];

    #[Url(as: 'profile')]
    public ?int $profileId = null;

    protected bool $suppressProfileChange = false;

    #[Locked]
    public ?CompanyProfileModel $record = null;

    public function getTitle(): string | Htmlable
    {
        return translate(static::$title);
    }

    public static function getNavigationLabel(): string
    {
        return translate(static::$title);
    }

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::ScreenTwoExtraLarge;
    }

    public function mount(): void
    {
        $this->loadRecord($this->profileId);

        abort_unless(static::canView($this->record), 404);
    }

    public function updatedProfileId($profileId): void
    {
        if ($this->suppressProfileChange) {
            return;
        }

        $profileId = filled($profileId) ? (int) $profileId : null;

        if ($profileId === $this->record?->getKey()) {
            return;
        }

        $this->loadRecord($profileId);
    }

    protected function loadRecord(?int $profileId = null): void
    {
        $this->record = $this->resolveRecord($profileId);

        $this->ensureAddressExists($this->record);

        $this->suppressProfileChange = true;
        $this->profileId = $this->record->getKey();
        $this->suppressProfileChange = false;

        $this->fillForm();
    }

    protected function resolveRecord(?int $profileId = null): CompanyProfileModel
    {
        $baseQuery = CompanyProfileModel::query();

        if ($profileId) {
            $record = (clone $baseQuery)->find($profileId);

            if ($record) {
                return $record;
            }
        }

        $record = (clone $baseQuery)->where('is_default', true)->first()
            ?? (clone $baseQuery)->first();

        if ($record) {
            return $record;
        }

        return CompanyProfileModel::create([
            'company_id' => auth()->user()->current_company_id,
            'name' => 'Default',
        ]);
    }

    protected function ensureAddressExists(CompanyProfileModel $record): void
    {
        if ($record->address()->exists()) {
            return;
        }

        $record->address()->create([
            'type' => AddressType::General,
        ]);
    }

    protected function copyAddressData(?CompanyProfileModel $source): array
    {
        if (! $source?->address) {
            return [
                'type' => AddressType::General,
            ];
        }

        $address = $source->address;

        return [
            ...Arr::only($address->toArray(), [
                'recipient',
                'phone',
                'address_line_1',
                'address_line_2',
                'city',
                'state_id',
                'postal_code',
                'country_code',
                'notes',
            ]),
            'type' => $address->type ?? AddressType::General,
        ];
    }

    public function getProfileOptionsProperty(): array
    {
        return CompanyProfileModel::query()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (CompanyProfileModel $profile) => [
                $profile->getKey() => $profile->name . ($profile->is_default ? ' (Default)' : ''),
            ])
            ->all();
    }

    public function fillForm(): void
    {
        $data = $this->record->attributesToArray();

        $this->form->fill($data);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->handleRecordUpdate($this->record, $data);
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()->send();
    }

    protected function updateTimezone(string $countryCode): void
    {
        $model = \App\Models\Setting\Localization::firstOrFail();

        $timezones = Timezone::getTimezonesForCountry($countryCode);

        if (! empty($timezones)) {
            $model->update([
                'timezone' => $timezones[0],
            ]);
        }
    }

    protected function getTimezoneChangeNotification(): Notification
    {
        return Notification::make()
            ->info()
            ->title('Timezone update required')
            ->body('You have changed your country or state. Please update your timezone to ensure accurate date and time information.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('updateTimezone')
                    ->label('Update timezone')
                    ->url(Localization::getUrl()),
            ])
            ->persistent()
            ->send();
    }

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getIdentificationSection(),
                $this->getNeedsAddressCompletionAlert(),
                $this->getLocationDetailsSection(),
                $this->getLegalAndComplianceSection(),
            ])
            ->model($this->record)
            ->statePath('data')
            ->operation('edit');
    }

    protected function getIdentificationSection(): Component
    {
        return Section::make('Identification')
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Profile name')
                            ->maxLength(100)
                            ->softRequired()
                            ->unique(
                                table: CompanyProfileModel::class,
                                column: 'name',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('company_id', auth()->user()->current_company_id),
                            ),
                        TextInput::make('email')
                            ->email()
                            ->localizeLabel()
                            ->maxLength(255)
                            ->softRequired(),
                        TextInput::make('phone_number')
                            ->tel()
                            ->localizeLabel(),
                    ])->columns(1),
                FileUpload::make('logo')
                    ->openable()
                    ->maxSize(2048)
                    ->localizeLabel()
                    ->visibility('public')
                    ->disk('public')
                    ->directory('logos/company')
                    ->imageResizeMode('contain')
                    ->imageCropAspectRatio('1:1')
                    ->panelAspectRatio('1:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('center bottom')
                    ->uploadButtonPosition('center bottom')
                    ->uploadProgressIndicatorPosition('center bottom')
                    ->getUploadedFileNameForStorageUsing(
                        static fn (TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                            ->prepend(Auth::user()->currentCompany->id . '_'),
                    )
                    ->extraAttributes(['class' => 'w-32 h-32'])
                    ->acceptedFileTypes(['image/png', 'image/jpeg']),
            ])->columns();
    }

    protected function getNeedsAddressCompletionAlert(): Component
    {
        return Banner::make('needsAddressCompletion')
            ->warning()
            ->title('Address information incomplete')
            ->description('Please complete the required address information for proper business operations.')
            ->visible(fn (CompanyProfileModel $record) => (bool) $record->address?->isIncomplete())
            ->columnSpanFull();
    }

    protected function getLocationDetailsSection(): Component
    {
        return Section::make('Address Information')
            ->relationship('address')
            ->schema([
                Hidden::make('type')
                    ->default('general'),
                AddressFields::make()
                    ->required()
                    ->softRequired()
                    ->disabledCountry(is_demo_environment()),
            ])
            ->columns(2);
    }

    protected function getLegalAndComplianceSection(): Component
    {
        return Section::make('Legal & Compliance')
            ->schema([
                Select::make('entity_type')
                    ->localizeLabel()
                    ->options(EntityType::class)
                    ->softRequired(),
                TextInput::make('tax_id')
                    ->localizeLabel('Tax ID')
                    ->maxLength(50),
            ])->columns();
    }

    protected function handleRecordUpdate(CompanyProfileModel $record, array $data): CompanyProfileModel
    {
        $record->fill($data);

        $keysToWatch = [
            'logo',
        ];

        if ($record->isDirty($keysToWatch)) {
            $this->dispatch('companyProfileUpdated');
        }

        $record->save();

        return $record;
    }

    protected function createProfileFromAction(array $data): void
    {
        $source = ! empty($data['copy_from_id'])
            ? CompanyProfileModel::query()->find($data['copy_from_id'])
            : null;

        $profile = CompanyProfileModel::create([
            'company_id' => auth()->user()->current_company_id,
            'name' => $data['name'],
            'email' => $source?->email,
            'phone_number' => $source?->phone_number,
            'tax_id' => $source?->tax_id,
            'entity_type' => $source?->entity_type,
            'logo' => $source?->logo,
        ]);

        if ($source?->address) {
            $profile->address()->create($this->copyAddressData($source));
        } else {
            $this->ensureAddressExists($profile);
        }

        $this->loadRecord($profile->getKey());

        Notification::make()
            ->success()
            ->title('Profile created')
            ->send();
    }

    protected function setCurrentProfileAsDefault(): void
    {
        if (! $this->record) {
            return;
        }

        $this->record->is_default = true;
        $this->record->save();

        $this->loadRecord($this->record->getKey());

        Notification::make()
            ->success()
            ->title('Default profile updated')
            ->send();
    }

    protected function deleteCurrentProfile(): void
    {
        if (! $this->record) {
            return;
        }

        $deletedDefault = $this->record->is_default;

        $this->record->delete();

        if ($deletedDefault) {
            $replacement = CompanyProfileModel::query()->first();

            if ($replacement && ! $replacement->is_default) {
                $replacement->is_default = true;
                $replacement->save();
            }
        }

        $this->loadRecord();

        Notification::make()
            ->success()
            ->title('Profile deleted')
            ->send();
    }

    protected function canDeleteCurrentProfile(): bool
    {
        return CompanyProfileModel::query()->count() > 1;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createProfile')
                ->label('New profile')
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('name')
                        ->label('Profile name')
                        ->required()
                        ->maxLength(100),
                    Select::make('copy_from_id')
                        ->label('Copy from')
                        ->options($this->profileOptions)
                        ->searchable()
                        ->placeholder('Start from scratch')
                        ->native(false),
                ])
                ->action(fn (array $data) => $this->createProfileFromAction($data)),
            Action::make('setDefaultProfile')
                ->label('Set as default')
                ->icon('heroicon-m-star')
                ->visible(fn () => $this->record?->is_default === false)
                ->action(fn () => $this->setCurrentProfileAsDefault()),
            Action::make('deleteProfile')
                ->label('Delete profile')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->canDeleteCurrentProfile())
                ->action(fn () => $this->deleteCurrentProfile()),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public static function canView(Model $record): bool
    {
        try {
            return authorize('update', $record)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}
