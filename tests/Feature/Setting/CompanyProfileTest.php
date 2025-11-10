<?php

use App\DTO\DocumentDTO;
use App\DTO\DocumentPreviewDTO;
use App\Models\Accounting\Invoice;
use App\Models\Setting\CompanyProfile;
use App\Models\Setting\DocumentDefault;

it('maintains a single default company profile per company', function () {
    $company = $this->testCompany;
    $initialDefault = $company->profile;

    $newProfile = CompanyProfile::factory()
        ->forCompany($company)
        ->withAddress()
        ->create([
            'name' => 'Branch West',
            'is_default' => true,
        ]);

    $company->refresh();

    expect($company->profiles()->where('is_default', true)->count())->toBe(1)
        ->and($company->profile->getKey())->toBe($newProfile->getKey())
        ->and($initialDefault->refresh()->is_default)->toBeFalse();
});

it('uses the selected company profile on invoices', function () {
    $company = $this->testCompany;

    $this->withOfferings();

    $secondaryProfile = CompanyProfile::factory()
        ->forCompany($company)
        ->withAddress()
        ->create([
            'name' => 'North Branch',
        ]);

    $invoice = Invoice::factory()->create([
        'company_id' => $company->id,
        'company_profile_id' => $secondaryProfile->id,
    ]);

    $dto = DocumentDTO::fromModel($invoice->fresh());

    expect($dto->company->city)->toBe($secondaryProfile->address->city)
        ->and($dto->company->country)->toBe($secondaryProfile->address->country?->name ?? '');
});

it('previews document defaults with the assigned company profile', function () {
    $company = $this->testCompany;

    $profile = CompanyProfile::factory()
        ->forCompany($company)
        ->withAddress()
        ->create([
            'name' => 'South Branch',
        ]);

    /** @var DocumentDefault $settings */
    $settings = $company->defaultInvoice;
    $settings->update([
        'company_profile_id' => $profile->id,
    ]);

    $preview = DocumentPreviewDTO::fromSettings($settings->fresh());

    expect($preview->company->city)->toBe($profile->address->city);
});
