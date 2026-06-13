 function changeQty(btn, delta, maxStock) {
        const input = btn.parentElement.querySelector('.qty-input');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > maxStock) val = maxStock;
        input.value = val;
        // Auto submit after short delay
        clearTimeout(input._timer);
        input._timer = setTimeout(() => input.form.submit(), 600);
    }