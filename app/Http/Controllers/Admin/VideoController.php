<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class VideoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $videos = Video::query();

    if (request()->has('search')) {
      $videos
        ->where('titulo', 'ILIKE', '%' . request()->input('search') . '%')
        ->orWhere('descricao', 'ILIKE', '%' . request()->input('search') . '%');
    }

    if (request()->query('sort')) {
      $attribute = request()->query('sort');
      $sort_order = 'ASC';
      if (strncmp($attribute, '-', 1) === 0) {
        $sort_order = 'DESC';
        $attribute = substr($attribute, 1);
      }
      $videos->orderBy($attribute, $sort_order);
    } else {
      $videos->ordenados();
    }

    $videos = $videos
      ->paginate(config('admin.paginate.per_page', 15))
      ->onEachSide(config('admin.paginate.each_side', 1))
      ->appends(request()->query());

    return Inertia::render('Admin/Video/Index', [
      'videos' => $videos,
      'filters' => request()->all('search'),
      'can' => [
        'create' => Auth::user()->can('video create'),
        'edit' => Auth::user()->can('video edit'),
        'delete' => Auth::user()->can('video delete'),
      ],
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return Inertia::render('Admin/Video/Create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'titulo' => 'required|string|max:255',
      'youtube_url' => 'required|url',
      'descricao' => 'nullable|string',
      'ordem' => 'nullable|integer|min:0',
      'ativo' => 'boolean',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
      $video = Video::create([
        'titulo' => $request->titulo,
        'youtube_url' => $request->youtube_url,
        'descricao' => $request->descricao,
        'ordem' => $request->ordem ?? 0,
        'ativo' => $request->ativo ?? true,
      ]);

      // Invalidar cache
      $this->invalidarCache();

      return redirect()
        ->route('admin.video.index')
        ->with('message', 'Vídeo adicionado com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao criar vídeo: ' . $e->getMessage());

      return redirect()
        ->back()
        ->withErrors(['error' => 'Erro ao adicionar vídeo. Tente novamente.'])
        ->withInput();
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Video $video)
  {
    return Inertia::render('Admin/Video/Show', [
      'video' => $video,
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Video $video)
  {
    return Inertia::render('Admin/Video/Edit', [
      'video' => $video,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Video $video)
  {
    $validator = Validator::make($request->all(), [
      'titulo' => 'required|string|max:255',
      'youtube_url' => 'required|url',
      'descricao' => 'nullable|string',
      'ordem' => 'nullable|integer|min:0',
      'ativo' => 'boolean',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
      $video->update([
        'titulo' => $request->titulo,
        'youtube_url' => $request->youtube_url,
        'descricao' => $request->descricao,
        'ordem' => $request->ordem ?? $video->ordem,
        'ativo' => $request->ativo ?? $video->ativo,
      ]);

      // Invalidar cache
      $this->invalidarCache();

      return redirect()
        ->route('admin.video.index')
        ->with('message', 'Vídeo atualizado com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao atualizar vídeo: ' . $e->getMessage());

      return redirect()
        ->back()
        ->withErrors(['error' => 'Erro ao atualizar vídeo. Tente novamente.'])
        ->withInput();
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Video $video)
  {
    try {
      $video->delete();

      // Invalidar cache
      $this->invalidarCache();

      return redirect()
        ->route('admin.video.index')
        ->with('message', 'Vídeo removido com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao deletar vídeo: ' . $e->getMessage());

      return redirect()
        ->back()
        ->withErrors(['error' => 'Erro ao remover vídeo. Tente novamente.']);
    }
  }

  /**
   * Atualizar ordem dos vídeos
   */
  public function atualizarOrdem(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'videos' => 'required|array',
      'videos.*.id' => 'required|exists:videos,id',
      'videos.*.ordem' => 'required|integer|min:0',
    ]);

    if ($validator->fails()) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Dados inválidos.',
        ],
        422,
      );
    }

    try {
      foreach ($request->videos as $videoData) {
        Video::where('id', $videoData['id'])->update([
          'ordem' => $videoData['ordem'],
        ]);
      }

      // Invalidar cache
      $this->invalidarCache();

      return response()->json([
        'success' => true,
        'message' => 'Ordem atualizada com sucesso!',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao atualizar ordem dos vídeos: ' . $e->getMessage());

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
  public function toggleAtivo(Video $video)
  {
    try {
      $video->update(['ativo' => !$video->ativo]);

      // Invalidar cache
      $this->invalidarCache();

      return redirect()->back()->with('message', 'Status do vídeo atualizado!');
    } catch (\Exception $e) {
      Log::error('Erro ao alternar status do vídeo: ' . $e->getMessage());

      return redirect()
        ->back()
        ->withErrors(['error' => 'Erro ao atualizar status.']);
    }
  }

  /**
   * Invalidar cache dos vídeos
   */
  private function invalidarCache()
  {
    try {
      Cache::forget('videos_home');
      Cache::forget('videos_ativos');

      Log::info('Cache de vídeos invalidado com sucesso');
    } catch (\Exception $e) {
      Log::warning('Erro ao invalidar cache de vídeos: ' . $e->getMessage());
    }
  }
}
