<?php

use App\Livewire\ReportPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the report page', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get(route('report'))
        ->assertOk()
        ->assertSeeLivewire(ReportPage::class)
        ->assertSeeText('Usage insights');
});
