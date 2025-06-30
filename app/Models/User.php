<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable; // Użycie traitów HasFactory i Notifiable

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Dodano atrybut roli
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token', 
    ]; // Ukrywanie hasła i tokenu pamięci

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array 
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ]; // Rzutowanie atrybutów
    }

    // Metody sprawdzania roli
    public function isAdmin()
    {
        return $this->role === 'admin'; // Sprawdzenie, czy użytkownik jest administratorem
    }

    public function isSerwis()
    {
        return $this->role === 'serwis'; // Sprawdzenie, czy użytkownik jest serwisem
        
    }

    public function isProdukcja()
    {
        return $this->role === 'produkcja'; // Sprawdzenie, czy użytkownik jest produkcją
    }
}