<?php
namespace Devinweb\LaravelPaytabs\Models;

use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use Devinweb\LaravelPaytabs\Traits\HasUniqueID;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasUniqueID;
    /**
     * Disable auto-increment.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Specify The type of the primary key ID..
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user that owns the transation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->owner();
    }

    public function owner()
    {
        $model = LaravelPaytabsFacade::config()->get('model');
        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    public function children()
    {
        return $this->hasMany(Transaction::class, 'parent', 'transaction_ref');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
