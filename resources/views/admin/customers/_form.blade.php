{{-- 登録情報 --}}
<section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
    <h2 class="mb-6 text-lg font-bold">
        登録情報
    </h2>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- 氏名 --}}
        <div>
            <label for="name" class="mb-2 block text-sm font-semibold">
                氏名 <span class="text-error">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $customer->name ?? '') }}"
                maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" class="input w-full" required>
        </div>

        {{-- 電話番号 --}}
        <div>
            <label for="phone" class="mb-2 block text-sm font-semibold">
                電話番号 <span class="text-error">*</span>
            </label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone ?? '') }}"
                maxlength="{{ \App\Support\InputLimits::PHONE }}" class="input w-full" required>
        </div>

        {{-- 郵便番号 --}}
        <div>
            <label for="postal_code" class="mb-2 block text-sm font-semibold">
                郵便番号 <span class="text-error">*</span>
            </label>
            <input type="text" name="postal_code" id="postal_code"
                value="{{ old('postal_code', $customer->postal_code ?? '') }}"
                maxlength="{{ \App\Support\InputLimits::POSTAL_CODE }}" class="input w-full" required>
        </div>

        {{-- メールアドレス --}}
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold">
                メールアドレス
            </label>
            <input type="email" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}"
                maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" class="input w-full">
        </div>

        {{-- 住所 --}}
        <div class="lg:col-span-2">
            <label for="address" class="mb-2 block text-sm font-semibold">
                住所 <span class="text-error">*</span>
            </label>
            <input type="text" name="address" id="address" value="{{ old('address', $customer->address ?? '') }}"
                maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" class="input w-full" required>
        </div>

        {{-- 利用区分 --}}
        <div class="lg:col-span-2">
            <p class="mb-2 text-sm font-semibold">
                利用区分 <span class="text-error">*</span>
            </p>

            <div class="flex flex-wrap items-center gap-6">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="hidden" name="is_orderer" value="0">
                    <input type="checkbox" name="is_orderer" value="1" class="checkbox checkbox-success"
                        @checked(old('is_orderer', $customer->is_orderer ?? false))>
                    注文者
                </label>

                <label class="flex cursor-pointer items-center gap-2">
                    <input type="hidden" name="is_recipient" value="0">
                    <input type="checkbox" name="is_recipient" value="1" class="checkbox checkbox-success"
                        @checked(old('is_recipient', $customer->is_recipient ?? false))>
                    配送先
                </label>
            </div>

            <p class="mt-2 text-xs text-slate-500">
                注文者・配送先のいずれか、または両方を選択してください。
            </p>
        </div>

        {{-- メモ --}}
        <div class="lg:col-span-2">
            <label for="memo" class="mb-2 block text-sm font-semibold">
                メモ
            </label>
            <textarea name="memo" id="memo" rows="5" maxlength="{{ \App\Support\InputLimits::CUSTOMER_NOTE }}"
                placeholder="電話・FAX注文時の対応事項など" class="textarea w-full">{{ old('memo', $customer->memo ?? '') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">
                顧客固有の対応事項や注意点を記録できます。
            </p>
        </div>
    </div>
</section>
