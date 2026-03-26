<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
  public function index()
  {
    $this->authorize('adminViewAny', Banner::class);

    $banners = new Banner()->newQuery();

    if (request()->has('search')) {
      $searchTerm = trim(request()->input('search'));
      if (!empty($searchTerm)) {
        $banners->where(function ($query) use ($searchTerm) {
          $query
            ->where('titulo', 'ILIKE', "%{$searchTerm}%")
            ->orWhere('descricao', 'ILIKE', "%{$searchTerm}%");
        });
      }
    }

    // Filtro por tipo (opcional)
    if (request()->has('tipo') && in_array(request()->query('tipo'), ['topo', 'inferior'])) {
      $banners->where('tipo', request()->query('tipo'));
    }

    if (request()->query('sort')) {
      $attribute  = request()->query('sort');
      $sort_order = 'ASC';
      if (strncmp($attribute, '-', 1) === 0) {
        $sort_order = 'DESC';
        $attribute  = substr($attribute, 1);
      }
      $banners->orderBy($attribute, $sort_order);
    } else {
      $banners->orderBy('tipo')->orderBy('ordem')->latest();
    }

    $banners = $banners
      ->paginate(config('admin.paginate.per_page', 10))
      ->onEachSide(config('admin.paginate.each_side', 1))
      ->appends(request()->query());

    $banners->getCollection()->transform(function ($banner) {
      return [
        'id'            => $banner->id,
        'titulo'        => $banner->titulo,
        'descricao'     => $banner->descricao,
        'imagem'        => $banner->imagem_url,
        'link'          => $banner->link,
        'nova_aba'      => $banner->nova_aba,
        'ordem'         => $banner->ordem,
        'ativo'         => $banner->ativo,
        'tipo'          => $banner->tipo,
        'data_inicio'   => $banner->data_inicio?->format('d/m/Y'),
        'data_fim'      => $banner->data_fim?->format('d/m/Y'),
        'pode_ser_exibido' => $banner->podeSerExibido(),
        'created_at'    => $banner->created_at->format('d/m/Y H:i'),
      ];
    });

    return Inertia::render('Admin/Banners/Index', [
      'banners' => $banners,
      'filters' => request()->all('search', 'tipo'),
      'can' => [
        'create' => Auth::user()->can('banner create'),
        'edit'   => Auth::user()->can('banner edit'),
        'delete' => Auth::user()->can('banner delete'),
      ],
    ]);
  }

  public function create()
  {
    $this->authorize('adminCreate', Banner::class);
    return Inertia::render('Admin/Banners/Create');
  }

  public function store(Request $request)
  {
    $this->authorize('adminCreate', Banner::class);

    $validated = $request->validate([
      'titulo'      => 'required|string|max:255',
      'descricao'   => 'nullable|string|max:255',
      'imagem'      => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
      'link'        => 'nullable|url|max:500',
      'nova_aba'    => 'boolean',
      'ordem'       => 'integer|min:0',
      'ativo'       => 'boolean',
      'tipo'        => 'required|in:topo,inferior',
      'data_inicio' => 'nullable|date',
      'data_fim'    => 'nullable|date|after_or_equal:data_inicio',
    ]);

    if ($request->hasFile('imagem')) {
      $imagem      = $request->file('imagem');
      $nomeArquivo = time() . '_' . $imagem->getClientOriginalName();
      $path        = $imagem->storeAs('banners', $nomeArquivo, 'public');
      $validated['imagem'] = $path;
    }

    // Limite de 4 banners inferiores
    if ($validated['tipo'] === 'inferior') {
      $total = Banner::where('tipo', 'inferior')->count();
      if ($total >= 4) {
        return back()->withErrors([
          'tipo' => 'O limite máximo de 4 banners inferiores foi atingido. Remova um antes de adicionar outro.',
        ]);
      }
    }

    Banner::create($validated);

    return redirect()
      ->route('admin.banners.index')
      ->with('message', 'Banner criado com sucesso!');
  }

  public function show(Banner $banner)
  {
    $this->authorize('adminView', $banner);

    return Inertia::render('Admin/Banners/Show', [
      'banner' => [
        'id'          => $banner->id,
        'titulo'      => $banner->titulo,
        'descricao'   => $banner->descricao,
        'imagem'      => $banner->imagem_url,
        'link'        => $banner->link,
        'nova_aba'    => $banner->nova_aba,
        'ordem'       => $banner->ordem,
        'ativo'       => $banner->ativo,
        'tipo'        => $banner->tipo,
        'data_inicio' => $banner->data_inicio,
        'data_fim'    => $banner->data_fim,
        'created_at'  => $banner->created_at->format('d/m/Y H:i'),
      ],
    ]);
  }

  public function edit(Banner $banner)
  {
    $this->authorize('adminUpdate', $banner);

    return Inertia::render('Admin/Banners/Edit', [
      'banner' => [
        'id'          => $banner->id,
        'titulo'      => $banner->titulo,
        'descricao'   => $banner->descricao,
        'imagem'      => $banner->imagem_url,
        'imagem_path' => $banner->imagem,
        'link'        => $banner->link,
        'nova_aba'    => $banner->nova_aba,
        'ordem'       => $banner->ordem,
        'ativo'       => $banner->ativo,
        'tipo'        => $banner->tipo,
        'data_inicio' => $banner->data_inicio?->format('Y-m-d'),
        'data_fim'    => $banner->data_fim?->format('Y-m-d'),
      ],
    ]);
  }

  public function update(Request $request, Banner $banner)
  {
    $this->authorize('adminUpdate', $banner);

    $validated = $request->validate([
      'titulo'         => 'required|string|max:255',
      'descricao'      => 'nullable|string|max:255',
      'imagem'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
      'link'           => 'nullable|url|max:500',
      'nova_aba'       => 'boolean',
      'ordem'          => 'integer|min:0',
      'ativo'          => 'boolean',
      'tipo'           => 'required|in:topo,inferior',
      'data_inicio'    => 'nullable|date',
      'data_fim'       => 'nullable|date|after_or_equal:data_inicio',
      'remover_imagem' => 'boolean',
    ]);

    // Limite de 4 banners inferiores (excluindo o próprio)
    if ($validated['tipo'] === 'inferior' && $banner->tipo !== 'inferior') {
      $total = Banner::where('tipo', 'inferior')->count();
      if ($total >= 4) {
        return back()->withErrors([
          'tipo' => 'O limite máximo de 4 banners inferiores foi atingido.',
        ]);
      }
    }

    if ($request->input('remover_imagem') && $banner->imagem) {
      Storage::disk('public')->delete($banner->imagem);
      $validated['imagem'] = null;
    }

    if ($request->hasFile('imagem')) {
      if ($banner->imagem) {
        Storage::disk('public')->delete($banner->imagem);
      }
      $imagem      = $request->file('imagem');
      $nomeArquivo = time() . '_' . $imagem->getClientOriginalName();
      $path        = $imagem->storeAs('banners', $nomeArquivo, 'public');
      $validated['imagem'] = $path;
    } elseif (!$request->input('remover_imagem')) {
      unset($validated['imagem']);
    }

    unset($validated['remover_imagem']);

    $banner->update($validated);

    return redirect()
      ->route('admin.banners.index')
      ->with('message', 'Banner atualizado com sucesso!');
  }

  public function destroy(Banner $banner)
  {
    $this->authorize('adminDelete', $banner);

    if ($banner->imagem) {
      Storage::disk('public')->delete($banner->imagem);
    }

    $banner->delete();

    return redirect()
      ->route('admin.banners.index')
      ->with('message', 'Banner removido com sucesso!');
  }

  public function toggleAtivo(Banner $banner)
  {
    $this->authorize('adminUpdate', $banner);

    $banner->update(['ativo' => !$banner->ativo]);

    return back()->with(
      'message',
      $banner->ativo ? 'Banner ativado com sucesso!' : 'Banner desativado com sucesso!',
    );
  }

  public function updateOrdem(Request $request)
  {
    $validated = $request->validate([
      'banners'         => 'required|array',
      'banners.*.id'    => 'required|exists:banners,id',
      'banners.*.ordem' => 'required|integer|min:0',
    ]);

    foreach ($validated['banners'] as $bannerData) {
      Banner::where('id', $bannerData['id'])->update(['ordem' => $bannerData['ordem']]);
    }

    return redirect()
      ->route('admin.banners.index')
      ->with('message', 'Ordem dos banners atualizada com sucesso!');
  }
}