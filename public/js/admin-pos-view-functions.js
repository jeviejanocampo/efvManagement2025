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

// document.addEventListener('DOMContentLoaded', function () {
//     initOrderSystem();
// });



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


//GCash Payment Modal
function togglePaymentInput() {
    const method = document.querySelector('input[name="paymentMethod"]:checked').value;
    const cashInputSection = document.getElementById('cashInputSection');
    const gcashModal = document.getElementById('gcashModal');
    const pnbModal = document.getElementById('pnbModal');
    const gcashPaymentInfo = document.getElementById('gcashPaymentInfo');
    const pnbPaymentInfo = document.getElementById('pnbPaymentInfo');

    // Toggle sections
    cashInputSection.style.display = method === 'cash' ? 'block' : 'none';
    gcashModal.style.display = method === 'gcash' ? 'flex' : 'none';
    pnbModal.style.display = method === 'pnb' ? 'flex' : 'none';

    if (method !== 'gcash') gcashPaymentInfo.style.display = 'none';
    if (method !== 'pnb') pnbPaymentInfo.style.display = 'none';
}

let uploadedGCashImageBase64 = null;
let uploadedGCashImageFilename = null;

function saveGCashPayment() {
    const uploadInput = document.getElementById('uploadImage');
    const uploadedImage = uploadInput.files[0];

    if (uploadedImage) {
        const reader = new FileReader();
        reader.onload = function (e) {
            uploadedGCashImageBase64 = e.target.result;
            uploadedGCashImageFilename = uploadedImage.name;

            const gcashPaymentInfo = document.getElementById('gcashPaymentInfo');
            gcashPaymentInfo.innerHTML = `
                <div class="p-4 bg-green-200 rounded-lg mb-4">
                    <p class="text-green-800">GCash payment saved.</p>
                    <button onclick="editGCashPayment()" class="text-blue-600">Edit</button>
                    <button onclick="saveGCashImage()" class="text-green-600 ml-4">Save Image</button> <!-- Save Button -->
                </div>
                <img src="${e.target.result}" alt="Uploaded GCash Screenshot" class="mt-4 w-full h-auto rounded-md">
                <p class="text-sm mt-2 text-gray-600">File: ${uploadedImage.name}</p>
            `;
            gcashPaymentInfo.style.display = 'block';
            closeModal();

            // Show the Save button and the image
            document.getElementById('gcashImage').src = e.target.result;
            document.getElementById('gcashImageFileName').innerText = `File: ${uploadedImage.name}`;
        };
        reader.readAsDataURL(uploadedImage);
    } else {
        alert("Please upload a screenshot of the GCash payment.");
    }
}

function closeModal(modalId = null) {
    if (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    } else {
        // Default behavior: close all known modals
        const gcashModal = document.getElementById('gcashModal');
        const pnbModal = document.getElementById('pnbModal');

        if (gcashModal) gcashModal.style.display = 'none';
        if (pnbModal) pnbModal.style.display = 'none';
    }
}


function editGCashPayment() {
    const gcashModal = document.getElementById('gcashModal');
    const gcashPaymentInfo = document.getElementById('gcashPaymentInfo');
    
    gcashPaymentInfo.style.display = 'none'; // Hide saved info
    gcashModal.style.display = 'flex'; // Show the modal again for editing
}
//End

//PNG Payment Modal
let uploadedPNBImageBase64 = null;
let uploadedPNBImageFilename = null;

function savePNBPayment() {
    const uploadInput = document.getElementById('uploadPNBImage');
    const uploadedImage = uploadInput.files[0];

    if (uploadedImage) {
        const reader = new FileReader();
        reader.onload = function (e) {
            uploadedPNBImageBase64 = e.target.result;
            uploadedPNBImageFilename = uploadedImage.name;

            const pnbPaymentInfo = document.getElementById('pnbPaymentInfo');
            pnbPaymentInfo.innerHTML = `
                <div class="p-4 bg-blue-200 rounded-lg mb-4">
                    <p class="text-blue-800">PNB payment saved.</p>
                    <button onclick="editPNBPayment()" class="text-blue-600">Edit</button>
                    <button onclick="savePNBImage()" class="text-blue-600 ml-4">Save</button> <!-- Save Button -->
                </div>
                <img src="${e.target.result}" alt="Uploaded PNB Screenshot" class="mt-4 w-full h-auto rounded-md">
                <p class="text-sm mt-2 text-gray-600">File: ${uploadedImage.name}</p>
            `;
            pnbPaymentInfo.style.display = 'block';
            closeModal();

            // Show the Save button and the image
            document.getElementById('pnbImage').src = e.target.result;
            document.getElementById('pnbImageFileName').innerText = `File: ${uploadedImage.name}`;
        };
        reader.readAsDataURL(uploadedImage);
    } else {
        alert("Please upload a screenshot of the PNB payment.");
    }
}

function editPNBPayment() {
    const pnbModal = document.getElementById('pnbModal');
    const pnbPaymentInfo = document.getElementById('pnbPaymentInfo');
    
    pnbPaymentInfo.style.display = 'none';
    pnbModal.style.display = 'flex';
}
//End


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

// Helper function to format currency
function formatCurrency(amount) {
    return '₱' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
        fetch(`/admin/get-brand-models/${brandId}`)
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


const POSUtils = (() => {

    function formatCurrency(amount) {
        return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function renderModelCard(data, type, modelId = null) {
        if (type === 'variant') {
            return `
                <div class="bg-white rounded-lg p-4 text-center border flex flex-col h-full">
                    <img src="/product-images/${data.variant_image}" class="h-24 w-24 object-cover mx-auto mb-2">
                    <h3 class="text-sm font-semibold">${data.product_name}</h3>
                    <h3 class="text-sm font-semibold text-green-600 hidden">${data.brand_name}</h3>
                    <p class="text-green-600 font-medium mt-1">${formatCurrency(data.price)}</p>
                    <p class="text-sm hidden">Model ID: ${data.model_id}</p>
                    <p class="text-sm hidden">Part ID: ${data.part_id}</p>
                    <p class="text-sm hidden">Stocks: ${data.stocks_quantity}</p>
                    <p class="text-sm hidden">Variant ID: ${data.variant_id}</p>
                    <button class="add-to-order mt-auto bg-black text-white px-3 py-1 flex items-center justify-center gap-2 pt-2 w-full"
                        data-name="${data.product_name}"
                        data-price="${parseFloat(data.price)}"
                        data-id="${data.variant_id}"
                        data-type="variant"
                        data-model-id="${data.model_id}"
                        data-stocks="${data.stocks_quantity}"
                        data-part-id="${data.part_id}"
                        data-brand-name="${data.brand_name}">
                        <i class="fas fa-plus text-white"></i> 
                    </button>
                </div>
            `;
        } else {
            const stockQuantity = data.products.reduce((sum, p) => sum + parseInt(p.stocks_quantity), 0);
            const partIds = data.products.map(p => p.m_part_id).filter(Boolean).join(', ');
            const brandName = data.products[0]?.brand_name || 'Unknown Brand';
            return `
                <div class="bg-white rounded-lg p-4 text-center border flex flex-col h-full">
                    <img src="/product-images/${data.model_img}" class="h-24 w-24 object-cover mx-auto mb-2">
                    <h2 class="text-sm font-semibold">${data.model_name}</h2>
                    <h3 class="text-sm font-semibold text-green-600 hidden">${brandName}</h3>
                    <p class="text-green-600 font-medium mt-1">${formatCurrency(data.price)}</p>
                    <p class="text-sm hidden">Model ID: ${data.model_id}</p>
                    <p class="text-sm hidden">Part ID: ${partIds}</p>
                    <button class="add-to-order mt-auto bg-black text-white px-3 py-1 flex items-center justify-center gap-2 pt-2 w-full"
                        data-name="${data.model_name}"
                        data-price="${parseFloat(data.price)}"
                        data-id="${data.model_id}"
                        data-type="model"
                        data-stocks="${stockQuantity}"
                        data-part-id="${partIds}"
                        data-brand-name="${brandName}">
                        <i class="fas fa-plus text-white"></i> 
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
        const brandName = button.getAttribute('data-brand-name');
        const idLabel = type === 'variant' ? 'Variant ID' : 'Model ID';
    
        const exists = orderList.querySelector(`[data-order-id="${id}"]`);
        if (exists) return;
    
        const item = document.createElement('li');
            item.className = "bg-gray-200 p-3 rounded-md flex flex-col justify-between";
            item.setAttribute('data-order-id', id);

            item.innerHTML = `
                <div class="mb-2">
                    <p class="font-medium text-lg">${name}</p>
                    <p class="text-sm text-gray-700 italic">Brand: ${brandName}</p> 
                    <p class="text-green-600 text-lg">${formatCurrency(price)}</p>
                    <p class="text-gray-500 text-lg hidden">${idLabel}: ${id}</p>
                    <p class="text-gray-500 text-lg hidden">Part ID: ${partId}</p>
                    ${type === 'variant' ? `<p class="text-gray-500 text-lg hidden">Model ID: ${modelId}</p>` : ''}
                    <p class="text-red-700 font-semibold text-sm stock-info mt-1">
                    Stocks left: ${ (stocks - 1).toLocaleString() }
                    </p>
                </div>

                <div class="flex justify-between items-end mt-2">
                    <!-- Left side: Subtotal -->
                    <p class="text-black font-medium text-sm">
                        Subtotal: <span class="subtotal">${formatCurrency(price)}</span>
                    </p>

                    <div class="flex items-center gap-3">
                        <span class="text-base">Qty:</span>
                        <button class="qty-decrease px-2 py-1 bg-black text-white rounded text-md">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity text-lg font-medium">1</span>
                        <button class="qty-increase px-2 py-1 bg-black text-white rounded text-md">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700 remove-item text-xl">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>


                </div>
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
            const price = parseFloat(li.querySelector('.text-green-600').textContent.replace(/[₱,]/g, ''));
            totalAmount -= price;  // Subtract the price of the removed item
            totalItems -= parseInt(li.querySelector('.quantity').textContent);  // Subtract quantity from total items
            li.remove();
            updateTotal();
        }

        const qtyEl = li.querySelector('.quantity');
        const stockEl = li.querySelector('.stock-info');
        const subtotalEl = li.querySelector('.subtotal');
        const originalStocks = parseInt(li.querySelector('.stock-info').textContent.split(': ')[1]) + parseInt(qtyEl.textContent);
        const price = parseFloat(li.querySelector('.text-green-600').textContent.replace(/[₱,]/g, ''));

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
        subtotalEl.textContent = formatCurrency(subtotal);
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
            const price = parseFloat(item.querySelector('.text-green-600').textContent.replace(/[₱,]/g, ''));
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


function saveGCashImage() {
    if (uploadedGCashImageBase64) {
        if (confirm("Are you sure you want to save this GCash screenshot?")) {
            fetch('/admin-save-gcash-image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    image: uploadedGCashImageBase64,
                    filename: uploadedGCashImageFilename
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("GCash payment saved successfully!");
                } else {
                    alert(data.message); // Display the error message if file already exists
                }
            })
            .catch(() => alert("An error occurred while saving the GCash payment."));
        }
    } else {
        alert("No GCash image to save.");
    }
}

function savePNBImage() {
    if (uploadedPNBImageBase64) {
        if (confirm("Are you sure you want to save this PNB screenshot?")) {
            fetch('/admin-save-pnb-image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    image: uploadedPNBImageBase64,
                    filename: uploadedPNBImageFilename
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("PNB payment saved successfully!");
                } else {
                    alert(data.message); // Display the error message if file already exists
                }
            })
            .catch(() => alert("An error occurred while saving the PNB payment."));
        }
    } else {
        alert("No PNB image to save.");
    }
}

function saveOrder() {
    const items = document.querySelectorAll('#orderList li');
    const orderData = [];
    let customerId = document.getElementById('customerSelect').value;
    const customerName = document.getElementById('chosenCustomer').textContent;
    const total = parseFloat(document.getElementById('totalAmount').textContent.replace('₱', '').replace(/,/g, '')) || 0;
    const cashInput = document.getElementById('cashInput');
    const cash = parseFloat(cashInput.value.replace(/,/g, '')) || 0;
    const customerSelect = document.getElementById('customerSelect');
    const imageInput = document.getElementById('imageInput'); // Assuming you have an input for the image

    function formatCurrency(amount) {
        return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    if (!customerId) {
        customerId = 0;
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
    const changeAmount = parseInt(changeText.replace('₱', '').replace(/,/g, '').trim(), 10) || 0;

    items.forEach(item => {
        const name = item.querySelector('.font-medium').textContent;
        const price = parseFloat(item.querySelector('.text-green-600').textContent.replace('₱', '').replace(/,/g, ''));
        const quantity = parseInt(item.querySelector('.quantity').textContent);
        const subtotal = price * quantity;

        totalAmount += subtotal;
        totalItems += quantity;

        let modelId = null;
        let variantId = null;
        let partId = null;
        let mPartId = null;
        let brandName = "0"; // Default if not found

        item.querySelectorAll('.text-gray-500').forEach(p => {
            const text = p.textContent;
            if (text.includes('Model ID')) modelId = text.split(': ')[1];
            if (text.includes('Variant ID')) variantId = text.split(': ')[1];
            if (text.includes('Part ID')) partId = text.split(': ')[1];
            if (text.includes('M Part ID')) mPartId = text.split(': ')[1];
            if (text.includes('Brand Name')) brandName = text.split(': ')[1];
        });

        if (!mPartId) {
            mPartId = partId;
        }

        let brandPart = brandName.substring(0, 3).toUpperCase();
        let partSuffix = (partId || mPartId || "000000").slice(-6);

        referenceId = brandPart + partSuffix;

        orderData.push({
            model_id: modelId,
            variant_id: variantId || null,
            part_id: partId || null,
            m_part_id: mPartId || null,
            product_name: name,
            price: price,
            quantity: quantity,
            total_price: subtotal
        });
    });

    const selectedPaymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;

    let image = null;
    if (selectedPaymentMethod === 'gcash') {
        image = uploadedGCashImageFilename || null;
    } else if (selectedPaymentMethod === 'pnb') {
        image = uploadedPNBImageFilename || null;
    }
    
    // Prepare the full payload for the backend
    const payload = {
        customerId: customerId,
        referenceId: referenceId,
        totalItems: totalItems,
        totalPrice: totalAmount,
        changeAmount: changeAmount,  // Send the numeric value of the change
        cashReceived: cash, // ✅ add this line to send the cash input
        paymentMethod: selectedPaymentMethod, 
        image: image, // ✅ Add this line
        orderItems: orderData
    };

    // Send data to Laravel backend via fetch
    fetch('/admin-save-order-pos', {
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
            // SweetAlert2 success message
            Swal.fire({
                title: 'Purchase Successful!',
                text: 'The order has been saved successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // Print the fetched order data
                console.log('Order:', data.order);  // This will print the order data in the console

                let isTransferPayment = ['gcash', 'pnb'].includes(data.order.payment_method.toLowerCase());
                let amountLabel = isTransferPayment ? "Amount Transferred" : "Amount Paid";
                let amountValue = isTransferPayment ? data.order.total_price : data.order.cash_received;

                const vatAmountDisplay = (data.order.total_price * 0.12).toFixed(2);
                const vatableSalesDisplay = (data.order.total_price - (data.order.total_price * 0.12)).toFixed(2);            

        
                // Create a custom HTML structure for the order details report
                let orderDetailsHTML = `
                   <div style="text-align: center; margin-top: 20px;">
                        <img src="/product-images/EFVLOGOREPORT.jpg" alt="EFV Logo"
                            style="width: 80px; height: auto; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
                        <h2 class="text-sm font-bold">
                            EFV AUTO PARTS MERCHANDISE<br>
                            Diversion Road, Sibulan, Dumaguete City<br>
                            Negros Oriental
                        </h2>
                        <p class="text-sm mb-4">
                          TEL #035 277808 | CELL # 09267745314 | Email: imh.ksa@gmail.com
                        </p>
                        <br>
                        <h2 class="text-sm font-bold mb-4">Order Summary</h2>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;" class="text-sm">
                        <tr>
                        <td><strong>Reference ID:</strong></td>
                            <td>${data.order.order_reference.reference_id}</td>
                        </tr>
                        
                        <tr>
                            <td><strong>Cash Received:</strong></td>
                            <td>${formatCurrency(data.order.cash_received)}</td>
                        </tr>
                        <tr>
                            <td><strong>Change:</strong></td>
                            <td>${formatCurrency(data.order.customers_change)}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>
                                ${data.order.payment_method} 
                                ${data.order.payment_method.toLowerCase() === 'cash' ? '(WALK-IN)' : '(Online)'}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Processed By:</strong></td>
                            <td>${data.processed_by}</td>
                        </tr>
                        <tr>
                            <td><strong>Order To:</strong></td>
                            <td>${data.customer_full_name}</td>
                        </tr>
                        <tr>
                            <td><strong>Order Status:</strong></td>
                            <td>${data.order.status}</td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border-collapse: collapse;" class="text-sm">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2">Product Name</th>
                                <th class="border px-4 py-2">Brand Name</th>
                                <th class="border px-4 py-2">Qty</th>
                                <th class="border px-4 py-2">Price</th>
                                <th class="border px-4 py-2">SubTotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.order.order_details.map(item => `
                                <tr>
                                    <td class="border px-4 py-2">${item.product_name}</td>
                                    <td class="border px-4 py-2">${item.brand_name}</td>
                                    <td class="border px-4 py-2">${item.quantity}</td>
                                    <td class="border px-4 py-2">₱${item.price}</td>
                                    <td class="border px-4 py-2">₱${item.total_price}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                  <div style="margin-top: 20px; width: 250px; margin-left: auto; font-size: 0.875rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <div><strong>Total Items:</strong></div>
                            <div>${data.order.total_items}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <div><strong>VAT Amount (12%):</strong></div>
                            <div>${formatCurrency(vatAmountDisplay)}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <div><strong>VATable Sales:</strong></div>
                            <div>${formatCurrency(vatableSalesDisplay)}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <div><strong>${amountLabel}:</strong></div>
                            <div>${formatCurrency(amountValue)}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <div><strong>Change:</strong></div>
                            <div>₱${data.order.customers_change}</div>
                        </div>
                         <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <div><strong>TOTAL:</strong></div>
                            <div>${formatCurrency(data.order.total_price)}</div>
                        </div>
                    </div>


                    <hr class="my-4" />

                   <div style="text-align: center; margin-top: 20px;">
                        <p class="font-semibold text-sm">
                            Operated By
                        </p>
                         <p class="font-semibold text-sm">EFV AUTO PARTS MANAGEMENT SYSTEM </p>
                         <p class="font-semibold text-sm"> Thank you for your purchase! </p>
                        </p>
                         <tr>
                            <td><strong>Printed On:</strong></td>
                            <td>${new Date(data.order.created_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            })}</td>
                        </tr>
                    </div>
                `;
        
                // Show the order details in SweetAlert2 modal
                Swal.fire({
                    title: 'EFV Auto Parts Merchandise',
                    html: orderDetailsHTML,
                    icon: 'info',
                    confirmButtonText: 'Done',
                    showCancelButton: true,
                    cancelButtonText: 'Print',
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Optionally, handle confirmation (like redirecting or resetting)
                        location.reload();
                    } else {
                        // Trigger print functionality if 'Print' is clicked
                        const printWindow = window.open('', '', 'height=500,width=800');
                        printWindow.document.write('<html><head><title>EFV Auto Parts Merchandise</title></head><body>');
                        printWindow.document.write(orderDetailsHTML);  // Inject the HTML into the print window
                        printWindow.document.write('</body></html>');
                        printWindow.document.close();
                        printWindow.print();  // Trigger the print dialog
                    }
                });
            });
        } else {
            // SweetAlert2 error message
            Swal.fire({
                title: 'Error!',
                text: 'Failed to save order: ' + data.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
    
    

    
}




