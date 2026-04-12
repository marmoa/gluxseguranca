<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rota raiz: redireciona para o painel correto conforme a role do usuário autenticado
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect('/admin/login');
    }

    $user = Auth::user();

    if ($user->hasRole('admin')) {
        return redirect('/admin');
    }

    if ($user->hasRole('campo')) {
        return redirect('/campo');
    }

    if ($user->hasRole('comum')) {
        return redirect('/comum');
    }

    // Usuário sem role: força logout e redireciona para login admin
    Auth::logout();
    return redirect('/admin/login')->withErrors(['email' => 'Usuário sem perfil de acesso configurado.']);
});
