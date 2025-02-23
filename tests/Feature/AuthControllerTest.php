<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de registro de usuário com sucesso.
     */
    public function test_register_success()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário registrado com sucesso.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
        ]);
    }

    /**
     * Teste de registro de usuário com falha (validação).
     */
    public function test_register_validation_error()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'email-invalido',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Erro de validação.',
            ]);
    }

    /**
     * Teste de login com sucesso.
     */
    public function test_login_success()
    {
        $user = User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'senha123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login realizado com sucesso.',
            ])
            ->assertJsonStructure(['access_token']);
    }

    /**
     * Teste de login com credenciais incorretas.
     */
    public function test_login_failure()
    {
        $user = User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciais incorretas.',
            ]);
    }

    /**
     * Teste de logout com sucesso.
     */
    public function test_logout_success()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso.',
            ]);
    }

    /**
     * Teste de obtenção do usuário autenticado.
     */
    public function test_me_success()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário autenticado.',
            ])
            ->assertJsonPath('data.email', $user->email);
    }

    /**
     * Teste de envio de link de recuperação de senha com sucesso.
     */
    public function test_send_reset_link_email_success()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link de recuperação de senha enviado com sucesso.',
            ]);
    }

    /**
     * Teste de envio de link de recuperação de senha com e-mail inválido.
     */
    public function test_send_reset_link_email_failure()
    {
        $response = $this->postJson('/api/password/reset', [
            'email' => 'email-invalido@example.com',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Erro ao enviar o link de recuperação de senha.',
            ]);
    }

    /**
     * Teste de exclusão de conta com sucesso.
     */
    public function test_delete_account_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/delete-account', [
            'email' => $user->email,
            'password' => 'senha123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Conta deletada com sucesso.',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
        ]);
    }

    /**
     * Teste de exclusão de conta com credenciais incorretas.
     */
    public function test_delete_account_failure()
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/delete-account', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciais incorretas. A conta não foi deletada.',
            ]);
    }
}