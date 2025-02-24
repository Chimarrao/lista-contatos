<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CepController extends Controller
{
    /**
     * Busca informações de um CEP usando a API ViaCEP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookup(Request $request)
    {
        $cep = $request->input('cep');

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        return response()->json($response->json());
    }
}
