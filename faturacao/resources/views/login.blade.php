@extends('layout')

@section('title', 'Login')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logo">GF</div>
      <h1 class="auth-title">Bem-vindo ao GESFaturação</h1>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.process') }}" novalidate>
      @csrf

      <div class="mb-3">
        <label for="usernameInput" class="form-label">Utilizador</label>
        <input type="text" name="username" id="usernameInput" class="form-control" placeholder="O seu utilizador" required>
      </div>

      <div class="mb-3">
        <label for="passwordInput" class="form-label">Palavra-Passe</label>
        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary">Entrar</button>

      <p class="small-muted mt-2">© {{ date('Y') }} GESFaturação</p>
    </form>
  </div>
</div>
@endsection
