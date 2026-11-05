<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'email',
        'username',
        'email_verified_at',
        'password',
        'role',
        'phone',
        'contact',
        'address',
        'region',
        'province',
        'city',
        'barangay',
        'street',
        'is_active',
        'must_change_password',
        'avatar',
        'permissions',
    ];

    public const MODULE_DASHBOARD = 'dashboard';
    public const MODULE_ORDERS = 'orders';
    public const MODULE_TICKETS = 'tickets';
    public const MODULE_MATERIALS = 'materials';
    public const MODULE_PRODUCTS = 'products';
    public const MODULE_HERO = 'hero';
    public const MODULE_SUPPLIERS = 'suppliers';

    public static function availablePermissions(): array
    {
        return [
            self::MODULE_ORDERS => [
                'label' => 'Order Management',
                'description' => 'Review customer orders, statuses, design files, invoices, and buyer chat.',
                'icon' => 'fa-clipboard-list',
                'route' => 'admin.orders.index',
            ],
            self::MODULE_TICKETS => [
                'label' => 'Support Inbox',
                'description' => 'View, reply to, and resolve customer support and inquiries tickets.',
                'icon' => 'fa-headset',
                'route' => 'admin.tickets.index',
            ],
            self::MODULE_MATERIALS => [
                'label' => 'Raw Materials & Inventory',
                'description' => 'Manage raw material stocks, log restocks, and track material usage.',
                'icon' => 'fa-boxes-packing',
                'route' => 'admin.materials.index',
            ],
            self::MODULE_SUPPLIERS => [
                'label' => 'Suppliers & Purchase Orders',
                'description' => 'Manage supplier profiles, supplier catalogs, purchase orders, and accounts payable.',
                'icon' => 'fa-truck-field',
                'route' => 'admin.suppliers.index',
            ],
            self::MODULE_PRODUCTS => [
                'label' => 'Product Management',
                'description' => 'View products catalogue, edit details, specifications, and add new items.',
                'icon' => 'fa-box',
                'route' => 'admin.products.index',
            ],
            self::MODULE_HERO => [
                'label' => 'Hero Slider / Banners',
                'description' => 'Manage homepage promotional banners and showcase slide carousels.',
                'icon' => 'fa-images',
                'route' => 'super-admin.hero.edit',
            ],
        ];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'permissions' => 'array',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Enforce Single Super Admin Rule and system-level protections.
     */
    protected static function booted(): void
    {
        // 1. Prevent creating additional Super Admin users
        static::creating(function (User $user) {
            if ($user->role === 'super_admin') {
                if (static::where('role', 'super_admin')->exists()) {
                    throw new \InvalidArgumentException('Only one exclusive Super Admin account is permitted in the system.');
                }
            }
        });

        // 2. Prevent role escalation to Super Admin or demotion of the original Super Admin
        static::updating(function (User $user) {
            if ($user->isDirty('role')) {
                $originalRole = $user->getOriginal('role');

                // Prevent promoting any account to Super Admin if a Super Admin already exists
                if ($user->role === 'super_admin' && $originalRole !== 'super_admin') {
                    if (static::where('role', 'super_admin')->where('id', '!=', $user->id)->exists()) {
                        throw new \InvalidArgumentException('Only one exclusive Super Admin account is permitted in the system.');
                    }
                }

                // Prevent changing/demoting the Super Admin's role
                if ($originalRole === 'super_admin' && $user->role !== 'super_admin') {
                    throw new \InvalidArgumentException('The Super Admin account role cannot be changed or demoted.');
                }
            }
        });

        // 3. Prevent deleting the Super Admin account
        static::deleting(function (User $user) {
            if ($user->role === 'super_admin') {
                throw new \InvalidArgumentException('The exclusive Super Admin account cannot be deleted.');
            }
        });
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!empty($this->avatar)) {
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    public function getContactAttribute()
    {
        return $this->attributes['contact'] ?? $this->attributes['phone'] ?? null;
    }

    /**
     * The user's age in whole years, derived from date_of_birth (null if unknown).
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Find a user by either their registered email address or username.
     */
    public static function findByEmailOrUsername(string $login): ?self
    {
        $login = trim($login);
        if ($login === '') {
            return null;
        }

        return static::where('email', $login)
            ->orWhere(function ($q) use ($login) {
                $q->whereNotNull('username')->where('username', strtolower($login));
            })
            ->first();
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        $allowedRoles = [];
        foreach ((array) $roles as $r) {
            foreach (explode(',', (string) $r) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        return in_array($this->role, $allowedRoles, true);
    }

    /**
     * Check if user has permission to access a specific module.
     * Super Admin always has full access.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->isAdmin()) {
            return false;
        }

        // Overview / Dashboard is strictly reserved for Super Admin
        if ($permission === self::MODULE_DASHBOARD) {
            return false;
        }

        // Backward compatibility: If an existing admin has null permissions,
        // default to standard operational modules.
        if ($this->permissions === null) {
            return in_array($permission, [
                self::MODULE_ORDERS,
                self::MODULE_TICKETS,
                self::MODULE_MATERIALS,
                self::MODULE_SUPPLIERS,
                self::MODULE_PRODUCTS,
            ], true);
        }

        return is_array($this->permissions) && in_array($permission, $this->permissions, true);
    }

    /**
     * Return the first accessible module route URL based on granted permissions.
     */
    public function getFirstAccessibleRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return route('super-admin.dashboard');
        }

        if ($this->isAdmin()) {
            $modules = self::availablePermissions();
            foreach ($modules as $key => $meta) {
                if ($this->hasPermission($key)) {
                    return route($meta['route']);
                }
            }

            return route('login');
        }

        return route('customer.dashboard');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function orderMessages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Count of purchasable items in the customer's completed orders that
     * have not been reviewed yet (drives the "To Rate" nav badge).
     */
    public function getToRateCountAttribute(): int
    {
        $completedOrders = $this->orders()
            ->where('status', 'completed')
            ->with(['items', 'reviews'])
            ->get();

        $count = 0;
        foreach ($completedOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->product_id && !$order->reviews->contains('product_id', $item->product_id)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->street,
            $this->barangay,
            $this->city,
            $this->province,
            $this->region,
        ]);

        return !empty($parts) ? implode(', ', $parts) : ($this->address ?: null);
    }
}
