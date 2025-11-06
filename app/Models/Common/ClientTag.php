<?php

namespace App\Models\Common;

use App\Concerns\Blamable;
use App\Concerns\CompanyOwned;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientTag extends Model
{
    use Blamable;
    use CompanyOwned;
    use HasFactory;

    protected $table = 'client_tags';

    protected $fillable = [
        'company_id',
        'name',
        'color',
        'created_by',
        'updated_by',
    ];

    /**
     * @return BelongsToMany<Client>
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)
            ->withTimestamps();
    }
}
