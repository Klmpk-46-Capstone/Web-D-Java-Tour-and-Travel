import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { indonesianLocale } from './flatpickr-id';

const formatRupiah = (amount) =>
    'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount || 0);

const formatDate = (date) =>
    date
        ? new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date)
        : 'Pilih tanggal';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('armadaReservationForm');
    const dateInput = document.getElementById('rentalDate');
    const durationInput = document.getElementById('rentalDuration');
    const passengerInput = document.getElementById('passengerCount');
    const optionSelect = document.getElementById('rentalOption');
    const paymentButton = document.getElementById('continueToPayment');

    if (!form || !dateInput || !durationInput || !passengerInput || !optionSelect || !paymentButton) {
        return;
    }

    const dateError = document.getElementById('rentalDateError');
    const optionDescription = document.getElementById('rentalOptionDescription');
    const notice = document.getElementById('armadaFormNotice');

    const summaryOption = document.getElementById('summaryOption');
    const summaryDate = document.getElementById('summaryDate');
    const summaryDuration = document.getElementById('summaryDuration');
    const summaryPax = document.getElementById('summaryPax');
    const summaryRate = document.getElementById('summaryRate');
    const summaryDays = document.getElementById('summaryDays');
    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryTotal = document.getElementById('summaryTotal');
    const summaryDp = document.getElementById('summaryDp');
    const summaryRemaining = document.getElementById('summaryRemaining');

    const decreaseDuration = document.getElementById('decreaseDuration');
    const increaseDuration = document.getElementById('increaseDuration');
    const decreasePax = document.getElementById('decreasePax');
    const increasePax = document.getElementById('increasePax');

    // Elemen Logika Delivery / Penjemputan Mobil
    const deliverySection = document.getElementById('vehicleDeliverySection');
    const pickupAddressSection = document.getElementById('pickupAddressSection');
    const pickupDetailsSection = document.getElementById('pickupDetailsSection');
    const pickupLocationLabel = document.getElementById('pickupLocationLabel');
    const pickupLocation = document.getElementById('pickup_location');
    const pickupDetails = document.getElementById('pickup_details');
    const deliverySelfPickup = document.getElementById('deliverySelfPickup');
    const deliveryToAddress = document.getElementById('deliveryToAddress');

    const seats = Math.max(1, Number.parseInt(form.dataset.seats, 10) || 1);
    const maxDuration = Math.max(1, Number.parseInt(form.dataset.maxDuration, 10) || 30);
    const downPaymentRate = 30;
    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    const state = {
        date: null,
        duration: 1,
        pax: 1,
        rate: 0,
        label: '',
        description: '',
        key: '',
    };

    state.duration = clamp(
        Number.parseInt(durationInput.value, 10) || 1,
        1,
        maxDuration
    );
    state.pax = clamp(
        Number.parseInt(passengerInput.value, 10) || 1,
        1,
        seats
    );

    const showDateError = () => {
        dateError?.classList.remove('hidden');
    };

    const hideDateError = () => {
        dateError?.classList.add('hidden');
    };

    const hideNotice = () => {
        notice?.classList.add('hidden');
    };

    const showNotice = () => {
        notice?.classList.remove('hidden');
        notice?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const getTotal = () => state.rate * state.duration;
    const getDownPayment = () => Math.round((getTotal() * downPaymentRate) / 100);

    const updateDeliveryFields = () => {
        if (!optionSelect || !pickupLocation) {
            return;
        }

        const selectedOption = optionSelect.selectedOptions[0];
        const isSelfDrive = selectedOption?.dataset.rentalType === 'self-drive';
        const isDelivery = deliveryToAddress?.checked === true;

        // Opsi penerimaan mobil hanya berlaku untuk sewa tanpa supir.
        deliverySection?.classList.toggle('hidden', !isSelfDrive);

        // Dengan supir: tampilkan titik awal penjemputan.
        // Tanpa supir: tampilkan alamat hanya jika mobil diantar.
        const showAddress = !isSelfDrive || isDelivery;

        pickupAddressSection?.classList.toggle('hidden', !showAddress);
        pickupLocation.required = showAddress;

        pickupDetailsSection?.classList.toggle('hidden', !showAddress);

        if (pickupDetails) {
            pickupDetails.required = false;
        }

        if (pickupLocationLabel) {
            pickupLocationLabel.textContent = isSelfDrive
                ? 'Alamat Pengantaran Mobil'
                : 'Titik Awal Penjemputan';
        }

        // Placeholder alamat disesuaikan dengan opsi sewa.
        pickupLocation.placeholder = isSelfDrive
            ? 'Contoh: Jl. Melati No. 10, Tangerang, Banten'
            : 'Contoh: Lobi hotel, Bandara Juanda (SUB), atau alamat penjemputan';

        // Placeholder detail jam disesuaikan dengan opsi sewa.
        if (pickupDetails) {
            pickupDetails.placeholder = isSelfDrive
                ? 'Contoh: Mobil diantar pukul 08.00 WIB. Mohon tiba 15 menit sebelum waktu yang ditentukan.'
                : 'Contoh: Penjemputan pukul 08.00 WIB di lobi hotel.';

            if (!showAddress) {
                pickupDetails.value = '';
            }
        }

        if (!showAddress) {
            pickupLocation.value = '';
        }
    };

    const syncDurationControls = () => {
        if (durationInput.value !== String(state.duration)) {
            durationInput.value = String(state.duration);
        }

        if (decreaseDuration) {
            decreaseDuration.disabled = state.duration <= 1;
        }

        if (increaseDuration) {
            increaseDuration.disabled = state.duration >= maxDuration;
        }
    };

    const syncPaxControls = () => {
        if (passengerInput.value !== String(state.pax)) {
            passengerInput.value = String(state.pax);
        }

        if (decreasePax) {
            decreasePax.disabled = state.pax <= 1;
        }

        if (increasePax) {
            increasePax.disabled = state.pax >= seats;
        }
    };

    const syncSummary = () => {
        const total = getTotal();
        const downPayment = getDownPayment();

        if (summaryOption) {
            summaryOption.textContent = state.label || 'Opsi sewa';
        }

        if (summaryDate) {
            summaryDate.textContent = formatDate(state.date);
        }

        if (summaryDuration) {
            summaryDuration.textContent = String(state.duration);
        }

        if (summaryPax) {
            summaryPax.textContent = String(state.pax);
        }

        if (summaryRate) {
            summaryRate.textContent = formatRupiah(state.rate);
        }

        if (summaryDays) {
            summaryDays.textContent = String(state.duration);
        }

        if (summarySubtotal) {
            summarySubtotal.textContent = formatRupiah(total);
        }

        if (summaryTotal) {
            summaryTotal.textContent = formatRupiah(total);
        }

        if (summaryDp) {
            summaryDp.textContent = formatRupiah(downPayment);
        }

        if (summaryRemaining) {
            summaryRemaining.textContent = formatRupiah(total - downPayment);
        }
    };

    const syncOption = () => {
        if (optionDescription) {
            optionDescription.textContent = state.description || 'Pilih opsi sewa yang sesuai.';
        }

        syncSummary();
        updateDeliveryFields();
    };

    const setDuration = (value) => {
        const parsed = Number.parseInt(value, 10);
        const safeDuration = Number.isFinite(parsed) ? parsed : 1;

        state.duration = clamp(safeDuration, 1, maxDuration);
        syncDurationControls();
        syncSummary();
        hideNotice();
    };

    const setPax = (value) => {
        const parsed = Number.parseInt(value, 10);
        const safePax = Number.isFinite(parsed) ? parsed : 1;

        state.pax = clamp(safePax, 1, seats);
        syncPaxControls();
        syncSummary();
        hideNotice();
    };

    const syncOptionFromSelect = () => {
        const selected = optionSelect.options[optionSelect.selectedIndex];

        state.rate = Number.parseInt(selected?.dataset.price, 10) || 0;
        state.label = selected?.dataset.label || selected?.textContent.trim() || 'Sewa Harian';
        state.description = selected?.dataset.description || '';
        state.key = selected?.value || '';
    };

    syncOptionFromSelect();

    const picker = flatpickr(dateInput, {
        locale: indonesianLocale,
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'j F Y',
        altInputClass: dateInput.className,
        minDate: 'today',
        defaultDate: dateInput.dataset.defaultDate || null,
        allowInput: false,
        disableMobile: true,
        onChange: (selectedDates) => {
            state.date = selectedDates[0] || null;

            if (state.date) {
                hideDateError();
            }

            syncSummary();
            hideNotice();
        },
    });

    state.date = picker.selectedDates[0] || null;

    const dateLabel = document.querySelector('label[for="rentalDate"]');

    if (picker.altInput) {
        picker.altInput.id = 'rentalDateDisplay';
        picker.altInput.setAttribute('aria-label', 'Tanggal mulai sewa');
        picker.altInput.setAttribute('aria-required', 'true');
        dateLabel?.setAttribute('for', 'rentalDateDisplay');
    }

    dateLabel?.addEventListener('click', (event) => {
        event.preventDefault();
        picker.open();
        (picker.altInput || dateInput).focus();
    });

    optionSelect.addEventListener('change', () => {
        syncOptionFromSelect();
        syncOption();
        hideNotice();
    });

    deliverySelfPickup?.addEventListener('change', updateDeliveryFields);
    deliveryToAddress?.addEventListener('change', updateDeliveryFields);

    decreaseDuration?.addEventListener('click', () => {
        setDuration(state.duration - 1);
    });

    increaseDuration?.addEventListener('click', () => {
        setDuration(state.duration + 1);
    });

    durationInput.addEventListener('change', () => {
        setDuration(durationInput.value);
    });

    durationInput.addEventListener('blur', () => {
        setDuration(durationInput.value);
    });

    decreasePax?.addEventListener('click', () => {
        setPax(state.pax - 1);
    });

    increasePax?.addEventListener('click', () => {
        setPax(state.pax + 1);
    });

    passengerInput.addEventListener('change', () => {
        setPax(passengerInput.value);
    });

    passengerInput.addEventListener('blur', () => {
        setPax(passengerInput.value);
    });

    const validateBookingForm = () => {
        hideDateError();

        if (!state.date) {
            showDateError();

            const focusTarget = picker.altInput || dateInput;
            focusTarget.focus();
            focusTarget.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });

            return false;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        hideNotice();
        return true;
    };

    paymentButton.addEventListener('click', (event) => {
        if (!validateBookingForm()) {
            event.preventDefault();
        }
    });

    form.addEventListener('submit', (event) => {
        if (!validateBookingForm()) {
            event.preventDefault();
        }
    });

    form.addEventListener('input', hideNotice);
    form.addEventListener('change', hideNotice);

    syncDurationControls();
    syncPaxControls();
    syncOption();
    syncSummary();
    updateDeliveryFields();
});
