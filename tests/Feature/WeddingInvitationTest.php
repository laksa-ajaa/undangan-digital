<?php

use App\Models\GuestInvitation;
use App\Models\GuestWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the invitation landing page', function () {
    $response = $this->get(route('invitation.index'));

    $response->assertOk();
    $response->assertSee('Wafii & Tasya');
});

it('stores guest invitations for sharing', function () {
    $response = $this->post(route('invitation.store'), [
        'guest_name' => 'Bapak Ahmad',
        'share_message' => 'Kami mengundang Bapak Ahmad untuk hadir di hari bahagia kami.',
    ]);

    $response->assertRedirect(route('invitation.index'));

    $this->assertDatabaseCount('guest_invitations', 1);
    $this->assertDatabaseHas('guest_invitations', [
        'guest_name' => 'Bapak Ahmad',
    ]);
});

it('stores guest wishes', function () {
    $invitation = GuestInvitation::create([
        'guest_name' => 'Bapak Ahmad',
        'share_code' => '01J1ZK8G2B5H4J0A9M8W7X6Y5Z',
        'share_message' => 'Kami mengundang Bapak Ahmad untuk hadir di hari bahagia kami.',
        'share_link' => 'https://example.com/undangan/01J1ZK8G2B5H4J0A9M8W7X6Y5Z',
    ]);

    $response = $this->post(route('wishes.store'), [
        'guest_invitation_code' => $invitation->share_code,
        'guest_name' => 'Siti',
        'attendance_status' => 'hadir',
        'message' => 'Selamat menempuh hidup baru.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseCount('guest_wishes', 1);
    $this->assertDatabaseHas('guest_wishes', [
        'guest_name' => 'Siti',
        'attendance_status' => 'hadir',
    ]);
});

it('allows an admin to log in and open the dashboard', function () {
    $user = User::factory()->create([
        'email' => 'admin@wedding.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'admin@wedding.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);

    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertOk();
});
