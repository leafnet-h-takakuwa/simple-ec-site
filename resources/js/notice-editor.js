
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

document.addEventListener('DOMContentLoaded', () => {
    const editorElement = document.getElementById('notice-editor');
    const bodyInput = document.getElementById('body');
    const form = document.getElementById('notice-form');

    if (!editorElement || !bodyInput || !form) {
        return;
    }

    const quill = new Quill(editorElement, {
        theme: 'snow',
        placeholder: 'お知らせの本文を入力してください',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'link'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean'],
            ],
        },
        formats: ['header', 'bold', 'link', 'list'],
    });

    // 編集時・バリデーションエラー時の本文を復元
    if (bodyInput.value) {
        const delta = quill.clipboard.convert({
            html: bodyInput.value,
        });
        quill.setContents(delta);
    }

    // フォーム送信時にHTMLをtextareaへ格納
    form.addEventListener('submit', (event) => {
        const text = quill.getText().trim();

        if (!text) {
            event.preventDefault();
            alert('本文を入力してください。');
            quill.focus();
            return;
        }

        bodyInput.value = quill.getSemanticHTML();
    });
});
