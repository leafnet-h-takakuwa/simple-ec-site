<div class="mt-6 space-y-4">
    <div>
        <label class="mb-2 block text-sm font-semibold">
            お客様への連絡方法
        </label>

        <select name="contact_method" class="select w-full" required>
            <option value="" selected disabled>連絡方法を選択してください</option>

            @if ($canSendEmail)
                <option value="email">メール送信する</option>
            @endif

            <option value="phone">電話連絡済</option>
            <option value="fax">FAX連絡済</option>
            <option value="in_person">対面連絡済</option>
            <option value="none">連絡不要</option>
        </select>

        @if ($canSendEmail)
            <p class="mt-2 text-sm text-amber-700">
                ※「メール送信する」を選択すると、ステータス更新時にお客様へメールを自動送信します。
            </p>
        @endif
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold">
            対応メモ
        </label>

        <textarea name="contact_note" rows="3" class="textarea w-full" maxlength="2000" placeholder="例：電話で総額を案内し、了承済み"></textarea>

        <p class="mt-1 text-xs text-slate-500">
            連絡内容や、連絡しなかった理由を記録できます。
        </p>
    </div>
</div>
