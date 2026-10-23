import './notice-editor';

// ドロップダウンの外側クリックで閉じる
document.addEventListener('click', (event) => {
    document.querySelectorAll('details.js-auto-close[open]').forEach((details) => {
        if (!details.contains(event.target)) {
            details.open = false;
        }
    });
});

// Escキーでドロップダウンを閉じる
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('details.js-auto-close[open]').forEach((details) => {
            details.open = false;
        });
    }
});