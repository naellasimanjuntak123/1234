const radios = document.querySelectorAll('input[name="payment_method"]');
const payLabels = document.querySelectorAll("#paymentOptions .payment-option-label");
radios.forEach((radio, i) => {
  radio.addEventListener("change", () => {
    payLabels.forEach((l) => l.classList.remove("selected"));
    payLabels[i].classList.add("selected");
  });
});

// Shipping service toggle
const shipRadios = document.querySelectorAll('input[name="shipping_service"]');
const shipLabels = document.querySelectorAll("#shippingOptions .payment-option-label");
const ongkirMap = {
  gojek: 15000,
  grab: 15000,
  jnt: 12000,
  pickup: 0,
};

function formatRp(n) {
  return "Rp " + n.toLocaleString("id-ID");
}

shipRadios.forEach((radio, i) => {
  radio.addEventListener("change", () => {
    shipLabels.forEach((l) => l.classList.remove("selected"));
    shipLabels[i].classList.add("selected");
    const ongkir = ongkirMap[radio.value] ?? 15000;
    document.getElementById("ongkirDisplay").textContent = ongkir === 0 ? "Gratis!" : formatRp(ongkir);
    document.getElementById("grandTotalDisplay").textContent = formatRp(baseTotal + ongkir);
    // Tampilkan maps box kalau pilih Ambil di Toko
    document.getElementById("pickupMapBox").style.display = radio.value === "pickup" ? "block" : "none";
  });
});
