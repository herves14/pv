<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Classe parente de tous les formulaires de l'application.
 *
 * Elle fournit les messages d'erreur en français pour les règles courantes.
 * Chaque formulaire enfant déclare ses règles (rules) et le nom lisible de
 * ses champs (attributes), utilisé à la place de :attribute dans les messages.
 */
abstract class RequeteFormulaire extends FormRequest
{
    public function messages(): array
    {
        return [
            'required' => 'Le champ « :attribute » est obligatoire.',
            'required_if' => 'Le champ « :attribute » est obligatoire dans ce cas.',
            'prohibited_unless' => 'Le champ « :attribute » ne doit pas être rempli pour ce rôle.',
            'string' => 'Le champ « :attribute » doit être un texte.',
            'integer' => 'Le champ « :attribute » doit être un nombre entier.',
            'email' => 'Le champ « :attribute » doit être une adresse e-mail valide.',
            'max.string' => 'Le champ « :attribute » ne doit pas dépasser :max caractères.',
            'max.file' => 'Le fichier ne doit pas dépasser :max Ko.',
            'max.numeric' => 'Le champ « :attribute » ne doit pas dépasser :max.',
            'min.string' => 'Le champ « :attribute » doit contenir au moins :min caractères.',
            'min.numeric' => 'Le champ « :attribute » doit être au moins :min.',
            'unique' => 'Cette valeur de « :attribute » existe déjà.',
            'exists' => 'La valeur choisie pour « :attribute » n\'existe pas.',
            'in' => 'La valeur choisie pour « :attribute » n\'est pas valide.',
            'enum' => 'La valeur choisie pour « :attribute » n\'est pas valide.',
            'date' => 'Le champ « :attribute » doit être une date.',
            'before_or_equal' => 'Le champ « :attribute » ne peut pas être dans le futur.',
            'file' => 'Le champ « :attribute » doit être un fichier.',
            'mimes' => 'Le fichier doit être un PDF.',
            'mimetypes' => 'Le fichier doit être un PDF.',
            'regex' => 'Le format de « :attribute » n\'est pas valide.',
            'confirmed' => 'La confirmation de « :attribute » ne correspond pas.',
            'current_password' => 'Le mot de passe actuel est incorrect.',
            'boolean' => 'Le champ « :attribute » doit être vrai ou faux.',
        ];
    }
}
