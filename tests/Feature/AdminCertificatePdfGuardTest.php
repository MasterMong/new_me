<?php

use App\Enums\UserRole;
use App\Livewire\Admin\CertificatesManager;
use App\Models\Certificate;
use App\Models\User;
use Livewire\Livewire;

test('a certificate with no pdf yet shows a regenerate action instead of a dead download link', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $cert = Certificate::factory()->create(['pdf_url' => null]);

    $this->actingAs($admin);

    Livewire::test(CertificatesManager::class)
        ->assertSee('regeneratePdf('.$cert->id.')', false)
        ->assertDontSee('href=""', false);
});

test('admin can regenerate a missing certificate pdf', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $cert = Certificate::factory()->create(['pdf_url' => null]);

    $this->actingAs($admin);

    Livewire::test(CertificatesManager::class)
        ->call('regeneratePdf', $cert->id);

    expect($cert->fresh()->pdf_url)->not->toBeNull();
});
