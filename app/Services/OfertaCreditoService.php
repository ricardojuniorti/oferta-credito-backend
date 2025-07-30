<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Oferta;

class OfertaCreditoService
{
    private string $urlCredito = 'https://dev.gosat.org/api/v1/simulacao/credito';
    private string $urlOferta  = 'https://dev.gosat.org/api/v1/simulacao/oferta';

    public function obterOfertasOrdenadas(string $cpf): array
    {
        $instituicoesResponse = Http::post($this->urlCredito, ['cpf' => $cpf]);

        if (!$instituicoesResponse->ok()) {
            throw new \Exception('Erro ao consultar instituições.');
        }

        $instituicoes = $instituicoesResponse->json('instituicoes');
        $ofertas = [];

        $numeroOferta = (Oferta::max('numero_oferta') ?? 0) + 1;

        foreach ($instituicoes as $instituicao) {
            foreach ($instituicao['modalidades'] as $modalidade) {
                $dadosOferta = [
                    'cpf' => $cpf,
                    'instituicao_id' => $instituicao['id'],
                    'codModalidade' => $modalidade['cod'],
                ];

                $simulacao = Http::post($this->urlOferta, $dadosOferta);

                if ($simulacao->ok()) {
                    $dados = $simulacao->json();

                    $valor = $dados['valorMax'];
                    $parcelas = $dados['QntParcelaMax'];
                    $juros = $dados['jurosMes'];

                    $valorAPagar = $valor * pow(1 + $juros, $parcelas);

                    Oferta::create([
                        'cpf'                   => $cpf,
                        'instituicao_financeira' => $instituicao['nome'],
                        'modalidade_credito'     => $modalidade['nome'],
                        'valor_solicitado'       => $valor,
                        'qnt_parcelas'           => $parcelas,
                        'taxa_juros'             => $juros,
                        'valor_a_pagar'          => round($valorAPagar, 2),
                        'data_oferta' => now(),
                        'numero_oferta'          => $numeroOferta,
                    ]);

                    // Prepara o array pra exibir na resposta
                    $ofertas[] = [
                        'instituicaoFinanceira' => $instituicao['nome'],
                        'modalidadeCredito'     => $modalidade['nome'],
                        'valorSolicitado'       => $valor,
                        'qntParcelas'           => $parcelas,
                        'taxaJuros'             => $juros,
                        'valorAPagar'           => round($valorAPagar, 2),
                    ];
                }
            }
        }

        // Ordena do menor valor a pagar para o maior
        usort($ofertas, fn ($a, $b) => $a['valorAPagar'] <=> $b['valorAPagar']);

        // Retorna até 3 ofertas
        return array_slice($ofertas, 0, 3);
    }
    
    
}
