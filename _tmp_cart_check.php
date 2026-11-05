<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- Payment methods (active) ---\n";
foreach (App\Models\PaymentMethod::where('is_active', true)->get() as $pm) {
    printf("#%d | %s | digital=%s | cash=%s\n", $pm->id, $pm->name, var_export($pm->isDigitalMethod(), true), var_export($pm->isCashOrOfflineMethod(), true));
}

echo "\n--- Products in catalog (customizable flag) ---\n";
foreach (App\Models\Product::orderBy('id')->get() as $p) {
    printf("#%d | %s | is_customizable=%s | status=%s\n", $p->id, $p->name, var_export((bool) $p->is_customizable, true), $p->status ?? 'n/a');
}
