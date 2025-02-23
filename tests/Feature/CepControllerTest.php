<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CepControllerTest extends TestCase
{
    use RefreshDatabase;
 
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Teste de consulta de CEP com sucesso.
     */
    public function test_cep_lookup_success()
    {
        Http::fake([
            'https://viacep.com.br/ws/01001000/json/' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'complemento' => 'lado ímpar',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'ibge' => '3550308',
                'gia' => '1004',
                'ddd' => '11',
                'siafi' => '7107',
            ], 200),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cep', [
            'cep' => '01001000',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
            ]);
    }

    /**
     * Teste de consulta de CEP com erro.
     */
    public function test_cep_lookup_error()
    {
        Http::fake([
            'https://viacep.com.br/ws/00000000/json/' => Http::response([
                'erro' => true,
            ], 200),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cep', [
            'cep' => '00000000',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'erro' => true,
            ]);
    }
}
