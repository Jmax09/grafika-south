<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'message',
        'attachment_path',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the message timestamp explicitly formatted in Philippine Standard Time (PHT, UTC+8).
     */
    public function getFormattedPhtTimeAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->timezone('Asia/Manila')->format('M d, h:i A');
    }

    /**
     * Get full date and time in Philippine Standard Time (PHT, UTC+8).
     */
    public function getFormattedPhtFullAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->timezone('Asia/Manila')->format('F d, Y h:i A \P\H\T');
    }
}
