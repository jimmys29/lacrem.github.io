// Global Cart Debug Script
(function () {
    console.log("Cart Debug Script Loading...");

    // Ensure jQuery is available
    if (typeof jQuery === 'undefined') {
        console.error("jQuery missing!");
        return;
    }
    var $ = jQuery;

    // Global Cart Object
    window.Cart = {
        key: "lacrem_cart",
        items: [],

        init: function () {
            console.log("Cart.init()");
            try {
                var stored = localStorage.getItem(this.key);
                this.items = stored ? JSON.parse(stored) : [];
            } catch (e) {
                console.error("Cart load error", e);
                this.items = [];
            }
            this.updateBadge();
        },

        add: function (product) {
            console.log("Cart.add", product);
            var existing = this.items.find(i => i.id == product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({ ...product, quantity: 1 });
            }
            this.save();
            alert("Producto agregado: " + product.name);
        },

        save: function () {
            localStorage.setItem(this.key, JSON.stringify(this.items));
            this.updateBadge();
        },

        updateBadge: function () {
            var count = this.items.reduce((s, i) => s + i.quantity, 0);
            $('#cart-badge').text(count);
        },

        render: function () { /* simplified for now */ }
    };

    // Global Add Function
    window.addToCart = function (btn) {
        console.log("addToCart clicked", btn);
        var $btn = $(btn);
        var $parent = $btn.closest('.product-item');
        var description = $parent.find('p').text().trim();

        // Fallback if description is empty or structure is different
        if (!description) {
            console.warn("Description not found via .closest(), trying siblings");
            description = $btn.siblings('p').text().trim();
        }

        var product = {
            id: $btn.data('id'),
            name: $btn.data('name'),
            price: $btn.data('price'),
            image: $btn.data('image'),
            description: description
        };

        window.Cart.add(product);
    };

    // Initialize
    $(document).ready(function () {
        window.Cart.init();
    });

})();
