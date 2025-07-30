<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\OfertaCreditoService;
use Illuminate\Http\Request;
use App\Models\Oferta;

class OfertaCreditoController extends Controller
{
    public function consultar(Request $request, OfertaCreditoService $service)
    {
        $cpf = $request->input('cpf');

        if (!$cpf) {
            return response()->json(['error' => 'CPF é obrigatório.'], 400);
        }

        try {
            $ofertas = $service->obterOfertasOrdenadas($cpf);
            return response()->json($ofertas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar ofertas.', 'detalhe' => $e->getMessage()], 500);
        }
    }
    public function buscarOfertasSalvas()
    {
        $cpfs = Oferta::select('cpf')->distinct()->get();
        $resultados = [];

        foreach ($cpfs as $cpfItem) {
            $lotes = Oferta::where('cpf', $cpfItem->cpf)
                ->select('numero_oferta')
                ->distinct()
                ->orderBy('numero_oferta', 'asc')
                ->get();

            $ofertasPorLote = [];

            foreach ($lotes as $lote) {
                $ofertas = Oferta::where('cpf', $cpfItem->cpf)
                    ->where('numero_oferta', $lote->numero_oferta)
                    ->orderBy('valor_a_pagar', 'asc')
                    ->get([
                        'instituicao_financeira as instituicaoFinanceira',
                        'modalidade_credito as modalidadeCredito',
                        'valor_solicitado as valorSolicitado',
                        'qnt_parcelas as qntParcelas',
                        'taxa_juros as taxaJuros',
                        'valor_a_pagar as valorAPagar',
                        'data_oferta',
                    ])
                    ->map(function ($oferta) {
                        $oferta->dataOferta = Carbon::parse($oferta->data_oferta)->format('d/m/Y H:i');
                        unset($oferta->data_oferta);
                        return $oferta;
                    });

                $ofertasPorLote[] = [
                    'numero_oferta' => $lote->numero_oferta,
                    'ofertas' => $ofertas,
                ];
            }

            $resultados[] = [
                'cpf' => $cpfItem->cpf,
                'lotes' => $ofertasPorLote,
            ];
        }

        return response()->json($resultados);
    }

    public function excluirOferta($id)
    {
        $ofertas = Oferta::where('numero_oferta', $id)->get();

        if ($ofertas->isEmpty()) {
            return response()->json([
                'mensagem' => 'Nenhuma oferta encontrada com o número ' . $id . '.'
            ], 404);
        }

        $quantidade = $ofertas->count();

        Oferta::where('numero_oferta', $id)->delete();

        return response()->json([
            'mensagem' => "Todas as $quantidade oferta(s) com o número $id foram excluídas com sucesso."
        ]);
    }
   
}
