<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('customer-select-modal');
        const search = document.getElementById('customer-search');
        const filterOrderer = document.getElementById('filter-orderer');
        const filterRecipient = document.getElementById('filter-recipient');
        const results = document.getElementById('customer-results');
        const resultCount = document.getElementById('customer-result-count');
        const closeButton = document.getElementById('close-customer-modal');

        const customers = JSON.parse(
            document.getElementById('customer-data').textContent
        );

        let target = 'orderer';

        // HTMLとして表示する文字列をエスケープ
        const escapeHtml = (value) => {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[char]);
        };

        // 検索用の文字列を正規化
        const normalize = (value) => {
            return String(value ?? '')
                .normalize('NFKC')
                .toLowerCase()
                .replace(/[\sー－‐‑‒–—―-]/g, '');
        };

        // 検索結果を描画
        const renderCustomers = () => {
            const keyword = normalize(search.value);

            const filtered = customers.filter((customer) => {
                const matchesType =
                    (filterOrderer.checked && customer.is_orderer) ||
                    (filterRecipient.checked && customer.is_recipient);

                if (!matchesType) return false;

                const searchable = [
                    customer.name,
                    customer.phone,
                    customer.postal_code,
                    customer.address,
                    customer.email,
                    customer.memo,
                ].join(' ');

                return normalize(searchable).includes(keyword);
            });

            resultCount.textContent = `検索結果：${filtered.length}件`;

            if (filtered.length === 0) {
                results.innerHTML = `
                    <div class="p-8 text-center text-sm text-slate-500">
                        該当する顧客はいません。
                    </div>
                `;
                return;
            }

            results.innerHTML = filtered.map((customer) => `
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold">
                                ${escapeHtml(customer.name)}
                            </p>
                            <p class="mt-1 text-sm">
                                ${escapeHtml(customer.phone)}
                            </p>
                            <p class="mt-1 break-words text-xs text-slate-500">
                                〒${escapeHtml(customer.postal_code)}
                                ${escapeHtml(customer.address)}
                            </p>
                        </div>

                        <button type="button"
                            class="choose-customer btn btn-outline btn-success btn-sm shrink-0"
                            data-id="${customer.id}">
                            選択
                        </button>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        ${customer.email ? `
                            <span class="badge badge-success badge-outline badge-sm">
                                メール
                            </span>
                        ` : ''}

                        ${customer.memo ? `
                            <button type="button"
                                class="show-customer-memo badge badge-outline badge-sm cursor-pointer border-amber-600 text-amber-700"
                                data-id="${customer.id}"
                                title="メモを表示">
                                メモ
                            </button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        };

        // 顧客情報を入力欄へ反映
        const applyCustomer = (customer) => {
            const fields = {
                name: customer.name,
                postal_code: customer.postal_code,
                address: customer.address,
                phone: customer.phone,
            };

            if (target === 'orderer') {
                fields.email = customer.email ?? '';
            }

            Object.entries(fields).forEach(([field, value]) => {
                const input = document.getElementById(`${target}_${field}`);

                if (input) {
                    input.value = value ?? '';
                    input.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
            });

            modal.close();
        };

        // モーダルを開く
        document.querySelectorAll('.select-customer').forEach((button) => {
            button.addEventListener('click', () => {
                target = button.dataset.target;

                search.value = '';
                filterOrderer.checked = target === 'orderer';
                filterRecipient.checked = target === 'shipping';

                renderCustomers();
                modal.showModal();
            });
        });

        // 検索・絞り込み
        search.addEventListener('input', renderCustomers);
        filterOrderer.addEventListener('change', renderCustomers);
        filterRecipient.addEventListener('change', renderCustomers);

        // 検索結果のボタン操作
        results.addEventListener('click', (event) => {
            const chooseButton = event.target.closest('.choose-customer');

            if (chooseButton) {
                const customer = customers.find(
                    (item) => String(item.id) === chooseButton.dataset.id
                );

                if (customer) {
                    applyCustomer(customer);
                }
                return;
            }

            const memoButton = event.target.closest('.show-customer-memo');

            if (memoButton) {
                const customer = customers.find(
                    (item) => String(item.id) === memoButton.dataset.id
                );

                if (!customer?.memo) return;

                showMemo(memoButton, customer.memo);
            }
        });

        // メモの吹き出し
        let memoPopover = null;

        const closeMemo = () => {
            memoPopover?.remove();
            memoPopover = null;
        };

        const showMemo = (button, memo) => {
            if (memoPopover?.dataset.customerId === button.dataset.id) {
                closeMemo();
                return;
            }

            closeMemo();

            const popover = document.createElement('div');
            popover.dataset.customerId = button.dataset.id;
            popover.className =
                'fixed z-[9999] w-64 max-w-[calc(100vw-2rem)] ' +
                'rounded-lg border border-slate-200 bg-white p-4 ' +
                'text-sm text-slate-700 shadow-xl';

            const title = document.createElement('p');
            title.className = 'mb-2 font-bold';
            title.textContent = '顧客メモ';

            const content = document.createElement('p');
            content.className = 'max-h-48 overflow-y-auto whitespace-pre-wrap break-words';
            content.textContent = memo;

            popover.append(title, content);
            modal.appendChild(popover);

            memoPopover = popover;

            const rect = button.getBoundingClientRect();
            const width = popover.offsetWidth;
            const height = popover.offsetHeight;

            // 左右が画面からはみ出さないよう調整
            const left = Math.max(
                16,
                Math.min(rect.left, window.innerWidth - width - 16)
            );

            // 上に余裕がなければ下に表示
            const top = rect.top >= height + 12 ?
                rect.top - height - 8 :
                rect.bottom + 8;

            popover.style.left = `${left}px`;
            popover.style.top = `${top}px`;
        };

        // 吹き出し以外をクリックしたら閉じる
        document.addEventListener('click', (event) => {
            if (
                memoPopover &&
                !event.target.closest('.show-customer-memo') &&
                !memoPopover.contains(event.target)
            ) {
                closeMemo();
            }
        });

        // スクロール・サイズ変更時に吹き出しを閉じる
        results.addEventListener('scroll', closeMemo);
        window.addEventListener('resize', closeMemo);

        // モーダルを閉じる
        closeButton.addEventListener('click', () => modal.close());
        modal.addEventListener('close', closeMemo);
    });
</script>
