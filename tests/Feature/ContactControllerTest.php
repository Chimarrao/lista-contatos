<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * Teste de criação de contato com CPF inválido.
     */
    public function test_store_with_invalid_cpf()
    {
        $contactData = [
            'name' => 'João Silva',
            'cpf' => '123.456.789-00',
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

        $response->assertStatus(422)->assertJsonValidationErrors(['cpf']);
    }

    /**
     * Teste de criação de contato com CPF duplicado para o mesmo usuário.
     */
    public function test_store_with_duplicate_cpf_for_same_user()
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

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/contacts', $contactData);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/contacts', $contactData);

        $response->assertStatus(422)->assertJsonValidationErrors(['cpf']);
    }

    /**
     * Teste de criação de contato com campos obrigatórios ausentes.
     */
    public function test_store_with_missing_required_fields()
    {
        $contactData = [
            'name' => '', // Campo obrigatório vazio
            'cpf' => '123.456.789-09',
            'phone' => '', // Campo obrigatório vazio
            'cep' => '01001-000',
            'street' => '', // Campo obrigatório vazio
            'number' => '', // Campo obrigatório vazio
            'neighborhood' => '', // Campo obrigatório vazio
            'city' => '', // Campo obrigatório vazio
            'state' => '', // Campo obrigatório vazio
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/contacts', $contactData);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'phone', 'street', 'number', 'neighborhood', 'city', 'state']);
    }

    /**
     * Teste de criação de contato sem o campo complemento.
     */
    public function test_store_without_complement()
    {
        $contactData = [
            'name' => 'João Silva',
            'cpf' => '123.456.789-09',
            'phone' => '(11) 99999-9999',
            'cep' => '01001-000',
            'street' => 'Rua Exemplo',
            'number' => '123',
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
            ]);
    }

    /**
     * Teste de atualização de contato com CPF duplicado para o mesmo usuário.
     */
    public function test_update_with_duplicate_cpf_for_same_user()
    {
        $contact1 = Contact::factory()->create(['user_id' => $this->user->id, 'cpf' => '123.456.789-09']);
        $contact2 = Contact::factory()->create(['user_id' => $this->user->id, 'cpf' => '987.654.321-00']);

        $updatedData = [
            'name' => 'João Silva Atualizado',
            'cpf' => '123.456.789-09', // CPF do primeiro contato
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
        ])->putJson('/api/contacts/' . $contact2->id, $updatedData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
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

    /**
     * Teste de listagem de contatos com filtro por nome.
     */
    public function test_index_with_name_filter()
    {
        Contact::factory()->create(['user_id' => $this->user->id, 'name' => 'João Silva']);
        Contact::factory()->create(['user_id' => $this->user->id, 'name' => 'Maria Santos']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts?search=João');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contatos listados com sucesso.',
            ])
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'João Silva');
    }

    /**
     * Teste de listagem de contatos com filtro por CPF.
     */
    public function test_index_with_cpf_filter()
    {
        Contact::factory()->create(['user_id' => $this->user->id, 'cpf' => '123.456.789-09']);
        Contact::factory()->create(['user_id' => $this->user->id, 'cpf' => '987.654.321-00']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts?search=123.456.789-09');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contatos listados com sucesso.',
            ])
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.cpf', '123.456.789-09');
    }

    /**
     * Teste de listagem de contatos com ordenação crescente.
     */
    public function test_index_with_ascending_order()
    {
        Contact::factory()->create(['user_id' => $this->user->id, 'name' => 'Maria Santos']);
        Contact::factory()->create(['user_id' => $this->user->id, 'name' => 'João Silva']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts?orderBy=name&orderDirection=asc');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contatos listados com sucesso.',
            ])
            ->assertJsonPath('data.data.0.name', 'João Silva')
            ->assertJsonPath('data.data.1.name', 'Maria Santos');
    }

    /**
     * Teste de listagem de contatos com ordenação decrescente.
     */
    public function test_index_with_descending_order()
    {
        Contact::factory()->create(['user_id' => $this->user->id, 'name' => 'Maria Santos']);
        Contact::factory()->create(['user_id' => $this->user->id, 'name' => 'João Silva']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts?orderBy=name&orderDirection=desc');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contatos listados com sucesso.',
            ])
            ->assertJsonPath('data.data.0.name', 'Maria Santos')
            ->assertJsonPath('data.data.1.name', 'João Silva');
    }

    /**
     * Teste de listagem de contatos com paginação.
     */
    public function test_index_with_pagination()
    {
        Contact::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/contacts?qtd=10');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contatos listados com sucesso.',
            ])
            ->assertJsonCount(10, 'data.data') 
            ->assertJsonPath('data.total', 15); 
    }
}
