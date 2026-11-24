{{-- Jeton CSRF expiré : la page est restée ouverte trop longtemps avant l'envoi du formulaire. --}}
@extends('errors.layout')

@section('code', '419')
@section('titre', 'Page expirée')
@section('message', 'La page est restée ouverte trop longtemps. Revenez en arrière, actualisez la page et recommencez.')
