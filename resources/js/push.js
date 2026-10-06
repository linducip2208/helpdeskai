function urlBase64ToUint8Array(b64) {
    const padding = '='.repeat((4 - b64.length % 4) % 4);
    const base64 = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    return Uint8Array.from(raw, c => c.charCodeAt(0));
}

async function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

export async function isPushSupported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
}

export async function pushStatus() {
    if (!await isPushSupported()) return 'unsupported';
    const reg = await navigator.serviceWorker.ready;
    const sub = await reg.pushManager.getSubscription();
    return sub ? 'subscribed' : (Notification.permission === 'denied' ? 'denied' : 'unsubscribed');
}

export async function subscribePush() {
    if (!await isPushSupported()) throw new Error('Push not supported in this browser');

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') throw new Error('Notification permission denied');

    const { key } = await fetch('/push/vapid-key').then(r => r.json());
    if (!key) throw new Error('VAPID key not configured on server');

    const reg = await navigator.serviceWorker.ready;
    const sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(key),
    });

    const res = await fetch('/push/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': await csrf(),
            'Accept': 'application/json',
        },
        body: JSON.stringify(sub.toJSON()),
    });

    if (!res.ok) throw new Error('Server rejected subscription');
    return true;
}

export async function unsubscribePush() {
    const reg = await navigator.serviceWorker.ready;
    const sub = await reg.pushManager.getSubscription();
    if (!sub) return false;

    await fetch('/push/unsubscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': await csrf(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ endpoint: sub.endpoint }),
    });

    await sub.unsubscribe();
    return true;
}

export async function testPush() {
    const res = await fetch('/push/test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': await csrf(),
            'Accept': 'application/json',
        },
    });
    return res.json();
}

window.HelpDeskPush = { isPushSupported, pushStatus, subscribePush, unsubscribePush, testPush };
