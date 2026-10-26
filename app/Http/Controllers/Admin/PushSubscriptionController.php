<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Notifications\TestPushNotification;

class PushSubscriptionController extends Controller
{
    // この端末の通知を登録
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:2048'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
            $validated['contentEncoding'] ?? 'aes128gcm'
        );

        return response()->json([
            'message' => 'プッシュ通知を有効にしました。',
        ]);
    }

    // この端末の通知を解除
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:2048'],
        ]);

        $request->user()->deletePushSubscription($validated['endpoint']);

        return response()->json([
            'message' => 'プッシュ通知を解除しました。',
        ]);
    }

    // テスト通知
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->pushSubscriptions()->exists()) {
            return response()->json([
                'message' => 'プッシュ通知が登録されていません。',
            ], 422);
        }

        $user->notify(new TestPushNotification());

        return response()->json([
            'message' => 'テスト通知を送信しました。',
        ]);
    }
}
