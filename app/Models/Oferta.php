<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $fillable = [
        'cpf',
        'instituicao_financeira',
        'modalidade_credito',
        'valor_solicitado',
        'qnt_parcelas',
        'taxa_juros',
        'valor_a_pagar',
        'numero_oferta',
        'data_oferta'
    ];
}

