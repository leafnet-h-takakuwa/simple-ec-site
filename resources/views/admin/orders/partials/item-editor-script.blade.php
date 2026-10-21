<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('items-container');
        const template = document.getElementById('item-template');
        const formatter = new Intl.NumberFormat('ja-JP');

        const modal = document.getElementById('product-select-modal');
        const productSearch = document.getElementById('product-search');
        const productResults = document.getElementById('product-results');
        const productRows = [...productResults.querySelectorAll('.product-result')];
        const productResultCount = document.getElementById('product-result-count');
        const closeProductModal = document.getElementById('close-product-modal');

        // 現在、商品選択の対象になっている明細
        let targetItem = null;

        const formatYen = (amount) => {
            return `${amount < 0 ? '-' : ''}¥${formatter.format(Math.abs(amount))}`;
        };

        // 検索用の文字列を正規化
        const normalizeProductName = (value) => {
            return String(value ?? '')
                .normalize('NFKC')
                .toLowerCase()
                .trim();
        };

        // 商品をリアルタイムで絞り込み
        function filterProducts() {
            const keyword = normalizeProductName(productSearch.value);
            let count = 0;

            productRows.forEach(row => {
                const productName = normalizeProductName(row.dataset.name);
                const matches = productName.includes(keyword);

                row.style.display = matches ? '' : 'none';

                if (matches) {
                    count++;
                }
            });

            productResultCount.textContent = `検索結果：${count}件`;
        }

        productSearch.addEventListener('input', filterProducts);

        // 商品連携ボタンの表示を切り替える
        function updateLinkButton(item) {
            const productId = item.querySelector('[data-field="product_id"]').value;
            const button = item.querySelector('.select-product');

            if (productId) {
                button.textContent = '商品連携済';
                button.classList.remove('btn-outline', 'btn-neutral');
                button.classList.add('btn-success');
            } else {
                button.textContent = '商品を選択';
                button.classList.remove('btn-success');
                button.classList.add('btn-outline', 'btn-neutral');
            }
        }

        // 商品マスタとの連携を解除
        function unlinkProduct(item) {
            item.querySelector('[data-field="product_id"]').value = '';
            updateLinkButton(item);
        }

        // 商品連携時の値を記録
        function rememberProductValues(item) {
            item.dataset.linkedName =
                item.querySelector('[data-field="product_name"]').value;

            item.dataset.linkedVolume =
                item.querySelector('[data-field="volume"]').value;

            item.dataset.linkedPrice =
                item.querySelector('[data-field="price"]').value;

            item.dataset.linkedTaxRate =
                item.querySelector('[data-field="tax_rate"]').value;
        }

        function refresh() {
            let total = 0;

            container.querySelectorAll('.order-item').forEach((item, index) => {
                item.querySelector('.item-title').textContent = `商品 ${index + 1}`;

                item.querySelectorAll('[data-field]').forEach(input => {
                    input.name = `items[${index}][${input.dataset.field}]`;
                });

                const price = Number(item.querySelector('.item-price').value) || 0;
                const quantity = Number(item.querySelector('.item-quantity').value) || 0;
                const subtotal = price * quantity;

                item.querySelector('.item-subtotal').textContent = formatYen(subtotal);

                total += subtotal;

                updateLinkButton(item);
            });

            document.getElementById('total-amount').textContent = formatYen(total);
        }

        // 商品追加
        document.getElementById('add-item').addEventListener('click', () => {
            container.appendChild(template.content.cloneNode(true));
            refresh();
        });

        // 商品削除・商品選択
        container.addEventListener('click', event => {
            const removeButton = event.target.closest('.remove-item');

            if (removeButton) {
                if (container.querySelectorAll('.order-item').length <= 1) {
                    alert('注文明細は最低1件必要です。');
                    return;
                }

                removeButton.closest('.order-item').remove();
                refresh();
                return;
            }

            const selectButton = event.target.closest('.select-product');

            if (selectButton) {
                targetItem = selectButton.closest('.order-item');

                // 検索条件をリセット
                productSearch.value = '';
                filterProducts();

                modal.showModal();
                productSearch.focus();
            }
        });

        // 一覧の「選択」ボタンから商品を確定
        productResults.addEventListener('click', event => {
            const button = event.target.closest('.choose-product');

            if (!button || !targetItem) return;

            const product = button.closest('.product-result');

            if (!product) return;

            targetItem.querySelector('[data-field="product_id"]').value =
                product.dataset.id;

            targetItem.querySelector('[data-field="product_name"]').value =
                product.dataset.name;

            targetItem.querySelector('[data-field="volume"]').value =
                product.dataset.volume;

            targetItem.querySelector('[data-field="price"]').value =
                product.dataset.price;

            targetItem.querySelector('[data-field="tax_rate"]').value =
                product.dataset.taxRate;

            // 連携時の商品名・単価・税率を記録
            rememberProductValues(targetItem);

            // 数量は変更しない
            updateLinkButton(targetItem);
            refresh();

            modal.close();
            targetItem = null;
        });

        // キャンセル
        closeProductModal.addEventListener('click', () => {
            modal.close();
        });

        modal.addEventListener('close', () => {
            targetItem = null;
        });

        // 金額は入力中もリアルタイムで更新
        container.addEventListener('input', refresh);

        // 商品名・単価の変更が確定したら連携解除
        container.addEventListener('change', event => {
            const input = event.target;
            const item = input.closest('.order-item');

            if (!item) return;

            const productId = item.querySelector('[data-field="product_id"]').value;
            if (!productId) return;

            if (input.dataset.field === 'product_name') {
                if (input.value !== item.dataset.linkedName) {
                    unlinkProduct(item);
                }
            }

            if (input.dataset.field === 'volume') {
                if (input.value !== item.dataset.linkedVolume) {
                    unlinkProduct(item);
                }
            }

            if (input.dataset.field === 'price') {
                if (Number(input.value) !== Number(item.dataset.linkedPrice)) {
                    unlinkProduct(item);
                }
            }

            if (input.dataset.field === 'tax_rate') {
                if (Number(input.value) !== Number(item.dataset.linkedTaxRate)) {
                    unlinkProduct(item);
                }
            }
        });

        container.querySelectorAll('.order-item').forEach(item => {
            const productId = item.querySelector('[data-field="product_id"]').value;

            if (productId) {
                rememberProductValues(item);
            }
        });

        refresh();
    });
</script>
