<?php

namespace App\Models\Common;

use App\Concerns\Blamable;
use App\Concerns\CompanyOwned;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientCategory extends Model
{
    use Blamable;
    use CompanyOwned;
    use HasFactory;

    protected $table = 'client_categories';

    protected $fillable = [
        'company_id',
        'name',
        'color',
        'created_by',
        'updated_by',
    ];

    /**
     * @return HasMany<Client>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'client_category_id');
    }
}
