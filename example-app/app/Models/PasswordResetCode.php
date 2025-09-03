<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Générer un nouveau code de réinitialisation
     */
    public static function generateCode($email)
    {
        // Supprimer les anciens codes non utilisés pour cet email
        self::where('email', $email)->delete();

        // Générer un code à 6 chiffres
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Créer le nouveau code avec expiration dans 15 minutes
        return self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'used' => false
        ]);
    }

    /**
     * Vérifier si le code est valide
     */
    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Marquer le code comme utilisé
     */
    public function markAsUsed()
    {
        $this->update(['used' => true]);
    }

    /**
     * Trouver un code valide
     */
    public static function findValidCode($email, $code)
    {
        return self::where('email', $email)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Nettoyer les codes expirés (à appeler périodiquement)
     */
    public static function cleanExpired()
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
}
