<?php

namespace App\Http\Middleware;

use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\User;
use App\Support\BranchAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class EnsurePurchaseOrderSaveIsIdempotent
{
    public function handle(Request $request, Closure $next): Response
    {
        // Older clients may continue to save without a request key.
        $key = $request->header('Idempotency-Key');
        if ($key === null) {
            return $next($request);
        }

        Validator::make(['request_key' => $key], ['request_key' => ['required', 'uuid']])->validate();
        abort_unless((string) $request->header('X-PO-User') === (string) $request->user()->id, 403,
            'Akun telah berubah. Masuk kembali dengan akun pembuat isian PO.');

        $fingerprint = hash('sha256', json_encode([
            $request->method(), $request->path(), $request->except('_token'),
        ], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($request, $next, $key, $fingerprint) {
            // Serialize saves by this user, including two concurrent copies of a new request.
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $previous = DB::table('purchase_order_save_requests')
                ->where('user_id', $request->user()->id)->where('request_key', $key)->first();

            if ($previous) {
                abort_unless(hash_equals($previous->fingerprint, $fingerprint), 409,
                    'Percobaan simpan ini memiliki data berbeda. Periksa PO yang sudah tersimpan.');
                abort_unless(PembelianModel::whereKey($previous->purchase_order_id)
                    ->whereIn('branch_id', BranchAccess::userBranchIds())->exists(), 409,
                    'PO sebelumnya sudah dihapus atau tidak dapat diakses. Periksa daftar PO.');

                return response($previous->response, 200, ['Content-Type' => 'application/json']);
            }

            $response = $next($request);
            $body = json_decode($response->getContent(), true);
            if ($response->isSuccessful() && ($body['status'] ?? null) === 'success' && isset($body['data']['id'])) {
                // Record the response in the same transaction as the PO itself.
                DB::table('purchase_order_save_requests')->insert([
                    'user_id' => $request->user()->id,
                    'request_key' => $key,
                    'fingerprint' => $fingerprint,
                    'purchase_order_id' => $body['data']['id'],
                    'response' => $response->getContent(),
                    'created_at' => now(),
                ]);
            }

            return $response;
        }, 5);
    }
}
