/**
 * La Crem - Shopping Cart Logic
 * 
 * Includes:
 * - Robust jQuery checks
 * - LocalStorage persistence
 * - Discount code system
 * - Global usage via window.Cart and window.addToCart
 */

(function ($) {
    'use strict';

    // --- CONFIGURATION ---
    const CONFIG = {
        key: "lacrem_cart", // LocalStorage key
        // Add or remove coupons here
        validCoupons: {
            'CREM10': 0.10,   // 10%
            'HELADO20': 0.20, // 20%
            'SABADO15': 0.15  // Example
        }
    };

    // --- CART OBJECT ---
    const Cart = {
        items: [],
        discount: 0,
        discountCode: null,

        init: function () {
            // console.log("Cart initializing...");
            try {
                const stored = localStorage.getItem(CONFIG.key);
                if (stored) {
                    this.items = JSON.parse(stored) || [];
                }
            } catch (e) {
                console.error("Error loading cart:", e);
                this.items = [];
            }
            this.updateBadge();
        },

        save: function () {
            localStorage.setItem(CONFIG.key, JSON.stringify(this.items));
            this.updateBadge();
        },

        add: function (product) {
            const existing = this.items.find(item => item.id == product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({ ...product, quantity: 1 });
            }
            this.save();
            alert("Producto agregado al carrito:\n" + product.name);
        },

        remove: function (index) {
            if (confirm("¿Estás seguro de eliminar este producto?")) {
                this.items.splice(index, 1);
                this.save();
                this.render();
            }
        },

        updateQuantity: function (index, newQty) {
            if (newQty < 1) return;
            this.items[index].quantity = parseInt(newQty);
            this.save();
            this.render();
        },

        clear: function () {
            if (confirm("¿Estás seguro de vaciar todo el carrito?")) {
                this.items = [];
                this.discount = 0;
                this.discountCode = null;
                this.save();
                this.render();
            }
        },

        applyDiscount: function (code) {
            const cleanCode = code.trim().toUpperCase();
            if (CONFIG.validCoupons.hasOwnProperty(cleanCode)) {
                this.discount = CONFIG.validCoupons[cleanCode];
                this.discountCode = cleanCode;
                alert(`¡Código ${cleanCode} aplicado con éxito!`);
                this.render();
            } else {
                alert("Código de descuento no válido o expirado.");
                // Optional: valid logic to reset discount? 
                // this.discount = 0; this.discountCode = null; this.render();
            }
        },

        calculateTotals: function () {
            const subtotal = this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discountAmount = subtotal * this.discount;
            const total = subtotal - discountAmount;
            return { subtotal, discountAmount, total };
        },

        updateBadge: function () {
            const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
            const $badge = $('#cart-badge');
            $badge.text(count);
            const $cartLink = $badge.closest('a');
            if (count > 0) {
                $cartLink.addClass('cart-has-items');
            } else {
                $cartLink.removeClass('cart-has-items');
            }
            // Pulse the badge when items change
            if ($badge.length) {
                $badge.removeClass('cart-badge-pulse');
                void $badge[0].offsetWidth;
                $badge.addClass('cart-badge-pulse');
            }
        },

        render: function () {
            const $table = $('#cart-table-body');
            const $subtotal = $('#subtotal-display');
            const $discount = $('#discount-display');
            const $total = $('#total-display');

            // Only run if we are on the cart page
            if ($table.length === 0) return;

            $table.empty();

            if (this.items.length === 0) {
                $table.html('<tr><td colspan="5" class="text-center py-5">Tu carrito está vacío. <a href="product.html">¡Ve a comprar!</a></td></tr>');
                $subtotal.text('$0');
                $discount.text('$0');
                $total.text('$0');
                return;
            }

            this.items.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                const row = `
                    <tr>
                        <td class="align-middle text-left">
                            <img src="${item.image}" alt="" style="width: 50px;"> 
                            ${item.name}
                            <br><small class="text-muted">${item.description || ''}</small>
                        </td>
                        <td class="align-middle">$${item.price}</td>
                        <td class="align-middle">
                            <div class="input-group quantity mx-auto" style="width: 100px;">
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-primary btn-minus" onclick="Cart.updateQuantity(${index}, ${item.quantity - 1})">
                                    <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                                <input type="text" class="form-control form-control-sm bg-secondary border-0 text-center" value="${item.quantity}" readonly>
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-primary btn-plus" onclick="Cart.updateQuantity(${index}, ${item.quantity + 1})">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">$${itemTotal}</td>
                        <td class="align-middle">
                            <button class="btn btn-sm btn-danger" onclick="Cart.remove(${index})"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $table.append(row);
            });

            // Update Totals
            const totals = this.calculateTotals();
            $subtotal.text('$' + totals.subtotal.toLocaleString());
            $discount.text('-$' + totals.discountAmount.toLocaleString());
            $total.text('$' + totals.total.toLocaleString());
        }
    };

    // --- GLOBAL HELPERS ---

    // Expose Cart globally
    window.Cart = Cart;

    // Global addToCart function for Product Page
    window.addToCart = function (element) {
        // Find product details relative to the clicked button
        // We use jQuery here since it's loaded
        const $btn = $(element);

        /* 
           Logic to find description:
           1. Try finding closest .product-item, then find p tag inside
           2. Fallback if structure is different
        */
        const $productItem = $btn.closest('.product-item');
        let description = $productItem.find('p').text().trim();

        if (!description) {
            // Fallback: maybe it's a sibling? 
            // description = $btn.siblings('p').text().trim(); 
        }

        const product = {
            id: $btn.data('id'),
            name: $btn.data('name'),
            price: $btn.data('price'),
            image: $btn.data('image'),
            description: description
        };

        Cart.add(product);
    };

    // Global function to add to cart and redirect to cart page
    window.addToCartAndRedirect = function (element) {
        const $btn = $(element);
        const $productItem = $btn.closest('.product-item');
        let description = $productItem.find('p').text().trim();

        const product = {
            id: $btn.data('id'),
            name: $btn.data('name'),
            price: $btn.data('price'),
            image: $btn.data('image'),
            description: description
        };

        Cart.add(product);
        // Redirect to cart page
        window.location.href = './cart.html';
    };

    // --- INITIALIZATION ---
    $(document).ready(function () {
        Cart.init();
        Cart.render();

        // Bind Coupon Button
        $('#apply-coupon').click(function (e) {
            e.preventDefault();
            const code = $('#coupon-code').val();
            if (code) Cart.applyDiscount(code);
        });

        // Bind Checkout Form (Simplified)
        $('#checkout-form').submit(function (e) {
            e.preventDefault();
            if (Cart.items.length === 0) {
                alert("No hay productos en el carrito.");
                return;
            }
            // Add checkout logic here...
            alert("¡Pedido recibido! (Simulación)");
            Cart.clear();
        });
    });

})(jQuery);
