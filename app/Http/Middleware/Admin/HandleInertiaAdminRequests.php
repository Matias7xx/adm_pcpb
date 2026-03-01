<?php

namespace App\Http\Middleware\Admin;

use BalajiDharma\LaravelMenu\Models\Menu;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaAdminRequests extends Middleware
{
  protected $rootView = 'app';

  public function version(Request $request): string|null
  {
    return parent::version($request);
  }

  public function share(Request $request): array
  {
    return [
      ...parent::share($request),
      'navigation' => [
        'menu' => $this->getFilteredMenu($request),
        'breadcrumbs' => $this->getBreadcrumbs($request),
      ],
      'flash' => [
        'message' => fn() => $request->session()->get('message'),
        'error' => fn() => $request->session()->get('error'),
        'destaques_cheios' => fn() => $request
          ->session()
          ->get('destaques_cheios'),
      ],
    ];
  }

  /**
   * Busca o menu admin e filtra os itens pela coluna `roles` (JSON).
   * - roles = null  → visível para qualquer admin autenticado
   * - roles = [{"name":"super-admin"},...] → visível apenas para as roles listadas
   */
  protected function getFilteredMenu(Request $request): array
  {
    $user = $request->user();

    $menuTree = Menu::getMenuTree('admin', false, false);

    if (!$user || !$menuTree) {
      return $menuTree ?? [];
    }

    // Usa o método do Spatie que lê da tabela pivot model_has_roles
    $userRoles = $user->getRoleNames()->toArray();

    return $this->filterMenuItems(collect($menuTree)->all(), $userRoles);
  }

  /**
   * Filtra recursivamente os itens do menu.
   */
  protected function filterMenuItems(array $items, array $userRoles): array
  {
    $filtered = [];

    foreach ($items as $item) {
      // roles null ou vazio = sem restrição
      $itemRoles = $item->roles ?? null;

      if ($itemRoles !== null) {
        // Decodifica se vier como string JSON
        if (is_string($itemRoles)) {
          $itemRoles = json_decode($itemRoles, true) ?? [];
        }

        $allowedRoles = collect($itemRoles)->pluck('name')->toArray();

        // Usuário não tem nenhuma das roles permitidas → pula
        if (!array_intersect($userRoles, $allowedRoles)) {
          continue;
        }
      }

      // Filtra filhos recursivamente se existirem
      if (!empty($item->children)) {
        $item->children = $this->filterMenuItems(
          collect($item->children)->all(),
          $userRoles,
        );
      }

      $filtered[] = $item;
    }

    return $filtered;
  }

  protected function getBreadcrumbs(Request $request)
  {
    if ($request->isMethod('get')) {
      return Breadcrumbs::generate();
    }
  }
}
