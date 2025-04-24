function showBrandId(id) {
    document.getElementById('selectedBrandId').textContent = id;
}


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

function filterModels() {
    const searchTerm = document.getElementById('modelSearchInput').value.toLowerCase();
    const modelCards = document.querySelectorAll('#modelsContainer > div');

    modelCards.forEach(card => {
        const nameElement = card.querySelector('h2') || card.querySelector('h3');
        const name = nameElement ? nameElement.textContent.toLowerCase() : '';

        card.style.display = name.includes(searchTerm) ? 'block' : 'none';
    });
}


