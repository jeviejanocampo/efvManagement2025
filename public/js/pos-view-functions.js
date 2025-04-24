// function showBrandId(id, element) {
//     document.getElementById('selectedBrandId').textContent = id;

//     // Remove highlight from all
//     document.querySelectorAll('.brand-select-box').forEach(el => {
//         el.classList.remove('bg-green-100', 'ring-2', 'ring-green-400');
//     });

//     // Add highlight to selected
//     element.classList.add('bg-green-100', 'ring-2', 'ring-green-400');

//     // Fetch models for this brand
//     fetch(`/staff/get-models-by-brand/${id}`)
//         .then(response => response.json())
//         .then(data => {
//             const container = document.getElementById('modelsContainer');
//             container.innerHTML = ''; // Clear previous results

//             if (data.length === 0) {
//                 container.innerHTML = '<p class="col-span-2 text-gray-500">No models found for this brand.</p>';
//                 return;
//             }

//             const basePath = `${window.location.origin}/product-images/`;

//             data.forEach(model => {
//                 // Calculate total stock quantity
//                 let stockQuantity = 0;
//                 if (model.products && model.products.length > 0) {
//                     stockQuantity = model.products.reduce((total, product) => total + product.stocks_quantity, 0);
//                 }

//                 // Render model card
//                 container.innerHTML += `
//                     <div class="bg-white shadow-md rounded-lg p-4 text-center border">
//                         <img src="${basePath + model.model_img}" class="h-24 w-24 object-cover mx-auto mb-2 rounded-md" alt="${model.model_name}">
//                         <h2 class="text-lg">${model.model_name}</h2>
//                         <p class="text-green-600 font-medium mt-1">₱${parseFloat(model.price).toFixed(2)}</p>
//                         <p class="text-sm">Available Stocks: ${stockQuantity}</p>
//                         <p class="text-sm">Model ID: ${model.model_id}</p>
//                         <p class="text-sm">With Variant: ${model.w_variant}</p>
//                     </div>
//                 `;

//                 // Check if model has variants
//                 if (model.w_variant === 'YES' && model.variants && model.variants.length > 0) {
//                     model.variants.forEach(variant => {
//                         container.innerHTML += `
//                             <div class="bg-gray shadow-md rounded-lg p-4 text-center border">
//                                 <img src="${basePath + variant.variant_image}" class="h-24 w-24 object-cover mx-auto mb-2 rounded-md" alt="${variant.product_name}">
//                                 <h3 class="text-md">${variant.product_name}</h3>
//                                 <p class="text-green-600 font-medium mt-1">₱${parseFloat(variant.price).toFixed(2)}</p>
//                                 <p class="text-sm">Variant ID: ${variant.variant_id}</p>
//                                 <p class="text-sm">Available Stocks: ${variant.stocks_quantity}</p>
//                             </div>
//                         `;
//                     });
//                 }
//             });

//             // Call filter function to apply search immediately
//             filterModels();
//         })
//         .catch(error => {
//             console.error('Error fetching models:', error);
//             document.getElementById('modelsContainer').innerHTML = '<p class="text-red-500">Failed to load models.</p>';
//         });
// }

function showBrandId(id) {
    document.getElementById('selectedBrandId').textContent = id;
}

function filterModels() {
    const searchTerm = document.getElementById('modelSearchInput').value.toLowerCase();
    const modelCards = document.querySelectorAll('#modelsContainer > div');

    modelCards.forEach(card => {
        const nameElement = card.querySelector('h2') || card.querySelector('h3');
        const name = nameElement ? nameElement.textContent.toLowerCase() : '';

        card.style.display = name.includes(searchTerm) ? 'block' : 'none';
    });
}

(function initCustomerSelector() {
    const customerSelect = document.getElementById('customerSelect');
    const chosenCustomer = document.getElementById('chosenCustomer');
    const chosenCustomerId = document.getElementById('chosenCustomerId');

    if (!customerSelect || !chosenCustomer || !chosenCustomerId) return;

    customerSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const customerName = selectedOption.getAttribute('data-name');
        const customerId = selectedOption.value;

        chosenCustomer.textContent = customerName;
        chosenCustomerId.textContent = `ID: ${customerId}`;
    });
})();


function togglePaymentInput() {
    const method = document.querySelector('input[name="paymentMethod"]:checked').value;
    document.getElementById('cashInputSection').style.display = method === 'cash' ? 'block' : 'none';
}

function formatCashInput(input) {
    const rawValue = input.value.replace(/,/g, '');
    if (!isNaN(rawValue)) {
        input.value = Number(rawValue).toLocaleString();
    }
}

function calculateChange() {
    const total = parseFloat(document.getElementById('totalAmount').textContent.replace('₱', '').replace(/,/g, '')) || 0;
    const cashRaw = document.getElementById('cashInput').value.replace(/,/g, '');
    const cash = parseFloat(cashRaw) || 0;
    const change = cash - total;
    document.getElementById('changeAmount').textContent = `₱${change > 0 ? change.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00'}`;
}


(function initBrandSelector() {
    function handleBrandClick(event) {
        const box = event.currentTarget;
        const brandId = box.getAttribute('data-brand-id');

        // Remove highlights
        document.querySelectorAll('.brand-select-box').forEach(el => {
            el.classList.remove('bg-green-100', 'ring-2', 'ring-green-400');
        });

        // Highlight selected
        box.classList.add('bg-green-100', 'ring-2', 'ring-green-400');

        // Fetch models
        fetch(`/staff/get-brand-models/${brandId}`)
            .then(res => res.json())
            .then(models => {
                const container = document.getElementById('modelsContainer');
                container.innerHTML = '';

                models.forEach(model => {
                    if (model.w_variant === 'YES') {
                        model.variants.forEach(variant => {
                            container.innerHTML += POSUtils.renderModelCard(variant, 'variant', model.model_id);
                        });
                    } else {
                        container.innerHTML += POSUtils.renderModelCard(model, 'model');
                    }
                });

                // initOrderSystem(); // rebind any model-related events
            });
    }

    function bindBrandSelectors() {
        const brandBoxes = document.querySelectorAll('.brand-select-box');
        brandBoxes.forEach(box => {
            box.removeEventListener('click', handleBrandClick); // prevent duplicate binding
            box.addEventListener('click', handleBrandClick);
        });
    }

    // Run it once DOM is ready
    document.addEventListener('DOMContentLoaded', bindBrandSelectors);
})();

// document.addEventListener('DOMContentLoaded', function () {
//     initOrderSystem();
// });


const POSUtils = (() => {
    function renderModelCard(data, type, modelId = null) {
        if (type === 'variant') {
            return `
                <div class="bg-white rounded-lg p-4 text-center border flex flex-col h-full">
                    <img src="/product-images/${data.variant_image}" class="h-24 w-24 object-cover mx-auto mb-2">
                    <h3 class="text-sm font-semibold">${data.product_name}</h3>
                    <p class="text-green-600 font-medium mt-1">₱${parseFloat(data.price).toFixed(2)}</p>
                    <p class="text-sm">Model ID: ${data.model_id}</p>
                    <p class="text-sm">Part ID: ${data.part_id}</p>
                    <p class="text-sm">Stocks: ${data.stocks_quantity}</p>
                    <p class="text-sm">Variant ID: ${data.variant_id}</p>
                    <button class="add-to-order mt-auto bg-black text-white px-3 py-1 flex items-center justify-center gap-2 w-full"
                        data-name="${data.product_name}"
                        data-price="${parseFloat(data.price)}"
                        data-id="${data.variant_id}"
                        data-type="variant"
                        data-model-id="${data.model_id}"
                        data-stocks="${data.stocks_quantity}"
                        data-part-id="${data.part_id}">
                        <i class="fas fa-plus text-white"></i> Add
                    </button>
                </div>
            `;
        } else {
            const stockQuantity = data.products.reduce((sum, p) => sum + parseInt(p.stocks_quantity), 0);
            const partIds = data.products.map(p => p.m_part_id).filter(Boolean).join(', ');
            return `
                <div class="bg-white rounded-lg p-4 text-center border flex flex-col h-full">
                    <img src="/product-images/${data.model_img}" class="h-24 w-24 object-cover mx-auto mb-2">
                    <h2 class="text-sm font-semibold">${data.model_name}</h2>
                    <p class="text-green-600 font-medium mt-1">₱${parseFloat(data.price).toFixed(2)}</p>
                    <p class="text-sm">Model ID: ${data.model_id}</p>
                    <p class="text-sm">Part ID: ${partIds}</p>
                    <button class="add-to-order mt-auto bg-black text-white px-3 py-1 flex items-center justify-center gap-2 w-full"
                        data-name="${data.model_name}"
                        data-price="${parseFloat(data.price)}"
                        data-id="${data.model_id}"
                        data-type="model"
                        data-stocks="${stockQuantity}"
                        data-part-id="${partIds}">
                        <i class="fas fa-plus text-white"></i> Add
                    </button>
                </div>
            `;
        }
    }
    
    

    return {
        renderModelCard
    };
})();

(function initOrderSystem() {
    const orderList = document.getElementById('orderList');
    const totalAmountEl = document.getElementById('totalAmount');
    const totalItemsEl = document.getElementById('totalItems');  // Reference to total items element
    let totalAmount = 0;  // Track the total price
    let totalItems = 0;   // Track the total number of items

    document.addEventListener('click', (e) => {
        const button = e.target.closest('.add-to-order');
        if (!button) return;
    
        const name = button.getAttribute('data-name');
        const price = parseFloat(button.getAttribute('data-price'));
        const id = button.getAttribute('data-id');
        const type = button.getAttribute('data-type');
        const modelId = button.getAttribute('data-model-id');
        const partId = button.getAttribute('data-part-id');
        const stocks = parseInt(button.getAttribute('data-stocks'));
        const idLabel = type === 'variant' ? 'Variant ID' : 'Model ID';
    
        const exists = orderList.querySelector(`[data-order-id="${id}"]`);
        if (exists) return;
    
        const item = document.createElement('li');
        item.className = "flex justify-between items-center bg-gray-50 p-2 rounded-md";
        item.setAttribute('data-order-id', id);
    
        item.innerHTML = `
            <div>
                <p class="font-medium text-sm">${name}</p>
                <p class="text-green-600 text-sm">₱${price.toFixed(2)}</p>
                <p class="text-gray-500 text-sm">${idLabel}: ${id}</p>
                <p class="text-gray-500 text-sm">Part ID: ${partId}</p>
                ${type === 'variant' ? `<p class="text-gray-500 text-sm">Model ID: ${modelId}</p>` : ''}
                <p class="text-sm flex items-center gap-2 mt-1">
                    Quantity:
                    <button class="qty-decrease px-2 bg-black text-white rounded">−</button>
                    <span class="quantity">1</span>
                    <button class="qty-increase px-2 bg-black text-white rounded">+</button>
                </p>
                <p class="text-red-700 font-semibold text-sm stock-info">Stocks left: ${stocks - 1}</p>
                <p class="text-black font-medium text-1xl pt-4">
                    Subtotal: <span class="subtotal">${formatCurrency(price)}</span>
                </p>
            </div>
            <button class="text-red-500 hover:text-red-700 remove-item">
                <i class="fas fa-trash"></i>
            </button>
        `;
    
        orderList.appendChild(item);
        totalAmount += price;
        totalItems += 1;  // Add to total items on new item
        updateTotal();
    });
    
    orderList.addEventListener('click', (e) => {
        const li = e.target.closest('li');
        if (!li) return;

        if (e.target.closest('.remove-item')) {
            const price = parseFloat(li.querySelector('.text-green-600').textContent.replace('₱', ''));
            totalAmount -= price;  // Subtract the price of the removed item
            totalItems -= parseInt(li.querySelector('.quantity').textContent);  // Subtract quantity from total items
            li.remove();
            updateTotal();
        }

        const qtyEl = li.querySelector('.quantity');
        const stockEl = li.querySelector('.stock-info');
        const subtotalEl = li.querySelector('.subtotal');
        const originalStocks = parseInt(li.querySelector('.stock-info').textContent.split(': ')[1]) + parseInt(qtyEl.textContent);
        const price = parseFloat(li.querySelector('.text-green-600').textContent.replace('₱', ''));

        let qty = parseInt(qtyEl.textContent);

        if (e.target.closest('.qty-increase')) {
            if (qty < originalStocks) qty++;
        }

        if (e.target.closest('.qty-decrease')) {
            if (qty > 1) qty--;
        }

        qtyEl.textContent = qty;
        stockEl.textContent = `Stocks left: ${originalStocks - qty}`;

        // Update subtotal and total
        const subtotal = price * qty;
        subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
        totalAmount = calculateTotal(); // Recalculate the total when quantity changes
        totalItems = calculateTotalItems();  // Update the total items
        updateTotal();
    });

    // Function to update the total display
    function updateTotal() {
        totalAmountEl.textContent = formatCurrency(totalAmount);
        totalItemsEl.textContent = totalItems;  // Update total items display
    }

    // Function to calculate total dynamically
    function calculateTotal() {
        let total = 0;
        const items = orderList.querySelectorAll('li');
        items.forEach(item => {
            const price = parseFloat(item.querySelector('.text-green-600').textContent.replace('₱', ''));
            const qty = parseInt(item.querySelector('.quantity').textContent);
            total += price * qty;
        });
        return total;
    }

    // Function to calculate total items dynamically
    function calculateTotalItems() {
        let total = 0;
        const items = orderList.querySelectorAll('li');
        items.forEach(item => {
            const qty = parseInt(item.querySelector('.quantity').textContent);
            total += qty;
        });
        return total;
    }

    // Format numbers as currency with commas
    function formatCurrency(amount) {
        return '₱' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

    }
})();



function saveOrder() {
    const items = document.querySelectorAll('#orderList li');
    const orderData = [];
    const customerId = document.getElementById('customerSelect').value;
    const customerName = document.getElementById('chosenCustomer').textContent;
    const total = parseFloat(document.getElementById('totalAmount').textContent.replace('₱', '').replace(/,/g, '')) || 0;
    const cashInput = document.getElementById('cashInput');
    const cash = parseFloat(cashInput.value.replace(/,/g, '')) || 0;
    const customerSelect = document.getElementById('customerSelect');

    if (!customerSelect.value) {
        alert('Please select a customer before saving the order.');
        customerSelect.focus();
        return;
    }

    // ✅ Cash vs Total validation
    if (document.querySelector('input[name="paymentMethod"]:checked').value === 'cash' && cash < total) {
        alert('Cash received is less than the total amount. Please enter the correct amount.');
        cashInput.focus();
        return;
    }

    let referenceId = ""; // This will hold the reference_id.
    let totalAmount = 0;   // To track total price
    let totalItems = 0;    // To track total items

    // Get the change amount, extract the numeric value
    const changeText = document.getElementById('changeAmount').textContent.trim();
    // Remove the ₱ symbol and any spaces, then convert to integer
    const changeAmount = parseInt(changeText.replace('₱', '').replace(/,/g, '').trim(), 10) || 0;

    items.forEach(item => {
        const name = item.querySelector('.font-medium').textContent;
        const price = parseFloat(item.querySelector('.text-green-600').textContent.replace('₱', '').replace(/,/g, ''));
        const quantity = parseInt(item.querySelector('.quantity').textContent);
        const subtotal = price * quantity;

        // Update total values
        totalAmount += subtotal;
        totalItems += quantity;

        let modelId = null;
        let variantId = null;
        let partId = null;
        let mPartId = null;

        item.querySelectorAll('.text-gray-500').forEach(p => {
            const text = p.textContent;
            if (text.includes('Model ID')) modelId = text.split(': ')[1];
            if (text.includes('Variant ID')) variantId = text.split(': ')[1];
            if (text.includes('Part ID')) partId = text.split(': ')[1];
            if (text.includes('M Part ID')) mPartId = text.split(': ')[1];
        });

        // Ensure m_part_id is always set, either from itself or fallback to part_id
        if (!mPartId) {
            mPartId = partId;  // If m_part_id is null, use part_id
        }

        // Generate reference_id using last 6 characters of part_id or m_part_id
        let referenceSuffix = "";
        if (variantId) {
            referenceSuffix = partId ? partId.slice(-6) : "000000"; // Take last 6 chars of partId if variantId exists
        } else {
            referenceSuffix = mPartId ? mPartId.slice(-6) : "000000"; // Last 6 chars of m_part_id
        }

        referenceSuffix = referenceSuffix.padStart(12, "0");

        referenceId = referenceSuffix + "-OR000";  // Only one dash before OR000

        // Prepare the order data for backend insertion
        orderData.push({
            model_id: modelId,
            variant_id: variantId || null,
            part_id: partId || null, // Ensure part_id is either partId or null
            m_part_id: mPartId || null, // Ensure m_part_id is set (fallback to part_id if null)
            product_name: name,
            price: price,
            quantity: quantity,
            total_price: subtotal
        });
    });

    // Prepare the full payload for the backend
    const payload = {
        customerId: customerId,
        referenceId: referenceId,
        totalItems: totalItems,
        totalPrice: totalAmount,
        changeAmount: changeAmount,  // Send the numeric value of the change
        cashReceived: cash, // ✅ add this line to send the cash input
        orderItems: orderData
    };

    // Send data to Laravel backend via fetch
    fetch('/save-order-pos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order saved successfully!');
            // Optionally reset the form or reload
            location.reload();
        } else {
            alert('Failed to save order: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error sending order: ' + error.message);
    });
}



// Helper function to format currency
function formatCurrency(amount) {
    return '₱' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}


