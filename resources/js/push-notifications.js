const enableButton = document.getElementById('push-enable');
const disableButton = document.getElementById('push-disable');
const testButton = document.getElementById('push-test');
const statusBadge = document.getElementById('push-status');
const statusMessage = document.getElementById('push-status-message');

if (enableButton && disableButton && statusBadge && statusMessage) {
    const config = document.getElementById('push-config');

    const publicKey = config?.dataset.publicKey;
    const subscribeUrl = config?.dataset.subscribeUrl;
    const unsubscribeUrl = config?.dataset.unsubscribeUrl;
    const testUrl = config?.dataset.testUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const setStatus = (label, message, enabled) => {
        statusBadge.textContent = label;
        statusMessage.textContent = message;

        enableButton.hidden = enabled;
        enableButton.disabled = false;

        disableButton.hidden = !enabled;

        if (testButton) {
            testButton.hidden = !enabled;
        }
    };

    const urlBase64ToUint8Array = (base64String) => {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = atob(base64);

        return Uint8Array.from(rawData, (char) => char.charCodeAt(0));
    };

    const post = async (url, data) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    };

    testButton?.addEventListener('click', async () => {
        testButton.disabled = true;

        try {
            const result = await post(testUrl, {});

            alert(result.message);
        } catch (error) {
            console.error(error);
            alert('テスト通知の送信に失敗しました。');
        } finally {
            testButton.disabled = false;
        }
    });

    const initialize = async () => {
        if (!('serviceWorker' in navigator) ||
            !('PushManager' in window) ||
            !('Notification' in window) ||
            !window.isSecureContext) {
            setStatus('非対応', 'このブラウザではプッシュ通知を利用できません。', false);
            enableButton.disabled = true;
            return;
        }

        if (!publicKey || !subscribeUrl || !unsubscribeUrl || !csrfToken) {
            setStatus('設定エラー', '通知の設定情報を取得できません。', false);
            enableButton.disabled = true;
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            const subscription = await registration.pushManager.getSubscription();

            if (subscription && Notification.permission === 'granted') {
                setStatus('有効', 'この端末でプッシュ通知を受け取れます。', true);
            } else {
                setStatus('未設定', 'この端末では通知が有効になっていません。', false);
            }
        } catch (error) {
            console.error(error);
            setStatus('エラー', '通知の設定状況を確認できませんでした。', false);
            enableButton.disabled = true;
        }
    };

    enableButton.addEventListener('click', async () => {
        enableButton.disabled = true;

        try {
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                throw new Error('通知が許可されませんでした。');
            }

            const registration = await navigator.serviceWorker.ready;

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey),
            });

            const json = subscription.toJSON();

            await post(subscribeUrl, {
                endpoint: subscription.endpoint,
                keys: json.keys,
                contentEncoding: 'aes128gcm',
            });

            setStatus('有効', 'この端末でプッシュ通知を受け取れます。', true);
        } catch (error) {
            console.error(error);
            setStatus('エラー', error.message, false);
        } finally {
            enableButton.disabled = false;
        }
    });

    disableButton.addEventListener('click', async () => {
        disableButton.disabled = true;

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                await post(unsubscribeUrl, {
                    endpoint: subscription.endpoint,
                });

                await subscription.unsubscribe();
            }

            setStatus('未設定', 'この端末の通知を解除しました。', false);
        } catch (error) {
            console.error(error);
            setStatus('エラー', error.message, true);
        } finally {
            disableButton.disabled = false;
        }
    });

    initialize();
}