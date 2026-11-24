{{-- Accès refusé : rôle non autorisé (middleware « role ») ou Policy qui refuse (ex. autre filière). --}}
@extends('errors.layout')

@section('code', '403')
@section('titre', 'Accès refusé')
@section('message', $exception->getMessage() ?: 'Vous n\'avez pas le droit d\'ouvrir cette page.')
