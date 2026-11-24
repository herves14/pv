{{-- Trop de tentatives (ex. plus de 5 essais de connexion en une minute). --}}
@extends('errors.layout')

@section('code', '429')
@section('titre', 'Trop de tentatives')
@section('message', 'Patientez une minute avant de réessayer.')
