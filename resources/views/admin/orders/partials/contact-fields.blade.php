<div class="mt-6 space-y-4">
    <div>
        <label class="mb-2 block text-sm font-semibold">
            お客様への連絡方法
        </label>

        <select name="contact_method" class="select w-full" required>
            <option value="none">連絡なし</option>
            <option value="phone">電話</option>
            <option value="fax">FAX</option>
            <option value="in_person">対面</option>
        </select>
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
