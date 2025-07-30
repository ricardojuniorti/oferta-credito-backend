<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfertaCreditoController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/gerar-ofertas', [OfertaCreditoController::class, 'consultar']);
Route::get('/historico-ofertas', [OfertaCreditoController::class, 'buscarOfertasSalvas']);
Route::delete('/excluir-oferta/{id}', [OfertaCreditoController::class, 'excluirOferta']);

