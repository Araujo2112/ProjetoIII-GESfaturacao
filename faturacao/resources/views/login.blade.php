@extends('layout')

@section('title', 'Login')

@section('content')
<div class="container mt-5">
  <form method="POST" action="{{ route('login.process') }}">
    @csrf
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <div class="mb-3">
        <label for="usernameInput" class="form-label">Username</label>
        <input type="text" name="username" class="form-control" id="usernameInput" required>
    </div>
    <div class="mb-3">
      <label for="exampleInputPassword1" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" id="exampleInputPassword1" required>
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="exampleCheck1" name="remember">
      <label class="form-check-label" for="exampleCheck1">Manter sessão iniciada</label>
    </div>
    <button type="submit" class="btn btn-primary">Entrar</button>
  </form>
</div>
@endsection
