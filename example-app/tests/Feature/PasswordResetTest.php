<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create([
            'name' => 'admin',
            'display_name' => 'Administrateur',
            'permissions' => ['*']
        ]);
        
        Role::create([
            'name' => 'recruiter',
            'display_name' => 'Recruteur',
            'permissions' => ['view_candidates', 'manage_candidates']
        ]);
    }

    public function test_password_reset_link_can_be_requested()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'recruiter')->first()->id
        ]);

        $response = $this->postJson('/api/auth/password/email', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
                ]);
    }

    public function test_password_reset_link_fails_for_invalid_email()
    {
        $response = $this->postJson('/api/auth/password/email', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Erreur de validation'
                ]);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
            'role_id' => Role::where('name', 'recruiter')->first()->id
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Votre mot de passe a été réinitialisé avec succès.'
                ]);

        // Verify password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_reset_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'recruiter')->first()->id
        ]);

        $response = $this->postJson('/api/auth/password/reset', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false
                ]);
    }

    public function test_token_verification_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'recruiter')->first()->id
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/password/verify-token', [
            'token' => $token,
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Token valide'
                ]);
    }

    public function test_invalid_token_verification_fails()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'recruiter')->first()->id
        ]);

        $response = $this->postJson('/api/auth/password/verify-token', [
            'token' => 'invalid-token',
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ]);
    }
}
