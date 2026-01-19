const SITE_URL = 'http://localhost/project';

function addToCart(productId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', SITE_URL + '/api/cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('✅ Added to cart!');
            } else {
                alert(response.message || '❌ Please login first');
                if (!response.success && response.message.includes('login')) {
                    window.location.href = SITE_URL + '/?page=login';
                }
            }
        } catch (e) {
            alert('❌ Error adding to cart');
        }
    };
    
    xhr.onerror = function() {
        alert('❌ Network error');
    };
    
    xhr.send('action=add&product_id=' + productId + '&quantity=1');
}

function removeFromCart(cartId) {
    if (!confirm('Remove this item from cart?')) return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', SITE_URL + '/api/cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        location.reload();
    };
    
    xhr.onerror = function() {
        alert('❌ Network error');
    };
    
    xhr.send('action=remove&cart_id=' + cartId);
}

function buyNow(productId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', SITE_URL + '/api/cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                window.location.href = SITE_URL + '/?page=checkout';
            } else {
                alert(response.message || 'Please login');
                window.location.href = SITE_URL + '/?page=login';
            }
        } catch (e) {
            alert('Error processing request');
        }
    };
    
    xhr.send('action=add&product_id=' + productId + '&quantity=1');
}