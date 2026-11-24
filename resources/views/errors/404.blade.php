{{-- Page ou PV introuvable (adresse erronée, PV supprimé). --}}
@extends('errors.layout')

@section('code', '404')
@section('titre', 'Page introuvable')
@section('message', 'Cette page n\'existe pas ou a été supprimée.')
