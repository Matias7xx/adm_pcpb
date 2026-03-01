<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Auth\Access\HandlesAuthorization;

class VeiculoPolicy
{
  use HandlesAuthorization;

  /** Qualquer admin pode listar */
  public function adminViewAny(User $user): bool
  {
    return $user->hasAnyRole(['super-admin', 'admin', 'diop']);
  }

  /** Qualquer admin pode visualizar */
  public function adminView(User $user, Veiculo $veiculo): bool
  {
    return $user->hasAnyRole(['super-admin', 'admin', 'diop']);
  }

  /** Somente super-admin e diop podem criar */
  public function adminCreate(User $user): bool
  {
    return $user->hasAnyRole(['super-admin', 'diop']);
  }

  /** Somente super-admin e diop podem editar */
  public function adminUpdate(User $user, Veiculo $veiculo = null): bool
  {
    return $user->hasAnyRole(['super-admin', 'diop']);
  }

  /** Somente super-admin e diop podem excluir */
  public function adminDelete(User $user, Veiculo $veiculo = null): bool
  {
    return $user->hasAnyRole(['super-admin', 'diop']);
  }
}
