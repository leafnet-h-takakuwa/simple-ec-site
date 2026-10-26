self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// プッシュ通知を受信
self.addEventListener('push', (event) => {
    let data = {};

    if (event.data) {
        try {
            data = event.data.json();
        } catch {
            data = { body: event.data.text() };
        }
    }

    const title = data.title || '新しい通知';

    const options = {
        body: data.body || '',
        icon: '/favicon.svg',
        badge: '/favicon.svg',
        data: {
            url: data.data?.url || data.url || new URL('admin', self.registration.scope).href,
        },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// 通知をクリック
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = new URL(
        event.notification.data?.url || '/admin',
        self.location.origin
    );

    // 外部サイトへの遷移を防止
    if (url.origin !== self.location.origin) {
        return;
    }

    event.waitUntil(
        clients.openWindow(url.href)
    );
});