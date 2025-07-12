<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class System extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Adjust to false if no created_at/updated_at in systems

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true; // True if id is auto-incrementing integer

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'int'; // Adjust to 'string' if systems.id is char(36)

    /**
     * Get the users associated with this system.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'application_user', 'application_id', 'user_id')
                    ->withPivot('id');
    }
}