{{-- Erreur inattendue du serveur. Le détail est enregistré dans storage/logs. --}}
@extends('errors.layout')

@section('code', '500')
@section('titre', 'Erreur du serveur')
@section('message', 'Un problème inattendu est survenu. Réessayez dans un instant ; si cela persiste, prévenez l\'administrateur.')
