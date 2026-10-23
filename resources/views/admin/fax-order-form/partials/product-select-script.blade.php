{{-- edit.blade.php の DOMContentLoaded 内から読み込む。rows / render / markDirty / products を利用 --}}
            const productModal = document.getElementById('product-select-modal');
            const productSearch = document.getElementById('product-search');
            const productResults = document.getElementById('product-results');
            const productRows = [...productResults.querySelectorAll('.product-result')];
            const productResultCount = document.getElementById('product-result-count');
            const closeProductModal = document.getElementById('close-product-modal');
            let editingProductIndex = null;

            const normalizeProductName = value => String(value ?? '')
                .normalize('NFKC')
                .toLowerCase()
                .trim();

            function filterProducts() {
                const keyword = normalizeProductName(productSearch.value);
                let count = 0;
                productRows.forEach(row => {
                    const matches = normalizeProductName(row.dataset.name).includes(keyword);
                    row.style.display = matches ? '' : 'none';
                    if (matches) count++;
                });
                productResultCount.textContent = `検索結果：${count}件`;
            }

            productSearch.addEventListener('input', filterProducts);

            function openProductModal(index) {
                editingProductIndex = index;
                productSearch.value = '';
                filterProducts();
                productModal.showModal();
                // 見出しに autofocus を設定しているため、検索欄へ focus() しない。
            }

            productResults.addEventListener('click', event => {
                const button = event.target.closest('.choose-product');
                if (!button || editingProductIndex === null) return;

                const selected = button.closest('.product-result');
                if (!selected) return;
                const product = products.find(item => String(item.id) === selected.dataset.id);
                if (!product) return;

                rows[editingProductIndex].product_id = product.id;
                rows[editingProductIndex].display_name = product.name;
                editingProductIndex = null;
                productModal.close();
                markDirty();
                render();
                // 選択後もスマホのキーボードを自動表示しない。
            });

            closeProductModal.addEventListener('click', () => productModal.close());
            productModal.addEventListener('close', () => {
                editingProductIndex = null;
            });
