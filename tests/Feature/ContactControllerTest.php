<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ContactControllerTest extends TestCase
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
     * Teste de listagem de contatos com autenticação.
     */
    public function test_index_with_authentication()
    {
        Contact::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contatos listados com sucesso.',
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Teste de criação de contato com autenticação.
     */
    public function test_store_with_authentication()
    {
        $contactData = [
            'name' => 'João Silva',
            'cpf' => '123.456.789-09',
            'phone' => '(11) 99999-9999',
            'cep' => '01001-000',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'complement' => 'Apto 456',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/contacts', $contactData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Contato criado com sucesso.',
            ])
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('contacts', [
            'name' => 'João Silva',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Teste de visualização de contato com autenticação.
     */
    public function test_show_with_authentication()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts/' . $contact->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contato encontrado com sucesso.',
            ])
            ->assertJsonPath('data.id', $contact->id);
    }

    /**
     * Teste de atualização de contato com autenticação.
     */
    public function test_update_with_authentication()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'name' => 'João Silva Atualizado',
            'cpf' => '123.456.789-09',
            'phone' => '(11) 99999-9999',
            'cep' => '01001-000',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'complement' => 'Apto 456',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/contacts/' . $contact->id, $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contato atualizado com sucesso.',
            ])
            ->assertJsonPath('data.name', 'João Silva Atualizado');
    }

    /**
     * Teste de exclusão de contato com autenticação.
     */
    public function test_destroy_with_authentication()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/contacts/' . $contact->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contato excluído com sucesso.',
            ]);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    /**
     * Teste de listagem de contatos sem autenticação.
     */
    public function test_index_without_authentication()
    {
        $response = $this->getJson('/api/contacts');

        $response->assertStatus(401);
    }

    /**
     * Teste de criação de contato sem autenticação.
     */
    public function test_store_without_authentication()
    {
        $contactData = [
            'name' => 'João Silva',
            'cpf' => '123.456.789-09',
            'phone' => '(11) 99999-9999',
            'cep' => '01001-000',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'complement' => 'Apto 456',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ];

        $response = $this->postJson('/api/contacts', $contactData);

        $response->assertStatus(401);
    }

    /**
     * Teste de visualização de contato sem autenticação.
     */
    public function test_show_without_authentication()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/contacts/' . $contact->id);

        $response->assertStatus(401);
    }

    /**
     * Teste de atualização de contato sem autenticação.
     */
    public function test_update_without_authentication()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'name' => 'João Silva Atualizado',
            'cpf' => '123.456.789-09',
            'phone' => '(11) 99999-9999',
            'cep' => '01001-000',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'complement' => 'Apto 456',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ];

        $response = $this->putJson('/api/contacts/' . $contact->id, $updatedData);

        $response->assertStatus(401);
    }

    /**
     * Teste de exclusão de contato sem autenticação.
     */
    public function test_destroy_without_authentication()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson('/api/contacts/' . $contact->id);

        $response->assertStatus(401);
    }
}