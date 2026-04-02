<?php

namespace App\Policies;

use App\Models\Operacao;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperacaoPolicy
{
  use HandlesAuthorization;

  /**
   * Apenas super-admin e diop podem visualizar operações no painel admin.
   */
  public function adminViewAny(User $user): bool
  {
    return $user->hasAnyRole(['super-admin', 'diop']);
  }
}
