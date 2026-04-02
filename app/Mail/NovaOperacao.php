<?php

namespace App\Mail;

use App\Models\Operacao;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaOperacao extends Mailable
{
  use SerializesModels;

  public function __construct(public Operacao $operacao) {}

  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Nova Operação Cadastrada — ' . $this->operacao->nome_operacao,
    );
  }

  public function content(): Content
  {
    return new Content(
      markdown: 'emails.operacao.nova',
      with: [
        'operacao' => $this->operacao,
        'url' => route('admin.operacoes-admin.show', $this->operacao->id),
      ],
    );
  }

  public function attachments(): array
  {
    return [];
  }
}
