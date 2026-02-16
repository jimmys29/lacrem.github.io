(function ($) {
    "use strict";

    // Cart Object
    const Cart = {
        key: "lacrem_cart",
        items: [],
        discount: 0,
        discountCode: null,

        init: function () {
            const stored = localStorage.getItem(this.key);
            if (stored) {
                try {
                    this.items = JSON.parse(stored);
                } catch (e) {
                    console.error("Error parsing cart data:", e);
                    this.items = [];
                }
            }
            this.updateBadge();
        },

        save: function () {
            localStorage.setItem(this.key, JSON.stringify(this.items));
            this.updateBadge();
        },

        add: function (product) {
            const existing = this.items.find(item => item.id === product.id);
            if (existing) {
                existing.quantity += 1;
            } else {
                this.items.push({ ...product, quantity: 1 });
            }
            this.save();
            console.log("Product added:", product);
            alert("Producto agregado al carrito!");
        },

        remove: function (index) {
            this.items.splice(index, 1);
            this.save();
            this.render();
        },

        updateQuantity: function (index, quantity) {
            if (quantity < 1) return;
            this.items[index].quantity = parseInt(quantity);
            this.save();
            this.render();
        },

        clear: function () {
            this.items = [];
            this.discount = 0;
            this.discountCode = null;
            this.save();
            this.render();
        },

        calculateTotal: function () {
            const subtotal = this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discountAmount = subtotal * this.discount;
            const total = subtotal - discountAmount;
            return { subtotal, discountAmount, total };
        },

        applyDiscount: function (code) {
            const validCodes = {
                'CREM10': 0.10, // 10%
                'HELADO20': 0.20 // 20%
            };

            if (validCodes[code]) {
                this.discount = validCodes[code];
                this.discountCode = code;
                alert("Código de descuento aplicado!");
                this.render();
                return true;
            } else {
                alert("Código inválido.");
                return false;
            }
        },

        updateBadge: function () {
            const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
            $('#cart-badge').text(count);
        },

        render: function () {
            // Only render if we are on the cart page
            const $cartTable = $('#cart-table-body');
            const $cartSummary = $('#cart-summary');

            if ($cartTable.length === 0) return;

            $cartTable.empty();

            if (this.items.length === 0) {
                $cartTable.html('<tr><td colspan="6" class="text-center">El carrito está vacío.</td></tr>');
            } else {
                this.items.forEach((item, index) => {
                    const total = item.price * item.quantity;
                    const row = `
                        <tr>
                            <td class="align-middle"><img src="${item.image}" alt="" style="width: 50px;"> ${item.name}</td>
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
                            <td class="align-middle">$${total}</td>
                            <td class="align-middle"><button class="btn btn-sm btn-danger" onclick="Cart.remove(${index})"><i class="fa fa-times"></i></button></td>
                        </tr>
                    `;
                    $cartTable.append(row);
                });
            }

            const totals = this.calculateTotal();
            $('#subtotal-display').text('$' + totals.subtotal);
            $('#discount-display').text('-$' + totals.discountAmount);
            $('#total-display').text('$' + totals.total);
        },

        checkout: function (formData) {
            if (this.items.length === 0) {
                alert("El carrito está vacío.");
                return;
            }

            const orderData = {
                customer: Object.fromEntries(formData.entries()),
                items: this.items,
                totals: this.calculateTotal(),
                discountCode: this.discountCode
            };

            // Send to PHP backend
            fetch('mail/order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData),
            })
                .then(response => response.text())
                .then(data => {
                    console.log('Success:', data);
                    alert("Gracias por tu compra! Hemos enviado la confirmación a tu correo.");
                    this.clear();
                    window.location.href = 'index.html';
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("Hubo un error al procesar tu pedido. Por favor intenta de nuevo.");
                });
        }
    };

    // Global Access
    window.Cart = Cart;

    $(document).ready(function () {
        Cart.init();
        Cart.render();

        // Add to Cart Buttons - Delegated Event
        $(document).on('click', '.add-to-cart', function (e) {
            e.preventDefault();
            const product = {
                id: $(this).data('id'),
                name: $(this).data('name'),
                price: $(this).data('price'),
                image: $(this).data('image')
            };
            Cart.add(product);
        });

        // Apply Coupon
        $('#apply-coupon').click(function () {
            const code = $('#coupon-code').val().toUpperCase();
            Cart.applyDiscount(code);
        });

        // Checkout Form Submit
        $('#checkout-form').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            Cart.checkout(formData);
        });
    });

})(jQuery);
