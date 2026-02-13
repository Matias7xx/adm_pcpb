<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VeiculoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    // Validar parâmetros de entrada
    $request->validate([
      'search' => 'nullable|string|max:255',
      'status' => 'nullable|in:ativos,inativos,expirados,validos',
      'tipo' => 'nullable|in:pdf,excel',
      'sort' =>
        'nullable|in:titulo,data_publicacao,data_expiracao,downloads,ordem',
      'direction' => 'nullable|in:asc,desc',
    ]);

    $query = Veiculo::query();

    // Filtro de busca
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('titulo', 'ILIKE', "%{$search}%")->orWhere(
          'descricao',
          'ILIKE',
          "%{$search}%",
        );
      });
    }

    // Filtro de status
    if ($request->filled('status')) {
      if ($request->status === 'ativos') {
        $query->where('ativo', true);
      } elseif ($request->status === 'inativos') {
        $query->where('ativo', false);
      } elseif ($request->status === 'expirados') {
        $query->where('data_expiracao', '<', now());
      } elseif ($request->status === 'validos') {
        $query->validos();
      }
    }

    // Filtro de tipo
    if ($request->filled('tipo')) {
      $query->where('tipo_arquivo', $request->tipo);
    }

    // Ordenação
    $sortField = $request->get('sort', 'created_at');
    $sortDirection = $request->get('direction', 'desc');
    $query->orderBy($sortField, $sortDirection);

    $veiculos = $query->paginate(10)->withQueryString();

    return Inertia::render('Admin/Veiculo/Index', [
      'veiculos' => $veiculos,
      'filters' => $request->only(['search', 'status', 'tipo']),
      'can' => [
        'create' => auth()->user()->can('create', Veiculo::class),
        'edit' => auth()->user()->can('update', Veiculo::class),
        'delete' => auth()->user()->can('delete', Veiculo::class),
      ],
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return Inertia::render('Admin/Veiculo/Create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validator = Validator::make(
      $request->all(),
      [
        'titulo' => 'required|string|max:255',
        'descricao' => 'nullable|string|max:1000',
        'arquivo' => 'required|file|mimes:pdf,xlsx,xls|max:10240', // Máx 10MB
        'dias_exibicao' => 'required|integer|min:1|max:365',
        'data_publicacao' => 'nullable|date|after_or_equal:today',
        'ordem' => 'nullable|integer|min:0|max:9999',
        'ativo' => 'boolean',
      ],
      [
        'arquivo.required' => 'O arquivo é obrigatório.',
        'arquivo.mimes' => 'O arquivo deve ser PDF ou Excel (.xlsx, .xls).',
        'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
        'dias_exibicao.required' =>
          'Informe por quantos dias o documento ficará visível.',
        'dias_exibicao.min' =>
          'O documento deve ficar visível por pelo menos 1 dia.',
        'dias_exibicao.max' =>
          'O documento não pode ficar visível por mais de 365 dias.',
        'data_publicacao.after_or_equal' =>
          'A data de publicação não pode ser anterior a hoje.',
        'titulo.required' => 'O título é obrigatório.',
        'titulo.max' => 'O título não pode ter mais de 255 caracteres.',
        'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
      ],
    );

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
      // Upload do arquivo
      $file = $request->file('arquivo');
      $nomeOriginal = $file->getClientOriginalName();
      $extensao = $file->getClientOriginalExtension();
      $nomeArquivo =
        Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)) .
        '_' .
        time() .
        '.' .
        $extensao;

      // Determinar tipo de arquivo
      $tipoArquivo = $extensao === 'pdf' ? 'pdf' : 'excel';

      // Fazer upload para S3
      $caminhoArquivo = 'veiculos/' . $nomeArquivo;
      Storage::disk('s3')->put(
        $caminhoArquivo,
        file_get_contents($file),
        'public',
      );

      // Obter tamanho do arquivo em KB
      $tamanhoKb = round($file->getSize() / 1024);

      // Criar documento de veículo
      $veiculo = Veiculo::create([
        'titulo' => $request->titulo,
        'descricao' => $request->descricao,
        'arquivo' => $nomeArquivo,
        'tipo_arquivo' => $tipoArquivo,
        'tamanho_kb' => $tamanhoKb,
        'data_publicacao' => $request->data_publicacao ?? now(),
        'dias_exibicao' => $request->dias_exibicao,
        'ordem' => $request->ordem ?? 0,
        'ativo' => $request->ativo ?? true,
      ]);

      // Invalidar cache
      $this->invalidarCache();

      Log::info('Documento de veículo criado com sucesso', [
        'id' => $veiculo->id,
        'titulo' => $veiculo->titulo,
        'tipo' => $veiculo->tipo_arquivo,
        'tamanho_kb' => $veiculo->tamanho_kb,
        'user_id' => auth()->id(),
      ]);

      return redirect()
        ->route('admin.veiculo.index')
        ->with('message', 'Lista de veículos adicionada com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao criar documento de veículo: ' . $e->getMessage(), [
        'exception' => $e,
        'user_id' => auth()->id(),
      ]);

      return redirect()
        ->back()
        ->withErrors([
          'error' => 'Erro ao adicionar lista de veículos. Tente novamente.',
        ])
        ->withInput();
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Veiculo $veiculo)
  {
    return Inertia::render('Admin/Veiculo/Show', [
      'veiculo' => $veiculo,
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Veiculo $veiculo)
  {
    return Inertia::render('Admin/Veiculo/Edit', [
      'veiculo' => $veiculo,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Veiculo $veiculo)
  {
    $validator = Validator::make(
      $request->all(),
      [
        'titulo' => 'required|string|max:255',
        'descricao' => 'nullable|string|max:1000',
        'arquivo' => 'nullable|file|mimes:pdf,xlsx,xls|max:10240',
        'dias_exibicao' => 'required|integer|min:1|max:365',
        'data_publicacao' => 'nullable|date',
        'ordem' => 'nullable|integer|min:0|max:9999',
        'ativo' => 'boolean',
      ],
      [
        'arquivo.mimes' => 'O arquivo deve ser PDF ou Excel (.xlsx, .xls).',
        'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
        'dias_exibicao.required' =>
          'Informe por quantos dias o documento ficará visível.',
        'dias_exibicao.min' =>
          'O documento deve ficar visível por pelo menos 1 dia.',
        'dias_exibicao.max' =>
          'O documento não pode ficar visível por mais de 365 dias.',
        'titulo.required' => 'O título é obrigatório.',
        'titulo.max' => 'O título não pode ter mais de 255 caracteres.',
        'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
      ],
    );

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
      $dados = [
        'titulo' => $request->titulo,
        'descricao' => $request->descricao,
        'dias_exibicao' => $request->dias_exibicao,
        'data_publicacao' =>
          $request->data_publicacao ?? $veiculo->data_publicacao,
        'ordem' => $request->ordem ?? $veiculo->ordem,
        'ativo' => $request->ativo ?? $veiculo->ativo,
      ];

      // Se enviou novo arquivo
      if ($request->hasFile('arquivo')) {
        // Deletar arquivo antigo
        if (
          $veiculo->arquivo &&
          Storage::disk('s3')->exists($veiculo->caminho_arquivo)
        ) {
          Storage::disk('s3')->delete($veiculo->caminho_arquivo);
        }

        // Upload novo arquivo
        $file = $request->file('arquivo');
        $nomeOriginal = $file->getClientOriginalName();
        $extensao = $file->getClientOriginalExtension();
        $nomeArquivo =
          Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)) .
          '_' .
          time() .
          '.' .
          $extensao;

        $tipoArquivo = $extensao === 'pdf' ? 'pdf' : 'excel';
        $caminhoArquivo = 'veiculos/' . $nomeArquivo;
        Storage::disk('s3')->put(
          $caminhoArquivo,
          file_get_contents($file),
          'public',
        );
        $tamanhoKb = round($file->getSize() / 1024);

        $dados['arquivo'] = $nomeArquivo;
        $dados['tipo_arquivo'] = $tipoArquivo;
        $dados['tamanho_kb'] = $tamanhoKb;
      }

      $veiculo->update($dados);

      // Invalidar cache
      $this->invalidarCache();

      Log::info('Documento de veículo atualizado com sucesso', [
        'id' => $veiculo->id,
        'titulo' => $veiculo->titulo,
        'user_id' => auth()->id(),
      ]);

      return redirect()
        ->route('admin.veiculo.index')
        ->with('message', 'Lista de veículos atualizada com sucesso!');
    } catch (\Exception $e) {
      Log::error(
        'Erro ao atualizar documento de veículo: ' . $e->getMessage(),
        [
          'veiculo_id' => $veiculo->id,
          'exception' => $e,
          'user_id' => auth()->id(),
        ],
      );

      return redirect()
        ->back()
        ->withErrors([
          'error' => 'Erro ao atualizar lista de veículos. Tente novamente.',
        ])
        ->withInput();
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Veiculo $veiculo)
  {
    try {
      $veiculo->delete();

      // Invalidar cache
      $this->invalidarCache();

      Log::info('Documento de veículo removido com sucesso', [
        'id' => $veiculo->id,
        'titulo' => $veiculo->titulo,
        'user_id' => auth()->id(),
      ]);

      return redirect()
        ->route('admin.veiculo.index')
        ->with('message', 'Lista de veículos removida com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao deletar documento de veículo: ' . $e->getMessage(), [
        'veiculo_id' => $veiculo->id,
        'exception' => $e,
        'user_id' => auth()->id(),
      ]);

      return redirect()
        ->back()
        ->withErrors([
          'error' => 'Erro ao remover lista de veículos. Tente novamente.',
        ]);
    }
  }

  /**
   * Atualizar ordem dos documentos
   */
  public function atualizarOrdem(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'veiculos' => 'required|array',
      'veiculos.*.id' => 'required|exists:veiculos,id',
      'veiculos.*.ordem' => 'required|integer|min:0',
    ]);

    if ($validator->fails()) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Dados inválidos.',
          'errors' => $validator->errors(),
        ],
        422,
      );
    }

    try {
      foreach ($request->veiculos as $veiculoData) {
        Veiculo::where('id', $veiculoData['id'])->update([
          'ordem' => $veiculoData['ordem'],
        ]);
      }

      // Invalidar cache
      $this->invalidarCache();

      Log::info('Ordem dos documentos de veículos atualizada', [
        'quantidade' => count($request->veiculos),
        'user_id' => auth()->id(),
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Ordem atualizada com sucesso!',
      ]);
    } catch (\Exception $e) {
      Log::error(
        'Erro ao atualizar ordem dos documentos: ' . $e->getMessage(),
        [
          'exception' => $e,
          'user_id' => auth()->id(),
        ],
      );

      return response()->json(
        [
          'success' => false,
          'message' => 'Erro ao atualizar ordem.',
        ],
        500,
      );
    }
  }

  /**
   * Alternar status ativo/inativo
   */
  public function toggleAtivo(Veiculo $veiculo)
  {
    try {
      $novoStatus = !$veiculo->ativo;
      $veiculo->update(['ativo' => $novoStatus]);

      // Invalidar cache
      $this->invalidarCache();

      Log::info('Status do documento de veículo alterado', [
        'id' => $veiculo->id,
        'titulo' => $veiculo->titulo,
        'novo_status' => $novoStatus ? 'ativo' : 'inativo',
        'user_id' => auth()->id(),
      ]);

      return redirect()->back()->with('message', 'Status atualizado!');
    } catch (\Exception $e) {
      Log::error('Erro ao alternar status do documento: ' . $e->getMessage(), [
        'veiculo_id' => $veiculo->id,
        'exception' => $e,
        'user_id' => auth()->id(),
      ]);

      return redirect()
        ->back()
        ->withErrors(['error' => 'Erro ao atualizar status.']);
    }
  }

  /**
   * Invalidar cache dos documentos
   */
  private function invalidarCache()
  {
    try {
      Cache::forget('veiculos_publicos');
      Cache::forget('veiculos_validos');

      Log::info('Cache de veículos invalidado com sucesso');
    } catch (\Exception $e) {
      Log::warning('Erro ao invalidar cache de veículos: ' . $e->getMessage());
    }
  }
}
