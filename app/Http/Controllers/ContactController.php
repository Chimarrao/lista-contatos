<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Rules\ValidCpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Lista os contatos.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $paginacao = $request->input('qtd') ?? 10;
        $search = $request->input('search');
        $orderBy = $request->input('orderBy', 'name');
        $orderDirection = $request->input('orderDirection', 'asc');

        $query = Contact::where('user_id', Auth::id());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cpf', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $query->orderBy($orderBy, $orderDirection);

        $contacts = $query->paginate($paginacao);

        return response()->json([
            'success' => true,
            'message' => 'Contatos listados com sucesso.',
            'data' => $contacts,
        ], 200);
    }

    /**
     * Grava um novo contato.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'max:14', new ValidCpf, 'unique:contacts,cpf,NULL,id,user_id,' . Auth::id()],
            'phone' => 'required|string|max:20',
            'cep' => 'required|string|max:9',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:10',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contact = Contact::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'cpf' => $request->cpf,
            'phone' => $request->phone,
            'cep' => $request->cep,
            'street' => $request->street,
            'number' => $request->number,
            'complement' => $request->complement,
            'neighborhood' => $request->neighborhood,
            'city' => $request->city,
            'state' => $request->state,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contato criado com sucesso.',
            'data' => $contact,
        ], 201);
    }

    /**
     * Retorna um contato específico.
     * 
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $contact = Contact::where('user_id', Auth::id())->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contato não encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contato encontrado com sucesso.',
            'data' => $contact,
        ], 200);
    }

    /**
     * Atualiza um contato.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::where('user_id', Auth::id())->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contato não encontrado.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:contacts,cpf,' . $id . ',id,user_id,' . Auth::id(),
            'phone' => 'required|string|max:20',
            'cep' => 'required|string|max:9',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:10',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contact->update([
            'name' => $request->name,
            'cpf' => $request->cpf,
            'phone' => $request->phone,
            'cep' => $request->cep,
            'street' => $request->street,
            'number' => $request->number,
            'complement' => $request->complement,
            'neighborhood' => $request->neighborhood,
            'city' => $request->city,
            'state' => $request->state,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contato atualizado com sucesso.',
            'data' => $contact,
        ], 200);
    }

    /**
     * Deleta um contato.
     * 
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $contact = Contact::where('user_id', Auth::id())->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contato não encontrado.',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contato excluído com sucesso.',
        ], 200);
    }
}
