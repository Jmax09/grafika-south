<?php
// TEMPORARY verification helper: swaps a known temporary password onto the
// local dev accounts so the signed-in screens can be screenshotted. The
// original bcrypt hashes are saved and restored byte-for-byte afterwards.

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$mode = trim(@file_get_contents(base_path('_tmp_pw_mode.txt')) ?: 'set');
$tmp = 'ThemeVerify!2026';
$file = base_path('_tmp_passwords.json');

if ($mode === 'set') {
    $rows = [];
    foreach ([5, 6, 9] as $id) {
        $u = User::find($id);
        if (!$u) continue;
        $rows[] = ['id' => $u->id, 'password' => $u->password];
    }
    file_put_contents($file, json_encode($rows));
    foreach ($rows as $r) {
        User::where('id', $r['id'])->update(['password' => Hash::make($tmp)]);
    }
    echo 'set ' . count($rows) . "\n";
} else {
    $rows = json_decode(file_get_contents($file), true);
    $ok = 0;
    foreach ($rows as $r) {
        DB::table('users')->where('id', $r['id'])->update(['password' => $r['password']]);
        $cur = DB::table('users')->where('id', $r['id'])->value('password');
        if ($cur === $r['password']) $ok++;
    }
    echo "restored {$ok}/" . count($rows) . "\n";
}
